<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\ConsumeLogService;
use PHPUnit\Framework\TestCase;

class ConsumeLogServiceTest extends TestCase
{
    public function testServiceCreation(): void
    {
        $repository = $this->createMock(\CreditBundle\Repository\TransactionRepository::class);
        $em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        
        $service = new ConsumeLogService($repository, $em);
        
        self::assertInstanceOf(ConsumeLogService::class, $service);
    }
} 