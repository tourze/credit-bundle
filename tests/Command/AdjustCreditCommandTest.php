<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\AdjustCreditCommand;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(AdjustCreditCommand::class)]
#[RunTestsInSeparateProcesses]
final class AdjustCreditCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(AdjustCreditCommand::class);
        $this->assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->addCommand($command);

        $command = $application->find('credit:adjust');
        $this->commandTester = new CommandTester($command);
    }

    public function testConstruct(): void
    {
        $command = self::getContainer()->get(AdjustCreditCommand::class);

        $this->assertInstanceOf(AdjustCreditCommand::class, $command);
        $this->assertEquals('credit:adjust', AdjustCreditCommand::NAME);
    }

    public function testExecuteWithNoAccounts(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }
}
