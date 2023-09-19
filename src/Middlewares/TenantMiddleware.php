<?php

namespace Thiagomeloo\Tenant\Middlewares;

use Closure;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Thiagomeloo\Tenant\Models\Domain;

class TenantMiddleware
{
  public function handle($request, Closure $next)
  {
    $host = $request->getHost();

    $domain = Domain::where('domain', $host)->first();

    if (!$domain) {
      throw new HttpException(404);
    }

    $tenant = $domain->tenant;

    if (!$tenant) {
      throw new HttpException(404);
    }

    //get default connection
    $defaultConnection = Config::get('database.default');

    //setup tenant connection
    Artisan::call('tenant:setup', [
      'tenant' => $tenant->id
    ]);

    //alter config
    $configTenant = array_merge(
      Config::get('database.connections.' . $defaultConnection),
      [
        'database' => $tenant->id,
      ]
    );

    Config::set('database.connections.tenant', $configTenant);

    DB::purge('tenant');
    DB::reconnect('tenant');

    return $next($request);

    DB::purge('tenant');
    DB::reconnect($defaultConnection);
  }
}
