<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Model;

use CreditBundle\Model\AsyncTransferMessage;
use PHPUnit\Framework\TestCase;

class AsyncTransferMessageTest extends TestCase
{
    public function testBasicMessageCreation(): void
    {
        $message = new AsyncTransferMessage();

        self::assertInstanceOf(AsyncTransferMessage::class, $message);
    }

    public function testMessageProperties(): void
    {
        $message = new AsyncTransferMessage();

        $message->setFromAccount('account1');
        $message->setToAccount('account2');
        $message->setAmount(100.0);
        $message->setRemark('测试转账');

        self::assertSame('account1', $message->getFromAccount());
        self::assertSame('account2', $message->getToAccount());
        self::assertSame(100.0, $message->getAmount());
        self::assertSame('测试转账', $message->getRemark());
    }
}
