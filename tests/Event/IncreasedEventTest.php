<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Event;

use CreditBundle\Entity\Account;
use CreditBundle\Event\IncreasedEvent;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(IncreasedEvent::class)]
final class IncreasedEventTest extends AbstractEventTestCase
{
    public function testBasicEventCreation(): void
    {
        $event = new IncreasedEvent();

        // 测试事件初始状态
        self::assertNull($event->getAmount());
        self::assertNull($event->getRemark());
        self::assertNull($event->getContext());
    }

    public function testEventWithData(): void
    {
        // 使用真实的 Account 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Account for Event');
        $event = new IncreasedEvent();

        $event->setAmount(200.75);
        $event->setAccount($account);
        $event->setRemark('测试增加');
        $event->setEventNo('INC456');
        $event->setContext(['type' => 'bonus']);

        self::assertSame(200.75, $event->getAmount());
        self::assertSame($account, $event->getAccount());
        self::assertSame('测试增加', $event->getRemark());
        self::assertSame('INC456', $event->getEventNo());
        self::assertSame(['type' => 'bonus'], $event->getContext());
    }
}
