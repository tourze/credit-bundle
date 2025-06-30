<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\CreditDecreaseService;
use PHPUnit\Framework\TestCase;

class CreditDecreaseServiceTest extends TestCase
{
    public function testServiceCreation(): void
    {
        $accountService = $this->createMock(\CreditBundle\Service\AccountService::class);
        $consumeService = $this->createMock(\CreditBundle\Service\ConsumeLogService::class);
        $repository = $this->createMock(\CreditBundle\Repository\TransactionRepository::class);
        $em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $limitService = $this->createMock(\CreditBundle\Service\TransactionLimitService::class);
        $dispatcher = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $lockService = $this->createMock(\Tourze\LockServiceBundle\Service\LockService::class);
        
        $service = new CreditDecreaseService(
            $accountService, $consumeService, $repository, $em, $limitService, $dispatcher, $lockService
        );
        
        self::assertInstanceOf(CreditDecreaseService::class, $service);
    }
} 