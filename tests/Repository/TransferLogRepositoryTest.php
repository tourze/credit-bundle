<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\TransferLog;
use CreditBundle\Repository\TransferLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TransferLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class TransferLogRepositoryTest extends AbstractRepositoryTestCase
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

    public function testFindOneByWithOrderBy(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->findOneBy([], ['id' => 'DESC']);

        if (null !== $result) {
            self::assertInstanceOf(TransferLog::class, $result);
        } else {
            self::addToAssertionCount(1);
        }
    }

    public function testSave(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        // 使用真实的 Account 对象而不是 Mock
        $outAccount = $this->createAccount('Test Out Account for Save');
        $inAccount = $this->createAccount('Test In Account for Save');

        $entity = new TransferLog();
        $entity->setCurrency('CNY');
        $entity->setOutAccount($outAccount);
        $entity->setOutAmount('100.50');
        $entity->setInAccount($inAccount);
        $entity->setInAmount('100.50');
        $entity->setRemark('Test transfer');

        $repository->save($entity, false);

        self::assertNotNull($entity->getId());
    }

    public function testRemove(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        // 创建真实的Account实体而不是Mock
        $outAccount = new Account();
        $outAccount->setName('Test Out Account');
        $outAccount->setCurrency('USD');

        $inAccount = new Account();
        $inAccount->setName('Test In Account');
        $inAccount->setCurrency('USD');

        // 先持久化Account实体
        $this->persistAndFlush($outAccount);
        $this->persistAndFlush($inAccount);

        $entity = new TransferLog();
        $entity->setCurrency('USD');
        $entity->setOutAccount($outAccount);
        $entity->setOutAmount('50.25');
        $entity->setInAccount($inAccount);
        $entity->setInAmount('50.25');
        $entity->setRemark('Test remove transfer');

        $repository->save($entity, true);
        $entityId = $entity->getId();

        $repository->remove($entity, true);

        $found = $repository->find($entityId);
        self::assertNull($found);
    }

    public function testCountWithAssociationCriteria(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        // 使用真实的 Account 对象而不是 Mock
        $account = $this->createAccount('Test Count Account');

        $result = $repository->count(['outAccount' => $account]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testFindByWithOutAccountCriteria(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        // 使用真实的 Account 对象而不是 Mock
        $account = $this->createAccount('Test Find By Out Account');

        $result = $repository->findBy(['outAccount' => $account]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferLog::class, $entity);
        }
    }

    public function testFindByWithInAccountCriteria(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        // 使用真实的 Account 对象而不是 Mock
        $account = $this->createAccount('Test Find By In Account');

        $result = $repository->findBy(['inAccount' => $account]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferLog::class, $entity);
        }
    }

    public function testFindByWithRemarkNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->findBy(['remark' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferLog::class, $entity);
            self::assertNull($entity->getRemark());
        }
    }

    public function testFindByWithRelationIdNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->findBy(['relationId' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferLog::class, $entity);
            self::assertNull($entity->getRelationId());
        }
    }

    public function testCountWithRemarkNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->count(['remark' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testCountWithRelationIdNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->count(['relationId' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testFindOneByWithOrderByAndCriteria(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->findOneBy(['currency' => 'CNY'], ['id' => 'ASC']);

        if (null !== $result) {
            self::assertInstanceOf(TransferLog::class, $result);
            self::assertSame('CNY', $result->getCurrency());
        } else {
            self::addToAssertionCount(1);
        }
    }

    public function testCountWithInAccountAssociation(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        // 使用真实的 Account 对象而不是 Mock
        $account = $this->createAccount('Test Count In Account');

        $result = $repository->count(['inAccount' => $account]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testFindByWithExpireTimeNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->findBy(['expireTime' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferLog::class, $entity);
            self::assertNull($entity->getExpireTime());
        }
    }

    public function testFindByWithRelationModelNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->findBy(['relationModel' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferLog::class, $entity);
            self::assertNull($entity->getRelationModel());
        }
    }

    public function testCountWithExpireTimeNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->count(['expireTime' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testCountWithRelationModelNull(): void
    {
        $repository = self::getService(TransferLogRepository::class);

        $result = $repository->count(['relationModel' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    protected function createNewEntity(): object
    {
        $fromAccount = new Account();
        $fromAccount->setName('CreateNewEntity From Account_' . uniqid());
        $fromAccount->setCurrency('CNY');
        $fromAccount->setCreateTime(new \DateTimeImmutable());
        $fromAccount->setUpdateTime(new \DateTimeImmutable());
        $fromAccount->setCreatedFromIp('127.0.0.1');
        $fromAccount->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($fromAccount);

        $toAccount = new Account();
        $toAccount->setName('CreateNewEntity To Account_' . uniqid());
        $toAccount->setCurrency('CNY');
        $toAccount->setCreateTime(new \DateTimeImmutable());
        $toAccount->setUpdateTime(new \DateTimeImmutable());
        $toAccount->setCreatedFromIp('127.0.0.1');
        $toAccount->setUpdatedFromIp('127.0.0.1');
        $this->persistAndFlush($toAccount);

        $entity = new TransferLog();
        $entity->setOutAccount($fromAccount);
        $entity->setInAccount($toAccount);
        $entity->setOutAmount('CreateNewEntity_' . rand(100, 999) . '.00');
        $entity->setInAmount('CreateNewEntity_' . rand(100, 999) . '.00');
        $entity->setCurrency('CNY');
        $entity->setRemark('CreateNewEntity transfer ' . uniqid());

        return $entity;
    }

    protected function getRepository(): TransferLogRepository
    {
        return self::getService(TransferLogRepository::class);
    }

    private function createTestEntity(): TransferLog
    {
        $fromAccount = $this->createAccount('From Account');
        $toAccount = $this->createAccount('To Account');

        $entity = new TransferLog();
        $entity->setOutAccount($fromAccount);
        $entity->setInAccount($toAccount);
        $entity->setOutAmount('100.00');
        $entity->setInAmount('100.00');
        $entity->setCurrency('CNY');
        $entity->setRemark('Test transfer');

        return $entity;
    }

    private function createAccount(string $name): Account
    {
        $account = new Account();
        $account->setName($name . '_' . uniqid());
        $account->setCurrency('CNY');

        $this->persistAndFlush($account);

        return $account;
    }
}
