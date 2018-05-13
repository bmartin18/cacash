<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Transaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class TransactionRepository
 */
class TransactionRepository extends ServiceEntityRepository
{
    /**
     * TransactionRepository constructor.
     *
     * @param ManagerRegistry $registry
     * @param string          $entityClass
     */
    public function __construct(ManagerRegistry $registry, $entityClass = Transaction::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Transaction $transaction)
    {
        $this->getEntityManager()->persist($transaction);
        $this->getEntityManager()->flush();

        return $transaction;
    }

    /**
     * @param Transaction $transaction
     *
     * @return Transaction
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Transaction $transaction)
    {
        $this->getEntityManager()->remove($transaction);
        $this->getEntityManager()->flush();

        return $transaction;
    }
}
