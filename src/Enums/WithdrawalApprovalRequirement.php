<?php

declare(strict_types=1);

namespace LBHurtado\Cash\Enums;

enum WithdrawalApprovalRequirement: string
{
    case APPROVAL = 'approval';
    case OTP = 'otp';
    case BIOMETRIC = 'biometric';
    case KYC = 'kyc';
    case LOCATION = 'location';
    case SIGNATURE = 'signature';
    case VENDOR_MANDATE = 'vendor_mandate';
}