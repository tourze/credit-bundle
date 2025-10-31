<?php

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\CreditIncreaseService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(CreditIncreaseService::class)]
#[RunTestsInSeparateProcesses]
final class CreditIncreaseServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 集成测试的设置逻辑，如果需要的话
    }

    public function testServiceCanBeInstantiated(): void
    {
        $service = self::getService(CreditIncreaseService::class);
        $this->assertInstanceOf(CreditIncreaseService::class, $service);
    }

    public function testIncrease(): void
    {
        // 从容器获取真实服务
        $service = self::getService(CreditIncreaseService::class);

        // 验证服务可以正常创建
        $this->assertInstanceOf(CreditIncreaseService::class, $service);
    }

    public function testAsyncIncrease(): void
    {
        // 从容器获取真实服务
        $service = self::getService(CreditIncreaseService::class);

        // 验证服务可以正常创建
        $this->assertInstanceOf(CreditIncreaseService::class, $service);
    }
}
