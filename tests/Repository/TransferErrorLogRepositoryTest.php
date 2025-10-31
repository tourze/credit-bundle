<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Entity\TransferErrorLog;
use CreditBundle\Repository\TransferErrorLogRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(TransferErrorLogRepository::class)]
#[RunTestsInSeparateProcesses]
final class TransferErrorLogRepositoryTest extends AbstractRepositoryTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testFindOneByWithOrderBy(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->findOneBy([], ['id' => 'DESC']);

        if (null !== $result) {
            self::assertInstanceOf(TransferErrorLog::class, $result);
        } else {
            self::addToAssertionCount(1);
        }
    }

    public function testSave(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);
        $entity = new TransferErrorLog();

        // 使用一个简单的测试，验证save方法不会抛出异常
        try {
            $repository->save($entity, false);
            self::addToAssertionCount(1);
        } catch (\Exception $e) {
            self::fail('Save method should not throw exception: ' . $e->getMessage());
        }
    }

    public function testRemove(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $entity = new TransferErrorLog();
        $entity->setFromAccountId('2001');
        $entity->setFromAccountName('Test Remove From Account');
        $entity->setToAccountId('2002');
        $entity->setToAccountName('Test Remove To Account');
        $entity->setCurrency('USD');
        $entity->setAmount(50.25);
        $entity->setException('Test remove exception');

        $repository->save($entity, true);
        $entityId = $entity->getId();

        $repository->remove($entity, true);

        $found = $repository->find($entityId);
        self::assertNull($found);
    }

    public function testFindByWithAmountNull(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->findBy(['amount' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferErrorLog::class, $entity);
            self::assertNull($entity->getAmount());
        }
    }

    public function testFindByWithExceptionNull(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->findBy(['exception' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferErrorLog::class, $entity);
            self::assertNull($entity->getException());
        }
    }

    public function testCountWithAmountNull(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->count(['amount' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testCountWithExceptionNull(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->count(['exception' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    public function testFindOneByWithOrderByAndCriteria(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->findOneBy(['currency' => 'CNY'], ['id' => 'ASC']);

        if (null !== $result) {
            self::assertInstanceOf(TransferErrorLog::class, $result);
            self::assertSame('CNY', $result->getCurrency());
        } else {
            self::addToAssertionCount(1);
        }
    }

    public function testFindByWithContextNull(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->findBy(['context' => null]);

        self::assertIsArray($result);
        foreach ($result as $entity) {
            self::assertInstanceOf(TransferErrorLog::class, $entity);
        }
    }

    public function testCountWithContextNull(): void
    {
        $repository = self::getService(TransferErrorLogRepository::class);

        $result = $repository->count(['context' => null]);

        self::assertIsInt($result);
        self::assertGreaterThanOrEqual(0, $result);
    }

    protected function getRepository(): TransferErrorLogRepository
    {
        return self::getService(TransferErrorLogRepository::class);
    }

    protected function createNewEntity(): object
    {
        $entity = new TransferErrorLog();
        $entity->setFromAccountId((string) random_int(1_000_000_000_000, 9_999_999_999_999));
        $entity->setFromAccountName('CreateNewEntity From Account ' . uniqid());
        $entity->setToAccountId((string) random_int(1_000_000_000_000, 9_999_999_999_999));
        $entity->setToAccountName('CreateNewEntity To Account ' . uniqid());
        $entity->setCurrency('CNY');
        $entity->setAmount(rand(100, 999) * 1.0);
        $entity->setException('CreateNewEntity error message ' . uniqid());

        return $entity;
    }
}
