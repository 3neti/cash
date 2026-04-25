<?php

declare(strict_types=1);

use LBHurtado\Cash\Services\DefaultCashWithdrawalEligibilityService;

function withdrawalEligibilityService(): DefaultCashWithdrawalEligibilityService
{
    return new DefaultCashWithdrawalEligibilityService();
}

use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;

function fakeEligibilityWithdrawableInstrument(array $overrides = []): WithdrawableInstrumentContract
{
    return new class($overrides) implements WithdrawableInstrumentContract {
        public function __construct(private array $overrides = []) {}

        public function isWithdrawable(): bool
        {
            return $this->overrides['isWithdrawable'] ?? true;
        }

        public function isDivisible(): bool
        {
            return $this->overrides['isDivisible'] ?? true;
        }

        public function getSliceMode(): ?string
        {
            return $this->overrides['sliceMode'] ?? 'open';
        }

        public function getSliceAmount(): ?float
        {
            return $this->overrides['sliceAmount'] ?? null;
        }

        public function getRemainingBalance(): float
        {
            return $this->overrides['remainingBalance'] ?? 100.0;
        }

        public function getMinWithdrawal(): ?float
        {
            return $this->overrides['minWithdrawal'] ?? 10.0;
        }

        public function getMaxSlices(): ?int
        {
            return array_key_exists('maxSlices', $this->overrides)
                ? $this->overrides['maxSlices']
                : 3;
        }

        public function getConsumedSlices(): int
        {
            return $this->overrides['consumedSlices'] ?? 0;
        }

        public function isExpired(): bool
        {
            return $this->overrides['isExpired'] ?? false;
        }

        public function getInstrumentState(): string
        {
            return $this->overrides['state'] ?? 'active';
        }

        public function getInstrumentId(): string|int|null
        {
            return $this->overrides['id'] ?? 1;
        }

        public function getOriginalClaimantId(): string|int|null
        {
            return $this->overrides['originalClaimantId'] ?? null;
        }
    };
}

it('passes when instrument is withdrawable', function () {
    withdrawalEligibilityService()->assertEligible(
        fakeEligibilityWithdrawableInstrument([
            'isWithdrawable' => true,
            'isExpired' => false,
            'state' => 'active',
            'isDivisible' => true,
            'maxSlices' => 3,
            'consumedSlices' => 0,
        ]),
    );

    expect(true)->toBeTrue();
});

it('fails when instrument is not withdrawable', function () {
    withdrawalEligibilityService()->assertEligible(
        fakeEligibilityWithdrawableInstrument([
            'isWithdrawable' => false,
        ]),
    );
})->throws(RuntimeException::class, 'This voucher is not withdrawable.');