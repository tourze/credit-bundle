<?php

namespace CreditBundle\Tests\Enum;

use CreditBundle\Enum\LimitType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(LimitType::class)]
final class LimitTypeTest extends AbstractEnumTestCase
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

    public function testToArray(): void
    {
        $array = LimitType::TOTAL_OUT_LIMIT->toArray();

        $this->assertEquals(['value' => 'total-out-limit', 'label' => '总限制转出'], $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);

        $arrayDaily = LimitType::DAILY_OUT_LIMIT->toArray();
        $this->assertEquals(['value' => 'daily-out-limit', 'label' => '每日限制转出'], $arrayDaily);

        $arrayDailyIn = LimitType::DAILY_IN_LIMIT->toArray();
        $this->assertEquals(['value' => 'daily-in-limit', 'label' => '每日限制转入'], $arrayDailyIn);

        $arrayCredit = LimitType::CREDIT_LIMIT->toArray();
        $this->assertEquals(['value' => 'credit-limit', 'label' => '信用额度'], $arrayCredit);
    }
}
