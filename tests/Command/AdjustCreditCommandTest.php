<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\AdjustCreditCommand;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AdjustCreditCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $transactionRepository = $this->createMock(TransactionRepository::class);
        $logger = $this->createMock(LoggerInterface::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $command = new AdjustCreditCommand(
            $accountRepository,
            $transactionRepository,
            $logger,
            $entityManager
        );
        
        $this->assertInstanceOf(AdjustCreditCommand::class, $command);
        $this->assertEquals('credit:adjust', AdjustCreditCommand::NAME);
    }
} 