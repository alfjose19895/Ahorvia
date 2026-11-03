<?php

declare(strict_types=1);

namespace Tests\Unit\Finance;

use App\Enums\AccessStatus;
use App\Enums\AccountType;
use PHPUnit\Framework\TestCase;

class AccountTypeAndAccessStatusEnumTest extends TestCase
{
    public function test_account_types_classify_assets_liabilities_and_available_liquidity(): void
    {
        $this->assertTrue(AccountType::Cash->isAsset());
        $this->assertTrue(AccountType::Checking->isAvailableLiquidity());
        $this->assertTrue(AccountType::Savings->isAsset());
        $this->assertFalse(AccountType::Savings->isAvailableLiquidity());

        $this->assertTrue(AccountType::CreditCard->isLiability());
        $this->assertTrue(AccountType::Loan->isLiability());
        $this->assertFalse(AccountType::CreditCard->isAvailableLiquidity());
    }

    public function test_access_status_only_grants_access_when_active(): void
    {
        $this->assertTrue(AccessStatus::Active->canAccessApplication());
        $this->assertFalse(AccessStatus::PendingActivation->canAccessApplication());
        $this->assertFalse(AccessStatus::Suspended->canAccessApplication());
        $this->assertFalse(AccessStatus::Revoked->canAccessApplication());
    }
}
