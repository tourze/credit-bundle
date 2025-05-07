<?php

namespace CreditBundle\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Transaction;
use CreditBundle\Model\ConsumptionPreview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;

/**
 * @method Transaction|null find($id, $lockMode = null, $lockVersion = null)
 * @method Transaction|null findOneBy(array $criteria, array $orderBy = null)
 * @method Transaction[]    findAll()
 * @method Transaction[]    findBy(array $criteria, array $orderBy = null, $Transaction = null, $offset = null)
 */
class TransactionRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * 优化的方法，一次性查询账户可消费的积分记录
     * 按照FIFO原则(先过期的先消费，相同过期时间的按ID排序先进先出)排序
     *
     * @param Account $account 账户
     * @param float $amount 需要消费的金额
     * @return array 可消费的积分记录数组
     */
    public function findConsumableRecords(Account $account, float $amount): array
    {
        // 首先尝试查询即将过期的记录
        $expirableRecords = $this->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->andWhere('a.amount > 0')
            ->andWhere('a.balance > 0')
            ->andWhere('a.expireTime IS NOT NULL')
            ->setParameter('account', $account)
            ->addOrderBy('a.expireTime', Criteria::ASC)
            ->addOrderBy('a.id', Criteria::ASC)
            ->getQuery()
            ->getResult();

        // 计算可消费金额
        $remainingAmount = $amount;
        $result = [];

        // 先处理有过期时间的积分
        foreach ($expirableRecords as $record) {
            $result[] = $record;
            $remainingAmount -= $record->getBalance();

            if ($remainingAmount <= 0) {
                return $result;
            }
        }

        // 如果有过期时间的积分不够，查询普通积分
        if ($remainingAmount > 0) {
            $regularRecords = $this->createQueryBuilder('a')
                ->andWhere('a.account = :account')
                ->andWhere('a.amount > 0')
                ->andWhere('a.balance > 0')
                ->andWhere('a.expireTime IS NULL')
                ->setParameter('account', $account)
                ->addOrderBy('a.id', Criteria::ASC)
                ->getQuery()
                ->getResult();

            foreach ($regularRecords as $record) {
                $result[] = $record;
                $remainingAmount -= $record->getBalance();

                if ($remainingAmount <= 0) {
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * 获取积分消费预览，并提供合并小额积分的功能
     *
     * @param Account $account 账户
     * @param float $amount 需要消费的金额
     * @param int $maxRecords 最大记录数，超过此数量将触发小额积分合并
     * @return ConsumptionPreview 包含消费预览信息的对象
     */
    public function getConsumptionPreview(Account $account, float $amount, int $maxRecords = 100): ConsumptionPreview
    {
        // 获取可消费的记录
        $records = $this->findConsumableRecords($account, $amount);

        // 如果记录数量超过阈值，建议执行合并
        $needsMerge = count($records) > $maxRecords;

        return new ConsumptionPreview($records, $needsMerge, count($records));
    }
}
