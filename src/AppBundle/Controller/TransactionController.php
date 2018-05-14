<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Entity\Transaction;
use AppBundle\Form\TransactionType;
use AppBundle\Repository\TransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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

    /**
     * TransactionController constructor.
     *
     * @param TransactionRepository $transactionRepository
     */
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @Route("/transaction/account-{id}/create", name="create_transaction",
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
    public function createTransactionAction(Account $account, Request $request)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $transaction = new Transaction();

        $form = $this->createForm(TransactionType::class, $transaction, array(
            'action' => $this->generateUrl('create_transaction', ['id' => $account->getId()]),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $transaction = $form->getData();
            $transaction->setAccount($account);

            $this->transactionRepository->save($transaction);

            return $this->redirectToRoute('account', ['id' => $account->getId(), 'slug' => $account->getSlug()]);
        }

        return $this->render('transaction/form.html.twig', array(
            'form' => $form->createView(),
        ));
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
        $transactions = $this
            ->transactionRepository
            ->getTransactions($account)
        ;

        return $this->render('transaction/list.html.twig', array(
            'transactions' => $transactions,
        ));
    }
}
