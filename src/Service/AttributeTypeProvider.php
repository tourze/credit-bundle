<?php

declare(strict_types=1);

namespace CreditBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Tourze\EnumExtra\SelectDataFetcher;

#[AutoconfigureTag(name: 'box-code.attribute-type.provider')]
class AttributeTypeProvider implements SelectDataFetcher
{
    public const PREFIX = 'credit:';

    public const SPECIAL = 'special_credit';

    public function genSelectData(): iterable
    {
        // 提供常见的币种选项
        $currencies = [
            ['code' => 'CNY', 'name' => '人民币'],
            ['code' => 'USD', 'name' => '美元'],
            ['code' => 'EUR', 'name' => '欧元'],
            ['code' => 'POINT', 'name' => '积分'],
        ];

        foreach ($currencies as $currency) {
            yield [
                'label' => $currency['name'],
                'text' => $currency['name'],
                'value' => self::PREFIX . $currency['code'],
                'name' => $currency['name'],
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
