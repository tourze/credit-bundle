<?php

namespace CreditBundle\Tests\Service;

use Brick\Money\Currency;
use CreditBundle\Service\CurrencyManager;
use CreditBundle\Service\CurrencyServiceInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CurrencyManagerTest extends TestCase
{
    private CurrencyManager $currencyManager;
    private CurrencyServiceInterface|MockObject $currencyService;
    private Currency $cnyCurrency;

    protected function setUp(): void
    {
        $this->cnyCurrency = new Currency('CNY', 0, '元', 2);
        $this->currencyService = $this->createMock(CurrencyServiceInterface::class);
        $this->currencyManager = new CurrencyManager($this->currencyService);

        // 设置环境变量，这些在CurrencyManager中会被使用
        $_ENV['DEFAULT_PRICE_PRECISION'] = 2;
        $_ENV['PRICE_PRECISION_STYLE'] = 'default';
    }

    public function testGenSelectData_returnsFormattedArray(): void
    {
        $this->currencyService->expects($this->once())
            ->method('getCurrencies')
            ->willReturn([$this->cnyCurrency]);

        $result = $this->currencyManager->genSelectData();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals([
            'label' => '元',
            'text' => '元',
            'value' => 'CNY',
            'name' => '元',
        ], $result[0]);
    }

    public function testGetCurrencyByCode_withValidCode_returnsCurrency(): void
    {
        $this->currencyService->expects($this->once())
            ->method('getCurrencies')
            ->willReturn([$this->cnyCurrency]);

        $result = $this->currencyManager->getCurrencyByCode('CNY');

        $this->assertInstanceOf(Currency::class, $result);
        $this->assertSame('CNY', $result->getCurrencyCode());
    }

    public function testGetCurrencyByCode_withInvalidCode_returnsNull(): void
    {
        $this->currencyService->expects($this->once())
            ->method('getCurrencies')
            ->willReturn([$this->cnyCurrency]);

        $result = $this->currencyManager->getCurrencyByCode('INVALID_CODE');

        $this->assertNull($result);
    }

    public function testGetCurrencyName_withValidCode_returnsName(): void
    {
        $this->currencyService->expects($this->once())
            ->method('getCurrencies')
            ->willReturn([$this->cnyCurrency]);

        $result = $this->currencyManager->getCurrencyName('CNY');

        $this->assertSame('元', $result);
    }

    public function testGetCurrencyName_withInvalidCode_returnsCode(): void
    {
        $this->currencyService->expects($this->once())
            ->method('getCurrencies')
            ->willReturn([$this->cnyCurrency]);

        $result = $this->currencyManager->getCurrencyName('INVALID_CODE');

        $this->assertSame('INVALID_CODE', $result);
    }

    public function testGetPriceNumber_withDefaultStyle(): void
    {
        // 默认风格会移除尾随的零
        $this->assertSame('5', $this->currencyManager->getPriceNumber('5.00'));
        $this->assertSame('5.1', $this->currencyManager->getPriceNumber('5.10'));
        $this->assertSame('5.12', $this->currencyManager->getPriceNumber('5.12'));
    }

    public function testGetPriceNumber_withToBStyle(): void
    {
        // 修改为TO B风格
        $_ENV['PRICE_PRECISION_STYLE'] = 'to-b';

        // TO B风格保留所有小数位
        $this->assertSame('5.00', $this->currencyManager->getPriceNumber('5'));
        $this->assertSame('5.10', $this->currencyManager->getPriceNumber('5.1'));
        $this->assertSame('5.12', $this->currencyManager->getPriceNumber('5.12'));
    }

    public function testGetPriceNumber_withDifferentTypes(): void
    {
        // 测试不同类型的输入
        $this->assertSame('5', $this->currencyManager->getPriceNumber(5));
        $this->assertSame('5.1', $this->currencyManager->getPriceNumber(5.1));
        $this->assertSame('5.12', $this->currencyManager->getPriceNumber(5.12));

        // 测试四舍五入
        $this->assertSame('5.12', $this->currencyManager->getPriceNumber(5.123));
        $this->assertSame('5.13', $this->currencyManager->getPriceNumber(5.125));
    }

    public function testGetDisplayPrice_returnsFormattedString(): void
    {
        // 由于getDisplayPrice内部会多次调用getCurrencyByCode，而后者会调用getCurrencies
        // 所以我们需要允许多次调用getCurrencies方法
        $this->currencyService->expects($this->atLeastOnce())
            ->method('getCurrencies')
            ->willReturn([$this->cnyCurrency]);

        $result = $this->currencyManager->getDisplayPrice('CNY', '5.12');
        $this->assertSame('5.12元', $result);

        // 为了测试无效代码场景，我们需要再次设置期望
        // 由于前面已经设置了atLeastOnce，所以这里不需要再设置
        $result = $this->currencyManager->getDisplayPrice('INVALID_CODE', '5.12');
        $this->assertSame('5.12INVALID_CODE', $result);
    }
}
