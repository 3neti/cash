<?php

namespace LBHurtado\Cash\Contracts;

interface CashClaimantAuthorizationContract
{
    public function authorize(
        WithdrawableInstrumentContract $instrument,
        ClaimantIdentityContract $claimant
    ): void;
}