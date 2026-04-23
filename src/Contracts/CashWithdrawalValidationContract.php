<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

interface CashWithdrawalValidationContract
{
    public function validate(WithdrawableInstrumentContract $instrument, array $payload): void;
}