<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Account;
use AppBundle\Entity\Category;
use AppBundle\Entity\Transaction;
use AppBundle\Repository\CategoryRepository;
use AppBundle\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImportTransactionsHelper
 */
class ImportTransactionsHelper
{
    /** @var TransactionRepository */
    private $transactionRepository;

    /** @var CategoryRepository */
    private $categoryRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * ImportTransactionsHelper constructor.
     *
     * @param TransactionRepository  $transactionRepository
     * @param CategoryRepository     $categoryRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        TransactionRepository $transactionRepository,
        CategoryRepository $categoryRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->categoryRepository = $categoryRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param UploadedFile $file
     * @param Account      $account
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function importFromQif(UploadedFile $file, Account $account)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $file = $file->openFile();

        $transaction = new Transaction();
        $transaction->setAccount($account);

        while (false === $file->eof()) {
            $line = $file->fgets();

            if (!isset($line[0])) {
                continue;
            }

            $type = $line[0];
            $content = substr($line, 1);

            switch ($type) {
                case '!':
                    break;

                case '^':
                    $this->entityManager->persist($transaction);

                    $transaction = new Transaction();
                    $transaction->setAccount($account);
                    break;

                case 'N':
                    $transaction->setHash($content);
                    break;

                case 'T':
                    $amount = (float) str_replace(',', '', $content);

                    $transaction->setAmount($amount*100);
                    break;

                case 'P':
                    $transaction->setDescription($content);
                    break;

                case 'C':
                    $transaction->setChecked(true);
                    break;

                case 'L':
                    $content = trim($content);

                    $categories = explode(':', $content);

                    $parent = null;

                    foreach ($categories as $category) {
                        $cat = $this
                            ->categoryRepository
                            ->findOneBy(['name' => $category])
                        ;

                        if (!$cat) {
                            $cat = new Category();
                            $cat->setName($category);
                            $cat->setParent($parent);
                            $cat->setUser($account->getUser());

                            $this->categoryRepository->save($cat);
                        }

                        $parent = $cat;
                    }

                    $transaction->setCategory($parent);
                    break;

                case 'D':
                    $content = explode(' ', str_replace(["'", "/"], " ", $content));

                    if (strlen((int) $content[2]) === 2) {
                        $content[2] = '20'.$content[2];
                    }

                    $timestamp = strtotime(implode('-', $content));

                    $transactionAt = new \DateTime();
                    $transactionAt->setTimestamp($timestamp);

                    $transaction->setTransactionAt($transactionAt);
                    break;

                default:
                    dump($type.' : '.$content);
            }
        }

        $this->entityManager->flush();
        $this->transactionRepository->updateBalance($account);
    }
}
