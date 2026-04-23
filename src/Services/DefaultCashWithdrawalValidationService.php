<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use InvalidArgumentException;
use LBHurtado\Cash\Contracts\CashWithdrawalValidationContract;
use LBHurtado\Cash\Contracts\WithdrawalIntervalEnforcerContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use RuntimeException;

class DefaultCashWithdrawalValidationService implements CashWithdrawalValidationContract
{
    public function __construct(
        protected WithdrawalIntervalEnforcerContract $intervalEnforcer,
    ) {}

    public function validate(WithdrawableInstrumentContract $instrument, array $payload): void
    {
        if ($this->isOpenSliceInstrument($instrument)) {
            $this->validateOpenSliceInstrument($instrument, $payload);
            $this->intervalEnforcer->enforce($instrument, $payload);

            return;
        }

        if (! $instrument->isWithdrawable()) {
            throw new RuntimeException('This voucher is not withdrawable.');
        }

        $sliceMode = $instrument->getSliceMode();
        $amount = data_get($payload, 'amount');

        if ($sliceMode === 'open') {
            if ($amount === null || $amount === '') {
                throw new InvalidArgumentException('Withdrawal amount is required.');
            }

            if (! is_numeric($amount)) {
                throw new InvalidArgumentException('Withdrawal amount must be numeric.');
            }

            $amount = (float) $amount;

            if ($amount <= 0) {
                throw new InvalidArgumentException('Withdrawal amount must be greater than zero.');
            }

            if ($amount > $instrument->getRemainingBalance()) {
                throw new InvalidArgumentException('Withdrawal amount exceeds remaining balance.');
            }

            $minWithdrawal = $instrument->getMinWithdrawal();

            if ($minWithdrawal !== null && $amount < $minWithdrawal) {
                throw new InvalidArgumentException('Withdrawal amount is below the minimum withdrawal amount.');
            }
        }
    }

    protected function isOpenSliceInstrument(WithdrawableInstrumentContract $instrument): bool
    {
        return $instrument->isDivisible() && $instrument->getSliceMode() === 'open';
    }

    protected function validateOpenSliceInstrument(WithdrawableInstrumentContract $instrument, array $payload): void
    {
        if ($instrument->getInstrumentState() !== 'active') {
            throw new RuntimeException('This voucher is not withdrawable.');
        }

        if ($instrument->isExpired()) {
            throw new RuntimeException('This voucher has expired.');
        }

        $amount = data_get($payload, 'amount');

        if ($amount === null || $amount === '') {
            throw new InvalidArgumentException('Withdrawal amount is required.');
        }

        if (! is_numeric($amount)) {
            throw new InvalidArgumentException('Withdrawal amount must be numeric.');
        }

        $amount = (float) $amount;

        if ($amount <= 0) {
            throw new InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        if ($amount > $instrument->getRemainingBalance()) {
            throw new InvalidArgumentException('Withdrawal amount exceeds remaining balance.');
        }

        $minWithdrawal = $instrument->getMinWithdrawal();

        if ($minWithdrawal !== null && $amount < $minWithdrawal) {
            throw new InvalidArgumentException('Withdrawal amount is below the minimum withdrawal amount.');
        }

        $maxSlices = $instrument->getMaxSlices();

        if ($maxSlices !== null && $instrument->getConsumedSlices() >= $maxSlices) {
            throw new RuntimeException('This voucher has no remaining slices.');
        }
    }
}