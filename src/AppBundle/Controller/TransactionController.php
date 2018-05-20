<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Entity\Transaction;
use AppBundle\Form\ImportTransactionType;
use AppBundle\Form\TransactionType;
use AppBundle\Helper\ImportTransactionsHelper;
use AppBundle\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TransactionController
 */
class TransactionController extends Controller
{
    /** @var TransactionRepository */
    private $transactionRepository;

    /** @var ImportTransactionsHelper */
    private $importTransactionsHelper;

    /**
     * TransactionController constructor.
     *
     * @param TransactionRepository    $transactionRepository
     * @param ImportTransactionsHelper $importTransactionsHelper
     */
    public function __construct(
        TransactionRepository $transactionRepository,
        ImportTransactionsHelper $importTransactionsHelper
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->importTransactionsHelper = $importTransactionsHelper;
    }

    /**
     * @Route("/transaction/account-{id}/create", name="create_transaction",
     *      requirements={
     *          "id": "\d+",
     *      }
     * )
     *
     * @Route("/transaction/account-{id}/edit/{transactionId}", name="edit_transaction",
     *      requirements={
     *          "id": "\d+",
     *          "transactionId": "\d+",
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Account")
     *
     * @param Account $account
     * @param Request $request
     * @param int     $transactionId
     *
     * @return JsonResponse|Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createTransactionAction(Account $account, Request $request, $transactionId = null)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $transaction = new Transaction();

        if ($transactionId) {
            $transaction = $this
                ->transactionRepository
                ->find($transactionId)
            ;
        }

        $form = $this->createForm(TransactionType::class, $transaction);

        $form->handleRequest($request);

        $json = ['success' => false];

        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            if ($form->isValid()) {
                $transaction = $form->getData();
                $transaction->setAccount($account);

                $this->transactionRepository->save($transaction);

                $json['success'] = true;
                $json['id'] = $transaction->getId();
            }

            return new JsonResponse($json);
        }

        return $this->render('transaction/form.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/transaction/check/{id}", name="check_transaction")
     *
     * @param int $id
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function checkTransactionAction($id = null)
    {
        /** @var Transaction $transaction */
        $transaction = $this
            ->transactionRepository
            ->find($id)
        ;

        if (!$transaction || $this->getUser() !== $transaction->getAccount()->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this transaction.');
        }

        $transaction->setChecked(!$transaction->isChecked());

        $this
            ->transactionRepository
            ->save($transaction)
        ;

        $json = [
            'success' => true,
            'id' => $transaction->getId(),
        ];

        return new JsonResponse($json);
    }

    /**
     * @Route("/transactions/list/account-{id}", name="list_transactions",
     *      requirements={
     *          "id": "\d+",
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Account")
     *
     * @param Account $account
     *
     * @return Response
     */
    public function listTransactionsAction(Account $account)
    {
        return $this->render('transaction/list.html.twig', [
            'account' => $account,
        ]);
    }

    /**
     * @Route("/transactions/list/account-{id}.json", name="list_json_transactions",
     *      requirements={
     *          "id": "\d+",
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Account")
     *
     * @param Account $account
     *
     * @return Response
     */
    public function listJsonTransactionsAction(Account $account)
    {
        $transactions = $this
            ->transactionRepository
            ->getTransactions($account)
        ;

        $json = array();

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $json['data'][] = [
                'id' => $transaction->getId(),
                'transactionAt' => $transaction->getTransactionAt() ? $transaction->getTransactionAt()->format('d/m/Y') : null,
                'hash' => $transaction->getHash(),
                'description' => $transaction->getDescription(),
                'checked' => $transaction->isChecked() ? '✓' : null,
                'amount' => number_format($transaction->getAmount() / 100, 2, ',', ' ').'€',
            ];
        }

        return new JsonResponse($json);
    }

    /**
     * @Route("/transactions/import/account-{id}", name="import_transactions",
     *      requirements={
     *          "id": "\d+",
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Account")
     *
     * @param Account $account
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function importTransactionsAction(Account $account, Request $request)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $form = $this->createForm(ImportTransactionType::class, null, array(
            'action' => $this->generateUrl('import_transactions', ['id' => $account->getId()]),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this
                    ->importTransactionsHelper
                    ->importFromQif($form->get('file')->getData(), $account)
                ;

                return $this->redirectToRoute('account', ['id' => $account->getId(), 'slug' => $account->getSlug()]);
            } else {
                $this->addFlash(
                    'error',
                    'Erreur lors de l\'import.'
                );

                return $this->redirectToRoute('edit_account', ['id' => $account->getId(), 'slug' => $account->getSlug()]);
            }
        }

        return $this->render('transaction/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
