<?php

namespace Thiagomeloo\Tenant\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Thiagomeloo\Tenant\Events\TenantDatabaseCreated;
use Thiagomeloo\Tenant\Events\TenantSetupExecuted;
use Thiagomeloo\Tenant\Models\Tenant;

class SetupTenant extends Command
{
  protected $signature = 'tenant:setup {tenant}';
  protected $description = 'Setup tenant';

  public function handle()
  {
    $tenant = Tenant::find($this->argument('tenant'));

    if (!$tenant) {
      $this->error("Tenant not found!");
      return;
    }

    $this->info("Setup tenant {$tenant->name}");

    $defaultConnection = Config::get('database.default');

    //check if database exists
    $result = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $tenant->id . "'");

    if (!count($result)) {
      Event::dispatch(new TenantDatabaseCreated($tenant));
      DB::statement("CREATE DATABASE IF NOT EXISTS `" . $tenant->id . "`;");
    }


    $configTenant = array_merge(
      Config::get('database.connections.' . $defaultConnection),
      [
        'database' => $tenant->id,
      ]
    );

    Config::set('database.connections.tenant', $configTenant);

    $this->call('migrate', [
      '--database' => 'tenant',
      '--path' => 'database/migrations/tenant/app',
    ]);

    $this->info("Tenant {$tenant->name} is ready!");

    Event::dispatch(new TenantSetupExecuted($tenant));
  }
}
