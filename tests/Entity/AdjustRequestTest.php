<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\AdjustRequest;
use CreditBundle\Enum\AdjustRequestStatus;
use CreditBundle\Enum\AdjustRequestType;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(AdjustRequest::class)]
final class AdjustRequestTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new AdjustRequest();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'amount' => ['amount', '100.00'],
            'type' => ['type', AdjustRequestType::INCREASE],
            'status' => ['status', AdjustRequestStatus::EXAMINE],
            'remark' => ['remark', 'Test adjustment'],
        ];
    }
}
