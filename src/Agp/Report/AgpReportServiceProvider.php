<?php

namespace Agp\Report;

use Illuminate\Support\ServiceProvider;

class AgpReportServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'Report');
    }
}
