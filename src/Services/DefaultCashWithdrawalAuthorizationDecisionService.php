<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\CashVendorMandatePolicyContract;
use LBHurtado\Cash\Contracts\CashWithdrawalAuthorizationDecisionContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Data\WithdrawalAuthorizationDecisionData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;

class DefaultCashWithdrawalAuthorizationDecisionService implements CashWithdrawalAuthorizationDecisionContract
{
    public function __construct(
        protected ?CashVendorMandatePolicyContract $vendorMandates = null,
    ) {
        $this->vendorMandates ??= new DefaultCashVendorMandatePolicy;
    }

    public function decide(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): WithdrawalAuthorizationDecisionData {
        try {
            if ($this->vendorMandates->authorize($instrument, $context)) {
                return WithdrawalAuthorizationDecisionData::allowed([
                    'source' => 'vendor_mandate',
                ]);
            }
        } catch (WithdrawalApprovalRequired $e) {
            return WithdrawalAuthorizationDecisionData::approvalRequired(
                reason: $e->getMessage(),
                requirements: ['approval'],
                meta: [
                    'source' => 'vendor_mandate',
                ],
            );
        }

        if ($context->approvalThreshold === null) {
            return WithdrawalAuthorizationDecisionData::allowed([
                'source' => 'default',
            ]);
        }

        if ($context->amount <= $context->approvalThreshold) {
            return WithdrawalAuthorizationDecisionData::allowed([
                'source' => 'threshold',
            ]);
        }

        if ($context->approved) {
            return WithdrawalAuthorizationDecisionData::allowed([
                'source' => 'preapproved',
            ]);
        }

        return WithdrawalAuthorizationDecisionData::approvalRequired(
            reason: "Withdrawal approval is required for amounts above {$context->approvalThreshold}.",
            requirements: ['approval'],
            meta: [
                'source' => 'threshold',
                'threshold' => $context->approvalThreshold,
                'amount' => $context->amount,
            ],
        );
    }
}