<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\CashVendorMandatePolicyContract;
use LBHurtado\Cash\Contracts\CashWithdrawalAuthorizationPolicyContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;

class DefaultCashWithdrawalAuthorizationPolicyService implements CashWithdrawalAuthorizationPolicyContract
{
    public function __construct(
        protected ?CashVendorMandatePolicyContract $vendorMandates = null,
    ) {
        $this->vendorMandates ??= new DefaultCashVendorMandatePolicy;
    }

    public function authorize(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): void {
        if ($this->vendorMandates->authorize($instrument, $context)) {
            return;
        }

        if ($context->approvalThreshold === null) {
            return;
        }

        if ($context->amount <= $context->approvalThreshold) {
            return;
        }

        if ($context->approved) {
            return;
        }

        throw WithdrawalApprovalRequired::forThreshold(
            amount: $context->amount,
            threshold: $context->approvalThreshold,
        );
    }
}