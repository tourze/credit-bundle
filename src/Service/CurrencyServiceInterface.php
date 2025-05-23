<?php

namespace CreditBundle\Service;

use Brick\Money\Currency;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
interface CurrencyServiceInterface
{
    /**
     * @return iterable<Currency>
     */
    public function getCurrencies(): iterable;

    /**
     * 读取币种
     */
    public function findByCode(string $currency): ?Currency;
}
