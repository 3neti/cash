<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

interface CashWithdrawalEligibilityContract
{
    public function assertEligible(WithdrawableInstrumentContract $instrument): void;
}