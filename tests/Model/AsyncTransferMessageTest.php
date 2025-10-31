<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Model;

use CreditBundle\Model\AsyncTransferMessage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AsyncTransferMessage::class)]
final class AsyncTransferMessageTest extends TestCase
{
    public function testBasicMessageCreation(): void
    {
        $message = new AsyncTransferMessage();

        // 测试消息对象创建成功
        // 注意：fromAccount, toAccount 和 amount 属性没有默认值，
        // 在未设置时访问会抛出错误，所以这里只测试 remark 的默认值
        self::assertNull($message->getRemark());
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
