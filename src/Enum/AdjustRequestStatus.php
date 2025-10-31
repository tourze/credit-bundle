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
 * 积分调整请求状态
 */
enum AdjustRequestStatus: int implements Labelable, Itemable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;

    case EXAMINE = 1;
    case PASS = 2;
    case TURN_DOWN = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::EXAMINE => '审核中',
            self::PASS => '通过',
            self::TURN_DOWN => '拒绝',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::EXAMINE => self::WARNING,
            self::PASS => self::SUCCESS,
            self::TURN_DOWN => self::DANGER,
        };
    }
}
