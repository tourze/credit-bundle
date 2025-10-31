<?php

declare(strict_types=1);

namespace CreditBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TestDataFactory::class)]
final class TestDataFactoryTest extends TestCase
{
    public function testCreateCurrency(): void
    {
        $currency = TestDataFactory::createCurrency();

        // 测试货币代码
        self::assertNotEmpty($currency);
        self::assertIsString($currency);
    }

    public function testCreateAccount(): void
    {
        $account = TestDataFactory::createAccount();

        // 测试账户属性
        self::assertNotEmpty($account->getName());
        self::assertNotEmpty($account->getCurrency());
    }

    public function testCreateTransaction(): void
    {
        $transaction = TestDataFactory::createTransaction();

        // 测试交易属性
        self::assertNotEmpty($transaction->getEventNo());
        self::assertNotEmpty($transaction->getAmount());
        self::assertNotEmpty($transaction->getAccount()->getName());
    }
}
