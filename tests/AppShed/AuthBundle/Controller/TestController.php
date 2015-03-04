<?php

namespace AppShed\AuthBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;


class TestController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
       return new JsonResponse($this->getUser());
    }
}
