<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\IncreaseCommand;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use CreditBundle\Service\TransactionService;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

class IncreaseCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $userLoader = $this->createMock(UserLoaderInterface::class);
        $accountService = $this->createMock(AccountService::class);
        $currencyService = $this->createMock(CurrencyService::class);
        $transactionService = $this->createMock(TransactionService::class);
        $snowflake = $this->createMock(Snowflake::class);
        
        $command = new IncreaseCommand(
            $userLoader,
            $accountService,
            $currencyService,
            $transactionService,
            $snowflake
        );
        
        $this->assertInstanceOf(IncreaseCommand::class, $command);
        $this->assertEquals('credit:increase', IncreaseCommand::NAME);
    }
} 