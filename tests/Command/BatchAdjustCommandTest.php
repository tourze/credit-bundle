<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\BatchAdjustCommand;
use CreditBundle\Repository\CurrencyRepository;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

class BatchAdjustCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $transactionService = $this->createMock(TransactionService::class);
        $accountService = $this->createMock(AccountService::class);
        $currencyRepository = $this->createMock(CurrencyRepository::class);
        $snowflake = $this->createMock(Snowflake::class);
        $kernel = $this->createMock(KernelInterface::class);
        
        $command = new BatchAdjustCommand(
            $transactionService,
            $accountService,
            $currencyRepository,
            $snowflake,
            $kernel
        );
        
        $this->assertInstanceOf(BatchAdjustCommand::class, $command);
        $this->assertEquals('credit:batch-adjust', BatchAdjustCommand::NAME);
    }
} 