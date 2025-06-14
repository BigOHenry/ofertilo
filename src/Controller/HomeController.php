<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\User\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    #[IsGranted(Role::READER->value)]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        return $this->render('hello/index.html.twig', [
            'message' => 'Hello World!',
        ]);
    }
}
