<?php

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\CalcExpireTransactionCommand;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\TransactionService;
use PHPUnit\Framework\TestCase;
use Tourze\SnowflakeBundle\Service\Snowflake;

class CalcExpireTransactionCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $accountRepository = $this->createMock(AccountRepository::class);
        $transactionRepository = $this->createMock(TransactionRepository::class);
        $transactionService = $this->createMock(TransactionService::class);
        $snowflake = $this->createMock(Snowflake::class);
        
        $command = new CalcExpireTransactionCommand(
            $accountRepository,
            $transactionRepository,
            $transactionService,
            $snowflake
        );
        
        $this->assertInstanceOf(CalcExpireTransactionCommand::class, $command);
        $this->assertEquals('credit:calc:expire-transaction', CalcExpireTransactionCommand::NAME);
    }
} 