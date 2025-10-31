<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Entity\Account;
use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Enum\AdjustRequestStatus;
use CreditBundle\Enum\AdjustRequestType;
use CreditBundle\Repository\AdjustRequestRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(AdjustRequestRepository::class)]
#[RunTestsInSeparateProcesses]
final class AdjustRequestRepositoryTest extends AbstractRepositoryTestCase
{
    private AdjustRequestRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(AdjustRequestRepository::class);

        // 检查当前测试是否需要 DataFixtures 数据
        $currentTest = $this->name();
        if ('testCountWithDataFixtureShouldReturnGreaterThanZero' === $currentTest) {
            // 为计数测试创建测试数据
            $this->createTestDataForCountTest();
        }
    }

    private function createTestDataForCountTest(): void
    {
        $account = $this->createAccount();
        $adjustRequest = new AdjustRequest();
        $adjustRequest->setAccount($account);
        $adjustRequest->setAmount('100.00');
        $adjustRequest->setType(AdjustRequestType::INCREASE);
        $adjustRequest->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($adjustRequest, true);
    }

    public function testSave(): void
    {
        $account = $this->createAccount();

        $adjustRequest = new AdjustRequest();
        $adjustRequest->setAccount($account);
        $adjustRequest->setAmount('100.50');
        $adjustRequest->setType(AdjustRequestType::INCREASE);
        $adjustRequest->setStatus(AdjustRequestStatus::EXAMINE);
        $adjustRequest->setRemark('Test adjustment');

        $this->repository->save($adjustRequest, true);

        self::assertGreaterThan(0, $adjustRequest->getId());
        self::assertEquals('100.50', $adjustRequest->getAmount());
        self::assertEquals(AdjustRequestType::INCREASE, $adjustRequest->getType());
        self::assertEquals(AdjustRequestStatus::EXAMINE, $adjustRequest->getStatus());
        self::assertEquals('Test adjustment', $adjustRequest->getRemark());
    }

    public function testRemove(): void
    {
        $account = $this->createAccount();

        // 创建一个调整请求
        $adjustRequest = new AdjustRequest();
        $adjustRequest->setAccount($account);
        $adjustRequest->setAmount('50.00');
        $adjustRequest->setType(AdjustRequestType::DECREASE);
        $adjustRequest->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($adjustRequest, true);

        $requestId = $adjustRequest->getId();

        // 删除调整请求
        $this->repository->remove($adjustRequest, true);

        // 验证调整请求已被删除
        $deletedRequest = $this->repository->find($requestId);
        self::assertNull($deletedRequest);
    }

    public function testFindOneByWithOrderBy(): void
    {
        // 清理数据库
        self::cleanDatabase();

        $account = $this->createAccount();

        // 创建多个相同状态的调整请求
        $request1 = new AdjustRequest();
        $request1->setAccount($account);
        $request1->setAmount('10.00');
        $request1->setType(AdjustRequestType::INCREASE);
        $request1->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($request1, false);

        $request2 = new AdjustRequest();
        $request2->setAccount($account);
        $request2->setAmount('90.00');
        $request2->setType(AdjustRequestType::INCREASE);
        $request2->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($request2, true);

        // 按金额正序查找第一个 - 使用account作为过滤条件确保只查询此测试创建的数据
        $lowestRequest = $this->repository->findOneBy(
            ['status' => AdjustRequestStatus::EXAMINE, 'account' => $account],
            ['amount' => 'ASC']
        );
        self::assertNotNull($lowestRequest);
        self::assertInstanceOf(AdjustRequest::class, $lowestRequest);
        self::assertEquals('10.00', $lowestRequest->getAmount());

        // 按金额倒序查找第一个 - 使用account作为过滤条件确保只查询此测试创建的数据
        $highestRequest = $this->repository->findOneBy(
            ['status' => AdjustRequestStatus::EXAMINE, 'account' => $account],
            ['amount' => 'DESC']
        );
        self::assertNotNull($highestRequest);
        self::assertInstanceOf(AdjustRequest::class, $highestRequest);
        self::assertEquals('90.00', $highestRequest->getAmount());
    }

    public function testCountWithJoinQuery(): void
    {
        // 清理数据库
        self::cleanDatabase();

        $account1 = $this->createAccount();
        $account2 = $this->createAccount();

        // 为第一个账户创建调整请求
        $request1 = new AdjustRequest();
        $request1->setAccount($account1);
        $request1->setAmount('100.00');
        $request1->setType(AdjustRequestType::INCREASE);
        $request1->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($request1, false);

        // 为第二个账户创建调整请求
        $request2 = new AdjustRequest();
        $request2->setAccount($account2);
        $request2->setAmount('200.00');
        $request2->setType(AdjustRequestType::INCREASE);
        $request2->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($request2, true);

        // 计算特定账户的调整请求数量
        $account1RequestCount = $this->repository->count(['account' => $account1]);
        self::assertEquals(1, $account1RequestCount);

        $account2RequestCount = $this->repository->count(['account' => $account2]);
        self::assertEquals(1, $account2RequestCount);
    }

    public function testFindByWithJoinQuery(): void
    {
        // 清理数据库
        self::cleanDatabase();

        $account = $this->createAccount();

        // 创建调整请求
        $request = new AdjustRequest();
        $request->setAccount($account);
        $request->setAmount('150.00');
        $request->setType(AdjustRequestType::INCREASE);
        $request->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($request, true);

        // 根据账户查找调整请求
        $accountRequests = $this->repository->findBy(['account' => $account]);

        self::assertCount(1, $accountRequests);
        self::assertEquals('150.00', $accountRequests[0]->getAmount());
        self::assertEquals(AdjustRequestType::INCREASE, $accountRequests[0]->getType());
        self::assertEquals(AdjustRequestStatus::EXAMINE, $accountRequests[0]->getStatus());
        self::assertEquals($account, $accountRequests[0]->getAccount());
    }

    public function testFindByWithNullRemark(): void
    {
        // 清理数据库
        self::cleanDatabase();

        $account = $this->createAccount();

        // 创建没有备注的调整请求
        $request = new AdjustRequest();
        $request->setAccount($account);
        $request->setAmount('100.00');
        $request->setType(AdjustRequestType::INCREASE);
        $request->setStatus(AdjustRequestStatus::EXAMINE);
        // 不设置备注，保持为null
        $this->repository->save($request, true);

        // 查找没有备注的调整请求
        $noRemarkRequests = $this->repository->findBy(['remark' => null]);

        self::assertCount(1, $noRemarkRequests);
        self::assertEquals('100.00', $noRemarkRequests[0]->getAmount());
        self::assertEquals(AdjustRequestType::INCREASE, $noRemarkRequests[0]->getType());
        self::assertEquals(AdjustRequestStatus::EXAMINE, $noRemarkRequests[0]->getStatus());
        self::assertNull($noRemarkRequests[0]->getRemark());
    }

    public function testCountWithNullRemark(): void
    {
        // 清理数据库
        self::cleanDatabase();

        $account = $this->createAccount();

        // 创建没有备注的调整请求
        $request = new AdjustRequest();
        $request->setAccount($account);
        $request->setAmount('100.00');
        $request->setType(AdjustRequestType::INCREASE);
        $request->setStatus(AdjustRequestStatus::EXAMINE);
        $this->repository->save($request, true);

        // 计算没有备注的调整请求数量
        $noRemarkCount = $this->repository->count(['remark' => null]);
        self::assertEquals(1, $noRemarkCount);
    }

    protected function getRepository(): AdjustRequestRepository
    {
        return self::getService(AdjustRequestRepository::class);
    }

    private function createAccount(?string $name = null): Account
    {
        $account = new Account();
        $account->setName($name ?? 'Test Account ' . uniqid());
        $account->setCurrency('CNY');

        $accountRepository = self::getService('CreditBundle\Repository\AccountRepository');
        $accountRepository->save($account, true);

        return $account;
    }

    protected function createNewEntity(): object
    {
        $account = $this->createAccount('CreateNewEntity Account ' . uniqid());

        $adjustRequest = new AdjustRequest();
        $adjustRequest->setAccount($account);
        $adjustRequest->setAmount('CreateNewEntity_' . rand(100, 999) . '.00');
        $adjustRequest->setType(AdjustRequestType::INCREASE);
        $adjustRequest->setStatus(AdjustRequestStatus::EXAMINE);
        $adjustRequest->setRemark('CreateNewEntity remark ' . uniqid());

        return $adjustRequest;
    }
}
