<?php

declare(strict_types=1);

namespace CreditBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO 发送欠费提醒
 */
#[AsCommand(name: self::NAME, description: '发送欠费提醒')]
final class SendNoticeCommand extends Command
{
    public const NAME = 'credit:send-notice';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
