<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\Account;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(Account::class)]
final class AccountTest extends AbstractEntityTestCase
{
    protected function createEntity(): object
    {
        return new Account();
    }

    /**
     * @return iterable<array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        return [
            'name' => ['name', 'Test Account'],
            'currency' => ['currency', 'CNY'],
            'user' => ['user', null],
            'endingBalance' => ['endingBalance', '100.00'],
            'increasedAmount' => ['increasedAmount', '50.00'],
            'decreasedAmount' => ['decreasedAmount', '30.00'],
            'expiredAmount' => ['expiredAmount', '10.00'],
        ];
    }
}
