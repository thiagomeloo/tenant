<?php

declare(strict_types=1);

namespace Thiagomeloo\Tenant\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Thiagomeloo\Tenant\Events\TenantDatabaseCreated;
use Thiagomeloo\Tenant\Events\TenantSetupExecuted;
use Thiagomeloo\Tenant\Models\Tenant;

final class SetupTenant extends Command
{
    protected $signature = 'tenant:setup {tenant}';

    protected $description = 'Setup tenant';

    public function handle(): void
    {
        $tenant = Tenant::find($this->argument('tenant'));

        if (!$tenant) {
            $this->error('Tenant not found!');

            return;
        }

        $this->info("Setup tenant {$tenant->name}");

        $defaultConnection = Config::get('database.default');

        $dbType = Config::get('database.default');

        switch ($dbType) {
            case 'mysql':
                $result = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $tenant->id . "'");

                break;
            case 'pgsql':
                $result = DB::select("SELECT datname FROM pg_database WHERE datname = '" . $tenant->id . "'");

                break;
            case 'sqlite':
                $result = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='" . $tenant->id . "';");

                break;
            default:
                $result = [];

                break;
        }

        if (!count($result)) {

            Event::dispatch(new TenantDatabaseCreated($tenant));

            switch ($dbType) {
                case 'mysql':
                case 'pgsql':
                    DB::statement('CREATE DATABASE IF NOT EXISTS `' . $tenant->id . '`;');

                    break;
                case 'sqlite':
                    $path = App::databasePath($tenant->id . '.sqlite');

                    if (!file_exists($path)) {
                        touch($path);
                    }

                    break;
                default:
                    break;
            }
        }

        $database = $tenant->id;

        if ($dbType == 'sqlite') {
            $database = App::databasePath($tenant->id . '.sqlite');
        }

        $configTenant = array_merge(
            Config::get('database.connections.' . $defaultConnection),
            [
                'database' => $database,
            ]
        );

        Config::set('database.connections.tenant', $configTenant);

        $this->call('migrate', [
            '--database' => 'tenant',
            '--path'     => 'database/migrations/tenant/app',
        ]);

        $this->info("Tenant {$tenant->name} is ready!");

        Event::dispatch(new TenantSetupExecuted($tenant));
    }
}
