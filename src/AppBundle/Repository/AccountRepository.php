<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class AccountRepository
 */
class AccountRepository extends ServiceEntityRepository
{
    /**
     * AccountRepository constructor.
     *
     * @param ManagerRegistry $registry
     * @param string          $entityClass
     */
    public function __construct(ManagerRegistry $registry, $entityClass = Account::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param Account $account
     *
     * @return Account
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Account $account)
    {
        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();

        return $account;
    }
}
