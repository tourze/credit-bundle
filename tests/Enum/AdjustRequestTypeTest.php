<?php

namespace CreditBundle\Tests\Enum;

use CreditBundle\Enum\AdjustRequestType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(AdjustRequestType::class)]
final class AdjustRequestTypeTest extends AbstractEnumTestCase
{
    public function testImplementsInterfaces(): void
    {
        $type = AdjustRequestType::INCREASE;

        $this->assertInstanceOf(Labelable::class, $type);
        $this->assertInstanceOf(Itemable::class, $type);
        $this->assertInstanceOf(Selectable::class, $type);
    }

    public function testEnumCases(): void
    {
        $this->assertEquals('increase', AdjustRequestType::INCREASE->value);
        $this->assertEquals('decrease', AdjustRequestType::DECREASE->value);
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('增加', AdjustRequestType::INCREASE->getLabel());
        $this->assertEquals('减少', AdjustRequestType::DECREASE->getLabel());
    }

    public function testGenOptions(): void
    {
        $options = AdjustRequestType::genOptions();

        $this->assertCount(2, $options);
    }

    public function testToArray(): void
    {
        $array = AdjustRequestType::INCREASE->toArray();

        $this->assertEquals(['value' => 'increase', 'label' => '增加'], $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);

        $arrayDecrease = AdjustRequestType::DECREASE->toArray();
        $this->assertEquals(['value' => 'decrease', 'label' => '减少'], $arrayDecrease);
    }
}
