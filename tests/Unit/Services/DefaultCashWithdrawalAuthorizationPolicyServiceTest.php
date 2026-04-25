<?php

declare(strict_types=1);

use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;
use LBHurtado\Cash\Services\DefaultCashWithdrawalAuthorizationPolicyService;

function fakeWithdrawableInstrumentForAuthorization(): WithdrawableInstrumentContract
{
    return new class implements WithdrawableInstrumentContract {
        public function isWithdrawable(): bool { return true; }
        public function isExpired(): bool { return false; }
        public function getInstrumentState(): string { return 'active'; }
        public function isDivisible(): bool { return true; }
        public function getSliceMode(): ?string { return 'open'; }
        public function getRemainingBalance(): float { return 1000.00; }
        public function getRemainingSlices(): ?int { return 3; }
        public function getMaxSlices(): ?int { return 3; }
        public function getConsumedSlices(): int { return 0; }
        public function getSliceAmount(): ?float { return null; }
        public function getMinWithdrawal(): ?float { return null; }
        public function getInstrumentId(): string|int { return 'test-instrument'; }
        public function getOriginalClaimantId(): string|int|null { return null; }
    };
}

it('allows withdrawal when no approval threshold is configured', function () {
    (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(amount: 500.00),
    );

    expect(true)->toBeTrue();
});

it('allows withdrawal below approval threshold', function () {
    (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(
            amount: 500.00,
            approvalThreshold: 1000.00,
        ),
    );

    expect(true)->toBeTrue();
});

it('allows withdrawal above threshold when already approved', function () {
    (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(
            amount: 1500.00,
            approvalThreshold: 1000.00,
            approved: true,
        ),
    );

    expect(true)->toBeTrue();
});

it('requires approval when withdrawal exceeds threshold and is not approved', function () {
    (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(
            amount: 1500.00,
            approvalThreshold: 1000.00,
            approved: false,
        ),
    );
})->throws(WithdrawalApprovalRequired::class, 'Withdrawal approval is required for amounts above 1000.');