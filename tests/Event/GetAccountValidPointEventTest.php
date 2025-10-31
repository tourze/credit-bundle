<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Event;

use CreditBundle\Entity\Account;
use CreditBundle\Event\GetAccountValidPointEvent;
use CreditBundle\Tests\TestDataFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitSymfonyUnitTest\AbstractEventTestCase;

/**
 * @internal
 */
#[CoversClass(GetAccountValidPointEvent::class)]
final class GetAccountValidPointEventTest extends AbstractEventTestCase
{
    public function testBasicEventCreation(): void
    {
        $event = new GetAccountValidPointEvent();

        // 测试事件初始状态
        self::assertNull($event->getResult());
    }

    public function testEventWithAccount(): void
    {
        // 使用真实的 Account 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Account for ValidPoint Event');
        $event = new GetAccountValidPointEvent();

        $event->setAccount($account);

        self::assertSame($account, $event->getAccount());
        self::assertNull($event->getResult());
    }

    public function testEventWithResult(): void
    {
        // 使用真实的 Account 对象而不是 Mock
        $account = TestDataFactory::createAccount('Test Account with Result');
        $event = new GetAccountValidPointEvent();

        $event->setAccount($account);
        $event->setResult(150.25);

        self::assertSame($account, $event->getAccount());
        self::assertSame(150.25, $event->getResult());
    }
}
