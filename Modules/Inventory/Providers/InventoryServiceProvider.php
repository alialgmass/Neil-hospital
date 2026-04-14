<?php

namespace Modules\Inventory\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Inventory\Repositories\Contracts\InventoryRepositoryInterface;
use Modules\Inventory\Repositories\Contracts\SupplierRepositoryInterface;
use Modules\Inventory\Repositories\InventoryRepository;
use Modules\Inventory\Repositories\SupplierRepository;

class InventoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryRepositoryInterface::class, InventoryRepository::class);
        $this->app->bind(SupplierRepositoryInterface::class, SupplierRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }
}
