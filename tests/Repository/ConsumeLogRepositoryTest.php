<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Repository\ConsumeLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ConsumeLogRepositoryTest extends TestCase
{
    public function testRepositoryCreation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        $repository = new ConsumeLogRepository($registry);

        self::assertInstanceOf(ConsumeLogRepository::class, $repository);
    }
}
