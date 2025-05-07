<?php

namespace CreditBundle\Command;

use Carbon\Carbon;
use CreditBundle\Entity\Transaction;
use CreditBundle\Model\TransactionParticipant;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\TransactionService;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tourze\SnowflakeBundle\Service\Snowflake;

#[AsCommand(name: CalcExpireTransactionCommand::NAME, description: '计算过期积分')]
class CalcExpireTransactionCommand extends Command
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
            ->addArgument('accountId', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $account = $this->accountRepository->find($input->getArgument('accountId'));
        if (!$account) {
            return Command::INVALID;
        }

        // 增加的积分才需要过期处理
        $transactions = $this->transactionRepository->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->andWhere('a.amount > 0')
            ->andWhere('a.balance > 0')
            ->andWhere('a.expireTime IS NOT NULL')
            ->andWhere('a.expireTime <= :now')
            ->setParameter('account', $account)
            ->setParameter('now', Carbon::now())
            ->orderBy('a.id', Criteria::ASC)
            ->getQuery()
            ->getResult();

        $costAmount = 0;
        foreach ($transactions as $transaction) {
            /** @var Transaction $transaction */
            $costAmount += $transaction->getBalance();
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
