<?php

namespace CreditBundle\Tests\Unit\Enum;

use CreditBundle\Enum\AdjustRequestStatus;
use PHPUnit\Framework\TestCase;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;

class AdjustRequestStatusTest extends TestCase
{
    public function testImplementsInterfaces(): void
    {
        $status = AdjustRequestStatus::EXAMINE;
        
        $this->assertInstanceOf(Labelable::class, $status);
        $this->assertInstanceOf(Itemable::class, $status);
        $this->assertInstanceOf(Selectable::class, $status);
    }

    public function testEnumCases(): void
    {
        $this->assertEquals(1, AdjustRequestStatus::EXAMINE->value);
        $this->assertEquals(2, AdjustRequestStatus::PASS->value);
        $this->assertEquals(3, AdjustRequestStatus::TURN_DOWN->value);
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('审核中', AdjustRequestStatus::EXAMINE->getLabel());
        $this->assertEquals('通过', AdjustRequestStatus::PASS->getLabel());
        $this->assertEquals('拒绝', AdjustRequestStatus::TURN_DOWN->getLabel());
    }

    public function testGenOptions(): void
    {
        $options = AdjustRequestStatus::genOptions();
        
        $this->assertCount(3, $options);
    }

    public function testToSelectItem(): void
    {
        $item = AdjustRequestStatus::EXAMINE->toSelectItem();
        
        $this->assertEquals('审核中', $item['label']);
        $this->assertEquals(1, $item['value']);
    }
}