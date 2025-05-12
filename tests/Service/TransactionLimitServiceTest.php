<?php

namespace CreditBundle\Tests\Service;

use CreditBundle\Enum\LimitType;
use CreditBundle\Exception\TransactionException;
use CreditBundle\Repository\TransactionRepository;
use CreditBundle\Service\TransactionLimitService;
use CreditBundle\Tests\AbstractTestCase;
use CreditBundle\Tests\TestDataFactory;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\MockObject\MockObject;

class TransactionLimitServiceTest extends AbstractTestCase
{
    private TransactionLimitService $service;
    private MockObject&TransactionRepository $transactionRepository;

    protected function setUp(): void
    {
        $this->transactionRepository = $this->createMock(TransactionRepository::class);
        $this->service = new TransactionLimitService($this->transactionRepository);
    }

    /**
     * 测试无限制情况下的增加限额检查
     */
    public function testCheckIncreaseLimit_withNoLimits_succeeds(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 100.00;

        // 确保账户没有任何限制
        $account->getLimits()->clear();

        // 模拟查询结果
        $qb = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willReturn(0);

        $this->transactionRepository->method('createQueryBuilder')->willReturn($qb);

        // 执行测试 - 应该不会抛出异常
        $this->service->checkIncreaseLimit($account, $amount);

        // 如果没有抛出异常，则测试通过
        $this->assertTrue(true);
    }

    /**
     * 测试限制不适用的情况
     */
    public function testCheckIncreaseLimit_withInapplicableLimits_succeeds(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 100.00;

        // 添加一个已过期的限制
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_IN_LIMIT,
            1000.00
        );
        $account->addLimit($limit);

        // 模拟查询结果
        $this->transactionRepository->method('createQueryBuilder')
            ->willReturn($this->createQueryBuilderMock(0));

        // 执行测试 - 应该不会抛出异常
        $this->service->checkIncreaseLimit($account, $amount);

        // 如果没有抛出异常，则测试通过
        $this->assertTrue(true);
    }

    /**
     * 测试在限额范围内的增加操作
     */
    public function testCheckIncreaseLimit_withinLimit_succeeds(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 100.00;
        $dailySum = 400.00; // 已使用的金额

        // 添加一个当前有效的限制，限额足够大
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_IN_LIMIT,
            500.00
        );
        $account->addLimit($limit);

        // 模拟查询结果 - 日交易总额为400，加上本次100，仍在500的限额内
        $qb = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willReturn($dailySum);

        $this->transactionRepository->method('createQueryBuilder')->willReturn($qb);

        // 执行测试 - 应该不会抛出异常
        $this->service->checkIncreaseLimit($account, $amount);

        // 如果没有抛出异常，则测试通过
        $this->assertTrue(true);
    }

    /**
     * 测试超出限额的增加操作
     */
    public function testCheckIncreaseLimit_exceedingLimit_throwsException(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 200.00;

        // 添加一个当前有效的限制，限额较小
        $limitAmount = 100;
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_IN_LIMIT,
            $limitAmount
        );
        $account->addLimit($limit);

        // 模拟查询结果 - 每日转入已经达到上限
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $query = $this->createMock(Query::class);
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willReturn(0);

        $this->transactionRepository->method('createQueryBuilder')->willReturn($qb);

        // 期望异常
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("转入限额已到达{$limitAmount}");

        // 执行测试
        $this->service->checkIncreaseLimit($account, $amount);
    }

    /**
     * 测试日常转入限额检查
     */
    public function testCheckIncreaseLimit_withInternalLimit_throwsException(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 200.00;

        // 模拟查询结果 - 每日转入设定为ENV配置
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $query = $this->createMock(Query::class);
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willReturn(900); // 已经有900积分了

        $this->transactionRepository->method('createQueryBuilder')->willReturn($qb);

        // 由于实际业务代码中检查了环境变量，我们无法直接验证枚举值检查
        // 这里我们只验证当达到内部转入限额时会抛出异常
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("转入限额已到达1000"); // 默认限额是1000

        // 执行测试
        $this->service->checkIncreaseLimit($account, $amount);
    }

    /**
     * 测试无限制情况下的减少限额检查
     */
    public function testCheckDecreaseLimit_withNoLimits_succeeds(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 100.00;

        // 确保账户没有任何限制
        $account->getLimits()->clear();

        // 执行测试 - 应该不会抛出异常
        $this->service->checkDecreaseLimit($account, $amount);

        // 如果没有抛出异常，则测试通过
        $this->assertTrue(true);
    }

    /**
     * 测试减少限额在范围内
     */
    public function testCheckDecreaseLimit_withinLimit_succeeds(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 100.00;

        // 添加一个当前有效的限制，限额足够大
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_OUT_LIMIT,
            500.00
        );
        $account->addLimit($limit);

        // 模拟查询结果
        $qb = $this->createMock(\Doctrine\ORM\QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $query = $this->createMock(\Doctrine\ORM\Query::class);
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willReturn(0);

        $this->transactionRepository->method('createQueryBuilder')->willReturn($qb);

        // 执行测试 - 应该不会抛出异常
        $this->service->checkDecreaseLimit($account, $amount);

        // 如果没有抛出异常，则测试通过
        $this->assertTrue(true);
    }

    /**
     * 测试超出减少限额
     */
    public function testCheckDecreaseLimit_exceedingLimit_throwsException(): void
    {
        // 准备测试数据
        $account = TestDataFactory::createAccount();
        $amount = 200.00;

        // 添加一个当前有效的限制，限额较小
        $limitAmount = 100;
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_OUT_LIMIT,
            $limitAmount
        );
        $account->addLimit($limit);

        // 模拟查询结果 - 每日转出已经达到上限
        $qb = $this->createMock(QueryBuilder::class);
        $qb->method('select')->willReturnSelf();
        $qb->method('where')->willReturnSelf();
        $qb->method('setParameter')->willReturnSelf();

        $query = $this->createMock(Query::class);
        $qb->method('getQuery')->willReturn($query);
        $query->method('getSingleScalarResult')->willReturn(0);

        $this->transactionRepository->method('createQueryBuilder')->willReturn($qb);

        // 期望异常
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("当日转出限额已到达{$limitAmount}");

        // 执行测试
        $this->service->checkDecreaseLimit($account, $amount);
    }

    /**
     * 辅助方法：创建查询构建器模拟对象
     */
    private function createQueryBuilderMock($resultValue)
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $query = $this->createMock(Query::class);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('setParameter')->willReturnSelf();
        $queryBuilder->method('getQuery')->willReturn($query);

        $query->method('getSingleScalarResult')->willReturn($resultValue);

        return $queryBuilder;
    }
}
