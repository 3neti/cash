<?php

declare(strict_types=1);

use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Services\DefaultCashWithdrawalAmountBoundsService;

function fakeWithdrawableInstrumentForAmountBounds(
    bool $divisible = true,
    ?string $sliceMode = 'open',
    float $remainingBalance = 100.00,
    ?float $minWithdrawal = null,
): WithdrawableInstrumentContract {
    return new class($divisible, $sliceMode, $remainingBalance, $minWithdrawal) implements WithdrawableInstrumentContract {
        public function __construct(
            protected bool $divisible,
            protected ?string $sliceMode,
            protected float $remainingBalance,
            protected ?float $minWithdrawal,
        ) {}

        public function isWithdrawable(): bool { return true; }

        public function isExpired(): bool { return false; }

        public function getInstrumentState(): string { return 'active'; }

        public function isDivisible(): bool { return $this->divisible; }

        public function getSliceMode(): ?string { return $this->sliceMode; }

        public function getRemainingBalance(): float { return $this->remainingBalance; }

        public function getRemainingSlices(): ?int { return 1; }

        public function getMaxSlices(): ?int { return 3; }

        public function getConsumedSlices(): int { return 0; }

        public function getSliceAmount(): ?float { return null; }

        public function getMinWithdrawal(): ?float { return $this->minWithdrawal; }

        public function getInstrumentId(): string|int { return 'test-instrument'; }

        public function getOriginalClaimantId(): string|int|null { return null; }
    };
}

it('passes when open-slice amount is within bounds', function () {
    $service = new DefaultCashWithdrawalAmountBoundsService;

    $service->assertWithinBounds(
        fakeWithdrawableInstrumentForAmountBounds(),
        50.00,
        10.00,
    );

    expect(true)->toBeTrue();
});

it('fails when open-slice amount is missing', function () {
    (new DefaultCashWithdrawalAmountBoundsService)
        ->assertWithinBounds(fakeWithdrawableInstrumentForAmountBounds(), null);
})->throws(InvalidArgumentException::class, 'Withdrawal amount is required for open-slice vouchers.');

it('fails when open-slice amount is non numeric', function () {
    (new DefaultCashWithdrawalAmountBoundsService)
        ->assertWithinBounds(fakeWithdrawableInstrumentForAmountBounds(), 'abc');
})->throws(InvalidArgumentException::class, 'Withdrawal amount must be numeric.');

it('fails when open-slice amount is not greater than zero', function () {
    (new DefaultCashWithdrawalAmountBoundsService)
        ->assertWithinBounds(fakeWithdrawableInstrumentForAmountBounds(), 0);
})->throws(InvalidArgumentException::class, 'Withdrawal amount must be greater than zero.');

it('fails when open-slice amount is below minimum', function () {
    (new DefaultCashWithdrawalAmountBoundsService)
        ->assertWithinBounds(fakeWithdrawableInstrumentForAmountBounds(), 5.00, 10.00);
})->throws(InvalidArgumentException::class, 'Withdrawal amount must be at least 10.');

it('fails when open-slice amount exceeds remaining balance', function () {
    (new DefaultCashWithdrawalAmountBoundsService)
        ->assertWithinBounds(fakeWithdrawableInstrumentForAmountBounds(remainingBalance: 100.00), 150.00);
})->throws(InvalidArgumentException::class, 'Withdrawal amount exceeds remaining voucher balance.');

it('ignores amount bounds when instrument is not open-slice', function () {
    $service = new DefaultCashWithdrawalAmountBoundsService;

    $service->assertWithinBounds(
        fakeWithdrawableInstrumentForAmountBounds(
            divisible: false,
            sliceMode: null,
        ),
        null,
    );

    expect(true)->toBeTrue();
});

it('uses instrument minimum withdrawal when explicit minimum is not provided', function () {
    $instrument = fakeWithdrawableInstrumentForAmountBounds(
        remainingBalance: 100.00,
        minWithdrawal: 25.00,
    );

    (new DefaultCashWithdrawalAmountBoundsService)
        ->assertWithinBounds($instrument, 10.00);
})->throws(InvalidArgumentException::class, 'Withdrawal amount must be at least 25.');