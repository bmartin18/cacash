<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction", indexes={@ORM\Index(name="order_by_date", columns={"transaction_at"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TransactionRepository")
 */
class Transaction
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="hash", type="string", length=255, nullable=true)
     */
    private $hash;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255)
     *
     * @Assert\NotBlank()
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="checked", type="boolean")
     */
    private $checked = false;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer")
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="transaction_at", type="date", nullable=true)
     */
    private $transactionAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="create")
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="Account", inversedBy="transactions")
     * @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     */
    private $account;


    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set hash.
     *
     * @param string|null $hash
     *
     * @return Transaction
     */
    public function setHash($hash = null)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Transaction
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set checked.
     *
     * @param bool $checked
     *
     * @return Transaction
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get checked.
     *
     * @return bool
     */
    public function isChecked()
    {
        return $this->checked;
    }

    /**
     * Set amount.
     *
     * @param int $amount
     *
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set amountDisplayable.
     *
     * @param int $amountDisplayable
     *
     * @return Transaction
     */
    public function setAmountDisplayable($amountDisplayable)
    {
        $this->amount = $amountDisplayable * 100;

        return $this;
    }

    /**
     * Get amountDisplayable.
     *
     * @return null|int
     */
    public function getAmountDisplayable()
    {
        if ($this->amount === null) {
            return null;
        }

        return $this->amount < 0 ? $this->amount * -1 / 100 : $this->amount / 100;
    }

    /**
     * Set transactionAt.
     *
     * @param \DateTime|null $transactionAt
     *
     * @return Transaction
     */
    public function setTransactionAt($transactionAt = null)
    {
        $this->transactionAt = $transactionAt;

        return $this;
    }

    /**
     * Get transactionAt.
     *
     * @return \DateTime|null
     */
    public function getTransactionAt()
    {
        return $this->transactionAt;
    }

    /**
     * Set transactionAtDisplayable.
     *
     * @param string|null $transactionAtDisplayable
     *
     * @return Transaction
     */
    public function setTransactionAtDisplayable($transactionAtDisplayable = null)
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp(strtotime(str_replace('/', '-', $transactionAtDisplayable)));

        $this->transactionAt = $dateTime;

        return $this;
    }

    /**
     * Get transactionAtDisplayable.
     *
     * @return string|null
     */
    public function getTransactionAtDisplayable()
    {
        if ($this->transactionAt !== null) {
            return $this->transactionAt->format('d/m/Y');
        }

        return null;
    }

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return Transaction
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return Transaction
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set debit.
     *
     * @param bool $debit
     *
     * @return $this
     */
    public function setDebit($debit)
    {
        if ($debit && $this->getAmount() > 0 || !$debit && $this->getAmount() < 0) {
            $this->setAmount($this->getAmount() * -1);
        }

        return $this;
    }

    /**
     * Get debit.
     *
     * @return bool
     */
    public function isDebit()
    {
        if ($this->getAmount() <= 0) {
            return true;
        }

        return false;
    }

    /**
     * Set account.
     *
     * @param Account $account
     *
     * @return Transaction
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account.
     *
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }
}
