<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Data;

use Spatie\LaravelData\Data;

class WithdrawalAuthorizationDecisionData extends Data
{
    public function __construct(
        public bool $allowed,
        public string $status = 'allowed',
        public ?string $reason = null,
        public array $requirements = [],
        public array $meta = [],
    ) {}

    public static function allowed(array $meta = []): self
    {
        return new self(
            allowed: true,
            status: 'allowed',
            meta: $meta,
        );
    }

    public static function approvalRequired(string $reason, array $requirements = [], array $meta = []): self
    {
        return new self(
            allowed: false,
            status: 'approval_required',
            reason: $reason,
            requirements: $requirements,
            meta: $meta,
        );
    }
}