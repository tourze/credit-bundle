<?php

declare(strict_types=1);

namespace CreditBundle\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Transaction;
use CreditBundle\Model\ConsumptionPreview;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<Transaction>
 */
#[Autoconfigure(public: true)]
#[AsRepository(entityClass: Transaction::class)]
class TransactionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Transaction::class);
    }

    /**
     * 优化的方法，一次性查询账户可消费的积分记录
     * 按照FIFO原则(先过期的先消费，相同过期时间的按ID排序先进先出)排序
     *
     * @param Account $account 账户
     * @param float   $amount  需要消费的金额
     *
     * @return array<Transaction> 可消费的积分记录数组
     */
    public function findConsumableRecords(Account $account, float $amount): array
    {
        $expirableRecords = $this->findExpirableRecords($account);
        [$result, $remainingAmount] = $this->collectExpirableRecords($expirableRecords, $amount);

        if ($remainingAmount > 0) {
            $regularRecords = $this->findRegularRecords($account);
            $regularResult = $this->collectRegularRecords($regularRecords, $remainingAmount);
            $result = array_merge($result, $regularResult);
        }

        return $result;
    }

    /**
     * @return array<Transaction>
     */
    private function findExpirableRecords(Account $account): array
    {
        /** @var array<Transaction> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->andWhere('a.amount > 0')
            ->andWhere('a.balance > 0')
            ->andWhere('a.expireTime IS NOT NULL')
            ->setParameter('account', $account)
            ->addOrderBy('a.expireTime', 'ASC')
            ->addOrderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return array<Transaction>
     */
    private function findRegularRecords(Account $account): array
    {
        /** @var array<Transaction> */
        return $this->createQueryBuilder('a')
            ->andWhere('a.account = :account')
            ->andWhere('a.amount > 0')
            ->andWhere('a.balance > 0')
            ->andWhere('a.expireTime IS NULL')
            ->setParameter('account', $account)
            ->addOrderBy('a.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array<Transaction> $records
     * @return array{0: array<Transaction>, 1: float} [$result, $remainingAmount]
     */
    private function collectExpirableRecords(array $records, float $amount): array
    {
        $result = [];
        $remainingAmount = $amount;
        foreach ($records as $record) {
            $result[] = $record;
            $remainingAmount -= (float) $record->getBalance();

            if ($remainingAmount <= 0) {
                return [$result, 0];
            }
        }

        return [$result, $remainingAmount];
    }

    /**
     * @param array<Transaction> $records
     * @return array<Transaction>
     */
    private function collectRegularRecords(array $records, float $remainingAmount): array
    {
        $result = [];
        foreach ($records as $record) {
            $result[] = $record;
            $remainingAmount -= (float) $record->getBalance();

            if ($remainingAmount <= 0) {
                break;
            }
        }

        return $result;
    }

    /**
     * 获取积分消费预览，并提供合并小额积分的功能
     *
     * @param Account $account    账户
     * @param float   $amount     需要消费的金额
     * @param int     $maxRecords 最大记录数，超过此数量将触发小额积分合并
     *
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

    public function save(Transaction $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Transaction $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
