<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Data;

use Spatie\LaravelData\Data;

class WithdrawalAuthorizationContextData extends Data
{
    public function __construct(
        public float $amount,
        public array $payload = [],
        public ?string $claimantId = null,
        public ?string $vendorId = null,
        public ?float $approvalThreshold = null,
        public bool $approved = false,
    ) {}
}