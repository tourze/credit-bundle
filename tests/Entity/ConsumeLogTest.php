<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\ConsumeLog;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(ConsumeLog::class)]
final class ConsumeLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new ConsumeLog();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'costTransaction' => ['costTransaction', null],
            'consumeTransaction' => ['consumeTransaction', null],
            'amount' => ['amount', '50.00'],
        ];
    }
}
