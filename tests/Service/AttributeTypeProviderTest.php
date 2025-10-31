<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\AttributeTypeProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeTypeProvider::class)]
#[RunTestsInSeparateProcesses]
final class AttributeTypeProviderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
    }

    public function testProviderHasRequiredMethods(): void
    {
        $provider = self::getContainer()->get(AttributeTypeProvider::class);
        self::assertInstanceOf(AttributeTypeProvider::class, $provider);

        // 验证提供者可以生成选择数据
        $data = $provider->genSelectData();
        self::assertInstanceOf(\Generator::class, $data);
    }

    public function testGenSelectData(): void
    {
        $provider = self::getContainer()->get(AttributeTypeProvider::class);
        self::assertInstanceOf(AttributeTypeProvider::class, $provider);
        $data = $provider->genSelectData();

        self::assertInstanceOf(\Generator::class, $data);

        $dataArray = iterator_to_array($data);
        self::assertNotEmpty($dataArray);
        self::assertArrayHasKey('label', $dataArray[0]);
        self::assertEquals('人民币', $dataArray[0]['label']);
        self::assertEquals('credit:CNY', $dataArray[0]['value']);

        // 验证特定积分选项
        $lastItem = end($dataArray);
        self::assertEquals('特定积分', $lastItem['label']);
        self::assertEquals('special_credit', $lastItem['value']);
    }
}
