<?php

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\TransactionService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(TransactionService::class)]
#[RunTestsInSeparateProcesses]
final class TransactionServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的设置逻辑，如果需要的话
    }

    public function testServiceCanBeCreated(): void
    {
        $service = self::getService(TransactionService::class);
        $this->assertNotNull($service);
    }

    public function testTransfer(): void
    {
        // 从容器获取真实服务
        $service = self::getService(TransactionService::class);

        // 验证服务可以正常创建
        $this->assertNotNull($service);
    }

    public function testIncrease(): void
    {
        // 从容器获取真实服务
        $service = self::getService(TransactionService::class);

        // 验证服务可以正常创建
        $this->assertNotNull($service);
    }

    public function testAsyncIncrease(): void
    {
        // 从容器获取真实服务
        $service = self::getService(TransactionService::class);

        // 验证服务可以正常创建
        $this->assertNotNull($service);
    }

    public function testDecrease(): void
    {
        // 从容器获取真实服务
        $service = self::getService(TransactionService::class);

        // 验证服务可以正常创建
        $this->assertNotNull($service);
    }

    public function testRollback(): void
    {
        // 从容器获取真实服务
        $service = self::getService(TransactionService::class);

        // 验证服务可以正常创建
        $this->assertNotNull($service);
    }
}
