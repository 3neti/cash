<?php

declare(strict_types=1);

use LBHurtado\Cash\Contracts\ClaimantIdentityContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Services\DefaultCashClaimantAuthorizationService;

function fakeClaimantIdentity(array $overrides = []): ClaimantIdentityContract
{
    return new class($overrides) implements ClaimantIdentityContract {
        public function __construct(private array $overrides = []) {}

        public function getClaimantId(): string|int|null
        {
            return array_key_exists('claimantId', $this->overrides)
                ? $this->overrides['claimantId']
                : 1;
        }

        public function getClaimantMobile(): ?string
        {
            return array_key_exists('mobile', $this->overrides)
                ? $this->overrides['mobile']
                : '639171234567';
        }
    };
}

function fakeAuthorizedWithdrawableInstrument(array $overrides = []): WithdrawableInstrumentContract
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
            return array_key_exists('sliceMode', $this->overrides)
                ? $this->overrides['sliceMode']
                : 'open';
        }

        public function getSliceAmount(): ?float
        {
            return array_key_exists('sliceAmount', $this->overrides)
                ? $this->overrides['sliceAmount']
                : null;
        }

        public function getRemainingBalance(): float
        {
            return $this->overrides['remainingBalance'] ?? 100.0;
        }

        public function getMinWithdrawal(): ?float
        {
            return array_key_exists('minWithdrawal', $this->overrides)
                ? $this->overrides['minWithdrawal']
                : 10.0;
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
            return array_key_exists('id', $this->overrides)
                ? $this->overrides['id']
                : 1;
        }

        public function getOriginalClaimantId(): string|int|null
        {
            return array_key_exists('originalClaimantId', $this->overrides)
                ? $this->overrides['originalClaimantId']
                : null;
        }
    };
}

function claimantAuthorizationService(): DefaultCashClaimantAuthorizationService
{
    return new DefaultCashClaimantAuthorizationService();
}

it('passes when no original claimant is known', function () {
    claimantAuthorizationService()->authorize(
        fakeAuthorizedWithdrawableInstrument([
            'originalClaimantId' => null,
        ]),
        fakeClaimantIdentity([
            'claimantId' => 123,
        ]),
    );

    expect(true)->toBeTrue();
});

it('passes when claimant id matches original claimant id', function () {
    claimantAuthorizationService()->authorize(
        fakeAuthorizedWithdrawableInstrument([
            'originalClaimantId' => 123,
        ]),
        fakeClaimantIdentity([
            'claimantId' => 123,
        ]),
    );

    expect(true)->toBeTrue();
});

it('fails when claimant id differs from original claimant id', function () {
    claimantAuthorizationService()->authorize(
        fakeAuthorizedWithdrawableInstrument([
            'originalClaimantId' => 123,
        ]),
        fakeClaimantIdentity([
            'claimantId' => 456,
        ]),
    );
})->throws(RuntimeException::class, 'Only the original redeemer may withdraw this voucher.');