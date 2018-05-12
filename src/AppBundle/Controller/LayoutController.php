<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LayoutController
 */
class LayoutController extends Controller
{
    /**
     * @Route("/header", name="header")
     *
     * @return Response
     */
    public function headerAction()
    {
        return $this->render('layout/header.html.twig');
    }
}
