<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;

interface CashWithdrawalAuthorizationPolicyContract
{
    public function authorize(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): void;
}