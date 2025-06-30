<?php

declare(strict_types=1);

namespace CreditBundle\Tests\Repository;

use CreditBundle\Repository\CurrencyRepository;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;

class CurrencyRepositoryTest extends TestCase
{
    public function testRepositoryCreation(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(\Doctrine\Persistence\ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($em);

        $repository = new CurrencyRepository($registry);

        self::assertInstanceOf(CurrencyRepository::class, $repository);
    }
}
