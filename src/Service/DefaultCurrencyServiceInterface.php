<?php

namespace CreditBundle\Service;

use Brick\Money\Currency;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(CurrencyServiceInterface::class, public: true)]
class DefaultCurrencyServiceInterface implements CurrencyServiceInterface
{
    public const CODE = 'CNY';

    private Currency $cny;

    public function __construct()
    {
        $this->cny = new Currency(self::CODE, 0, '元', 2);
    }

    public function getCurrencies(): iterable
    {
        yield $this->cny;
    }

    public function findByCode(string $currency): ?Currency
    {
        if (self::CODE === $currency) {
            return $this->cny;
        }

        return null;
    }
}
