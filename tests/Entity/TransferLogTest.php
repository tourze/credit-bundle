<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\TransferLog;
use PHPUnit\Framework\TestCase;

class TransferLogTest extends TestCase
{
    public function testEntityCreation(): void
    {
        $entity = new TransferLog();
        self::assertInstanceOf(TransferLog::class, $entity);
    }
}
