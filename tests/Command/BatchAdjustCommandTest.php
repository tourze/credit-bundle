<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\BatchAdjustCommand;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpKernel\KernelInterface;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;
use Tourze\SnowflakeBundle\Service\Snowflake;

/**
 * @internal
 */
#[CoversClass(BatchAdjustCommand::class)]
#[RunTestsInSeparateProcesses]
final class BatchAdjustCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(BatchAdjustCommand::class);
        $this->assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->addCommand($command);

        $command = $application->find('credit:batch-adjust');
        $this->commandTester = new CommandTester($command);
    }

    public function testConstruct(): void
    {
        $command = self::getContainer()->get(BatchAdjustCommand::class);

        $this->assertInstanceOf(BatchAdjustCommand::class, $command);
        $this->assertEquals('credit:batch-adjust', BatchAdjustCommand::NAME);
    }

    public function testArgumentXlsFileName(): void
    {
        $exitCode = $this->commandTester->execute(['xlsFileName' => '/non/existent/file.xls']);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('文件不存在', $this->commandTester->getDisplay());
    }

    public function testExecuteWithNonExistentFileReturnsFailure(): void
    {
        $exitCode = $this->commandTester->execute(['xlsFileName' => '/non/existent/file.xls']);

        $this->assertEquals(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('文件不存在', $this->commandTester->getDisplay());
    }
}
