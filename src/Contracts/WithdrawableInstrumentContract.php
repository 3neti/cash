<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Contracts;

interface WithdrawableInstrumentContract

{
    public function isWithdrawable(): bool;

    public function isDivisible(): bool;

    public function getSliceMode(): ?string;

    public function getSliceAmount(): ?float;

    public function getRemainingBalance(): float;

    public function getMinWithdrawal(): ?float;

    public function getMaxSlices(): ?int;

    public function getConsumedSlices(): int;

    public function isExpired(): bool;

    public function getInstrumentState(): string;

    public function getInstrumentId(): string|int|null;

    public function getOriginalClaimantId(): string|int|null;
}