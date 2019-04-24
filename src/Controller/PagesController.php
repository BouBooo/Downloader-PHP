<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PagesController extends AbstractController
{
    /**
     * @Route("/faq", name="faq")
     */
    public function fagPage()
    {
        return $this->render('pages/faq.html.twig');
    }

    /**
     * @Route("/copyright", name="copyright")
     */
    public function copyrightPage()
    {
        return $this->render('pages/copyright.html.twig');
    }

}
