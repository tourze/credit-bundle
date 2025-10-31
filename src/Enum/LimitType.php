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
 * 限制类型
 *
 * @see https://business.twitter.com/zh-cn/help/campaign-setup/spend-caps.html
 */
enum LimitType: string implements Labelable, Itemable, Selectable, BadgeInterface
{
    use ItemTrait;
    use SelectTrait;

    case TOTAL_OUT_LIMIT = 'total-out-limit';
    case DAILY_OUT_LIMIT = 'daily-out-limit';
    case DAILY_IN_LIMIT = 'daily-in-limit';
    case CREDIT_LIMIT = 'credit-limit';

    public function getLabel(): string
    {
        return match ($this) {
            self::TOTAL_OUT_LIMIT => '总限制转出',
            self::DAILY_OUT_LIMIT => '每日限制转出',
            self::DAILY_IN_LIMIT => '每日限制转入',
            self::CREDIT_LIMIT => '信用额度',
        };
    }

    public function getBadge(): string
    {
        return match ($this) {
            self::TOTAL_OUT_LIMIT => self::DANGER,
            self::DAILY_OUT_LIMIT => self::WARNING,
            self::DAILY_IN_LIMIT => self::INFO,
            self::CREDIT_LIMIT => self::PRIMARY,
        };
    }
}
