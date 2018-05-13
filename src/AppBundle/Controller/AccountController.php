<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Form\AccountType;
use AppBundle\Repository\AccountRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AccountController
 */
class AccountController extends Controller
{
    /** @var AccountRepository */
    private $accountRepository;

    /**
     * AccountController constructor.
     *
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @Route("/account/{slug}-{id}.html", name="account",
     *      requirements={
     *          "id": "\d+",
     *          "slug": "[a-z0-9\-]+"
     *      }
     * )
     *
     * @ParamConverter("id", class="AppBundle:Account")
     *
     * @param Account $account
     *
     * @return Response
     */
    public function accountAction(Account $account)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        return $this->render('account/index.html.twig', array(
            'account' => $account,
        ));
    }

    /**
     * @Route("/account/create", name="create_account")
     *
     * @param Request $request
     *
     * @return RedirectResponse|Response
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function createAccountAction(Request $request)
    {
        $account = new Account();

        $form = $this->createForm(AccountType::class, $account, array(
            'action' => $this->generateUrl('create_account'),
        ));

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData();
            $account->setUser($this->getUser());

            $this->accountRepository->save($account);

            $this->addFlash(
                'notice',
                'Compte '.$account->getName().' crée avec succès.'
            );

            return $this->redirectToRoute('homepage');
        }

        return $this->render('account/create.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/account/edit/{slug}-{id}.html", name="edit_account",
     *      requirements={
     *          "id": "\d+",
     *          "slug": "[a-z0-9\-]+"
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
    public function editAccountAction(Account $account, Request $request)
    {
        if ($this->getUser() !== $account->getUser()) {
            throw $this->createAccessDeniedException('You cannot access to this account.');
        }

        $form = $this->createForm(AccountType::class, $account);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $account = $form->getData();

            $this->accountRepository->save($account);

            $this->addFlash(
                'notice',
                'Compte '.$account->getName().' édité avec succès.'
            );
        }

        return $this->render('account/edit.html.twig', array(
            'account' => $account,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/accounts/list", name="list_accounts")
     *
     * @return Response
     */
    public function listAccountsAction()
    {
        return $this->render('account/list.html.twig', array(
            'accounts' => $this->getUser()->getAccounts(),
        ));
    }
}
