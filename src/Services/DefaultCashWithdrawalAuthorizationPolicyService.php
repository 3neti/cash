<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\CashWithdrawalAuthorizationPolicyContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;

class DefaultCashWithdrawalAuthorizationPolicyService implements CashWithdrawalAuthorizationPolicyContract
{
    public function authorize(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): void {
        $mandates = data_get($context->payload, 'cash.mandates', []);

        if ($context->vendorAlias !== null) {
            foreach ($mandates as $mandate) {
                if (($mandate['alias'] ?? null) !== $context->vendorAlias) {
                    continue;
                }

                $maxAmount = isset($mandate['max_amount'])
                    ? (float) $mandate['max_amount']
                    : null;

                if ($maxAmount !== null && $context->amount > $maxAmount) {
                    throw WithdrawalApprovalRequired::forThreshold(
                        amount: $context->amount,
                        threshold: $maxAmount,
                    );
                }

                return;
            }
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