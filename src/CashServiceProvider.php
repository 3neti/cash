<?php

namespace LBHurtado\Cash;

use Illuminate\Support\ServiceProvider;
use LBHurtado\Cash\Contracts\CashClaimantAuthorizationContract;
use LBHurtado\Cash\Contracts\CashWithdrawalAmountResolverContract;
use LBHurtado\Cash\Contracts\CashWithdrawalEligibilityContract;
use LBHurtado\Cash\Contracts\CashWithdrawalValidationContract;
use LBHurtado\Cash\Contracts\WithdrawalIntervalEnforcerContract;
use LBHurtado\Cash\Services\DefaultCashClaimantAuthorizationService;
use LBHurtado\Cash\Services\DefaultCashWithdrawalAmountResolverService;
use LBHurtado\Cash\Services\DefaultCashWithdrawalEligibilityService;
use LBHurtado\Cash\Services\DefaultCashWithdrawalValidationService;
use LBHurtado\Cash\Services\NullWithdrawalIntervalEnforcer;

class CashServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CashWithdrawalValidationContract::class,
            DefaultCashWithdrawalValidationService::class
        );

        $this->app->bind(
            CashWithdrawalAmountResolverContract::class,
            DefaultCashWithdrawalAmountResolverService::class
        );

        $this->app->bind(
            WithdrawalIntervalEnforcerContract::class,
            NullWithdrawalIntervalEnforcer::class
        );

        $this->app->bind(
            CashClaimantAuthorizationContract::class,
            DefaultCashClaimantAuthorizationService::class,
        );

        $this->app->bind(
            CashWithdrawalEligibilityContract::class,
            DefaultCashWithdrawalEligibilityService::class,
        );

        $this->mergeConfigFrom(
            __DIR__.'/../config/cash.php',
            'cash'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/cash.php' => config_path('cash.php'),
        ], 'config');
    }
}
