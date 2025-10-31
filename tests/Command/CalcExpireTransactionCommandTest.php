<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\CalcExpireTransactionCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(CalcExpireTransactionCommand::class)]
#[RunTestsInSeparateProcesses]
final class CalcExpireTransactionCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(CalcExpireTransactionCommand::class);
        $this->assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->add($command);

        $command = $application->find('credit:calc:expire-transaction');
        $this->commandTester = new CommandTester($command);
    }

    public function testConstruct(): void
    {
        $command = self::getContainer()->get(CalcExpireTransactionCommand::class);

        $this->assertInstanceOf(CalcExpireTransactionCommand::class, $command);
        $this->assertEquals('credit:calc:expire-transaction', CalcExpireTransactionCommand::NAME);
    }

    public function testArgumentAccountId(): void
    {
        $exitCode = $this->commandTester->execute(['accountId' => '999']);

        $this->assertEquals(Command::INVALID, $exitCode);
    }

    public function testExecuteWithInvalidAccountIdReturnsInvalid(): void
    {
        $exitCode = $this->commandTester->execute(['accountId' => '999']);

        $this->assertEquals(Command::INVALID, $exitCode);
    }
}
