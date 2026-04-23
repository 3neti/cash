<?php

declare(strict_types=1);

use LBHurtado\Cash\Services\DefaultCashWithdrawalAmountResolverService;

function amountResolver(): DefaultCashWithdrawalAmountResolverService
{
    return new DefaultCashWithdrawalAmountResolverService();
}

it('resolves amount for fixed-slice instruments', function () {
    $amount = amountResolver()->resolve(
        fakeWithdrawableInstrument([
            'sliceMode' => 'fixed',
            'sliceAmount' => 25.0,
        ]),
        null,
    );

    expect($amount)->toBe(25.0);
});

it('fails when fixed-slice instrument has no slice amount', function () {
    amountResolver()->resolve(
        fakeWithdrawableInstrument([
            'sliceMode' => 'fixed',
            'sliceAmount' => null,
        ]),
        null,
    );
})->throws(InvalidArgumentException::class, 'Fixed-slice voucher is missing slice amount.');

it('resolves amount for open-slice instruments', function () {
    $amount = amountResolver()->resolve(
        fakeWithdrawableInstrument([
            'sliceMode' => 'open',
            'remainingBalance' => 100.0,
            'minWithdrawal' => 10.0,
        ]),
        50.0,
    );

    expect($amount)->toBe(50.0);
});

it('fails when open-slice amount is missing', function () {
    amountResolver()->resolve(
        fakeWithdrawableInstrument(['sliceMode' => 'open']),
        null,
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount is required.');

it('fails when open-slice amount is below minimum withdrawal amount', function () {
    amountResolver()->resolve(
        fakeWithdrawableInstrument([
            'sliceMode' => 'open',
            'minWithdrawal' => 50.0,
        ]),
        25.0,
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount is below the minimum withdrawal amount.');

it('fails when open-slice amount exceeds remaining balance', function () {
    amountResolver()->resolve(
        fakeWithdrawableInstrument([
            'sliceMode' => 'open',
            'remainingBalance' => 25.0,
        ]),
        50.0,
    );
})->throws(InvalidArgumentException::class, 'Withdrawal amount exceeds remaining balance.');

it('returns requested amount for non-slice instruments', function () {
    $amount = amountResolver()->resolve(
        fakeWithdrawableInstrument([
            'sliceMode' => null,
            'remainingBalance' => 100.0,
        ]),
        40.0,
    );

    expect($amount)->toBe(40.0);
});

it('returns remaining balance for non-slice instruments when amount is null', function () {
    $amount = amountResolver()->resolve(
        fakeWithdrawableInstrument([
            'sliceMode' => null,
            'remainingBalance' => 75.0,
        ]),
        null,
    );

    expect($amount)->toBe(75.0);
});