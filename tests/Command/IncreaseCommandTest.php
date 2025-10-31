<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\IncreaseCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(IncreaseCommand::class)]
#[RunTestsInSeparateProcesses]
final class IncreaseCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(IncreaseCommand::class);
        $this->assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->add($command);

        $command = $application->find('credit:increase');
        $this->commandTester = new CommandTester($command);
    }

    public function testConstruct(): void
    {
        $command = self::getContainer()->get(IncreaseCommand::class);

        $this->assertInstanceOf(IncreaseCommand::class, $command);
        $this->assertEquals('credit:increase', IncreaseCommand::NAME);
    }

    public function testArgumentCurrency(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $this->commandTester->execute([]);
    }

    public function testArgumentUserId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $this->commandTester->execute([]);
    }

    public function testArgumentAmount(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $this->commandTester->execute([]);
    }

    public function testExecuteWithCommandTester(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments');

        $this->commandTester->execute([]);
    }
}
