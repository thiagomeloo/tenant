<?php

declare(strict_types=1);

namespace Thiagomeloo\Tenant;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Thiagomeloo\Tenant\Commands\SetupTenant;
use Thiagomeloo\Tenant\Middlewares\TenantMiddleware;

final class TenantServiceProvider extends ServiceProvider
{
    public function register()
    {
        Config::set('database.connections.tenant', []);
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../routes/tenants/web.php' => App::basePath('routes/tenants/web.php'),
        ], 'tenant-routes');

        $this->publishes([
            __DIR__.'/../config/tenant-config.php' => App::basePath('config/tenant-config.php'),
        ], 'tenant-config');

        $this->commands([
            SetupTenant::class,
        ]);

        $routeTenantPath = App::basePath('routes/tenants');

        if (file_exists($routeTenantPath)) {
            $this->app->call(function () {
                Route::middleware(['web', TenantMiddleware::class])
                    ->domain('{tenant}.'.'localhost')
                    ->group(App::basePath('routes/tenants/web.php'));
            });
        }
    }
}
