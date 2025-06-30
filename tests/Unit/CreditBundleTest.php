<?php

namespace CreditBundle\Tests\Unit;

use CreditBundle\CreditBundle;
use PHPUnit\Framework\TestCase;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineIndexedBundle\DoctrineIndexedBundle;

class CreditBundleTest extends TestCase
{
    public function testImplementsBundleDependencyInterface(): void
    {
        $bundle = new CreditBundle();
        $this->assertInstanceOf(BundleDependencyInterface::class, $bundle);
    }

    public function testGetBundleDependencies(): void
    {
        $dependencies = CreditBundle::getBundleDependencies();
        
        $this->assertArrayHasKey(DoctrineIndexedBundle::class, $dependencies);
        $this->assertEquals(['all' => true], $dependencies[DoctrineIndexedBundle::class]);
    }
}