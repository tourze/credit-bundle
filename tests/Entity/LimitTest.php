<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\Limit;
use CreditBundle\Enum\LimitType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Limit::class)]
final class LimitTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Limit();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'type' => ['type', LimitType::DAILY_OUT_LIMIT],
            'value' => ['value', 1000],
            'remark' => ['remark', 'Test limit'],
        ];
    }
}
