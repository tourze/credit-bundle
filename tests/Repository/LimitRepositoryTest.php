<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\Limit;
use CreditBundle\Enum\LimitType;
use CreditBundle\Repository\LimitRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(LimitRepository::class)]
#[RunTestsInSeparateProcesses]
final class LimitRepositoryTest extends AbstractRepositoryTestCase
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
        self::assertInstanceOf(Limit::class, $found);
        self::assertEquals($entity->getValue(), $found->getValue());
        self::assertEquals($entity->getType(), $found->getType());
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
        // 清理数据库确保测试隔离
        self::cleanDatabase();

        $repository = $this->getRepository();

        // 创建一个特定的账户用于此测试
        $testAccount = $this->createAccount();

        $entity1 = new Limit();
        $entity1->setAccount($testAccount);
        $entity1->setType(LimitType::TOTAL_OUT_LIMIT);
        $entity1->setValue(250);
        $entity1->setRemark('Test Order By 1');
        $entity1->setCreateTime(new \DateTimeImmutable());
        $entity1->setUpdateTime(new \DateTimeImmutable());
        $entity1->setCreatedFromIp('127.0.0.1');
        $entity1->setUpdatedFromIp('127.0.0.1');

        $entity2 = new Limit();
        $entity2->setAccount($testAccount);
        $entity2->setType(LimitType::DAILY_IN_LIMIT);
        $entity2->setValue(750);
        $entity2->setRemark('Test Order By 2');
        $entity2->setCreateTime(new \DateTimeImmutable());
        $entity2->setUpdateTime(new \DateTimeImmutable());
        $entity2->setCreatedFromIp('127.0.0.1');
        $entity2->setUpdatedFromIp('127.0.0.1');

        $repository->save($entity1);
        $repository->save($entity2);

        // 使用account作为过滤条件，只查询此测试创建的数据
        $result = $repository->findOneBy(['account' => $testAccount], ['value' => 'DESC']);

        self::assertInstanceOf(Limit::class, $result);
        self::assertEquals(750, $result->getValue());
    }

    public function testFindByWithAccountAssociation(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $results = $repository->findBy(['account' => $entity->getAccount()]);

        self::assertIsArray($results);
        self::assertGreaterThanOrEqual(1, count($results));
        self::assertContainsOnlyInstancesOf(Limit::class, $results);
    }

    public function testCountWithAccountAssociation(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $count = $repository->count(['account' => $entity->getAccount()]);

        self::assertGreaterThanOrEqual(1, $count);
    }

    public function testFindByWithNullAccount(): void
    {
        $repository = $this->getRepository();

        $results = $repository->findBy(['account' => null]);

        self::assertIsArray($results);
    }

    public function testFindByWithNullRemark(): void
    {
        $repository = $this->getRepository();

        $results = $repository->findBy(['remark' => null]);

        self::assertIsArray($results);
    }

    public function testCountWithNullAccount(): void
    {
        $repository = $this->getRepository();

        $count = $repository->count(['account' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountWithNullRemark(): void
    {
        $repository = $this->getRepository();

        $count = $repository->count(['remark' => null]);

        self::assertIsInt($count);
        self::assertGreaterThanOrEqual(0, $count);
    }

    public function testCountByAssociationAccountShouldReturnCorrectNumber(): void
    {
        $repository = $this->getRepository();
        $account = $this->createAccount();

        for ($i = 0; $i < 3; ++$i) {
            $entity = $this->createTestEntity();
            $entity->setAccount($account);
            $entity->setType(match ($i) {
                0 => LimitType::TOTAL_OUT_LIMIT,
                1 => LimitType::DAILY_OUT_LIMIT,
                default => LimitType::DAILY_IN_LIMIT,
            });
            $repository->save($entity);
        }

        $count = $repository->count(['account' => $account]);

        self::assertGreaterThanOrEqual(3, $count);
    }

    public function testFindOneByAssociationAccountShouldReturnMatchingEntity(): void
    {
        $repository = $this->getRepository();
        $entity = $this->createTestEntity();

        $repository->save($entity);

        $result = $repository->findOneBy(['account' => $entity->getAccount()]);

        self::assertInstanceOf(Limit::class, $result);
        self::assertNotNull($result->getAccount());
        self::assertNotNull($entity->getAccount());
        self::assertEquals($entity->getAccount()->getId(), $result->getAccount()->getId());
    }

    protected function getRepository(): LimitRepository
    {
        return self::getService(LimitRepository::class);
    }

    private function createTestEntity(): Limit
    {
        $account = $this->createAccount();

        $entity = new Limit();
        $entity->setAccount($account);
        $entity->setType(LimitType::TOTAL_OUT_LIMIT);
        $entity->setValue(500);
        $entity->setRemark('Test limit');
        $entity->setCreateTime(new \DateTimeImmutable());
        $entity->setUpdateTime(new \DateTimeImmutable());
        $entity->setCreatedFromIp('127.0.0.1');
        $entity->setUpdatedFromIp('127.0.0.1');

        return $entity;
    }

    private function createAccount(): Account
    {
        $account = new Account();
        $account->setName('test_limit_account_' . uniqid());
        $account->setCurrency('CNY');
        $account->setCreateTime(new \DateTimeImmutable());
        $account->setUpdateTime(new \DateTimeImmutable());
        $account->setCreatedFromIp('127.0.0.1');
        $account->setUpdatedFromIp('127.0.0.1');

        $this->persistAndFlush($account);

        return $account;
    }

    protected function createNewEntity(): object
    {
        $account = $this->createAccount();

        $entity = new Limit();
        $entity->setAccount($account);
        $entity->setType(LimitType::TOTAL_OUT_LIMIT);
        $entity->setValue(rand(100, 9999));
        $entity->setRemark('CreateNewEntity limit ' . uniqid());
        $entity->setCreateTime(new \DateTimeImmutable());
        $entity->setUpdateTime(new \DateTimeImmutable());
        $entity->setCreatedFromIp('127.0.0.1');
        $entity->setUpdatedFromIp('127.0.0.1');

        return $entity;
    }
}
