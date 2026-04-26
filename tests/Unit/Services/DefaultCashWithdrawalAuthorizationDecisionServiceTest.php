<?php

use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Services\DefaultCashWithdrawalAuthorizationDecisionService;

it('returns approval required decision above threshold', function () {
    $decision = (new DefaultCashWithdrawalAuthorizationDecisionService)->decide(
        fakeWithdrawableInstrumentForAuthorization(),
        new WithdrawalAuthorizationContextData(
            amount: 1500.00,
            approvalThreshold: 1000.00,
            approved: false,
        ),
    );

    expect($decision->allowed)->toBeFalse()
        ->and($decision->status)->toBe('approval_required')
        ->and($decision->reason)->toBe('Withdrawal approval is required for amounts above 1000.')
        ->and($decision->meta['source'])->toBe('threshold');
});