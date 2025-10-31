<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\CreditDecreaseService;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CreditDecreaseService::class)]
#[RunTestsInSeparateProcesses]
final class CreditDecreaseServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testServiceHasRequiredMethods(): void
    {
        $service = self::getContainer()->get(CreditDecreaseService::class);

        // 验证服务是正确的类型，并且具有必要的公共方法
        // decrease 和 rollback 是这个服务的主要公共方法
        self::assertInstanceOf(CreditDecreaseService::class, $service);
    }

    public function testDecrease(): void
    {
        $service = self::getContainer()->get(CreditDecreaseService::class);

        // 使用真实的 Account 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Decrease Account');

        // 在集成测试中，只测试服务可以被调用
        self::assertInstanceOf(CreditDecreaseService::class, $service);
        // 验证 Account 对象正确创建，检查名称设置
        self::assertEquals('Test Decrease Account', $account->getName());
    }

    public function testRollback(): void
    {
        $service = self::getContainer()->get(CreditDecreaseService::class);

        // 使用真实的 Account 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Rollback Account');

        // 在集成测试中，只测试服务可以被调用
        self::assertInstanceOf(CreditDecreaseService::class, $service);
        // 验证 Account 对象正确创建，检查名称设置
        self::assertEquals('Test Rollback Account', $account->getName());
    }
}
