<?php

namespace CreditBundle\Service;

use CreditBundle\Entity\Account;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SnowflakeBundle\Service\Snowflake;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;

/**
 * 交易服务（基于Account）
 */
#[Autoconfigure(lazy: true, public: true)]
class TransactionService
{
    public function __construct(
        private readonly Snowflake $snowflake,
        private readonly CreditIncreaseService $increaseService,
        private readonly CreditDecreaseService $decreaseService,
    ) {
    }

    /**
     * 简易转账处理
     */
    public function transfer(Account $fromAccount, Account $toAccount, float $amount, ?string $remark = null, ?array $context = null): ?string
    {
        $eventNo = 'S' . $this->snowflake->id();

        // 转出
        if ($fromAccount->getUser() !== null) {
            $this->decreaseService->decrease(
                $eventNo,
                $fromAccount,
                $amount,
                $remark,
                context: $context,
            );
        }

        // 转入
        if ($toAccount->getUser() !== null) {
            $this->increaseService->increase(
                $eventNo,
                $toAccount,
                $amount,
                $remark,
                context: $context,
            );
        }

        return $eventNo;
    }

    /**
     * 加积分
     */
    public function increase(
        string $eventNo,
        Account $account,
        float $amount,
        ?string $remark = null,
        ?\DateTimeInterface $expireTime = null,
        ?string $relationModel = null,
        ?string $relationId = null,
        ?array $context = null,
    ): void {
        $this->increaseService->increase(
            $eventNo,
            $account,
            $amount,
            $remark,
            $expireTime,
            $relationModel,
            $relationId,
            $context
        );
    }

    /**
     * 异步转账
     */
    #[Async]
    public function asyncIncrease(
        string $eventNo,
        Account $account,
        float $amount,
        ?string $remark = null,
        ?\DateTimeInterface $expireTime = null,
        ?string $relationModel = null,
        ?string $relationId = null,
        ?array $context = null,
    ): void {
        $this->increaseService->asyncIncrease(
            $eventNo,
            $account,
            $amount,
            $remark,
            $expireTime,
            $relationModel,
            $relationId,
            $context
        );
    }

    /**
     * 扣减积分
     */
    public function decrease(
        string $eventNo,
        Account $account,
        float $amount,
        ?string $remark = null,
        ?string $relationModel = null,
        ?string $relationId = null,
        ?array $context = null,
        bool $isExpired = false,
    ): void {
        $this->decreaseService->decrease(
            $eventNo,
            $account,
            $amount,
            $remark,
            $relationModel,
            $relationId,
            $context,
            $isExpired
        );
    }

    /**
     * 回滚积分
     */
    public function rollback(
        string $eventNo,
        Account $account,
        float $amount,
        ?string $remark = null,
        ?string $relationModel = null,
        ?string $relationId = null,
        ?array $context = null,
        bool $isExpired = false,
    ): void {
        $this->decreaseService->rollback(
            $eventNo,
            $account,
            $amount,
            $remark,
            $relationModel,
            $relationId,
            $context,
            $isExpired
        );
    }
}
