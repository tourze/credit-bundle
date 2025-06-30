<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\DefaultCurrencyServiceInterface;
use PHPUnit\Framework\TestCase;

class DefaultCurrencyServiceInterfaceTest extends TestCase
{
    public function testServiceCreation(): void
    {
        $service = new DefaultCurrencyServiceInterface();

        self::assertInstanceOf(DefaultCurrencyServiceInterface::class, $service);
    }

    public function testGetCurrencies(): void
    {
        $service = new DefaultCurrencyServiceInterface();
        $currencies = $service->getCurrencies();

        self::assertInstanceOf(\Generator::class, $currencies);

        $currencyArray = iterator_to_array($currencies);
        self::assertNotEmpty($currencyArray);
    }

    public function testFindByCode(): void
    {
        $service = new DefaultCurrencyServiceInterface();

        $currency = $service->findByCode('CNY');
        self::assertNotNull($currency);

        $invalidCurrency = $service->findByCode('INVALID');
        self::assertNull($invalidCurrency);
    }
}
