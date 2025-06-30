<?php

declare(strict_types=1);

namespace CreditBundle\Tests\DependencyInjection;

use CreditBundle\DependencyInjection\CreditExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CreditExtensionTest extends TestCase
{
    public function testExtensionCreation(): void
    {
        $extension = new CreditExtension();

        self::assertInstanceOf(CreditExtension::class, $extension);
    }

    public function testLoad(): void
    {
        $extension = new CreditExtension();
        $container = new ContainerBuilder();

        $extension->load([], $container);

        // 检查是否加载了服务配置
        self::assertTrue($container->hasDefinition('CreditBundle\\Service\\AccountService'));
    }
}
