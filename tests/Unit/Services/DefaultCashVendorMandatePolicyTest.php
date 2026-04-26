<?php

declare(strict_types=1);

use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;
use LBHurtado\Cash\Services\DefaultCashVendorMandatePolicy;

it('authorizes matching vendor alias within mandate limit', function () {
    $allowed = (new DefaultCashVendorMandatePolicy)->authorize(
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

    expect($allowed)->toBeTrue();
});

it('authorizes matching vendor id within mandate limit', function () {
    $allowed = (new DefaultCashVendorMandatePolicy)->authorize(
        fakeWithdrawableInstrument(),
        new WithdrawalAuthorizationContextData(
            amount: 300.00,
            payload: [
                'cash' => [
                    'mandates' => [
                        [
                            'vendor_id' => 'vendor.meralco',
                            'max_amount' => 1000.00,
                        ],
                    ],
                ],
            ],
            vendorId: 'vendor.meralco',
        ),
    );

    expect($allowed)->toBeTrue();
});

it('does not authorize when no mandate matches', function () {
    $allowed = (new DefaultCashVendorMandatePolicy)->authorize(
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
            vendorAlias: 'BAYAD',
        ),
    );

    expect($allowed)->toBeFalse();
});

it('requires approval when matching mandate exceeds max amount', function () {
    (new DefaultCashVendorMandatePolicy)->authorize(
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
})->throws(WithdrawalApprovalRequired::class);

it('requires approval when mandate explicitly requires approval', function () {
    (new DefaultCashVendorMandatePolicy)->authorize(
        fakeWithdrawableInstrument(),
        new WithdrawalAuthorizationContextData(
            amount: 300.00,
            payload: [
                'cash' => [
                    'mandates' => [
                        [
                            'alias' => 'MERALCO',
                            'max_amount' => 1000.00,
                            'requires_approval' => true,
                        ],
                    ],
                ],
            ],
            vendorAlias: 'MERALCO',
            approved: false,
        ),
    );
})->throws(WithdrawalApprovalRequired::class, 'Withdrawal approval is required for this vendor mandate.');

it('allows mandate requiring approval when already approved', function () {
    $allowed = (new DefaultCashVendorMandatePolicy)->authorize(
        fakeWithdrawableInstrument(),
        new WithdrawalAuthorizationContextData(
            amount: 300.00,
            payload: [
                'cash' => [
                    'mandates' => [
                        [
                            'alias' => 'MERALCO',
                            'max_amount' => 1000.00,
                            'requires_approval' => true,
                        ],
                    ],
                ],
            ],
            vendorAlias: 'MERALCO',
            approved: true,
        ),
    );

    expect($allowed)->toBeTrue();
});