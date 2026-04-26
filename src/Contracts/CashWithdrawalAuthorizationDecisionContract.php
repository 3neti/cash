<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Data\WithdrawalAuthorizationDecisionData;

interface CashWithdrawalAuthorizationDecisionContract
{
    public function decide(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): WithdrawalAuthorizationDecisionData;
}