<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use CreditBundle\Entity\Account;
use CreditBundle\Event\GetAccountValidPointEvent;
use CreditBundle\Exception\AccountCreationException;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\LockServiceBundle\Service\LockService;

#[WithMonologChannel(channel: 'credit')]
readonly class AccountService
{
    public function __construct(
        private AccountRepository $accountRepository,
        private LoggerInterface $logger,
        private EventDispatcherInterface $eventDispatcher,
        private TransactionRepository $transactionRepository,
        private EntityManagerInterface $entityManager,
        private LockService $lockService,
    ) {
    }

    public function getSystemAccount(string $currency): Account
    {
        return $this->getAccountByName('system', $currency);
    }

    /**
     * 获取指定用户的指定账号
     */
    public function getAccountByUser(UserInterface $user, string $currency): Account
    {
        $account = $this->accountRepository->findOneBy([
            'user' => $user,
            'currency' => $currency,
        ]);
        if (null === $account) {
            $account = new Account();
            $account->setUser($user);
            $account->setCurrency($currency);
            $userIdentifier = $user->getUserIdentifier();
            $account->setName("用户{$userIdentifier}的{$currency}账户");
            $this->entityManager->persist($account);
            $this->entityManager->flush();
        }

        return $account;
    }

    /**
     * 获取指定名称的指定账号
     */
    public function getAccountByName(string $name, string $currency, ?UserInterface $user = null): Account
    {
        $condition = $this->buildAccountCondition($name, $currency, $user);

        $account = $this->accountRepository->findOneBy($condition);
        if (null !== $account) {
            return $account;
        }

        return $this->createAccountWithLock($name, $currency, $user, $condition);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAccountCondition(string $name, string $currency, ?UserInterface $user): array
    {
        $condition = [
            'name' => $name,
            'currency' => $currency,
        ];
        if (null !== $user) {
            $condition['user'] = $user;
        }

        return $condition;
    }

    /**
     * @param array<string, mixed> $condition
     */
    private function createAccountWithLock(string $name, string $currency, ?UserInterface $user, array $condition): Account
    {
        $lockName = $this->buildLockName($name, $currency, $user);
        $lockAcquired = $this->lockService->acquireLock($lockName);

        if (!$lockAcquired->isAcquired()) {
            return $this->handleLockFailure($condition);
        }

        try {
            return $this->createAccountIfNotExists($name, $currency, $user, $condition);
        } finally {
            $this->lockService->releaseLock($lockName);
        }
    }

    private function buildLockName(string $name, string $currency, ?UserInterface $user): string
    {
        $lockName = "account_creation_{$name}_{$currency}";
        if (null !== $user) {
            $lockName .= "_{$user->getUserIdentifier()}";
        }

        return $lockName;
    }

    /**
     * @param array<string, mixed> $condition
     */
    private function handleLockFailure(array $condition): Account
    {
        $this->logger->warning('获取账号创建锁失败', $condition);

        $account = $this->accountRepository->findOneBy($condition);
        if (null === $account) {
            throw new AccountCreationException('无法创建账号，获取锁失败');
        }

        return $account;
    }

    /**
     * @param array<string, mixed> $condition
     */
    private function createAccountIfNotExists(string $name, string $currency, ?UserInterface $user, array $condition): Account
    {
        $account = $this->accountRepository->findOneBy($condition);
        if (null !== $account) {
            return $account;
        }

        $account = new Account();
        $account->setName($name);
        $account->setCurrency($currency);
        if (null !== $user) {
            $account->setUser($user);
        }

        $this->entityManager->persist($account);
        $this->entityManager->flush();

        $this->logger->info('成功创建账号', [
            'currency' => $currency,
            'name' => $name,
            'user' => $user?->getUserIdentifier(),
        ]);

        return $account;
    }

    /**
     * 获取可用余额
     */
    public function getValidAmount(Account $account): float
    {
        $event = new GetAccountValidPointEvent();
        $event->setAccount($account);
        $this->eventDispatcher->dispatch($event);
        if (null !== $event->getResult()) {
            return $event->getResult();
        }

        return floatval($account->getEndingBalance());
    }

    /**
     * 获取所有过期积分总和
     */
    public function getExpireAmount(Account $account): float
    {
        return floatval($account->getExpiredAmount());
    }

    /**
     * 计算指定时间区间的可能过期积分总和
     */
    public function getExpiringAmount(Account $account, \DateTimeInterface $startTime, \DateTimeInterface $endTime): float
    {
        $rs = $this->transactionRepository->createQueryBuilder('a')
            ->select('SUM(a.balance)')
            ->where('a.account = :account')
            ->andWhere('a.expireTime BETWEEN :start AND :end')
            ->andWhere('a.balance > 0')
            ->setParameter('account', $account)
            ->setParameter('start', $startTime)
            ->setParameter('end', $endTime)
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return floatval($rs);
    }

    /**
     * （实时）计算指定用户的总积分，包含历史获得积分
     *
     * 不考虑并发 - 此方法仅进行数据读取，无状态修改
     */
    public function sumIncreasedAmount(Account $account): float
    {
        return floatval($account->getIncreasedAmount());
    }
}
