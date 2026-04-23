<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

interface WithdrawalIntervalEnforcerContract
{
    public function enforce(WithdrawableInstrumentContract $instrument, array $payload): void;
}