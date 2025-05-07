<?php

namespace CreditBundle\Service;

use Brick\Money\Currency;
use CreditBundle\Repository\CurrencyRepository;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(decorates: \AppBundle\Service\CurrencyService::class, priority: CreditCurrencyService::PRIORITY)]
class CreditCurrencyService implements \AppBundle\Service\CurrencyService
{
    public const PRIORITY = 99;

    public function __construct(
        #[AutowireDecorated] private readonly \AppBundle\Service\CurrencyService $inner,
        private readonly CurrencyRepository $pointRepository,
    ) {
    }

    public function getCurrencies(): iterable
    {
        foreach ($this->pointRepository->findBy(['valid' => true]) as $point) {
            yield new Currency($point->getCurrency(), 0, $point->getName(), 2);
        }

        foreach ($this->inner->getCurrencies() as $currency) {
            yield $currency;
        }
    }

    public function findByCode(string $currency): ?Currency
    {
        $point = $this->pointRepository->findOneBy(['currency' => $currency]);
        if ($point) {
            return new Currency($point->getCurrency(), 0, $point->getName(), 2);
        }

        return $this->inner->findByCode($currency);
    }
}
