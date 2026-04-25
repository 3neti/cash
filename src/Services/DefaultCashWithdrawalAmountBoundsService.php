<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use InvalidArgumentException;
use LBHurtado\Cash\Contracts\CashWithdrawalAmountBoundsContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;

class DefaultCashWithdrawalAmountBoundsService implements CashWithdrawalAmountBoundsContract
{
    public function assertWithinBounds(
        WithdrawableInstrumentContract $instrument,
        mixed $amount,
        ?float $minimumAmount = null,
    ): void {
        if (! $this->isOpenSlice($instrument)) {
            return;
        }

        if ($amount === null || $amount === '') {
            throw new InvalidArgumentException('Withdrawal amount is required for open-slice vouchers.');
        }

        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Withdrawal amount must be numeric.');
        }

        $amount = (float) $amount;

        if ($amount <= 0) {
            throw new InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        if ($minimumAmount !== null && $amount < $minimumAmount) {
            throw new InvalidArgumentException("Withdrawal amount must be at least {$minimumAmount}.");
        }

        $remainingBalance = $instrument->getRemainingBalance();

        if ($remainingBalance !== null && $amount > $remainingBalance) {
            throw new InvalidArgumentException('Withdrawal amount exceeds remaining voucher balance.');
        }
    }

    protected function isOpenSlice(WithdrawableInstrumentContract $instrument): bool
    {
        return $instrument->isDivisible()
            && $instrument->getSliceMode() === 'open';
    }
}