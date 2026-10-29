<?php

declare(strict_types=1);

namespace Larasell\Extension\Tests;

use Illuminate\Foundation\Application;
use Inertia\ServiceProvider as InertiaServiceProvider;
use Larasell\Admin\AdminServiceProvider;
use Larasell\Extension\ExtensionServiceProvider;
use Larasell\Larasell\LarasellServiceProvider;
use Laravel\Head\HeadServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('migrate', ['--database' => 'testing'])->run();
    }

    /**
     * @param  Application  $app
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            HeadServiceProvider::class,
            AdminServiceProvider::class,
            InertiaServiceProvider::class,
            LarasellServiceProvider::class,
            LaravelDataServiceProvider::class,
            ExtensionServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        config([
            'cache.default' => 'array',
            'database.default' => 'testing',
            'database.connections.testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                // Like production databases, so deletes cascade and null out as the migrations define.
                'foreign_key_constraints' => true,
            ],
        ]);
    }
}
