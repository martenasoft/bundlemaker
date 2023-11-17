<?php

namespace __REPLACE_NAMESPACE__;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('@__REPLACE_RENDER_NAMESPACE__/default/index.html.twig', [
            'controller_name' => 'DefaultController',
        ]);
    }
}
