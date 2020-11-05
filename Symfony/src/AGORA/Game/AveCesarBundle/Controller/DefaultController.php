<?php

namespace AGORA\Game\AveCesarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AGORAGameAveCesarBundle:Default:index.html.twig');
    }
}
