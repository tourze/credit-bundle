<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Event;

use CreditBundle\Entity\Account;
use CreditBundle\Event\DecreasedEvent;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(DecreasedEvent::class)]
final class DecreasedEventTest extends AbstractEventTestCase
{
    public function testBasicEventCreation(): void
    {
        $event = new DecreasedEvent();

        // 测试事件初始状态
        self::assertNull($event->getAmount());
        self::assertNull($event->getRemark());
        self::assertNull($event->getContext());
        self::assertTrue($event->isLocalExecute());
    }

    public function testEventWithData(): void
    {
        // 使用真实的 Account 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Account for Decreased Event');
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
