<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use CreditBundle\Entity\Account;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\SnowflakeBundle\Service\Snowflake;
use Tourze\Symfony\AopAsyncBundle\Attribute\Async;

/**
 * 交易服务（基于Account）
 */
#[Autoconfigure(public: true)]
class TransactionService
{
    public function __construct(
        private Snowflake $snowflake,
        private CreditIncreaseService $increaseService,
        private CreditDecreaseService $decreaseService,
    ) {
    }

    /**
     * 简易转账处理
     * @param array<string, mixed>|null $context
     */
    public function transfer(Account $fromAccount, Account $toAccount, float $amount, ?string $remark = null, ?array $context = null): ?string
    {
        $eventNo = 'S' . $this->snowflake->id();

        // 转出
        if (null !== $fromAccount->getUser()) {
            $this->decreaseService->decrease(
                $eventNo,
                $fromAccount,
                $amount,
                $remark,
                context: $context,
            );
        }

        // 转入
        if (null !== $toAccount->getUser()) {
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
     *
     * 不考虑并发 - 委托给CreditIncreaseService，该服务内部已通过LockService加锁控制并发
     * @param array<string, mixed>|null $context
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
     *
     * 不考虑并发 - 委托给CreditIncreaseService，该服务内部已通过LockService加锁控制并发
     * @param array<string, mixed>|null $context
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
     *
     * 不考虑并发 - 委托给CreditDecreaseService，该服务内部已通过LockService加锁控制并发
     * @param array<string, mixed>|null $context
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
     * @param array<string, mixed>|null $context
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
