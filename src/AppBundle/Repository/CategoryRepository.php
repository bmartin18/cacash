<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Category;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Class CategoryRepository
 */
class CategoryRepository extends ServiceEntityRepository
{
    /**
     * CategoryRepository constructor.
     *
     * @param ManagerRegistry $registry
     * @param string          $entityClass
     */
    public function __construct(ManagerRegistry $registry, $entityClass = Category::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * @param User $user
     *
     * @return Category[]
     */
    public function getParentList(User $user)
    {
        return $this
            ->createQueryBuilder('c')
            ->where('c.user = :user')
                ->setParameter('user', $user)
                ->andWhere('c.parent IS NULL')
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Category $category
     *
     * @return Category
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(Category $category)
    {
        $this->getEntityManager()->persist($category);
        $this->getEntityManager()->flush();

        return $category;
    }

    /**
     * @param Category $category
     *
     * @return Category
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete(Category $category)
    {
        $this->getEntityManager()->remove($category);
        $this->getEntityManager()->flush();

        return $category;
    }
}
