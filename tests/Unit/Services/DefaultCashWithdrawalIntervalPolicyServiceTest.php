<?php

declare(strict_types=1);

use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Services\DefaultCashWithdrawalIntervalPolicyService;

function fakeWithdrawableInstrumentForIntervalPolicy(
    bool $divisible = true,
    ?string $sliceMode = 'open',
): WithdrawableInstrumentContract {
    return new class($divisible, $sliceMode) implements WithdrawableInstrumentContract {
        public function __construct(
            protected bool $divisible,
            protected ?string $sliceMode,
        ) {}

        public function isWithdrawable(): bool { return true; }
        public function isExpired(): bool { return false; }
        public function getInstrumentState(): string { return 'active'; }
        public function isDivisible(): bool { return $this->divisible; }
        public function getSliceMode(): ?string { return $this->sliceMode; }
        public function getRemainingBalance(): float { return 100.00; }
        public function getRemainingSlices(): ?int { return 1; }
        public function getMaxSlices(): ?int { return 3; }
        public function getConsumedSlices(): int { return 1; }
        public function getSliceAmount(): ?float { return null; }
        public function getMinWithdrawal(): ?float { return null; }
        public function getInstrumentId(): string|int { return 'test-instrument'; }
        public function getOriginalClaimantId(): string|int|null { return null; }
    };
}

it('allows open-slice withdrawal when no previous withdrawal exists', function () {
    (new DefaultCashWithdrawalIntervalPolicyService)->assertAllowed(
        fakeWithdrawableInstrumentForIntervalPolicy(),
        null,
        60,
    );

    expect(true)->toBeTrue();
});

it('allows open-slice withdrawal when interval has elapsed', function () {
    (new DefaultCashWithdrawalIntervalPolicyService)->assertAllowed(
        fakeWithdrawableInstrumentForIntervalPolicy(),
        now()->subSeconds(120),
        60,
    );

    expect(true)->toBeTrue();
});

it('blocks open-slice withdrawal when interval has not elapsed', function () {
    (new DefaultCashWithdrawalIntervalPolicyService)->assertAllowed(
        fakeWithdrawableInstrumentForIntervalPolicy(),
        now()->subSeconds(10),
        60,
    );
})->throws(RuntimeException::class, 'Withdrawal interval has not yet elapsed.');

it('ignores interval for non-open-slice instruments', function () {
    (new DefaultCashWithdrawalIntervalPolicyService)->assertAllowed(
        fakeWithdrawableInstrumentForIntervalPolicy(
            divisible: false,
            sliceMode: null,
        ),
        now(),
        60,
    );

    expect(true)->toBeTrue();
});

it('ignores interval when minimum interval is zero', function () {
    (new DefaultCashWithdrawalIntervalPolicyService)->assertAllowed(
        fakeWithdrawableInstrumentForIntervalPolicy(),
        now(),
        0,
    );

    expect(true)->toBeTrue();
});