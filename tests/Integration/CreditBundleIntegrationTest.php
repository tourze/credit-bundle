<?php

namespace CreditBundle\Tests\Integration;

use CreditBundle\CreditBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CreditBundleIntegrationTest extends TestCase
{
    /**
     * 测试bundle是否能够正常实例化
     */
    public function testBundleInstantiation(): void
    {
        $bundle = new CreditBundle();
        $this->assertInstanceOf(CreditBundle::class, $bundle);
    }

    /**
     * 测试bundle名称
     */
    public function testGetBundleName(): void
    {
        $bundle = new CreditBundle();
        $this->assertEquals('CreditBundle', $bundle->getName());
    }

    /**
     * 测试bundle在容器构建过程中的注册
     */
    public function testBundleRegistration(): void
    {
        $bundle = new CreditBundle();
        $container = new ContainerBuilder();

        $method = new \ReflectionMethod(CreditBundle::class, 'build');
        $method->setAccessible(true);
        $method->invoke($bundle, $container);

        // 测试通过，如果build方法没有抛出异常
        $this->assertTrue(true);
    }
}
