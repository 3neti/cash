<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Exceptions;

use RuntimeException;

class WithdrawalApprovalRequired extends RuntimeException
{
    public static function forThreshold(float $amount, float $threshold): self
    {
        return new self("Withdrawal approval is required for amounts above {$threshold}.");
    }
}