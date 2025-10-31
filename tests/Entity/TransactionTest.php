<?php

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\Transaction;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Transaction::class)]
final class TransactionTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Transaction();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'eventNo' => ['eventNo', 'TEST-001'],
            'amount' => ['amount', '100.00'],
            'balance' => ['balance', '500.00'],
            'currency' => ['currency', 'CNY'],
            'remark' => ['remark', 'Test transaction'],
            'relationId' => ['relationId', 'REL-001'],
            'relationModel' => ['relationModel', 'TestModel'],
            'expireTime' => ['expireTime', new \DateTimeImmutable('+30 days')],
            'context' => ['context', ['key1' => 'value1']],
        ];
    }
}
