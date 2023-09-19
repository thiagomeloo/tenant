# Tenant Package

### Install

- install package
- run migrations for creating tables **tenants** and **domains**
- create folder migrations tenant in **database/migrations/tenant/app**

### Example usage

```php
<?php

  //create tenant and domain
  $tenant = new Thiagomeloo\Tenant\Models\Tenant();
  $tenant->save();
  $tenant->domains()->create(['domain' => 'test.localhost']);

  //Create route example in routes/tenants/web.php
  use Illuminate\Support\Facades\Route;

  Route::get('example', function () {
      dd("Ok"); 
  });

  //open test.localhost:8000/example 
  // output: Ok

```

### Publish

- Publish config

  ```
     php artisan vendor:publish --tag=tenant-config --force
  ```

- publish route file

  ```
    php artisan vendor:publish --tag=tenant-routes --force 
  ```

### Events

- Create new Tenant Model dispatch **Thiagomeloo\Tenant\Events\TenantCreating**
- Create database Tenant dispatch **Thiagomeloo\Tenant\Events\TenantDatabaseCreated**
- Tenant setup and try running new migrations **Thiagomeloo\Tenant\EventsTenantSetupExecuted**
