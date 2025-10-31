<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Transaction;
use CreditBundle\Event\DecreasedEvent;
use CreditBundle\Exception\CreditInsufficientException;
use CreditBundle\Exception\InvalidAmountException;
use CreditBundle\Exception\TransactionException;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Tourze\LockServiceBundle\Service\LockService;

/**
 * 积分减少服务
 */
#[Autoconfigure(public: true)]
readonly class CreditDecreaseService
{
    public function __construct(
        private AccountService $accountService,
        private ConsumeLogService $consumeLogService,
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager,
        private TransactionLimitService $limitService,
        private EventDispatcherInterface $eventDispatcher,
        private LockService $lockService,
    ) {
    }

    /**
     * 扣减积分
     *
     * @param array<string, mixed>|null $context
     *
     * 不考虑并发 - 此方法通过executeDecrease调用，已在executeDecrease中使用LockService加锁
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
        // 处理远程扣减事件
        if (!$this->processRemoteDecrease($eventNo, $account, $amount, $remark, $context)) {
            return;
        }

        // 验证积分有效性
        $this->validateUserCredit($account);

        // 检查积分扣减数额
        $costAmount = $this->validateAmount($amount);

        // 检查限额
        $this->limitService->checkDecreaseLimit($account, $costAmount);

        // 执行扣减
        $this->executeDecrease($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context, $isExpired);
    }

    /**
     * 回滚积分
     *
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
        // 处理远程回滚事件
        if (!$this->processRemoteDecrease($eventNo, $account, $amount, $remark, $context)) {
            return;
        }

        // 验证积分有效性
        $this->validateUserCredit($account);

        // 检查积分回滚数额
        $costAmount = $this->validateAmount($amount);

        // 查找原始事务
        $oldTransaction = $this->findOriginalTransaction($eventNo, $account, $amount);

        // 执行回滚
        $this->executeRollback($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context, $oldTransaction);
    }

    /**
     * 处理远程扣减事件
     *
     * @param array<string, mixed>|null $context
     *
     * 不考虑并发 - 此方法仅进行事件分发，无共享状态修改
     */
    private function processRemoteDecrease(
        string $eventNo,
        Account $account,
        float $amount,
        ?string $remark,
        ?array $context,
    ): bool {
        $event = new DecreasedEvent();
        $event->setAmount($amount);
        $event->setAccount($account);
        $event->setRemark($remark);
        $event->setEventNo($eventNo);
        $event->setContext($context);
        $this->eventDispatcher->dispatch($event);

        return $event->isLocalExecute();
    }

    /**
     * 验证用户积分是否充足
     *
     * 不考虑并发 - 此方法仅进行读取验证，调用方会在更高层加锁
     */
    private function validateUserCredit(Account $account): void
    {
        if (null !== $account->getUser()) {
            $validAmount = $this->accountService->getValidAmount($account);
            if ($validAmount <= 0) {
                throw new CreditInsufficientException($account . ' 积分不足');
            }
        }
    }

    /**
     * 验证积分数额合法性
     */
    private function validateAmount(float $amount): float
    {
        $costAmount = abs($amount);
        if ($costAmount <= 0) {
            throw new InvalidAmountException('扣减积分异常');
        }

        return $costAmount;
    }

    /**
     * 执行扣减操作
     *
     * @param array<string, mixed>|null $context
     */
    private function executeDecrease(
        Account $account,
        string $eventNo,
        float $costAmount,
        ?string $remark,
        ?string $relationModel,
        ?string $relationId,
        ?array $context,
        bool $isExpired,
    ): void {
        $this->lockService->blockingRun($account, function () use ($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context, $isExpired): void {
            $this->entityManager->getConnection()->transactional(function () use ($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context, $isExpired): void {
                $this->entityManager->refresh($account);

                // 创建交易记录
                $transaction = $this->createDecreaseTransaction($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context);

                // 更新账户余额
                $this->updateAccountBalance($account, $costAmount, $isExpired);

                $this->entityManager->flush();

                // 消费积分记录
                $this->consumeLogService->consumeCredits($transaction, $costAmount);
            });
        });
    }

    /**
     * 创建扣减交易记录
     *
     * @param array<string, mixed>|null $context
     *
     * 不考虑并发 - 此方法仅用于创建Transaction对象，无状态修改
     */
    private function createDecreaseTransaction(
        Account $account,
        string $eventNo,
        float $costAmount,
        ?string $remark,
        ?string $relationModel,
        ?string $relationId,
        ?array $context,
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setCurrency($account->getCurrency());
        $transaction->setEventNo($eventNo);
        $transaction->setAccount($account);
        $transaction->setAmount((string) (-$costAmount));
        $transaction->setRemark($remark);
        $transaction->setRelationModel($relationModel);
        $transaction->setRelationId($relationId);
        $transaction->setContext($context);
        $this->entityManager->persist($transaction);

        return $transaction;
    }

    /**
     * 更新账户余额
     *
     * 不考虑并发 - 上层decrease方法已通过LockService加锁控制并发
     */
    private function updateAccountBalance(Account $account, float $costAmount, bool $isExpired): void
    {
        $account->setEndingBalance((string) ((float) $account->getEndingBalance() - $costAmount));
        $account->setDecreasedAmount((string) ((float) $account->getDecreasedAmount() + $costAmount));
        if ($isExpired) {
            $account->setExpiredAmount((string) ((float) $account->getExpiredAmount() + $costAmount));
        }
        $this->entityManager->persist($account);
    }

    /**
     * 查找原始事务
     */
    private function findOriginalTransaction(string $eventNo, Account $account, float $amount): Transaction
    {
        $oldTransaction = $this->transactionRepository->findOneBy(['eventNo' => $eventNo, 'account' => $account]);
        if (null === $oldTransaction) {
            throw new TransactionException('未查询到对应事件积分');
        }

        if ($oldTransaction->getBalance() !== $amount) {
            throw new TransactionException('数值异常');
        }

        return $oldTransaction;
    }

    /**
     * 执行回滚操作
     *
     * @param array<string, mixed>|null $context
     */
    private function executeRollback(
        Account $account,
        string $eventNo,
        float $costAmount,
        ?string $remark,
        ?string $relationModel,
        ?string $relationId,
        ?array $context,
        Transaction $oldTransaction,
    ): void {
        $this->lockService->blockingRun($account, function () use ($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context, $oldTransaction): void {
            $this->entityManager->getConnection()->transactional(function () use ($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context, $oldTransaction): void {
                $this->entityManager->refresh($account);

                // 创建回滚交易记录
                $transaction = $this->createRollbackTransaction($account, $eventNo, $costAmount, $remark, $relationModel, $relationId, $context);

                // 更新账户余额
                $this->updateRollbackAccountBalance($account, $costAmount);

                $this->entityManager->flush();

                // 记录消费日志
                $this->consumeLogService->saveConsumeLog($oldTransaction, $transaction, (float) $oldTransaction->getBalance());
            });
        });
    }

    /**
     * 创建回滚交易记录
     *
     * @param array<string, mixed>|null $context
     */
    private function createRollbackTransaction(
        Account $account,
        string $eventNo,
        float $costAmount,
        ?string $remark,
        ?string $relationModel,
        ?string $relationId,
        ?array $context,
    ): Transaction {
        $transaction = new Transaction();
        $transaction->setCurrency($account->getCurrency());
        $transaction->setEventNo("{$eventNo}_rollback");
        $transaction->setAccount($account);
        $transaction->setAmount((string) $costAmount);
        $transaction->setRemark($remark);
        $transaction->setRelationModel($relationModel);
        $transaction->setRelationId($relationId);
        $transaction->setContext($context);
        $this->entityManager->persist($transaction);

        return $transaction;
    }

    /**
     * 更新回滚账户余额
     *
     * 不考虑并发 - 上层decrease方法已通过LockService加锁控制并发
     */
    private function updateRollbackAccountBalance(Account $account, float $costAmount): void
    {
        $account->setEndingBalance((string) ((float) $account->getEndingBalance() - $costAmount));
        $account->setDecreasedAmount((string) ((float) $account->getDecreasedAmount() + $costAmount));
        $this->entityManager->persist($account);
    }
}
