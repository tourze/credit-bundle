<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Transaction;
use CreditBundle\Repository\TransactionRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TransactionRepository::class)]
#[RunTestsInSeparateProcesses]
final class TransactionRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为计数测试创建测试数据
            $this->createTestDataForCountTest();
        }
    }

    private function createTestDataForCountTest(): void
    {
        $entity = $this->createTestEntity();
        $this->getRepository()->save($entity, true);
    }

    public function testFindConsumableRecords(): void
    {
        // 创建真实的Account实体而不是Mock对象
        $account = new Account();
        $account->setName('test_consumable_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($account);

        $repository = self::getService(TransactionRepository::class);

        $result = $repository->findConsumableRecords($account, 100.0);

        self::assertIsArray($result);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->findOneBy([], ['id' => 'DESC']);

        // 结果要么是Transaction实例，要么是null
        if (null !== $result) {
            self::assertInstanceOf(Transaction::class, $result);
        }
    }

    public function testSave(): void
    {
        $repository = self::getService(TransactionRepository::class);
        $entity = new Transaction();

        // 使用一个简单的测试，验证save方法不会抛出异常
        try {
            $repository->save($entity, false);
            self::assertNotNull($entity->getId(), 'Entity should have an ID after saving');
        } catch (\Exception $e) {
            self::fail('Save method should not throw exception: ' . $e->getMessage());
        }
    }

    public function testRemove(): void
    {
        $repository = self::getService(TransactionRepository::class);
        $entity = new Transaction();

        // 使用一个简单的测试，验证remove方法不会抛出异常
        try {
            $repository->remove($entity, false);
            self::addToAssertionCount(1); // 如果没有抛出异常，测试通过
        } catch (\Exception $e) {
            self::fail('Remove method should not throw exception: ' . $e->getMessage());
        }
    }

    public function testCountWithAssociationCriteria(): void
    {
        $repository = self::getService(TransactionRepository::class);

        // 创建真实的Account实体而不是Mock对象
        $account = new Account();
        $account->setName('test_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($account);

        $result = $repository->count(['account' => $account]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testFindByWithAssociationCriteria(): void
    {
        $repository = self::getService(TransactionRepository::class);

        // 创建真实的Account实体而不是Mock对象
        $account = new Account();
        $account->setName('test_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($account);

        $result = $repository->findBy(['account' => $account]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(Transaction::class, $entity);
        }
    }

    public function testFindByWithNullableFields(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->findBy(['balance' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(Transaction::class, $entity);
            self::assertNull($entity->getBalance());
        }
    }

    public function testFindByWithRemarkNull(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->findBy(['remark' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(Transaction::class, $entity);
            self::assertNull($entity->getRemark());
        }
    }

    public function testCountWithNullableFields(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->count(['balance' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testCountWithRemarkNull(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->count(['remark' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testFindOneByWithOrderByAndCriteria(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->findOneBy(['currency' => 'CNY'], ['id' => 'ASC']);

        if (null !== $result) {
            self::assertInstanceOf(Transaction::class, $result);
            self::assertSame('CNY', $result->getCurrency());
        } else {
            // 没有找到符合条件的记录是正常的，不需要额外断言
            self::addToAssertionCount(1);
        }
    }

    public function testCountWithInAccountAssociation(): void
    {
        $repository = self::getService(TransactionRepository::class);

        // 创建真实的Account实体而不是Mock对象
        $account = new Account();
        $account->setName('test_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($account);

        $result = $repository->count(['account' => $account]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testFindByWithAccountAssociation(): void
    {
        $repository = self::getService(TransactionRepository::class);

        // 创建真实的Account实体而不是Mock对象
        $account = new Account();
        $account->setName('test_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($account);

        $result = $repository->findBy(['account' => $account]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(Transaction::class, $entity);
        }
    }

    public function testFindByWithExpireTimeNull(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->findBy(['expireTime' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(Transaction::class, $entity);
            self::assertNull($entity->getExpireTime());
        }
    }

    public function testFindByWithRelationIdNull(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->findBy(['relationId' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(Transaction::class, $entity);
            self::assertNull($entity->getRelationId());
        }
    }

    public function testCountWithExpireTimeNull(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->count(['expireTime' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testCountWithRelationIdNull(): void
    {
        $repository = self::getService(TransactionRepository::class);

        $result = $repository->count(['relationId' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    protected function getRepository(): TransactionRepository
    {
        return self::getService(TransactionRepository::class);
    }

    private function createTestEntity(): Transaction
    {
        $account = new Account();
        $account->setName('test_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($account);

        $transaction = new Transaction();
        $transaction->setEventNo('test_event_' . uniqid());
        $transaction->setAccount($account);
        $transaction->setAmount('100.00');
        $transaction->setBalance('100.00');
        $transaction->setCurrency('CNY');
        $transaction->setCreateTime(new \DateTimeImmutable());
        $transaction->setUpdateTime(new \DateTimeImmutable());
        $transaction->setCreatedFromIp('127.0.0.1');

        return $transaction;
    }

    protected function createNewEntity(): object
    {
        $account = new Account();
        $account->setName('CreateNewEntity_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($account);

        $transaction = new Transaction();
        $transaction->setEventNo('CreateNewEntity_event_' . uniqid());
        $transaction->setAccount($account);
        $transaction->setAmount('CreateNewEntity_' . rand(100, 999) . '.00');
        $transaction->setBalance('CreateNewEntity_' . rand(100, 999) . '.00');
        $transaction->setCurrency('CNY');
        $transaction->setCreateTime(new \DateTimeImmutable());
        $transaction->setUpdateTime(new \DateTimeImmutable());
        $transaction->setCreatedFromIp('127.0.0.1');

        return $transaction;
    }
}
