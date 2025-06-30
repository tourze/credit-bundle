<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\ConsumeLog;
use PHPUnit\Framework\TestCase;

class ConsumeLogTest extends TestCase
{
    public function testEntityCreation(): void
    {
        $entity = new ConsumeLog();
        self::assertInstanceOf(ConsumeLog::class, $entity);
    }
}
