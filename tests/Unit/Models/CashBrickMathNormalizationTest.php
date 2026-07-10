<?php

declare(strict_types=1);

use LBHurtado\Cash\Models\Cash;

it('normalizes float amounts before BrickMoney receives them', function () {
    $warnings = [];

    set_error_handler(function (int $severity, string $message, string $file, int $line) use (&$warnings): bool {
        if (! str_contains($message, 'Passing floats to BigNumber::of()')) {
            return false;
        }

        $warnings[] = compact('severity', 'message', 'file', 'line');

        return true;
    });

    try {
        $cash = Cash::create([
            'amount' => 25.0,
            'currency' => 'PHP',
        ]);
    } finally {
        restore_error_handler();
    }

    expect($warnings)->toBeEmpty()
        ->and($cash->getRawOriginal('amount'))->toBe(2500)
        ->and($cash->amount->getAmount()->__toString())->toBe('25.00')
        ->and($cash->amount->getCurrency()->getCurrencyCode())->toBe('PHP');
});

it('preserves string decimal amount normalization', function () {
    $cash = Cash::create([
        'amount' => '25.00',
        'currency' => 'PHP',
    ]);

    expect($cash->getRawOriginal('amount'))->toBe(2500)
        ->and($cash->amount->getAmount()->__toString())->toBe('25.00')
        ->and($cash->amount->getCurrency()->getCurrencyCode())->toBe('PHP');
});
