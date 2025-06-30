<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Repository\AdjustRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class AdjustRequestRepositoryTest extends TestCase
{
    public function testRepositoryCreation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        $repository = new AdjustRequestRepository($registry);

        self::assertInstanceOf(AdjustRequestRepository::class, $repository);
    }
}
