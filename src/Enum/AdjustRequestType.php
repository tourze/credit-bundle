<?php

declare(strict_types=1);

namespace CreditBundle\Enum;

use Tourze\EnumExtra\BadgeInterface;
use Tourze\EnumExtra\Itemable;
use Tourze\EnumExtra\ItemTrait;
use Tourze\EnumExtra\Labelable;
use Tourze\EnumExtra\Selectable;
use Tourze\EnumExtra\SelectTrait;

/**
 * 积分调整请求类型
 */
enum AdjustRequestType: string implements Labelable, Itemable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;

    case INCREASE = 'increase';
    case DECREASE = 'decrease';

    public function getLabel(): string
    {
        return match ($this) {
            self::INCREASE => '增加',
            self::DECREASE => '减少',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::INCREASE => self::SUCCESS,
            self::DECREASE => self::WARNING,
        };
    }
}
