<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use InvalidArgumentException;
use LBHurtado\Cash\Contracts\CashWithdrawalAmountResolverContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;

class DefaultCashWithdrawalAmountResolverService implements CashWithdrawalAmountResolverContract
{
    public function resolve(WithdrawableInstrumentContract $instrument, ?float $amount): float
    {
        $sliceMode = $instrument->getSliceMode();

        if ($sliceMode === 'fixed') {
            $sliceAmount = $instrument->getSliceAmount();

            if ($sliceAmount === null) {
                throw new InvalidArgumentException('Fixed-slice voucher is missing slice amount.');
            }

            return $sliceAmount;
        }

        if ($sliceMode === 'open') {
            if ($amount === null) {
                throw new InvalidArgumentException('Withdrawal amount is required.');
            }

            $minWithdrawal = $instrument->getMinWithdrawal();

            if ($minWithdrawal !== null && $amount < $minWithdrawal) {
                throw new InvalidArgumentException('Withdrawal amount is below the minimum withdrawal amount.');
            }

            if ($amount > $instrument->getRemainingBalance()) {
                throw new InvalidArgumentException('Withdrawal amount exceeds remaining balance.');
            }

            return $amount;
        }

        return $amount ?? $instrument->getRemainingBalance();
    }
}