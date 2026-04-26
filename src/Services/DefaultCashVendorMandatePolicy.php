<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\CashVendorMandatePolicyContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Data\CashVendorMandateData;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;

class DefaultCashVendorMandatePolicy implements CashVendorMandatePolicyContract
{
    public function findMatchingMandate(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): ?CashVendorMandateData {
        $mandates = data_get($context->payload, 'cash.mandates', []);

        foreach ($mandates as $mandate) {
            $data = new CashVendorMandateData(
                alias: $mandate['alias'] ?? null,
                vendorId: $mandate['vendor_id'] ?? $mandate['vendorId'] ?? null,
                maxAmount: isset($mandate['max_amount'])
                    ? (float) $mandate['max_amount']
                    : null,
                requiresApproval: (bool) ($mandate['requires_approval'] ?? false),
                meta: $mandate['meta'] ?? [],
            );

            if ($data->matches(
                vendorAlias: $context->vendorAlias,
                vendorId: $context->vendorId,
            )) {
                return $data;
            }
        }

        return null;
    }

    public function authorize(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): bool {
        $mandate = $this->findMatchingMandate($instrument, $context);

        if ($mandate === null) {
            return false;
        }

        if ($mandate->exceeds($context->amount)) {
            throw WithdrawalApprovalRequired::forThreshold(
                amount: $context->amount,
                threshold: (float) $mandate->maxAmount,
            );
        }

        if ($mandate->requiresApproval && ! $context->approved) {
            throw new WithdrawalApprovalRequired(
                'Withdrawal approval is required for this vendor mandate.'
            );
        }

        return true;
    }
}