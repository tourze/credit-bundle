<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Repository\LimitRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class LimitRepositoryTest extends TestCase
{
    public function testRepositoryCreation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        $repository = new LimitRepository($registry);

        self::assertInstanceOf(LimitRepository::class, $repository);
    }
}
