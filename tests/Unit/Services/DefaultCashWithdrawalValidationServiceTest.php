<?php

declare(strict_types=1);

use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;
use LBHurtado\Cash\Services\DefaultCashWithdrawalAuthorizationPolicyService;
use LBHurtado\Cash\Services\DefaultCashWithdrawalValidationService;
use LBHurtado\Cash\Services\NullWithdrawalIntervalEnforcer;

function fakeWithdrawableInstrument(array $overrides = []): WithdrawableInstrumentContract
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
            return $this->overrides['maxSlices'] ?? 3;
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
            return array_key_exists('originalClaimantId', $this->overrides)
                ? $this->overrides['originalClaimantId']
                : null;
        }
    };
}

function withdrawalValidator(): DefaultCashWithdrawalValidationService
{
    return new DefaultCashWithdrawalValidationService(
        new NullWithdrawalIntervalEnforcer(),
    );
}

it('passes validation for a withdrawable open-slice instrument with valid amount', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument(),
        ['amount' => 50],
    );

    expect(true)->toBeTrue();
});

it('fails validation when instrument is not withdrawable', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument([
            'isDivisible' => false,
            'sliceMode' => null,
            'isWithdrawable' => false,
        ]),
        ['amount' => 50],
    );
})->throws(RuntimeException::class, 'This voucher is not withdrawable.');

it('fails validation when open-slice amount exceeds remaining balance', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument(['remainingBalance' => 25.0]),
        ['amount' => 50],
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount exceeds remaining balance.');

it('fails validation when open-slice amount is below minimum withdrawal amount', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument(['minWithdrawal' => 50.0]),
        ['amount' => 25],
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount is below the minimum withdrawal amount.');

it('fails validation when open-slice amount is missing', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument(),
        [],
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount is required.');

it('fails validation when open-slice amount is non-numeric', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument(),
        ['amount' => 'abc'],
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount must be numeric.');

it('fails validation when open-slice amount is not greater than zero', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument(),
        ['amount' => 0],
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount must be greater than zero.');

it('fails validation when open-slice instrument is expired', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument(['isExpired' => true]),
        ['amount' => 50],
    );
})->throws(RuntimeException::class, 'This voucher has expired.');

it('fails validation when open-slice instrument has no remaining slices', function () {
    withdrawalValidator()->validate(
        fakeWithdrawableInstrument([
            'maxSlices' => 3,
            'consumedSlices' => 3,
        ]),
        ['amount' => 50],
    );
})->throws(RuntimeException::class, 'This voucher has no remaining slices.');

it('calls the interval enforcer for valid open-slice instruments', function () {
    $called = false;

    $enforcer = new class($called) implements \LBHurtado\Cash\Contracts\WithdrawalIntervalEnforcerContract {
        public function __construct(private bool &$called) {}

        public function enforce(
            \LBHurtado\Cash\Contracts\WithdrawableInstrumentContract $instrument,
            array $payload
        ): void {
            $this->called = true;
        }
    };

    $service = new DefaultCashWithdrawalValidationService($enforcer);

    $service->validate(fakeWithdrawableInstrument(), ['amount' => 50]);

    expect($called)->toBeTrue();
});

it('allows withdrawal for trusted vendor alias within mandate limit', function () {
    (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrument(),
        new WithdrawalAuthorizationContextData(
            amount: 300.00,
            payload: [
                'cash' => [
                    'mandates' => [
                        [
                            'alias' => 'MERALCO',
                            'max_amount' => 1000.00,
                        ],
                    ],
                ],
            ],
            vendorAlias: 'MERALCO',
        ),
    );

    expect(true)->toBeTrue();
});

it('requires approval when trusted vendor alias exceeds mandate limit', function () {
    (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrument(),
        new WithdrawalAuthorizationContextData(
            amount: 1500.00,
            payload: [
                'cash' => [
                    'mandates' => [
                        [
                            'alias' => 'MERALCO',
                            'max_amount' => 1000.00,
                        ],
                    ],
                ],
            ],
            vendorAlias: 'MERALCO',
        ),
    );
})->throws(WithdrawalApprovalRequired::class, 'Withdrawal approval is required for amounts above 1000.');

it('falls back to threshold policy when vendor alias is not mandated', function () {
    (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrument(),
        new WithdrawalAuthorizationContextData(
            amount: 1500.00,
            payload: [
                'cash' => [
                    'mandates' => [
                        [
                            'alias' => 'MERALCO',
                            'max_amount' => 1000.00,
                        ],
                    ],
                ],
            ],
            vendorAlias: 'UNKNOWN',
            approvalThreshold: 1000.00,
        ),
    );
})->throws(WithdrawalApprovalRequired::class, 'Withdrawal approval is required for amounts above 1000.');