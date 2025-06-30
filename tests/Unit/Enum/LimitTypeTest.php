<?php

namespace CreditBundle\Tests\Unit\Enum;

use CreditBundle\Enum\LimitType;
use PHPUnit\Framework\TestCase;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;

class LimitTypeTest extends TestCase
{
    public function testImplementsInterfaces(): void
    {
        $type = LimitType::TOTAL_OUT_LIMIT;
        
        $this->assertInstanceOf(Labelable::class, $type);
        $this->assertInstanceOf(Itemable::class, $type);
        $this->assertInstanceOf(Selectable::class, $type);
    }

    public function testEnumCases(): void
    {
        $this->assertEquals('total-out-limit', LimitType::TOTAL_OUT_LIMIT->value);
        $this->assertEquals('daily-out-limit', LimitType::DAILY_OUT_LIMIT->value);
        $this->assertEquals('daily-in-limit', LimitType::DAILY_IN_LIMIT->value);
        $this->assertEquals('credit-limit', LimitType::CREDIT_LIMIT->value);
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('总限制转出', LimitType::TOTAL_OUT_LIMIT->getLabel());
        $this->assertEquals('每日限制转出', LimitType::DAILY_OUT_LIMIT->getLabel());
        $this->assertEquals('每日限制转入', LimitType::DAILY_IN_LIMIT->getLabel());
        $this->assertEquals('信用额度', LimitType::CREDIT_LIMIT->getLabel());
    }

    public function testGenOptions(): void
    {
        $options = LimitType::genOptions();
        
        $this->assertCount(4, $options);
    }

    public function testToSelectItem(): void
    {
        $item = LimitType::TOTAL_OUT_LIMIT->toSelectItem();
        
        $this->assertEquals('总限制转出', $item['label']);
        $this->assertEquals('total-out-limit', $item['value']);
    }
}