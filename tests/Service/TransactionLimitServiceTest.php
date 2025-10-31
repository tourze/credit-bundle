<?php

namespace CreditBundle\Tests\Service;

use Carbon\CarbonImmutable;
use CreditBundle\Entity\Account;
use CreditBundle\Enum\LimitType;
use CreditBundle\Exception\TransactionException;
use CreditBundle\Service\TransactionLimitService;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(TransactionLimitService::class)]
#[RunTestsInSeparateProcesses]
final class TransactionLimitServiceTest extends AbstractIntegrationTestCase
{
    private TransactionLimitService $service;

    protected function onSetUp(): void
    {
        $this->service = self::getService(TransactionLimitService::class);
    }

    private function createAccountWithTransactions(float $dailyIncreaseSum = 0, float $dailyDecreaseSum = 0): Account
    {
        $account = TestDataFactory::createAccount();
        self::getEntityManager()->persist($account);

        // 创建今日转入交易记录
        if ($dailyIncreaseSum > 0) {
            $increaseTransaction = TestDataFactory::createTransaction('TEST-IN-' . time(), $account, $dailyIncreaseSum);
            $increaseTransaction->setCreateTime(CarbonImmutable::now()->setHour(10));
            self::getEntityManager()->persist($increaseTransaction);
        }

        // 创建今日转出交易记录
        if ($dailyDecreaseSum > 0) {
            $decreaseTransaction = TestDataFactory::createTransaction('TEST-OUT-' . time(), $account, -$dailyDecreaseSum);
            $decreaseTransaction->setCreateTime(CarbonImmutable::now()->setHour(14));
            self::getEntityManager()->persist($decreaseTransaction);
        }

        self::getEntityManager()->flush();

        return $account;
    }

    /**
     * 测试无限制情况下的增加限额检查
     */
    public function testCheckIncreaseLimitWithNoLimitsSucceeds(): void
    {
        // 准备测试数据
        $account = $this->createAccountWithTransactions();
        $amount = 100.00;

        // 确保账户没有任何限制
        $account->getLimits()->clear();
        self::getEntityManager()->flush();

        // 执行测试 - 应该不会抛出异常
        $this->service->checkIncreaseLimit($account, $amount);

        // 验证账户无限制
        $this->assertCount(0, $account->getLimits());
    }

    /**
     * 测试限制充足的情况
     */
    public function testCheckIncreaseLimitWithSufficientLimitSucceeds(): void
    {
        // 准备测试数据
        $account = $this->createAccountWithTransactions();
        $amount = 100.00;

        // 添加一个足够大的限制
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_IN_LIMIT,
            1000
        );
        $account->addLimit($limit);
        self::getEntityManager()->flush();

        // 执行测试 - 应该不会抛出异常
        $this->service->checkIncreaseLimit($account, $amount);

        // 验证限制已添加并且数量充足
        $this->assertCount(1, $account->getLimits());
        $this->assertSame(1000, $limit->getValue());
    }

    /**
     * 测试在限额范围内的增加操作
     */
    public function testCheckIncreaseLimitWithinLimitSucceeds(): void
    {
        // 准备测试数据
        $dailySum = 400.00; // 已使用的金额
        $account = $this->createAccountWithTransactions($dailySum);
        $amount = 100.00;

        // 添加一个当前有效的限制，限额足够大
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_IN_LIMIT,
            500
        );
        $account->addLimit($limit);
        self::getEntityManager()->flush();

        // 执行测试 - 应该不会抛出异常，因为 400 + 100 = 500 在限额内
        $this->service->checkIncreaseLimit($account, $amount);

        // 验证限制已添加且在范围内
        $this->assertCount(1, $account->getLimits());
        $this->assertSame(500, $limit->getValue());
    }

    /**
     * 测试超出限额的增加操作
     */
    public function testCheckIncreaseLimitExceedingLimitThrowsException(): void
    {
        // 准备测试数据
        $account = $this->createAccountWithTransactions();
        $amount = 200.00;

        // 添加一个当前有效的限制，限额较小
        $limitAmount = 100;
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_IN_LIMIT,
            $limitAmount
        );
        $account->addLimit($limit);
        self::getEntityManager()->flush();

        // 期望异常
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("转入限额已到达{$limitAmount}");

        // 执行测试 - 尝试转入200但限额只有100
        $this->service->checkIncreaseLimit($account, $amount);
    }

    /**
     * 测试日常转入限额检查
     */
    public function testCheckIncreaseLimitWithInternalLimitThrowsException(): void
    {
        // 准备测试数据 - 已经有900积分了
        $dailySum = 900.00;
        $account = $this->createAccountWithTransactions($dailySum);
        $amount = 200.00;

        // 设置环境变量以确保内部限制生效
        $_ENV['CREDIT_INCREASE_DAY_LIMIT'] = '1000';

        // 由于实际业务代码中检查了环境变量，我们验证当达到内部转入限额时会抛出异常
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('转入限额已到达1000'); // 默认限额是1000

        // 执行测试 - 900 + 200 > 1000
        $this->service->checkIncreaseLimit($account, $amount);
    }

    /**
     * 测试无限制情况下的减少限额检查
     */
    public function testCheckDecreaseLimitWithNoLimitsSucceeds(): void
    {
        // 准备测试数据
        $account = $this->createAccountWithTransactions();
        $amount = 100.00;

        // 确保账户没有任何限制
        $account->getLimits()->clear();
        self::getEntityManager()->flush();

        // 执行测试 - 应该不会抛出异常
        $this->service->checkDecreaseLimit($account, $amount);

        // 验证账户无限制
        $this->assertCount(0, $account->getLimits());
    }

    /**
     * 测试减少限额在范围内
     */
    public function testCheckDecreaseLimitWithinLimitSucceeds(): void
    {
        // 准备测试数据
        $account = $this->createAccountWithTransactions();
        $amount = 100.00;

        // 添加一个当前有效的限制，限额足够大
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_OUT_LIMIT,
            500
        );
        $account->addLimit($limit);
        self::getEntityManager()->flush();

        // 执行测试 - 应该不会抛出异常
        $this->service->checkDecreaseLimit($account, $amount);

        // 验证限制已添加且操作成功
        $this->assertCount(1, $account->getLimits());
        $this->assertSame(500, $limit->getValue());
    }

    /**
     * 测试超出减少限额
     */
    public function testCheckDecreaseLimitExceedingLimitThrowsException(): void
    {
        // 准备测试数据
        $account = $this->createAccountWithTransactions();
        $amount = 200.00;

        // 添加一个当前有效的限制，限额较小
        $limitAmount = 100;
        $limit = TestDataFactory::createLimit(
            $account,
            LimitType::DAILY_OUT_LIMIT,
            $limitAmount
        );
        $account->addLimit($limit);
        self::getEntityManager()->flush();

        // 期望异常
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage("当日转出限额已到达{$limitAmount}");

        // 执行测试 - 尝试转出200但限额只有100
        $this->service->checkDecreaseLimit($account, $amount);
    }
}
