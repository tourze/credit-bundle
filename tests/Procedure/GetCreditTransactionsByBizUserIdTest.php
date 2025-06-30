<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Procedure;

use CreditBundle\Procedure\GetCreditTransactionsByBizUserId;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\CurrencyManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class GetCreditTransactionsByBizUserIdTest extends TestCase
{
    public function testConstruct(): void
    {
        $transactionRepository = $this->createMock(TransactionRepository::class);
        $accountRepository = $this->createMock(AccountRepository::class);
        $currencyManager = $this->createMock(CurrencyManager::class);
        $userLoader = $this->createMock(UserLoaderInterface::class);
        
        $procedure = new GetCreditTransactionsByBizUserId(
            $transactionRepository,
            $accountRepository,
            $currencyManager,
            $userLoader
        );
        
        $this->assertInstanceOf(GetCreditTransactionsByBizUserId::class, $procedure);
    }
} 