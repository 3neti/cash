<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Data;

use Spatie\LaravelData\Data;

class CashVendorMandateData extends Data
{
    public function __construct(
        public ?string $alias = null,
        public ?string $vendorId = null,
        public ?float $maxAmount = null,
        public bool $requiresApproval = false,
        public array $meta = [],
    ) {}

    public function matches(?string $vendorAlias = null, ?string $vendorId = null): bool
    {
        if ($this->vendorId !== null && $vendorId !== null) {
            return $this->vendorId === $vendorId;
        }

        if ($this->alias !== null && $vendorAlias !== null) {
            return strtoupper(trim($this->alias)) === strtoupper(trim($vendorAlias));
        }

        return false;
    }

    public function exceeds(float $amount): bool
    {
        return $this->maxAmount !== null && $amount > $this->maxAmount;
    }
}