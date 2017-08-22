<?php

namespace entimm\LaravelPayeer;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class PayeerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Config
        $this->publishes([
            __DIR__ . '/../config/payeer.php' => config_path('payeer.php'),
        ], 'config');

        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'payeer');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/payeer'),
        ], 'views');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/payeer.php', 'payeer');

        $this->app->singleton('payeer', function() {
            return new Payeer();
        });
    }
}
