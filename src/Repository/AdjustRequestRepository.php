<?php

namespace CreditBundle\Repository;

use CreditBundle\Entity\AdjustRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

/**
 * @method AdjustRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdjustRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdjustRequest[]    findAll()
 * @method AdjustRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
#[Autoconfigure(public: true)]
class AdjustRequestRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdjustRequest::class);
    }
}
