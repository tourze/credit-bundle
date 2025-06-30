<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Procedure;

use CreditBundle\Procedure\GetUserCreditTransaction;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class GetUserCreditTransactionTest extends TestCase
{
    public function testConstruct(): void
    {
        $security = $this->createMock(Security::class);
        $transactionRepository = $this->createMock(TransactionRepository::class);
        $accountService = $this->createMock(AccountService::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $currencyManager = $this->createMock(CurrencyManager::class);
        
        $procedure = new GetUserCreditTransaction(
            $security,
            $transactionRepository,
            $accountService,
            $accountRepository,
            $currencyManager
        );
        
        $this->assertInstanceOf(GetUserCreditTransaction::class, $procedure);
    }
} 