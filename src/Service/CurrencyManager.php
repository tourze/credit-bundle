<?php

namespace CreditBundle\Service;

use Brick\Money\Currency;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\EnumExtra\SelectDataFetcher;

/**
 * 通过这个服务获取系统所有货币的信息
 */
#[Autoconfigure(lazy: true, public: true)]
#[AutoconfigureTag('as-coroutine')]
class CurrencyManager implements SelectDataFetcher
{
    public function __construct(
        private readonly CurrencyServiceInterface $currencyService,
    ) {
    }

    public function genSelectData(): array
    {
        $arr = [];
        foreach ($this->currencyService->getCurrencies() as $item) {
            $arr[] = [
                'label' => $item->getName(),
                'text' => $item->getName(),
                'value' => $item->getCurrencyCode(),
                'name' => $item->getName(),
            ];
        }

        return $arr;
    }

    /**
     * 根据code获取币种信息
     */
    public function getCurrencyByCode(string $code): ?Currency
    {
        foreach ($this->currencyService->getCurrencies() as $currency) {
            if ($currency->getCurrencyCode() === $code) {
                return $currency;
            }
        }

        return null;
    }

    public function getCurrencyName(string $code): string
    {
        $currency = $this->getCurrencyByCode($code);

        return $currency ? $currency->getName() : $code;
    }

    /**
     * 获取价格字符串
     *
     * 有两种风格，以 5.00 为例：
     * 1 默认风格，返回 5；
     * 2 TO B 风格，返回 5.00；
     */
    public function getPriceNumber(string|float|int $money): string
    {
        $money = round($money, $_ENV['DEFAULT_PRICE_PRECISION']);
        $money = (string) $money;

        if ('default' === $_ENV['PRICE_PRECISION_STYLE']) {
            $i = $_ENV['DEFAULT_PRICE_PRECISION'];
            while ($i > 0) {
                if (str_contains($money, '.') && str_ends_with($money, '0')) {
                    $money = rtrim($money, '0');
                }
                --$i;
            }
        }
        if ('to-b' === $_ENV['PRICE_PRECISION_STYLE']) {
            $money = sprintf("%.{$_ENV['DEFAULT_PRICE_PRECISION']}f", $money);
        }

        return $money;
    }

    /**
     * 获取个时候的价格数值
     */
    public function getDisplayPrice(string $code, string|float|int $money): string
    {
        $money = $this->getPriceNumber($money);

        //        $defaultMap = [
        //            'CNY' => '元',
        //            'INTEGRAL' => '积分',
        //            'C_Point' => '积分',
        //            'Z_Point' => 'Z币',
        //        ];
        //        $name = $price->getCurrency();
        //        if (isset($defaultMap[$price->getCurrency()])) {
        //            $name = $defaultMap[$price->getCurrency()];
        //        }

        return "{$money}{$this->getCurrencyName($code)}";
    }
}
