<?php

namespace Agp\Report;

use Agp\Report\Console\Commands\MakeCommand;
use Illuminate\Support\ServiceProvider;

class AgpReportServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeCommand::class,
            ]);
        }
//        $this->loadRoutesFrom(__DIR__ . '/Routes/web.php');
    }

    public function register()
    {
        $this->loadViewsFrom(__DIR__ . '/Views', 'Report');
    }
}
