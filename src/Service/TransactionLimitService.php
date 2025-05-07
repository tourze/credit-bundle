<?php

namespace CreditBundle\Service;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CreditBundle\Entity\Account;
use CreditBundle\Enum\LimitType;
use CreditBundle\Exception\TransactionException;
use CreditBundle\Repository\TransactionRepository;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * 交易限额服务
 */
#[Autoconfigure(lazy: true, public: true)]
class TransactionLimitService
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
    ) {
    }

    /**
     * 检查转入限额
     */
    public function checkIncreaseLimit(Account $account, float $amount): void
    {
        $amount = abs($amount);
        // 全局每日转入限制
        $dayLimit = isset($_ENV['CREDIT_INCREASE_DAY_LIMIT']) ? intval($_ENV['CREDIT_INCREASE_DAY_LIMIT']) : '1000';
        $s = $this->transactionRepository->createQueryBuilder('a')
            ->select('SUM(a.amount)')
            ->where('a.account=:account AND a.amount > 0 and a.createTime >= :start and a.createTime < :end')
            ->setParameter('account', $account)
            ->setParameter('start', Carbon::now()->startOfDay())
            ->setParameter('end', Carbon::now()->endOfDay())
            ->getQuery()
            ->getSingleScalarResult();
        if (($s + $amount) > $dayLimit) {
            throw new TransactionException("转入限额已到达{$dayLimit}");
        }

        // 全局每月转入限制
        if (isset($_ENV['CREDIT_INCREASE_MONTH_LIMIT'])) {
            $monthLimit = intval($_ENV['CREDIT_INCREASE_MONTH_LIMIT']);
            $s = $this->transactionRepository->createQueryBuilder('a')
                ->select('SUM(a.amount)')
                ->where('a.account=:account AND a.amount > 0 and a.createTime >= :start and a.createTime < :end')
                ->setParameter('account', $account)
                ->setParameter('start', Carbon::now()->startOfMonth())
                ->setParameter('end', Carbon::now()->endOfMonth())
                ->getQuery()
                ->getSingleScalarResult();
            if (($s + $amount) > $monthLimit) {
                throw new TransactionException("转入限额已到达{$monthLimit}");
            }
        }

        foreach ($account->getLimits() as $limit) {
            // 局部每日限制转入
            if (LimitType::DAILY_IN_LIMIT === $limit->getType()) {
                if (($s + $amount) > $limit->getValue()) {
                    throw new TransactionException("转入限额已到达{$limit->getValue()}");
                }
            }
        }
    }

    /**
     * 检查转出限额
     */
    public function checkDecreaseLimit(Account $account, float $amount): void
    {
        $amount = abs($amount);
        // 转出账号的限制，我们检查一次
        foreach ($account->getLimits() as $limit) {
            // 每日限制转出
            if (LimitType::DAILY_OUT_LIMIT === $limit->getType()) {
                $now = CarbonImmutable::now();
                $s = $this->transactionRepository->createQueryBuilder('a')
                    ->select('SUM(a.amount)')
                    ->where('a.account = :account AND a.amount < 0 AND (a.createTime BETWEEN :start AND :end)')
                    ->setParameter('start', $now->startOfDay()->format('Y-m-d H:i:s'))
                    ->setParameter('end', $now->endOfDay()->format('Y-m-d H:i:s'))
                    ->setParameter('account', $account)
                    ->getQuery()
                    ->getSingleScalarResult();
                $s = abs($s);
                if (($s + $amount) > $limit->getValue()) {
                    throw new TransactionException("当日转出限额已到达{$limit->getValue()}");
                }
            }

            // 总限制转出
            if (LimitType::TOTAL_OUT_LIMIT === $limit->getType()) {
                $s = $this->transactionRepository->createQueryBuilder('a')
                    ->select('SUM(a.amount)')
                    ->where('a.account=:account AND a.amount < 0')
                    ->setParameter('account', $account)
                    ->getQuery()
                    ->getSingleScalarResult();
                $s = abs($s);
                if (($s + $amount) > $limit->getValue()) {
                    throw new TransactionException("总转出限额已到达{$limit->getValue()}");
                }
            }
        }
    }
}
