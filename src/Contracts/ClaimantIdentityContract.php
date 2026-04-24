<?php

namespace LBHurtado\Cash\Contracts;

interface ClaimantIdentityContract
{
    public function getClaimantId(): string|int|null;

    public function getClaimantMobile(): ?string;
}