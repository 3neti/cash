<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

use LBHurtado\Cash\Data\CashVendorMandateData;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;

interface CashVendorMandatePolicyContract
{
    public function findMatchingMandate(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): ?CashVendorMandateData;

    public function authorize(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): bool;
}