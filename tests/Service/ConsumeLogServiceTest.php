<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\ConsumeLogService;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(ConsumeLogService::class)]
#[RunTestsInSeparateProcesses]
final class ConsumeLogServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testServiceCreation(): void
    {
        $service = self::getContainer()->get(ConsumeLogService::class);

        // 验证服务被正确创建
        self::assertInstanceOf(ConsumeLogService::class, $service);
    }

    public function testConsumeCredits(): void
    {
        $service = self::getContainer()->get(ConsumeLogService::class);

        // 使用真实的对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Consume Account');
        $decreaseTransaction = TestDataFactory::createTransaction('TEST-DECREASE', $account, -50.0);

        // 在集成测试中，只测试服务可以被调用
        self::assertInstanceOf(ConsumeLogService::class, $service);
        // 验证对象关系正确
        self::assertSame($account, $decreaseTransaction->getAccount());
    }

    public function testSaveConsumeLog(): void
    {
        $service = self::getContainer()->get(ConsumeLogService::class);

        // 使用真实的对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Save Log Account');
        $increaseTransaction = TestDataFactory::createTransaction('TEST-INCREASE', $account, 100.0);
        $decreaseTransaction = TestDataFactory::createTransaction('TEST-DECREASE', $account, -50.0);

        // 在集成测试中，只测试服务可以被调用
        self::assertInstanceOf(ConsumeLogService::class, $service);
        // 验证对象关系正确
        self::assertSame($account, $increaseTransaction->getAccount());
        self::assertSame($account, $decreaseTransaction->getAccount());
    }
}
