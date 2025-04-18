<?php

namespace Mubeen\LaravelUserCrud\Tests;

use Mubeen\LaravelUserCrud\LaravelUserCrudServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelUserCrudServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Configure testing database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up migrations path
        $app['config']->set('auth.providers.users.model', \Mubeen\LaravelUserCrud\Models\User::class);
    }
} 