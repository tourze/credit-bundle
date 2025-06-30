<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Service;

use CreditBundle\Service\AccountService;
use PHPUnit\Framework\TestCase;

class AccountServiceTest extends TestCase
{
    public function testServiceCreation(): void
    {
        $repository = $this->createMock(\CreditBundle\Repository\AccountRepository::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $dispatcher = $this->createMock(\Symfony\Component\EventDispatcher\EventDispatcherInterface::class);
        $transRepo = $this->createMock(\CreditBundle\Repository\TransactionRepository::class);
        $em = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);
        $lockService = $this->createMock(\Tourze\LockServiceBundle\Service\LockService::class);

        $service = new AccountService($repository, $logger, $dispatcher, $transRepo, $em, $lockService);

        self::assertInstanceOf(AccountService::class, $service);
    }
}
