<?php

namespace Modules\Insurance\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Insurance\Repositories\Contracts\InsuranceRepositoryInterface;
use Modules\Insurance\Repositories\InsuranceRepository;

class InsuranceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InsuranceRepositoryInterface::class, InsuranceRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }
}
