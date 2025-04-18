<?php

namespace Mubeen\LaravelUserCrud;

use Illuminate\Support\ServiceProvider;
use Mubeen\LaravelUserCrud\Console\InstallCommand;

class LaravelUserCrudServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the config file
        $this->mergeConfigFrom(
            __DIR__ . '/config/laravel-user-crud.php', 'laravel-user-crud'
        );
        
        // Register commands
        $this->commands([
            InstallCommand::class,
        ]);
    }

    public function boot()
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        
        // Load routes based on interface type
        $interfaceType = config('laravel-user-crud.interface_type', 'web');
        
        if ($interfaceType === 'web') {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        } else {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        }
        
        // Load views if it's a web interface
        if ($interfaceType === 'web') {
            $this->loadViewsFrom(__DIR__ . '/resources/views', 'laravel-user-crud');
        }
        
        // Publish assets
        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations'),
            __DIR__ . '/config/laravel-user-crud.php' => config_path('laravel-user-crud.php'),
        ], 'laravel-user-crud');
        
        // Publish views only for web interface
        if ($interfaceType === 'web') {
            $this->publishes([
                __DIR__ . '/resources/views/' => resource_path('views/vendor/laravel-user-crud'),
            ], 'laravel-user-crud-views');
        }
    }
} 