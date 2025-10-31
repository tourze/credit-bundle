<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Model;

use CreditBundle\Model\TransactionParticipant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(TransactionParticipant::class)]
final class TransactionParticipantTest extends TestCase
{
    public function testBasicParticipantCreation(): void
    {
        $participant = new TransactionParticipant();

        // 测试默认值
        self::assertNull($participant->getRemark());
    }

    public function testParticipantProperties(): void
    {
        $participant = new TransactionParticipant();
        $expireTime = new \DateTime('2024-12-31');

        // 测试 setter/getter
        $participant->setName('Test User');
        $participant->setAmount(100.50);
        $participant->setRemark('Test remark');
        $participant->setExpireTime($expireTime);

        self::assertEquals('Test User', $participant->getName());
        self::assertEquals(100.50, $participant->getAmount());
        self::assertEquals('Test remark', $participant->getRemark());
        self::assertEquals($expireTime, $participant->getExpireTime());
    }
}
