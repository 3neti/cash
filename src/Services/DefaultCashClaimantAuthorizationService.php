<?php

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\CashClaimantAuthorizationContract;
use LBHurtado\Cash\Contracts\ClaimantIdentityContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use RuntimeException;

class DefaultCashClaimantAuthorizationService implements CashClaimantAuthorizationContract
{
    public function authorize(
        WithdrawableInstrumentContract $instrument,
        ClaimantIdentityContract $claimant
    ): void {

        $ownerId = $instrument->getOriginalClaimantId();

        if ($ownerId === null) {
            return;
        }

        if ((string) $ownerId !== (string) $claimant->getClaimantId()) {
            throw new RuntimeException('Only the original redeemer may withdraw this voucher.');
        }
    }
}