<?php

declare(strict_types=1);

use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Data\WithdrawalAuthorizationDecisionData;
use LBHurtado\Cash\Enums\WithdrawalApprovalRequirement;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;
use LBHurtado\Cash\Services\DefaultCashWithdrawalAuthorizationPolicyService;

it('allows withdrawal when no approval threshold is configured', function () {
    expect(fn () => (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(amount: 500.00),
    ))->not->toThrow(
        WithdrawalApprovalRequired::class,
        'Withdrawal approval is required for amounts above 1000.'
    );
});

it('allows withdrawal below approval threshold', function () {
    expect(fn () => (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(
            amount: 500.00,
            approvalThreshold: 1000.00,
        ),
    ))->not->toThrow(
        WithdrawalApprovalRequired::class,
        'Withdrawal approval is required for amounts above 1000.'
    );
});

it('allows withdrawal above threshold when already approved', function () {
    expect(fn () => (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(
            amount: 1500.00,
            approvalThreshold: 1000.00,
            approved: true,
        ),
    ))->not->toThrow(
        WithdrawalApprovalRequired::class,
        'Withdrawal approval is required for amounts above 1000.'
    );
});

it('requires approval when withdrawal exceeds threshold and is not approved', function () {
    expect(fn () => (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(
            amount: 1500.00,
            approvalThreshold: 1000.00,
            approved: false,
        ),
    ))->toThrow(
        WithdrawalApprovalRequired::class,
        'Withdrawal approval is required for amounts above 1000.'
    );
});

it('returns early when vendor mandate authorizes withdrawal', function () {
    expect(fn () => (new DefaultCashWithdrawalAuthorizationPolicyService)->authorize(
        fakeWithdrawableInstrumentForAuthorization(),
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
            approvalThreshold: 100.00,
        ),
    ))->not->toThrow(
        WithdrawalApprovalRequired::class,
        'Withdrawal approval is required for amounts above 1000.'
    );
});

it('uses standardized approval requirement values', function () {
    $decision = WithdrawalAuthorizationDecisionData::approvalRequired(
        reason: 'Approval required.',
    );

    expect($decision->requirements)
        ->toBe([WithdrawalApprovalRequirement::APPROVAL->value]);
});