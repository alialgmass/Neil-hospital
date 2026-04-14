<?php

namespace Modules\Doctor\Providers;

use Illuminate\Support\ServiceProvider;

class DoctorServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');
    }
}
