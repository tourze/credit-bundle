<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Repository\TransferLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TransferLogRepositoryTest extends TestCase
{
    public function testRepositoryCreation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        $repository = new TransferLogRepository($registry);

        self::assertInstanceOf(TransferLogRepository::class, $repository);
    }
}
