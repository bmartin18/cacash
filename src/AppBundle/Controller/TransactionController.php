<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Entity\Transaction;
use AppBundle\Form\ImportTransactionType;
use AppBundle\Form\TransactionType;
use AppBundle\Helper\ImportTransactionsHelper;
use AppBundle\Repository\TransactionRepository;
use SimpleThings\EntityAudit\AuditReader;
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

    /** @var AuditReader */
    private $auditReader;

    /**
     * TransactionController constructor.
     *
     * @param TransactionRepository    $transactionRepository
     * @param ImportTransactionsHelper $importTransactionsHelper
     * @param AuditReader              $auditReader
     */
    public function __construct(
        TransactionRepository $transactionRepository,
        ImportTransactionsHelper $importTransactionsHelper,
        AuditReader $auditReader
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->importTransactionsHelper = $importTransactionsHelper;
        $this->auditReader = $auditReader;
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

        $form = $this->createForm(TransactionType::class, $transaction, [
            'user' => $this->getUser(),
        ]);

        $form->handleRequest($request);

        $json = ['success' => false];

        if ($form->isSubmitted() && $request->isXmlHttpRequest()) {
            if ($form->isValid()) {
                $transaction = $form->getData();
                $transaction->setAccount($account);

                $this->transactionRepository->save($transaction);

                $json['success'] = true;
                $json['id'] = $transaction->getId();
                $json['data'] = $transaction->getApiResponse();
                $json['balance'] = $account->getBalanceDisplayable();
            }

            return new JsonResponse($json);
        }

        return $this->render('transaction/form.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/transaction/delete", name="data_delete_transaction")
     *
     * @Route("/transaction/delete/{id}", name="delete_transaction",
     *      requirements={
     *          "id": "\d+"
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Transaction")
     *
     * @param Transaction $transaction
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteTransactionAction(Transaction $transaction = null)
    {
        if (!$transaction || $this->getUser() !== $transaction->getAccount()->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this transaction.');
        }

        $transactionId = $transaction->getId();

        $this
            ->transactionRepository
            ->delete($transaction)
        ;

        $json = [
            'success' => true,
            'id' => $transactionId,
            'balance' => $transaction->getAccount()->getBalanceDisplayable(),
        ];

        return new JsonResponse($json);
    }

    /**
     * @Route("/transaction/check", name="data_check_transaction")
     *
     * @Route("/transaction/check/{id}", name="check_transaction",
     *      requirements={
     *          "id": "\d+"
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Transaction")
     *
     * @param Transaction $transaction
     *
     * @return JsonResponse
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function checkTransactionAction(Transaction $transaction = null)
    {
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
            'data' => $transaction->getApiResponse(),
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
     * @Route("/transactions/create/account-{id}.json", name="create_json_transactions",
     *      requirements={
     *          "id": "\d+",
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Account")
     *
     * @param Account $account
     *
     * @return JsonResponse
     */
    public function createJsonTransactionsAction(Account $account)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $transactions = $this
            ->transactionRepository
            ->getTransactions($account)
        ;

        $json = array();
        $json['balance'] = $account->getBalanceDisplayable();

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $json['data'][] = $transaction->getApiResponse();
        }

        file_put_contents(sprintf('data/%s-%d.json', $account->getSlug(), $account->getId()), json_encode($json));

        return new JsonResponse($json);
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
     * @return Response|RedirectResponse
     */
    public function listJsonTransactionsAction(Account $account)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $json = @file_get_contents(sprintf('data/%s-%d.json', $account->getSlug(), $account->getId()));
        $data = json_decode($json, true);

        if (!isset($data['data'])) {
            return $this->redirectToRoute('create_json_transactions', ['id' => $account->getId()]);
        }

        return new Response($json);
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
            }

            $this->addFlash(
                'error',
                'Erreur lors de l\'import.'
            );

            return $this->redirectToRoute('edit_account', ['id' => $account->getId(), 'slug' => $account->getSlug()]);
        }

        return $this->render('transaction/import.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/transactions/autocomplete/account-{id}.json", name="autocomplete_transactions",
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
    public function autocompleteTransactionsAction(Account $account)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $json = [];

        $descriptions = $this
            ->transactionRepository
            ->getAllDescriptions($account)
        ;

        foreach ($descriptions as $description) {
            $json[$description['description']] = null;
        }

        return new JsonResponse($json);
    }

    /**
     * @Route("/transaction-{id}/logs.html", name="transaction_logs",
     *      requirements={
     *          "id": "\d+",
     *      }
     * )
     *
     * @param int $id
     *
     * @return Response
     *
     * @throws \SimpleThings\EntityAudit\Exception\NotAuditedException
     */
    public function transactionLogsAction($id)
    {
        $transaction = $this->transactionRepository->find($id);

        if ($transaction && $this->getUser() !== $transaction->getAccount()->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $revisions = $this
            ->auditReader
            ->findRevisions(Transaction::class, $id)
        ;

        $changedEntities = [];

        foreach ($revisions as $revision) {
            if ($revision->getUsername() !== $this->getUser()->getUsername()) {
                continue;
            }

            $changedEntitiesAtRevision = $this->auditReader->findEntitiesChangedAtRevision($revision->getRev());

            foreach ($changedEntitiesAtRevision as $changedEntity) {
                $changedEntities[] = $changedEntity;
            }
        }

        return $this->render('transaction/logs.html.twig', array(
            'changedEntities' => $changedEntities,
        ));
    }

    /**
     * @Route("/transactions/logs.html", name="transactions_logs")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function transactionsLogsAction(Request $request)
    {
        $page = $request->get('page', 1);
        $revisions = true;
        $changedEntities = [];

        while ($revisions && count($changedEntities) < 20) {
            $revisions = $this
                ->auditReader
                ->findRevisionHistory(20, 20 * ($page - 1))
            ;

            foreach ($revisions as $revision) {
                if ($revision->getUsername() !== $this->getUser()->getUsername()) {
                    continue;
                }

                $changedEntitiesAtRevision = $this->auditReader->findEntitiesChangedAtRevision($revision->getRev());

                foreach ($changedEntitiesAtRevision as $changedEntity) {
                    $changedEntities[] = $changedEntity;
                }
            }

            $page++;
        }

        return $this->render('transaction/logs.html.twig', array(
            'changedEntities' => $changedEntities,
            'page' => $request->get('page', 1),
        ));
    }
}
