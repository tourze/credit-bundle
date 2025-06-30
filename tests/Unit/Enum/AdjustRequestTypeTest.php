<?php

namespace CreditBundle\Tests\Unit\Enum;

use CreditBundle\Enum\AdjustRequestType;
use PHPUnit\Framework\TestCase;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;

class AdjustRequestTypeTest extends TestCase
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

    public function testToSelectItem(): void
    {
        $item = AdjustRequestType::INCREASE->toSelectItem();
        
        $this->assertEquals('增加', $item['label']);
        $this->assertEquals('increase', $item['value']);
    }
}