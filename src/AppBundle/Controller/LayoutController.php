<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
