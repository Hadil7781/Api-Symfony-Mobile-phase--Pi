<?php

namespace MobileApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MobileApiBundle:Default:index.html.twig');
    }
}
