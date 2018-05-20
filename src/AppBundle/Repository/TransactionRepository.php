<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Account;
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
     * @param Account $account
     *
     * @return mixed
     */
    public function getTransactions(Account $account)
    {
        return $this
            ->createQueryBuilder('t')
            ->select('t')
            ->addSelect('CASE WHEN t.transactionAt IS NULL THEN 1 ELSE 0 END as HIDDEN transaction_at_is_null')
            ->where('t.account = :account')
            ->setParameter('account', $account)
            ->orderBy('transaction_at_is_null', 'ASC')
            ->addOrderBy('t.transactionAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
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

        $this->updateBalance($transaction->getAccount());

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

        $this->updateBalance($transaction->getAccount());

        return $transaction;
    }

    /**
     * @param Account $account
     *
     * @return Account
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function updateBalance(Account $account)
    {
        $account->setBalance($this->getBalance($account));

        $this->getEntityManager()->persist($account);
        $this->getEntityManager()->flush();

        return $account;
    }

    /**
     * @param Account $account
     *
     * @return int
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getBalance(Account $account)
    {
        $result = $this
            ->createQueryBuilder('t')
            ->select('SUM(t.amount) as balance')
            ->where('t.account = :account')
                ->setParameter('account', $account)
            ->groupBy('t.account')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $result ? $result['balance'] : 0;
    }
}
