<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\TransferLog;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(TransferLog::class)]
final class TransferLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new TransferLog();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'currency' => ['currency', 'USD'],
            'outAmount' => ['outAmount', '100.00'],
            'inAmount' => ['inAmount', '95.00'],
            'remark' => ['remark', 'Test transfer'],
            'relationId' => ['relationId', 'REL-001'],
            'relationModel' => ['relationModel', 'TestModel'],
            'expireTime' => ['expireTime', new \DateTimeImmutable('+30 days')],
        ];
    }
}
