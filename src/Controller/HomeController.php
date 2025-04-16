<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    public function index(): Response
    {
        return $this->render('hello/index.html.twig', [
            'message' => 'Hello World!',
        ]);
    }
}
