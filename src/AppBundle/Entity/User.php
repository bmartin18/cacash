<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="`user`")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="Account", mappedBy="user")
     */
    private $accounts;

    /**
     * User constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->roles = array('ROLE_USER');
        $this->accounts = new ArrayCollection();
    }

    /**
     * Add account.
     *
     * @param Account $account
     *
     * @return User
     */
    public function addAccount(Account $account)
    {
        $this->accounts[] = $account;

        return $this;
    }

    /**
     * Remove account.
     *
     * @param Account $account
     *
     * @return boolean TRUE if this collection contained the specified element, FALSE otherwise.
     */
    public function removeAccount(Account $account)
    {
        return $this->accounts->removeElement($account);
    }

    /**
     * Get accounts.
     *
     * @return Collection
     */
    public function getAccounts()
    {
        return $this->accounts;
    }
}
