<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\TransferErrorLog;
use PHPUnit\Framework\TestCase;

class TransferErrorLogTest extends TestCase
{
    public function testEntityCreation(): void
    {
        $entity = new TransferErrorLog();
        self::assertInstanceOf(TransferErrorLog::class, $entity);
    }
} 