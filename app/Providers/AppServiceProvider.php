<?php

namespace App\Providers;

use mysqli;
use Smarty;
use App\DataContainer;
use Illuminate\Support\ServiceProvider;

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
                $smarty->left_delimiter = '{%';
                $smarty->right_delimiter = '%}';
                if (is_production()) {
                    $smarty->compile_check = false;
                    $smarty->force_compile = false;
                } else {
                    $smarty->compile_check = true;
                    $smarty->force_compile = true;
                    $smarty->debugging = env('smarty_debug');
                }

                $smarty->registerPlugin('block', 'blade', 'smarty_blade_block');

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
