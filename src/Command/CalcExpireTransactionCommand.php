<?php

declare(strict_types=1);

namespace CreditBundle\Command;

use Carbon\CarbonImmutable;
use CreditBundle\Entity\Account;
use CreditBundle\Entity\Transaction;
use CreditBundle\Model\TransactionParticipant;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\TransactionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

#[AsCommand(name: self::NAME, description: '计算过期积分')]
final class CalcExpireTransactionCommand extends Command
{
    public const NAME = 'credit:calc:expire-transaction';

    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly TransactionService $transactionService,
        private readonly Snowflake $snowflake,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('结算account')
            ->addArgument('accountId', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountId = $input->getArgument('accountId');
        if (!is_string($accountId) && !is_numeric($accountId)) {
            $output->writeln('Invalid account ID provided');

            return Command::INVALID;
        }

        $account = $this->accountRepository->find($accountId);
        if (null === $account) {
            $output->writeln('Account not found');

            return Command::INVALID;
        }

        assert($account instanceof Account);

        // 增加的积分才需要过期处理
        $transactions = $this->transactionRepository->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->andWhere('a.amount > 0')
            ->andWhere('a.balance > 0')
            ->andWhere('a.expireTime IS NOT NULL')
            ->andWhere('a.expireTime <= :now')
            ->setParameter('account', $account)
            ->setParameter('now', CarbonImmutable::now())
            ->orderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        if (!is_array($transactions)) {
            $output->writeln('Failed to retrieve transactions');

            return Command::FAILURE;
        }

        $costAmount = 0.0;
        foreach ($transactions as $transaction) {
            if (!$transaction instanceof Transaction) {
                continue;
            }
            $balance = $transaction->getBalance();
            if (is_numeric($balance)) {
                $costAmount += (float) $balance;
            }
        }
        $costAmount = abs($costAmount);
        $output->writeln('$costAmount:' . $costAmount);
        if ($costAmount <= 0) {
            return Command::FAILURE;
        }

        $participant = new TransactionParticipant();
        $participant->setAmount(-$costAmount);
        $participant->setRemark('积分过期');

        // 开始扣积分
        $this->transactionService->decrease(
            'EXPIRE' . $this->snowflake->id(),
            $account,
            $participant->getAmount(),
            $participant->getRemark(),
            isExpired: true,
        );

        return Command::SUCCESS;
    }
}
