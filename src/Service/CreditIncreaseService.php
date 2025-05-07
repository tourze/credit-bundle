<?php

namespace CreditBundle\Service;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Transaction;
use CreditBundle\Event\IncreasedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\LockServiceBundle\Service\LockService;
use Tourze\Symfony\Async\Attribute\Async;

/**
 * 积分增加服务
 */
#[Autoconfigure(lazy: true, public: true)]
class CreditIncreaseService
{
    public function __construct(
        private readonly TransactionLimitService $limitService,
        private readonly EntityManagerInterface $entityManager,
        private readonly LockService $lockService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
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
        $amount = abs($amount);

        // 检查转入限额
        $this->limitService->checkIncreaseLimit($account, $amount);

        $this->lockService->blockingRun($account, function () use ($account, $amount, $remark, $eventNo, $context, $relationModel, $relationId, $expireTime) {
            $this->entityManager->refresh($account);

            $transaction = new Transaction();
            $transaction->setCurrency($account->getCurrency());
            $transaction->setEventNo($eventNo);
            $transaction->setAccount($account);
            $transaction->setAmount(abs($amount));
            $transaction->setBalance(abs($amount));
            $transaction->setRemark($remark);
            $transaction->setRelationModel($relationModel);
            $transaction->setRelationId($relationId);
            $transaction->setContext($context);
            $transaction->setExpireTime($expireTime);
            $this->entityManager->persist($transaction);

            $account->setEndingBalance($account->getEndingBalance() + $amount);
            $account->setIncreasedAmount($account->getIncreasedAmount() + $amount);
            $this->entityManager->persist($account);

            $this->entityManager->flush();
        });

        // 在这里，我们可以将数据同步给远程
        $event = new IncreasedEvent();
        $event->setAccount($account);
        $event->setRemark($remark);
        $event->setEventNo($eventNo);
        $event->setContext($context);
        $event->setAmount($amount);
        $this->eventDispatcher->dispatch($event);
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
        $this->increase($eventNo, $account, $amount, $remark, $expireTime, $relationModel, $relationId, $context);
    }
}
