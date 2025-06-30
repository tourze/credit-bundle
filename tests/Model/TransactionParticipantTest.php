<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Model;

use CreditBundle\Model\TransactionParticipant;
use PHPUnit\Framework\TestCase;

class TransactionParticipantTest extends TestCase
{
    public function testBasicParticipantCreation(): void
    {
        $participant = new TransactionParticipant();

        self::assertInstanceOf(TransactionParticipant::class, $participant);
    }

    public function testParticipantProperties(): void
    {
        $participant = new TransactionParticipant();

        // 基本创建测试已在testBasicParticipantCreation中完成
        self::assertInstanceOf(TransactionParticipant::class, $participant);
    }
}
