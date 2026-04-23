<?php

declare(strict_types=1);

use LBHurtado\Cash\Services\NullWithdrawalIntervalEnforcer;

it('does not throw when enforcing withdrawal interval', function () {
    $enforcer = new NullWithdrawalIntervalEnforcer();

    $enforcer->enforce(
        fakeWithdrawableInstrument(),
        ['amount' => 50],
    );

    expect(true)->toBeTrue();
});