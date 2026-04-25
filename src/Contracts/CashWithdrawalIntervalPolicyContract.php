<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

interface CashWithdrawalIntervalPolicyContract
{
    public function assertAllowed(
        WithdrawableInstrumentContract $instrument,
        ?\DateTimeInterface $lastWithdrawalAt = null,
        ?int $minimumIntervalSeconds = null,
    ): void;
}