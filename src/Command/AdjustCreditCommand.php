<?php

declare(strict_types=1);

namespace CreditBundle\Command;

use Carbon\CarbonImmutable;
use CreditBundle\Entity\Account;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: self::NAME, description: '通过流水调整积分')]
#[WithMonologChannel(channel: 'credit')]
final class AdjustCreditCommand extends Command
{
    public const NAME = 'credit:adjust';

    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly TransactionRepository $transactionRepository,
        private readonly LoggerInterface $logger,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $accountIterator = $this->accountRepository->createQueryBuilder('a')
            ->where('a.updateTime > :updateTime')
            ->setParameter('updateTime', CarbonImmutable::now()->subDays())
            ->getQuery()
            ->toIterable()
        ;

        /** @var Account $account */
        foreach ($accountIterator as $account) {
            $endingBalance = (float) ($account->getEndingBalance() ?? 0);
            $increasedAmount = (float) ($account->getIncreasedAmount() ?? 0);
            $decreasedAmount = (float) ($account->getDecreasedAmount() ?? 0);
            if ($endingBalance === $increasedAmount - $decreasedAmount) {
                continue;
            }

            $output->writeln("余额调整{$account->getId()},before,EndingBalance:{$account->getEndingBalance()},IncreasedAmount:{$account->getIncreasedAmount()},DecreasedAmount:{$account->getDecreasedAmount()}");
            $this->logger->info('余额调整前' . $account->getId(), [
                'EndingBalance' => $account->getEndingBalance(),
                'IncreasedAmount' => $account->getIncreasedAmount(),
                'DecreasedAmount' => $account->getDecreasedAmount(),
            ]);
            $result = $this->transactionRepository->createQueryBuilder('t')
                ->select('SUM(CASE WHEN t.amount >= 0 THEN t.amount ELSE 0 END) AS increase,
                              SUM(CASE WHEN t.amount < 0 THEN t.amount ELSE 0 END) AS decrease,
                              SUM(t.amount) AS balance')
                ->where('t.account = :account')
                ->setParameter('account', $account)
                ->getQuery()
                ->getSingleResult()
            ;

            if (!is_array($result)) {
                continue;
            }

            $increase = isset($result['increase']) && is_numeric($result['increase']) ? abs((float) $result['increase']) : 0.0;
            $decrease = isset($result['decrease']) && is_numeric($result['decrease']) ? abs((float) $result['decrease']) : 0.0;
            $balance = isset($result['balance']) && is_numeric($result['balance']) ? abs((float) $result['balance']) : 0.0;

            $account->setEndingBalance(abs($balance));
            $account->setIncreasedAmount(abs($increase));
            $account->setDecreasedAmount(abs($decrease));
            $this->entityManager->persist($account);
            $this->entityManager->flush();
            $output->writeln("余额调整{$account->getId()},after,EndingBalance:{$account->getEndingBalance()},IncreasedAmount:{$account->getIncreasedAmount()},DecreasedAmount:{$account->getDecreasedAmount()}");
            $this->logger->info('余额调整后' . $account->getId(), [
                'EndingBalance' => $account->getEndingBalance(),
                'IncreasedAmount' => $account->getIncreasedAmount(),
                'DecreasedAmount' => $account->getDecreasedAmount(),
            ]);
        }

        return Command::SUCCESS;
    }
}
