<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

interface CashWithdrawalAmountResolverContract
{
    public function resolve(WithdrawableInstrumentContract $instrument, ?float $amount): float;
}