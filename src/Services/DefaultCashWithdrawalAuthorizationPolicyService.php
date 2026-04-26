<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Services;

use LBHurtado\Cash\Contracts\CashWithdrawalAuthorizationDecisionContract;
use LBHurtado\Cash\Contracts\CashWithdrawalAuthorizationPolicyContract;
use LBHurtado\Cash\Contracts\WithdrawableInstrumentContract;
use LBHurtado\Cash\Data\WithdrawalAuthorizationContextData;
use LBHurtado\Cash\Exceptions\WithdrawalApprovalRequired;

class DefaultCashWithdrawalAuthorizationPolicyService implements CashWithdrawalAuthorizationPolicyContract
{
    public function __construct(
        protected ?CashWithdrawalAuthorizationDecisionContract $decisions = null,
    ) {
        $this->decisions ??= new DefaultCashWithdrawalAuthorizationDecisionService;
    }

    public function authorize(
        WithdrawableInstrumentContract $instrument,
        WithdrawalAuthorizationContextData $context,
    ): void {
        $decision = $this->decisions->decide($instrument, $context);

        if ($decision->allowed) {
            return;
        }

        if ($decision->status === 'approval_required') {
            throw new WithdrawalApprovalRequired(
                $decision->reason ?? 'Withdrawal approval is required.'
            );
        }
    }
}