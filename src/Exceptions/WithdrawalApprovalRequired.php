<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Exceptions;

use LBHurtado\Cash\Data\WithdrawalAuthorizationDecisionData;
use RuntimeException;
use Throwable;

class WithdrawalApprovalRequired extends RuntimeException
{
    public static function forThreshold(float $amount, float $threshold): self
    {
        return new self("Withdrawal approval is required for amounts above {$threshold}.");
    }

    public function __construct(
        string $message = 'Withdrawal approval is required.',
        protected array $requirements = ['approval'],
        protected array $meta = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function requirements(): array
    {
        return $this->requirements;
    }

    public function meta(): array
    {
        return $this->meta;
    }

    public static function fromDecision(WithdrawalAuthorizationDecisionData $decision): self
    {
        return new self(
            message: $decision->reason ?? 'Withdrawal approval is required.',
            requirements: $decision->requirements,
            meta: $decision->meta,
        );
    }
}