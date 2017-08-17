<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use mysqli;
use Smarty;
use App\DataContainer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('mysql', function () {
            return new mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'), env('DB_DATABASE'));
        });

        if (! env('use_blade')) {
            $this->app->singleton('smarty', function () {
                $smarty = new Smarty();
                $smarty->template_dir = tmpl_path();
                $smarty->compile_dir = storage_path('tmpl_c');
                $smarty->compile_check = true;
                $smarty->force_compile = true;
                $smarty->debugging = env('smarty_debug');

                return $smarty;
            });
        }

        $this->app->singleton('data', function () {
            return new DataContainer();
        });
        $this->app->singleton('view_data', function () {
            return new DataContainer();
        });
    }
}
