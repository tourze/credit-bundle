<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\ConsumeLog;
use CreditBundle\Entity\Transaction;
use CreditBundle\Repository\ConsumeLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(ConsumeLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class ConsumeLogRepositoryTest extends AbstractRepositoryTestCase
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
        $this->getRepository()->save($entity);
    }

    public function testSave(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        self::assertNotNull($entity->getId());

        $found = $repository->find($entity->getId());
        self::assertInstanceOf(ConsumeLog::class, $found);
        self::assertEquals($entity->getAmount(), $found->getAmount());
    }

    public function testRemove(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);
        $id = $entity->getId();
        self::assertNotNull($id);

        $repository->remove($entity);

        $found = $repository->find($id);
        self::assertNull($found);
    }

    public function testFindOneByWithOrderBy(): void
    {
        $repository = $this->getRepository();
        $entity1 = $this->createTestEntity();
        $entity1->setAmount('25.00');
        $entity2 = $this->createTestEntity();
        $entity2->setAmount('75.00');

        $repository->save($entity1);
        $repository->save($entity2);

        $result = $repository->findOneBy([], ['amount' => 'DESC']);

        self::assertInstanceOf(ConsumeLog::class, $result);
        self::assertEquals('75.00', $result->getAmount());
    }

    public function testFindByWithCostTransactionAssociation(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $results = $repository->findBy(['costTransaction' => $entity->getCostTransaction()]);

        self::assertIsArray($results);
        self::assertGreaterThanOrEqual(1, count($results));
        self::assertContainsOnlyInstancesOf(ConsumeLog::class, $results);
    }

    public function testFindByWithConsumeTransactionAssociation(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $results = $repository->findBy(['consumeTransaction' => $entity->getConsumeTransaction()]);

        self::assertIsArray($results);
        self::assertGreaterThanOrEqual(1, count($results));
        self::assertContainsOnlyInstancesOf(ConsumeLog::class, $results);
    }

    public function testCountWithCostTransactionAssociation(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $count = $repository->count(['costTransaction' => $entity->getCostTransaction()]);

        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithNullCostTransaction(): void
    {
        $repository = $this->getRepository();

        $results = $repository->findBy(['costTransaction' => null]);

        self::assertIsArray($results);
    }

    public function testFindByWithNullConsumeTransaction(): void
    {
        $repository = $this->getRepository();

        $results = $repository->findBy(['consumeTransaction' => null]);

        self::assertIsArray($results);
    }

    public function testCountWithNullCostTransaction(): void
    {
        $repository = $this->getRepository();

        $count = $repository->count(['costTransaction' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithNullConsumeTransaction(): void
    {
        $repository = $this->getRepository();

        $count = $repository->count(['consumeTransaction' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithConsumeTransactionAssociation(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $count = $repository->count(['consumeTransaction' => $entity->getConsumeTransaction()]);

        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testCountByAssociationCostTransactionShouldReturnCorrectNumber(): void
    {
        $repository = $this->getRepository();
        $account = $this->createAccount();
        $costTransaction = $this->createTransaction($account);

        for ($i = 0; $i < 3; ++$i) {
            $entity = $this->createTestEntity();
            $entity->setCostTransaction($costTransaction);
            $repository->save($entity);
        }

        $count = $repository->count(['costTransaction' => $costTransaction]);

        self::assertGreaterThanOrEqual(3, $count);
    }

    public function testFindOneByAssociationCostTransactionShouldReturnMatchingEntity(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $result = $repository->findOneBy(['costTransaction' => $entity->getCostTransaction()]);

        self::assertInstanceOf(ConsumeLog::class, $result);
        self::assertNotNull($result->getCostTransaction());
        self::assertNotNull($entity->getCostTransaction());
        self::assertEquals($entity->getCostTransaction()->getId(), $result->getCostTransaction()->getId());
    }

    protected function getRepository(): ConsumeLogRepository
    {
        return self::getService(ConsumeLogRepository::class);
    }

    private function createTestEntity(): ConsumeLog
    {
        $account = $this->createAccount();

        $costTransaction = $this->createTransaction($account);
        $consumeTransaction = $this->createTransaction($account);

        $entity = new ConsumeLog();
        $entity->setCostTransaction($costTransaction);
        $entity->setConsumeTransaction($consumeTransaction);
        $entity->setAmount('50.00');
        $entity->setCreateTime(new \DateTimeImmutable());
        $entity->setCreatedFromIp('127.0.0.1');

        return $entity;
    }

    private function createAccount(): Account
    {
        $account = new Account();
        $account->setName('test_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');

        $this->persistAndFlush($account);

        return $account;
    }

    private function createTransaction(Account $account): Transaction
    {
        $transaction = new Transaction();
        $transaction->setEventNo('test_event_' . uniqid());
        $transaction->setAccount($account);
        $transaction->setAmount('100.00');
        $transaction->setBalance('100.00');
        $transaction->setCurrency('CNY');
        $transaction->setCreateTime(new \DateTimeImmutable());
        $transaction->setUpdateTime(new \DateTimeImmutable());
        $transaction->setCreatedFromIp('127.0.0.1');

        $this->persistAndFlush($transaction);

        return $transaction;
    }

    protected function createNewEntity(): object
    {
        $account = $this->createAccount();

        $costTransaction = $this->createTransaction($account);
        $consumeTransaction = $this->createTransaction($account);

        $entity = new ConsumeLog();
        $entity->setCostTransaction($costTransaction);
        $entity->setConsumeTransaction($consumeTransaction);
        $entity->setAmount('CreateNewEntity_' . uniqid());
        $entity->setCreateTime(new \DateTimeImmutable());
        $entity->setCreatedFromIp('127.0.0.1');

        return $entity;
    }
}
