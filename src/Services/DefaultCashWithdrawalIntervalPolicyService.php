<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use DateTimeInterface;
use RuntimeException;
use LBHurtado\Cash\Contracts\CashWithdrawalIntervalPolicyContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;

class DefaultCashWithdrawalIntervalPolicyService implements CashWithdrawalIntervalPolicyContract
{
    public function assertAllowed(
        WithdrawableInstrumentContract $instrument,
        ?DateTimeInterface $lastWithdrawalAt = null,
        ?int $minimumIntervalSeconds = null,
    ): void {
        if (! $instrument->isDivisible() || $instrument->getSliceMode() !== 'open') {
            return;
        }

        if ($lastWithdrawalAt === null || $minimumIntervalSeconds === null || $minimumIntervalSeconds <= 0) {
            return;
        }

        $nextAllowedAt = $lastWithdrawalAt->getTimestamp() + $minimumIntervalSeconds;

        if (time() < $nextAllowedAt) {
            throw new RuntimeException('Withdrawal interval has not yet elapsed.');
        }
    }
}