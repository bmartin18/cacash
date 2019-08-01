<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomepageController
 */
class HomepageController extends Controller
{
    /**
     * @Route("/", name="homepage")
     *
     * @return Response
     */
    public function homepageAction()
    {
        return $this->render('homepage/index.html.twig');
    }
}
