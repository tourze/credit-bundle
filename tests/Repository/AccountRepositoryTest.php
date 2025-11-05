<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Repository\AccountRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;
use Tourze\UserServiceContracts\UserManagerInterface;

/**
 * @internal
 */
#[CoversClass(AccountRepository::class)]
#[RunTestsInSeparateProcesses]
final class AccountRepositoryTest extends AbstractRepositoryTestCase
{
    private AccountRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(AccountRepository::class);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为计数测试创建测试数据
            $this->createTestDataForCountTest();
        }
    }

    private function createTestDataForCountTest(): void
    {
        $account = new Account();
        $account->setName('Test Data Fixture Account ' . time() . rand(1000, 9999));
        $account->setCurrency('CNY');
        $this->repository->save($account, true);
    }

    public function testSave(): void
    {
        $account = new Account();
        $accountName = 'Test Account ' . time() . rand(1000, 9999);
        $account->setName($accountName);
        $account->setCurrency('CNY');

        $this->repository->save($account, true);

        self::assertGreaterThan(0, $account->getId());
        self::assertEquals($accountName, $account->getName());
        self::assertEquals('CNY', $account->getCurrency());
    }

    public function testRemove(): void
    {
        // 创建一个账户
        $account = new Account();
        $account->setName('Test Account to Remove ' . time() . rand(1000, 9999));
        $account->setCurrency('USD');
        $this->repository->save($account, true);

        $accountId = $account->getId();

        // 删除账户
        $this->repository->remove($account, true);

        // 验证账户已被删除
        $deletedAccount = $this->repository->find($accountId);
        self::assertNull($deletedAccount);
    }

    public function testFindOneByWithOrderBy(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 强制清理Account表
        $entityManager = self::getEntityManager();
        $entityManager->createQuery('DELETE FROM CreditBundle\Entity\Account')->execute();

        // 创建多个相同币种的账户
        $account1 = new Account();
        $nameA = 'Account A ' . time() . rand(1000, 9999);
        $account1->setName($nameA);
        $account1->setCurrency('CNY');
        $this->repository->save($account1, false);

        $account2 = new Account();
        $nameZ = 'Account Z ' . time() . rand(1000, 9999);
        $account2->setName($nameZ);
        $account2->setCurrency('CNY');
        $this->repository->save($account2, true);

        // 按名称正序查找第一个
        $firstAccount = $this->repository->findOneBy(['currency' => 'CNY'], ['name' => 'ASC']);
        self::assertNotNull($firstAccount);
        self::assertInstanceOf(Account::class, $firstAccount);
        self::assertEquals($nameA, $firstAccount->getName());

        // 按名称倒序查找第一个
        $lastAccount = $this->repository->findOneBy(['currency' => 'CNY'], ['name' => 'DESC']);
        self::assertNotNull($lastAccount);
        self::assertInstanceOf(Account::class, $lastAccount);
        self::assertEquals($nameZ, $lastAccount->getName());
    }

    public function testCountWithJoinQuery(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建一个有用户的账户
        $user = self::getService(UserManagerInterface::class)->createUser('testuser', password: 'password', roles: ['ROLE_USER']);
        // 显式persist user以避免级联persist错误
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $account = new Account();
        $account->setName('User Account');
        $account->setCurrency('CNY');
        $account->setUser($user);
        $this->repository->save($account, false);

        // 创建一个没有用户的账户
        $account2 = new Account();
        $account2->setName('No User Account');
        $account2->setCurrency('USD');
        $this->repository->save($account2, true);

        // 计算有用户的账户数量
        $userAccountCount = $this->repository->count(['user' => $user]);
        self::assertEquals(1, $userAccountCount);
    }

    public function testFindByWithJoinQuery(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建用户和关联账户
        $user = self::getService(UserManagerInterface::class)->createUser('testuser', password: 'password', roles: ['ROLE_USER']);
        // 显式persist user以避免级联persist错误
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $account = new Account();
        $account->setName('User Linked Account');
        $account->setCurrency('CNY');
        $account->setUser($user);
        $this->repository->save($account, true);

        // 根据用户查找账户
        $userAccounts = $this->repository->findBy(['user' => $user]);

        self::assertCount(1, $userAccounts);
        self::assertEquals('User Linked Account', $userAccounts[0]->getName());
        self::assertEquals('CNY', $userAccounts[0]->getCurrency());
        self::assertEquals($user, $userAccounts[0]->getUser());
    }

    public function testFindByWithNullUser(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 强制清理Account表
        $entityManager = self::getEntityManager();
        $entityManager->createQuery('DELETE FROM CreditBundle\Entity\Account')->execute();

        // 创建没有用户的账户
        $account = new Account();
        $account->setName('No User Account');
        $account->setCurrency('CNY');
        $this->repository->save($account, true);

        // 查找没有用户的账户
        $noUserAccounts = $this->repository->findBy(['user' => null]);

        self::assertCount(1, $noUserAccounts);
        self::assertEquals('No User Account', $noUserAccounts[0]->getName());
        self::assertEquals('CNY', $noUserAccounts[0]->getCurrency());
        self::assertNull($noUserAccounts[0]->getUser());
    }

    public function testCountWithNullUser(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 强制清理Account表
        $entityManager = self::getEntityManager();
        $entityManager->createQuery('DELETE FROM CreditBundle\Entity\Account')->execute();

        // 创建没有用户的账户
        $account = new Account();
        $account->setName('No User Account');
        $account->setCurrency('CNY');
        $this->repository->save($account, true);

        // 计算没有用户的账户数量
        $noUserCount = $this->repository->count(['user' => null]);
        self::assertEquals(1, $noUserCount);
    }

    public function testFindByWithNullEndingBalance(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建有余额的账户
        $account1 = new Account();
        $account1->setName('Account with Balance');
        $account1->setCurrency('CNY');
        $account1->setEndingBalance('100.00');
        $this->repository->save($account1, false);

        // 创建没有余额的账户（默认构造函数设为0，需要手动设为null）
        $account2 = new Account();
        $account2->setName('Account without Balance');
        $account2->setCurrency('USD');
        $account2->setEndingBalance(null);
        $this->repository->save($account2, true);

        // 查找没有余额的账户
        $noBalanceAccounts = $this->repository->findBy(['endingBalance' => null]);

        self::assertCount(1, $noBalanceAccounts);
        self::assertEquals('Account without Balance', $noBalanceAccounts[0]->getName());
        self::assertEquals('USD', $noBalanceAccounts[0]->getCurrency());
        self::assertNull($noBalanceAccounts[0]->getEndingBalance());
    }

    public function testCountWithNullEndingBalance(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建没有余额的账户
        $account = new Account();
        $account->setName('No Balance Account');
        $account->setCurrency('CNY');
        $account->setEndingBalance(null);
        $this->repository->save($account, true);

        // 计算没有余额的账户数量
        $noBalanceCount = $this->repository->count(['endingBalance' => null]);
        self::assertEquals(1, $noBalanceCount);
    }

    protected function getRepository(): AccountRepository
    {
        return self::getService(AccountRepository::class);
    }

    public function testFindByWithNullIncreasedAmount(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建有增加金额的账户
        $account1 = new Account();
        $account1->setName('Account with Increased Amount');
        $account1->setCurrency('CNY');
        $account1->setIncreasedAmount('50.00');
        $this->repository->save($account1, false);

        // 创建没有增加金额的账户
        $account2 = new Account();
        $account2->setName('Account without Increased Amount');
        $account2->setCurrency('USD');
        $account2->setIncreasedAmount(null);
        $this->repository->save($account2, true);

        // 查找没有增加金额的账户
        $noIncreasedAmountAccounts = $this->repository->findBy(['increasedAmount' => null]);

        self::assertCount(1, $noIncreasedAmountAccounts);
        self::assertEquals('Account without Increased Amount', $noIncreasedAmountAccounts[0]->getName());
        self::assertEquals('USD', $noIncreasedAmountAccounts[0]->getCurrency());
        self::assertNull($noIncreasedAmountAccounts[0]->getIncreasedAmount());
    }

    public function testCountWithNullIncreasedAmount(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建没有增加金额的账户
        $account = new Account();
        $account->setName('No Increased Amount Account');
        $account->setCurrency('CNY');
        $account->setIncreasedAmount(null);
        $this->repository->save($account, true);

        // 计算没有增加金额的账户数量
        $noIncreasedAmountCount = $this->repository->count(['increasedAmount' => null]);
        self::assertEquals(1, $noIncreasedAmountCount);
    }

    public function testFindByWithNullDecreasedAmount(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建没有减少金额的账户
        $account = new Account();
        $account->setName('No Decreased Amount Account');
        $account->setCurrency('CNY');
        $account->setDecreasedAmount(null);
        $this->repository->save($account, true);

        // 查找没有减少金额的账户
        $noDecreasedAmountAccounts = $this->repository->findBy(['decreasedAmount' => null]);

        self::assertCount(1, $noDecreasedAmountAccounts);
        self::assertEquals('No Decreased Amount Account', $noDecreasedAmountAccounts[0]->getName());
        self::assertEquals('CNY', $noDecreasedAmountAccounts[0]->getCurrency());
        self::assertNull($noDecreasedAmountAccounts[0]->getDecreasedAmount());
    }

    public function testCountWithNullDecreasedAmount(): void
    {
        // 清理数据库
        self::cleanDatabase();

        // 创建没有减少金额的账户
        $account = new Account();
        $account->setName('No Decreased Amount Account');
        $account->setCurrency('CNY');
        $account->setDecreasedAmount(null);
        $this->repository->save($account, true);

        // 计算没有减少金额的账户数量
        $noDecreasedAmountCount = $this->repository->count(['decreasedAmount' => null]);
        self::assertEquals(1, $noDecreasedAmountCount);
    }

    protected function createNewEntity(): object
    {
        $account = new Account();
        $account->setName('CreateNewEntity Account ' . uniqid());
        $account->setCurrency('CNY');

        return $account;
    }
}
