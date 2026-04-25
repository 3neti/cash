<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

interface CashWithdrawalAmountBoundsContract
{
    public function assertWithinBounds(
        WithdrawableInstrumentContract $instrument,
        mixed $amount,
        ?float $minimumAmount = null,
    ): void;
}