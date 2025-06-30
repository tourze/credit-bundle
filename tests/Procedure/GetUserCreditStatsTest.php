<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Procedure;

use CreditBundle\Procedure\GetUserCreditStats;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class GetUserCreditStatsTest extends TestCase
{
    public function testConstruct(): void
    {
        $security = $this->createMock(Security::class);
        $accountService = $this->createMock(AccountService::class);
        $currencyService = $this->createMock(CurrencyService::class);
        
        $procedure = new GetUserCreditStats(
            $security,
            $accountService,
            $currencyService
        );
        
        $this->assertInstanceOf(GetUserCreditStats::class, $procedure);
    }
} 