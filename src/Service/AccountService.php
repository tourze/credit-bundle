<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Currency;
use CreditBundle\Event\GetAccountValidPointEvent;
use CreditBundle\Repository\AccountRepository;
use CreditBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Tourze\LockServiceBundle\Service\LockService;

#[Autoconfigure(lazy: true)]
class AccountService
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
        private readonly LoggerInterface $logger,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly TransactionRepository $transactionRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly LockService $lockService,
    ) {
    }

    public function getSystemAccount(Currency $currency): Account
    {
        return $this->getAccountByName('system', $currency);
    }

    /**
     * 获取指定用户的指定账号
     */
    public function getAccountByUser(UserInterface $user, Currency $currency): Account
    {
        $account = $this->accountRepository->findOneBy([
            'user' => $user,
            'currency' => $currency,
        ]);
        if ($account === null) {
            $account = new Account();
            $account->setUser($user);
            $account->setCurrency($currency);
            $id = method_exists($user, 'getId') ? $user->getId() : $user->getUserIdentifier();
            $account->setName("用户{$id}的{$currency->getName()}账户");
            $this->entityManager->persist($account);
            $this->entityManager->flush();
        }

        return $account;
    }

    /**
     * 获取指定名称的指定账号
     */
    public function getAccountByName(string $name, Currency $currency, ?UserInterface $user = null): Account
    {
        $condition = [
            'name' => $name,
            'currency' => $currency,
        ];
        if ($user !== null) {
            $condition['user'] = $user;
        }

        // 先尝试查找账号是否存在
        $account = $this->accountRepository->findOneBy($condition);
        if ($account !== null) {
            return $account;
        }

        // 使用锁确保在并发环境下只有一个进程创建账号
        $lockName = "account_creation_{$name}_{$currency->getId()}" . ($user !== null ? "_{$user->getUserIdentifier()}" : '');
        $lockAcquired = $this->lockService->acquireLock($lockName);

        if (!$lockAcquired->isAcquired()) {
            $this->logger->warning('获取账号创建锁失败', [
                'currency' => $currency,
                'name' => $name,
                'user' => $user,
            ]);
            // 锁获取失败，再次尝试查找账号（可能已被其他进程创建）
            $account = $this->accountRepository->findOneBy($condition);
            if ($account === null) {
                throw new \RuntimeException('无法创建账号，获取锁失败');
            }
            return $account;
        }

        try {
            // 获得锁后再次检查账号是否存在（双重检查锁定模式）
            $account = $this->accountRepository->findOneBy($condition);
            if ($account === null) {
                // 创建新账号
                $account = new Account();
                $account->setName($name);
                $account->setCurrency($currency);
                if ($user !== null) {
                    $account->setUser($user);
                }
                $this->entityManager->persist($account);
                $this->entityManager->flush();
                $this->logger->info('成功创建账号', [
                    'currency' => $currency->getName(),
                    'name' => $name,
                    'user' => $user?->getUserIdentifier(),
                ]);
            }
        } finally {
            // 无论成功失败，最后都释放锁
            $this->lockService->releaseLock($lockName);
        }

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
            ->getSingleScalarResult();

        return floatval($rs);
    }

    /**
     * （实时）计算指定用户的总积分，包含历史获得积分
     */
    public function sumIncreasedAmount(Account $account): float
    {
        return floatval($account->getIncreasedAmount());
    }
}
