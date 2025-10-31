<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use CreditBundle\Entity\ConsumeLog;
use CreditBundle\Entity\Transaction;
use CreditBundle\Exception\ConsumeLogException;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * 消费日志服务
 */
#[Autoconfigure(public: true)]
readonly class ConsumeLogService
{
    public function __construct(
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * 保存消费日志并更新交易记录余额
     *
     * 不考虑并发 - 此方法由上层扣减服务在事务中调用，已在上层加锁
     */
    public function saveConsumeLog(Transaction $record, Transaction $transaction, float $costPer): void
    {
        // 单独一条LOG
        $consumeLog = new ConsumeLog();
        $consumeLog->setCostTransaction($record);
        $consumeLog->setConsumeTransaction($transaction);
        $consumeLog->setAmount((string) $costPer);
        $this->entityManager->persist($consumeLog);

        // 更新数据
        $record->setBalance((string) ((float) $record->getBalance() - $costPer));
        $this->entityManager->persist($record);

        $this->entityManager->flush();
    }

    /**
     * 批量消费积分
     *
     * 不考虑并发 - 此方法由上层扣减服务在事务中调用，已在上层加锁
     */
    public function consumeCredits(Transaction $transaction, float $costAmount): void
    {
        // 使用优化的查询方法，一次性获取所有可能被消费的积分记录
        $consumableRecords = $this->transactionRepository->findConsumableRecords(
            $transaction->getAccount(),
            $costAmount
        );

        if ([] === $consumableRecords) {
            throw new ConsumeLogException('没有可消费的积分记录');
        }

        // 开始消费积分
        $remainingAmount = $costAmount;
        foreach ($consumableRecords as $record) {
            $costPer = min($remainingAmount, floatval($record->getBalance()));
            $remainingAmount -= $costPer;

            // 记录消费日志
            $this->saveConsumeLog($record, $transaction, $costPer);

            if ($remainingAmount <= 0) {
                break;
            }
        }

        if ($remainingAmount > 0) {
            throw new ConsumeLogException('余额不够扣');
        }
    }
}
