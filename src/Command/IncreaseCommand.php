<?php

declare(strict_types=1);

namespace CreditBundle\Command;

use CreditBundle\Service\AccountService;
use CreditBundle\Service\TransactionService;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

#[AsCommand(name: self::NAME, description: '增加积分')]
final class IncreaseCommand extends Command
{
    public const NAME = 'credit:increase';

    public function __construct(
        private readonly UserLoaderInterface $userLoader,
        private readonly AccountService $accountService,
        private readonly TransactionService $transactionService,
        private readonly Snowflake $snowflake,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('增加积分')
            ->addArgument('currency', InputArgument::REQUIRED)
            ->addArgument('userId', InputArgument::REQUIRED)
            ->addArgument('amount', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currency = $input->getArgument('currency');
        $userId = $input->getArgument('userId');
        $amountArg = $input->getArgument('amount');

        if (!is_string($currency)) {
            $output->writeln('<error>Currency must be a string</error>');

            return Command::INVALID;
        }

        if (!is_string($userId)) {
            $output->writeln('<error>User ID must be a string</error>');

            return Command::INVALID;
        }

        if (!is_numeric($amountArg)) {
            $output->writeln('<error>Amount must be numeric</error>');

            return Command::INVALID;
        }

        $bizUser = $this->userLoader->loadUserByIdentifier($userId);
        if (null === $bizUser) {
            $output->writeln('<error>User not found</error>');

            return Command::FAILURE;
        }
        $account = $this->accountService->getAccountByUser($bizUser, $currency);

        $amount = abs((float) $amountArg);

        $this->transactionService->increase(
            $this->snowflake->id(),
            $account,
            $amount,
        );

        return Command::SUCCESS;
    }
}
