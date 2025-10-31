<?php

namespace CreditBundle\Tests\Enum;

use CreditBundle\Enum\AdjustRequestStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\PHPUnitEnum\AbstractEnumTestCase;

/**
 * @internal
 */
#[CoversClass(AdjustRequestStatus::class)]
final class AdjustRequestStatusTest extends AbstractEnumTestCase
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

    public function testToArray(): void
    {
        $array = AdjustRequestStatus::EXAMINE->toArray();

        $this->assertEquals(['value' => 1, 'label' => '审核中'], $array);
        $this->assertArrayHasKey('value', $array);
        $this->assertArrayHasKey('label', $array);

        $arrayPass = AdjustRequestStatus::PASS->toArray();
        $this->assertEquals(['value' => 2, 'label' => '通过'], $arrayPass);

        $arrayTurnDown = AdjustRequestStatus::TURN_DOWN->toArray();
        $this->assertEquals(['value' => 3, 'label' => '拒绝'], $arrayTurnDown);
    }
}
