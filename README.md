# Tenant Package

## Install

- Install package

    ```bash
        composer require thiagomeloo/tenant
    ```

- Run migrations for creating tables **tenants** and **domains**

    ```bash
        php artisan migrate
    ```

- Create folder migrations tenant in **database/migrations/tenant/app**

    ```bash
        mkdir -p ./database/migrations/tenant/app
    ```

- Publish

  - Config

    ```bash
        php artisan vendor:publish --tag=tenant-config --force
    ```

  - Route File

    ```bash
        php artisan vendor:publish --tag=tenant-routes --force 
    ```

## Usage

- ### Create tenant and domain

    ```php
    <?php

    #create tenant and domain
    $tenant = new Thiagomeloo\Tenant\Models\Tenant();
    $tenant->save();
    $tenant->domains()->create(['domain' => 'test.localhost']);
    ```

- ### Create migration for tenant

    ```php
    #database/migrations/tenant/app
    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        /**
         * Run the migrations.
         */
        public function up(): void
        {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('products');
        }
    };

    ```

- ### Create Model for Tenant

    ```php
        # app/Models/Product.php
        <?php

        namespace App\Models;

        use Illuminate\Database\Eloquent\Factories\HasFactory;
        use Illuminate\Database\Eloquent\Model;

        class Product extends Model
        {

            use HasFactory;

            protected $connection = 'tenant';
        }

    
    ```

- ### Create Route for Tenant

    ```php
        #routes/tenants/web.php
        
        use Illuminate\Support\Facades\Route;

        Route::get('example', function () {
            dd("Ok"); 
        }); #output: ok

        Route::get('save-product-example', function(){
            Product::create(['name' => 'Product 1']);
            dd("Ok");
        }); #output: ok
    ```

## Events

|        **Event**        | **Namespace**                                       |
|:-----------------------:|-----------------------------------------------------|
| Create new Tenant Model | **Thiagomeloo\Tenant\Events\TenantCreating**        |
| Create database Tenant  | **Thiagomeloo\Tenant\Events\TenantDatabaseCreated** |
| Setup Tenant            | **Thiagomeloo\Tenant\EventsTenantSetupExecuted**    |
