<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\WithdrawalIntervalEnforcerContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;

class NullWithdrawalIntervalEnforcer implements WithdrawalIntervalEnforcerContract
{
    public function enforce(WithdrawableInstrumentContract $instrument, array $payload): void
    {
        // no-op
    }
}