<?php

namespace Modules\Doctor\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Doctor\Repositories\Contracts\DoctorRepositoryInterface;
use Modules\Doctor\Repositories\DoctorRepository;

class DoctorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DoctorRepositoryInterface::class, DoctorRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
    }
}
