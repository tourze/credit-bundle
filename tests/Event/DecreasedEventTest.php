<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Event;

use CreditBundle\Event\DecreasedEvent;
use CreditBundle\Tests\AbstractTestCase;

class DecreasedEventTest extends AbstractTestCase
{
    public function testBasicEventCreation(): void
    {
        $event = new DecreasedEvent();

        self::assertInstanceOf(DecreasedEvent::class, $event);
        self::assertNull($event->getAmount());
        self::assertNull($event->getRemark());
        self::assertNull($event->getContext());
        self::assertTrue($event->isLocalExecute());
    }

    public function testEventWithData(): void
    {
        $account = $this->createMock(\CreditBundle\Entity\Account::class);
        $event = new DecreasedEvent();

        $event->setAmount(100.50);
        $event->setAccount($account);
        $event->setRemark('测试扣减');
        $event->setEventNo('TEST123');
        $event->setContext(['test' => 'value']);
        $event->setLocalExecute(true);

        self::assertSame(100.50, $event->getAmount());
        self::assertSame($account, $event->getAccount());
        self::assertSame('测试扣减', $event->getRemark());
        self::assertSame('TEST123', $event->getEventNo());
        self::assertSame(['test' => 'value'], $event->getContext());
        self::assertTrue($event->isLocalExecute());
    }
}
