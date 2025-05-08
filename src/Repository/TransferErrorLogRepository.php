<?php

namespace CreditBundle\Repository;

use CreditBundle\Entity\TransferErrorLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @method TransferErrorLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransferErrorLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransferErrorLog[]    findAll()
 * @method TransferErrorLog[]    findBy(array $criteria, array $orderBy = null, $TransferErrorLog = null, $offset = null)
 */
class TransferErrorLogRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransferErrorLog::class);
    }
}
