<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use Carbon\CarbonImmutable;
use CreditBundle\Entity\Account;
use CreditBundle\Entity\Limit;
use CreditBundle\Enum\LimitType;
use CreditBundle\Exception\TransactionException;
use CreditBundle\Repository\TransactionRepository;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * 交易限额服务
 */
#[Autoconfigure(public: true)]
readonly class TransactionLimitService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
    ) {
    }

    /**
     * 检查转入限额
     *
     * @param float $amount
     *
     * 注意：此方法不考虑并发，在设计上假设交易限额检查的并发冲突概率极低，
     * 且业务上可以容忍少量的超限情况。如需要严格的并发控制，可以考虑：
     * 1. 使用数据库行级锁
     * 2. 使用 Redis 分布式锁
     * 3. 实现乐观锁机制
     */
    public function checkIncreaseLimit(Account $account, float $amount): void
    {
        $amount = abs($amount);

        $this->checkDailyIncreaseLimit($account, $amount);
        $this->checkMonthlyIncreaseLimit($account, $amount);
        $this->checkAccountSpecificIncreaseLimit($account, $amount);
    }

    /**
     * 检查每日转入限额
     *
     * 不考虑并发 - 委托方法，上层已考虑并发问题
     */
    private function checkDailyIncreaseLimit(Account $account, float $amount): void
    {
        $dayLimit = 1000;
        if (isset($_ENV['CREDIT_INCREASE_DAY_LIMIT'])) {
            $envValue = $_ENV['CREDIT_INCREASE_DAY_LIMIT'];
            $dayLimit = is_string($envValue) || is_int($envValue) ? intval($envValue) : 1000;
        }
        $dailySum = $this->getDailyIncreaseSum($account);

        if (($dailySum + $amount) > $dayLimit) {
            throw new TransactionException("转入限额已到达{$dayLimit}");
        }
    }

    /**
     * 检查每月转入限额
     *
     * 不考虑并发 - 委托方法，上层已考虑并发问题
     */
    private function checkMonthlyIncreaseLimit(Account $account, float $amount): void
    {
        if (!isset($_ENV['CREDIT_INCREASE_MONTH_LIMIT'])) {
            return;
        }

        $envValue = $_ENV['CREDIT_INCREASE_MONTH_LIMIT'];
        $monthLimit = is_string($envValue) || is_int($envValue) ? intval($envValue) : 0;
        $monthlySum = $this->getMonthlyIncreaseSum($account);

        if (($monthlySum + $amount) > $monthLimit) {
            throw new TransactionException("转入限额已到达{$monthLimit}");
        }
    }

    /**
     * 检查账户特定转入限额
     *
     * 不考虑并发 - 委托方法，上层已考虑并发问题
     */
    private function checkAccountSpecificIncreaseLimit(Account $account, float $amount): void
    {
        $dailySum = $this->getDailyIncreaseSum($account);

        foreach ($account->getLimits() as $limit) {
            if (LimitType::DAILY_IN_LIMIT === $limit->getType()) {
                if (($dailySum + $amount) > $limit->getValue()) {
                    throw new TransactionException("转入限额已到达{$limit->getValue()}");
                }
            }
        }
    }

    /**
     * 获取账户当日转入总额
     *
     * 不考虑并发 - 此方法仅进行查询统计，无状态修改
     */
    private function getDailyIncreaseSum(Account $account): float
    {
        $result = $this->transactionRepository->createQueryBuilder('a')
            ->select('SUM(a.amount)')
            ->where('a.account=:account AND a.amount > 0 and a.createTime >= :start and a.createTime < :end')
            ->setParameter('account', $account)
            ->setParameter('start', CarbonImmutable::now()->startOfDay())
            ->setParameter('end', CarbonImmutable::now()->endOfDay())
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        return (float) $result;
    }

    /**
     * 获取账户当月转入总额
     *
     * 不考虑并发 - 此方法仅进行查询统计，无状态修改
     */
    private function getMonthlyIncreaseSum(Account $account): float
    {
        $result = $this->transactionRepository->createQueryBuilder('a')
            ->select('SUM(a.amount)')
            ->where('a.account=:account AND a.amount > 0 and a.createTime >= :start and a.createTime < :end')
            ->setParameter('account', $account)
            ->setParameter('start', CarbonImmutable::now()->startOfMonth())
            ->setParameter('end', CarbonImmutable::now()->endOfMonth())
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        return (float) $result;
    }

    /**
     * 检查转出限额
     *
     * @param float $amount
     *
     * 注意：此方法不考虑并发，在设计上假设交易限额检查的并发冲突概率极低，
     * 且业务上可以容忍少量的超限情况。如需要严格的并发控制，可以考虑：
     * 1. 使用数据库行级锁
     * 2. 使用 Redis 分布式锁
     * 3. 实现乐观锁机制
     */
    public function checkDecreaseLimit(Account $account, float $amount): void
    {
        $amount = abs($amount);

        foreach ($account->getLimits() as $limit) {
            $this->checkSpecificDecreaseLimit($account, $amount, $limit);
        }
    }

    /**
     * 检查特定的转出限额
     *
     * 不考虑并发 - 委托方法，上层已考虑并发问题
     */
    private function checkSpecificDecreaseLimit(Account $account, float $amount, Limit $limit): void
    {
        if (LimitType::DAILY_OUT_LIMIT === $limit->getType()) {
            $this->checkDailyDecreaseLimit($account, $amount, $limit);
        } elseif (LimitType::TOTAL_OUT_LIMIT === $limit->getType()) {
            $this->checkTotalDecreaseLimit($account, $amount, $limit);
        }
    }

    /**
     * 检查每日转出限额
     *
     * 不考虑并发 - 委托方法，上层已考虑并发问题
     */
    private function checkDailyDecreaseLimit(Account $account, float $amount, Limit $limit): void
    {
        $dailySum = $this->getDailyDecreaseSum($account);

        if (($dailySum + $amount) > $limit->getValue()) {
            throw new TransactionException("当日转出限额已到达{$limit->getValue()}");
        }
    }

    /**
     * 检查总转出限额
     *
     * 不考虑并发 - 委托方法，上层已考虑并发问题
     */
    private function checkTotalDecreaseLimit(Account $account, float $amount, Limit $limit): void
    {
        $totalSum = $this->getTotalDecreaseSum($account);

        if (($totalSum + $amount) > $limit->getValue()) {
            throw new TransactionException("总转出限额已到达{$limit->getValue()}");
        }
    }

    /**
     * 获取账户当日转出总额
     *
     * 不考虑并发 - 此方法仅进行查询统计，无状态修改
     */
    private function getDailyDecreaseSum(Account $account): float
    {
        $now = CarbonImmutable::now();
        $sum = $this->transactionRepository->createQueryBuilder('a')
            ->select('SUM(a.amount)')
            ->where('a.account = :account AND a.amount < 0 AND (a.createTime BETWEEN :start AND :end)')
            ->setParameter('start', $now->startOfDay()->format('Y-m-d H:i:s'))
            ->setParameter('end', $now->endOfDay()->format('Y-m-d H:i:s'))
            ->setParameter('account', $account)
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        return abs((float) $sum);
    }

    /**
     * 获取账户总转出金额
     *
     * 不考虑并发 - 此方法仅进行查询统计，无状态修改
     */
    private function getTotalDecreaseSum(Account $account): float
    {
        $sum = $this->transactionRepository->createQueryBuilder('a')
            ->select('SUM(a.amount)')
            ->where('a.account=:account AND a.amount < 0')
            ->setParameter('account', $account)
            ->getQuery()
            ->getSingleScalarResult() ?? 0
        ;

        return abs((float) $sum);
    }
}
