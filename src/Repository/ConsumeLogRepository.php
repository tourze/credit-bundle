<?php

namespace CreditBundle\Repository;

use CreditBundle\Entity\ConsumeLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;

/**
 * @method ConsumeLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConsumeLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsumeLog[]    findAll()
 * @method ConsumeLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsumeLogRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumeLog::class);
    }
}
