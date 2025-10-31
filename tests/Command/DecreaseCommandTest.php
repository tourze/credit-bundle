<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Command;

use CreditBundle\Command\DecreaseCommand;
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
#[CoversClass(DecreaseCommand::class)]
#[RunTestsInSeparateProcesses]
final class DecreaseCommandTest extends AbstractCommandTestCase
{
    private CommandTester $commandTester;

    protected function getCommandTester(): CommandTester
    {
        return $this->commandTester;
    }

    protected function onSetUp(): void
    {
        $command = self::getContainer()->get(DecreaseCommand::class);
        $this->assertInstanceOf(Command::class, $command);

        $application = new Application();
        $application->add($command);

        $command = $application->find('credit:decrease');
        $this->commandTester = new CommandTester($command);
    }

    public function testConstruct(): void
    {
        $command = self::getContainer()->get(DecreaseCommand::class);

        $this->assertInstanceOf(DecreaseCommand::class, $command);
        $this->assertEquals('credit:decrease', DecreaseCommand::NAME);
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
