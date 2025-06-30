<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\Limit;
use PHPUnit\Framework\TestCase;

class LimitTest extends TestCase
{
    public function testEntityCreation(): void
    {
        $entity = new Limit();
        self::assertInstanceOf(Limit::class, $entity);
    }
}
