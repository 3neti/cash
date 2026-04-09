<?php

namespace LBHurtado\Cash\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LBHurtado\Cash\Tests\Models\User;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'LBHurtado\\Cash\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );

        $this->loadConfig();
        $this->loginTestUser();
    }

    protected function getPackageProviders($app): array
    {
        return [
            \LBHurtado\Cash\CashServiceProvider::class,
            \LBHurtado\Wallet\WalletServiceProvider::class,
            \Bavix\Wallet\WalletServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('data.validation_strategy', 'always');
        $app['config']->set('data.max_transformation_depth', 6);
        $app['config']->set('data.throw_when_max_transformation_depth_reached', 6);
        $app['config']->set('data.normalizers', [
            \Spatie\LaravelData\Normalizers\ModelNormalizer::class,
            \Spatie\LaravelData\Normalizers\ArrayableNormalizer::class,
            \Spatie\LaravelData\Normalizers\ObjectNormalizer::class,
            \Spatie\LaravelData\Normalizers\ArrayNormalizer::class,
            \Spatie\LaravelData\Normalizers\JsonNormalizer::class,
        ]);

        $app['config']->set('model-status.status_model', \Spatie\ModelStatus\Status::class);
        $app['config']->set('auth.defaults.guard', 'web');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }

    protected function loginTestUser(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
            ]
        );

        $this->actingAs($user, 'web');
    }

    protected function loadConfig(): void
    {
        $this->app['config']->set(
            'cash',
            require __DIR__.'/../config/cash.php'
        );
    }
}