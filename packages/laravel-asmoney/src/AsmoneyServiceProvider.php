<?php

namespace entimm\LaravelAsmoney;

use Illuminate\Support\ServiceProvider;

class AsmoneyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Config
        $this->publishes([
            __DIR__.'/../config/asmoney.php' => config_path('asmoney.php'),
        ], 'config');

        // Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'asmoney');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/asmoney'),
        ], 'views');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/asmoney.php', 'asmoney');

        $this->app->singleton('asmoney', function () {
            return new Asmoney();
        });
    }
}
