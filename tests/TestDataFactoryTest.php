<?php

declare(strict_types=1);

namespace CreditBundle\Tests;

use PHPUnit\Framework\TestCase;

class TestDataFactoryTest extends TestCase
{
    private TestDataFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new TestDataFactory();
    }

    public function testFactoryCreation(): void
    {
        self::assertInstanceOf(TestDataFactory::class, $this->factory);
    }

    public function testCreateCurrency(): void
    {
        $currency = $this->factory->createCurrency();

        self::assertInstanceOf(\CreditBundle\Entity\Currency::class, $currency);
    }

    public function testCreateAccount(): void
    {
        $account = $this->factory->createAccount();

        self::assertInstanceOf(\CreditBundle\Entity\Account::class, $account);
    }

    public function testCreateTransaction(): void
    {
        $transaction = $this->factory->createTransaction();

        self::assertInstanceOf(\CreditBundle\Entity\Transaction::class, $transaction);
    }
}
