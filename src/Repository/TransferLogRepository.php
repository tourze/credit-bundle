<?php

namespace CreditBundle\Repository;

use CreditBundle\Entity\TransferLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DoctrineEnhanceBundle\Repository\CommonRepositoryAware;

/**
 * @method TransferLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferLog[]    findAll()
 * @method TransferLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransferLogRepository extends ServiceEntityRepository
{
    use CommonRepositoryAware;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferLog::class);
    }
}
