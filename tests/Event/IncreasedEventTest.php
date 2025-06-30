<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Event;

use CreditBundle\Event\IncreasedEvent;
use CreditBundle\Tests\AbstractTestCase;

class IncreasedEventTest extends AbstractTestCase
{
    public function testBasicEventCreation(): void
    {
        $event = new IncreasedEvent();

        self::assertInstanceOf(IncreasedEvent::class, $event);
        self::assertNull($event->getAmount());
        self::assertNull($event->getRemark());
        self::assertNull($event->getContext());
    }

    public function testEventWithData(): void
    {
        $account = $this->createMock(\CreditBundle\Entity\Account::class);
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
