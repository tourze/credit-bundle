<?php

namespace CreditBundle\Command;

use AppBundle\Repository\BizUserRepository;
use CreditBundle\Service\AccountService;
use CreditBundle\Service\CurrencyService;
use CreditBundle\Service\TransactionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

#[AsCommand(name: 'credit:decrease', description: '减少积分')]
class DecreaseCommand extends Command
{
    public function __construct(
        private readonly BizUserRepository $userRepository,
        private readonly AccountService $accountService,
        private readonly CurrencyService $currencyService,
        private readonly TransactionService $transactionService,
        private readonly Snowflake $snowflake,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('减少积分')
            ->addArgument('currency', InputArgument::REQUIRED)
            ->addArgument('userId', InputArgument::REQUIRED)
            ->addArgument('amount', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $currency = $this->currencyService->getCurrencyByCode($input->getArgument('currency'));

        $bizUser = $this->userRepository->find($input->getArgument('userId'));
        $account = $this->accountService->getAccountByUser($bizUser, $currency);

        $amount = abs($input->getArgument('amount'));

        $this->transactionService->decrease(
            $this->snowflake->id(),
            $account,
            $amount,
        );

        return Command::SUCCESS;
    }
}
