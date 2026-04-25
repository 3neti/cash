<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\CashWithdrawalEligibilityContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use RuntimeException;

class DefaultCashWithdrawalEligibilityService implements CashWithdrawalEligibilityContract
{
    public function assertEligible(WithdrawableInstrumentContract $instrument): void
    {
        if (! $instrument->isWithdrawable()) {
            throw new RuntimeException('This voucher is not withdrawable.');
        }

        if ($instrument->isExpired()) {
            throw new RuntimeException('This voucher has expired.');
        }

        if ($instrument->getInstrumentState() !== 'active') {
            throw new RuntimeException('This voucher is not withdrawable.');
        }

        if (
            $instrument->isDivisible()
            && $instrument->getMaxSlices() !== null
            && $instrument->getConsumedSlices() >= $instrument->getMaxSlices()
        ) {
            throw new RuntimeException('This voucher has no remaining slices.');
        }
    }
}