<?php

namespace AppBundle\Helper;

use AppBundle\Entity\Account;
use AppBundle\Entity\Transaction;
use AppBundle\Repository\TransactionRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImportTransactionsHelper
 */
class ImportTransactionsHelper
{
    /** @var TransactionRepository */
    private $transactionRepository;

    /**
     * ImportTransactionsHelper constructor.
     *
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @param UploadedFile $file
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function importFromQif(UploadedFile $file, Account $account)
    {
        set_time_limit(0);

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
                    $this->transactionRepository->save($transaction);

                    $transaction = new Transaction();
                    $transaction->setAccount($account);
                    break;

                case 'N':
                    $transaction->setHash($content);
                    break;

                case 'T':
                    $amount = (int) str_replace(',', '', $content);

                    $transaction->setAmount($amount*100);
                    break;

                case 'P':
                    $transaction->setDescription($content);
                    break;

                case 'C':
                    $transaction->setChecked(true);
                    break;

                case 'L':
                    /* TODO CATEGORY */
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
    }
}
