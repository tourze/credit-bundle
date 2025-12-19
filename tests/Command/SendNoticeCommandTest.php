<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\SendNoticeCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(SendNoticeCommand::class)]
#[RunTestsInSeparateProcesses]
final class SendNoticeCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(SendNoticeCommand::class);
        $this->assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->addCommand($command);

        $command = $application->find('credit:send-notice');
        $this->commandTester = new CommandTester($command);
    }

    public function testConstruct(): void
    {
        $command = self::getContainer()->get(SendNoticeCommand::class);

        $this->assertInstanceOf(SendNoticeCommand::class, $command);
        $this->assertEquals('credit:send-notice', SendNoticeCommand::NAME);
    }

    public function testExecuteReturnsSuccess(): void
    {
        $exitCode = $this->commandTester->execute([]);

        $this->assertEquals(Command::SUCCESS, $exitCode);
    }
}
