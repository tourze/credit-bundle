<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Event;

use CreditBundle\Event\GetAccountValidPointEvent;
use CreditBundle\Tests\AbstractTestCase;

class GetAccountValidPointEventTest extends AbstractTestCase
{
    public function testBasicEventCreation(): void
    {
        $event = new GetAccountValidPointEvent();

        self::assertInstanceOf(GetAccountValidPointEvent::class, $event);
        self::assertNull($event->getResult());
    }

    public function testEventWithAccount(): void
    {
        $account = $this->createMock(\CreditBundle\Entity\Account::class);
        $event = new GetAccountValidPointEvent();

        $event->setAccount($account);

        self::assertSame($account, $event->getAccount());
        self::assertNull($event->getResult());
    }

    public function testEventWithResult(): void
    {
        $account = $this->createMock(\CreditBundle\Entity\Account::class);
        $event = new GetAccountValidPointEvent();

        $event->setAccount($account);
        $event->setResult(150.25);

        self::assertSame($account, $event->getAccount());
        self::assertSame(150.25, $event->getResult());
    }
}
