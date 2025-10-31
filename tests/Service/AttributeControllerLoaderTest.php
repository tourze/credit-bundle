<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\AttributeControllerLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * AttributeControllerLoader 测试
 *
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    private AttributeControllerLoader $loader;

    protected function onSetUp(): void
    {
        $this->loader = self::getService(AttributeControllerLoader::class);
    }

    public function testControllerLoaderIsInstantiable(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);

        self::assertTrue($reflection->isInstantiable());
        self::assertTrue($reflection->isFinal());
    }

    public function testLoaderImplementsRequiredInterfaces(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);

        self::assertTrue(
            $reflection->isSubclassOf('Symfony\Component\Config\Loader\Loader'),
            'AttributeControllerLoader 必须继承 Loader'
        );

        self::assertTrue(
            $reflection->implementsInterface('Tourze\RoutingAutoLoaderBundle\Service\RoutingAutoLoaderInterface'),
            'AttributeControllerLoader 必须实现 RoutingAutoLoaderInterface'
        );
    }

    public function testLoadReturnsRouteCollection(): void
    {
        $result = $this->loader->load('test');
        // The method signature already guarantees RouteCollection return type
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testAutoloadReturnsRouteCollection(): void
    {
        $result = $this->loader->autoload();
        // The method signature already guarantees RouteCollection return type
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testSupportsAlwaysReturnsFalse(): void
    {
        self::assertFalse($this->loader->supports('anything'));
        self::assertFalse($this->loader->supports('anything', 'annotation'));
    }

    public function testLoaderHasCorrectAnnotations(): void
    {
        $reflection = new \ReflectionClass(AttributeControllerLoader::class);
        $attributes = $reflection->getAttributes();

        $hasAutoConfigureTagAttribute = false;
        foreach ($attributes as $attribute) {
            if (str_contains($attribute->getName(), 'AutoconfigureTag')) {
                $hasAutoConfigureTagAttribute = true;
                break;
            }
        }

        self::assertTrue($hasAutoConfigureTagAttribute, 'Loader应该有AutoconfigureTag注解');
    }
}
