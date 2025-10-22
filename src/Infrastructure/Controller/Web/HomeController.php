<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Domain\User\ValueObject\Role;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home_index')]
    #[IsGranted(Role::READER->value)]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
}
