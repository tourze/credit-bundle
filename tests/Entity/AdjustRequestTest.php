<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Entity;

use CreditBundle\Entity\AdjustRequest;
use PHPUnit\Framework\TestCase;

class AdjustRequestTest extends TestCase
{
    public function testEntityCreation(): void
    {
        $entity = new AdjustRequest();
        self::assertInstanceOf(AdjustRequest::class, $entity);
    }
}
