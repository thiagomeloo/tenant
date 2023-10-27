<?php

declare(strict_types=1);

namespace Thiagomeloo\Tenant\Middlewares;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Thiagomeloo\Tenant\Models\Domain;

final class TenantMiddleware
{
    public function handle($request, Closure $next)
    {
        //get host
        $host = $request->getHost();

        //get domain
        $domain = Domain::where('domain', $host)->first();

        //if domain not found or domain not have tenant
        if (!$domain || !$domain->tenant) {
            throw new HttpException(404);
        }

        //get tenant
        $tenant = $domain->tenant;

        //get default connection
        $defaultConnectionName  = Config::get('database.default');
        $defaultConnectionArray = Config::get('database.connections.' . $defaultConnectionName);

        //setup tenant connection
        Artisan::call('tenant:setup', [
            'tenant' => $tenant->id,
        ]);

        //get tenant connection driver
        $driver = $defaultConnectionArray['driver'];

        //get tenant database name
        $database = $tenant->id;

        //insert .sqlite extension if driver is sqlite
        if ($driver == 'sqlite') {
            $database = App::databasePath($tenant->id . '.sqlite');
        }

        //alter config default connection with tenant connection
        $configTenant = array_merge(
            Config::get('database.connections.' . $defaultConnectionName),
            [
                'database' => $database,
            ]
        );

        //set tenant connection
        Config::set('database.connections.' . $defaultConnectionName, $configTenant);
        DB::reconnect($defaultConnectionName);

        return $next($request);

        //reset default connection
        Config::set('database.connections.' . $defaultConnectionName, $defaultConnectionArray);
        DB::reconnect($defaultConnectionName);
    }
}
