<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\TransferErrorLog;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(TransferErrorLog::class)]
final class TransferErrorLogTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new TransferErrorLog();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'context' => ['context', ['error' => 'test']],
            'fromAccountId' => ['fromAccountId', '1001'],
            'fromAccountName' => ['fromAccountName', 'Test From Account'],
            'toAccountId' => ['toAccountId', '1002'],
            'toAccountName' => ['toAccountName', 'Test To Account'],
            'currency' => ['currency', 'USD'],
            'amount' => ['amount', 50.50],
            'exception' => ['exception', 'Test exception message'],
        ];
    }
}
