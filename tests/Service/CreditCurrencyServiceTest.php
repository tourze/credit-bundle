<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\CreditCurrencyService;
use PHPUnit\Framework\TestCase;

class CreditCurrencyServiceTest extends TestCase
{
    public function testServiceCreation(): void
    {
        $innerService = $this->createMock(\CreditBundle\Service\CurrencyServiceInterface::class);
        $repository = $this->createMock(\CreditBundle\Repository\CurrencyRepository::class);
        $service = new CreditCurrencyService($innerService, $repository);

        self::assertInstanceOf(CreditCurrencyService::class, $service);
    }

    public function testGetCurrencies(): void
    {
        $innerService = $this->createMock(\CreditBundle\Service\CurrencyServiceInterface::class);
        $innerService->method('getCurrencies')->willReturn([]);

        $currency = $this->createMock(\CreditBundle\Entity\Currency::class);
        $currency->method('getCurrency')->willReturn('TEST');
        $currency->method('getName')->willReturn('测试币种');

        $repository = $this->createMock(\CreditBundle\Repository\CurrencyRepository::class);
        $repository->method('findBy')->willReturn([$currency]);

        $service = new CreditCurrencyService($innerService, $repository);
        $currencies = $service->getCurrencies();

        self::assertInstanceOf(\Generator::class, $currencies);
        
        $currencyArray = iterator_to_array($currencies);
        self::assertNotEmpty($currencyArray);
        self::assertInstanceOf(\Brick\Money\Currency::class, $currencyArray[0]);
        self::assertEquals('TEST', $currencyArray[0]->getCurrencyCode());
        self::assertEquals(0, $currencyArray[0]->getNumericCode());
    }
}
