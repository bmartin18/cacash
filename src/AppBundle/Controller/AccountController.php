<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Account;
use AppBundle\Form\AccountType;
use AppBundle\Repository\AccountRepository;
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
}
