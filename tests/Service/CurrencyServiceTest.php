<?php

namespace CreditBundle\Tests\Service;

use Brick\Money\Currency;
use CreditBundle\Service\DefaultCurrencyServiceInterface;
use PHPUnit\Framework\TestCase;

class CurrencyServiceTest extends TestCase
{
    private DefaultCurrencyServiceInterface $currencyService;

    protected function setUp(): void
    {
        $this->currencyService = new DefaultCurrencyServiceInterface();
    }

    public function testGetCurrencies_returnsExpectedCurrencies(): void
    {
        $currencies = iterator_to_array($this->currencyService->getCurrencies());

        $this->assertCount(1, $currencies);
        $this->assertInstanceOf(Currency::class, $currencies[0]);
        $this->assertSame(DefaultCurrencyServiceInterface::CODE, $currencies[0]->getCurrencyCode());
        $this->assertSame('元', $currencies[0]->getName());
        $this->assertSame(2, $currencies[0]->getDefaultFractionDigits());
    }

    public function testFindByCode_withValidCode_returnsCurrency(): void
    {
        $currency = $this->currencyService->findByCode(DefaultCurrencyServiceInterface::CODE);

        $this->assertInstanceOf(Currency::class, $currency);
        $this->assertSame(DefaultCurrencyServiceInterface::CODE, $currency->getCurrencyCode());
        $this->assertSame('元', $currency->getName());
        $this->assertSame(2, $currency->getDefaultFractionDigits());
    }

    public function testFindByCode_withInvalidCode_returnsNull(): void
    {
        $currency = $this->currencyService->findByCode('INVALID_CODE');

        $this->assertNull($currency);
    }
}
