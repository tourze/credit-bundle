<?php

namespace CreditBundle\Service;

use CreditBundle\Repository\CurrencyRepository;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\EnumExtra\SelectDataFetcher;

#[AutoconfigureTag('box-code.attribute-type.provider')]
class AttributeTypeProvider implements SelectDataFetcher
{
    public const PREFIX = 'credit:';

    public const SPECIAL = 'special_credit';

    public function __construct(private readonly CurrencyRepository $currencyRepository)
    {
    }

    public function genSelectData(): iterable
    {
        $models = $this->currencyRepository->findBy(['valid' => true]);
        foreach ($models as $model) {
            yield [
                'label' => $model->getName(),
                'text' => $model->getName(),
                'value' => self::PREFIX . $model->getCurrency(),
                'name' => $model->getName(),
            ];
        }

        yield [
            'label' => '特定积分',
            'text' => '特定积分',
            'value' => self::SPECIAL,
            'name' => '特定积分',
        ];
    }
}
