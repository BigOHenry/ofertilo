<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\User\Command\CreateFirstSuperAdminUser\CreateFirstSuperAdminUserCommand;
use App\Application\User\Service\UserApplicationService;
use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserException;
use App\Infrastructure\Web\Form\FirstRunSetupFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Cache\CacheInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly UserApplicationService $userService,
        private readonly MessageBusInterface $bus,
        private readonly CacheInterface $cache,
    ) {
    }

    #[Route('/first-run', name: 'app_first_run_setup')]
    public function setup(Request $request): Response
    {
        $is_installed = $this->cache->get('ofertilo.first_run_done', function () {
            return $this->userService->isSystemInstalled();
        });

        if ($is_installed) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(FirstRunSetupFormType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            try {
                $data = $form->getData();
                $this->bus->dispatch(new CreateFirstSuperAdminUserCommand($data['email'], $data['password']));

                $this->cache->delete('ofertilo.first_run_done');

                $this->addFlash('success', 'SuperAdmin has been successfully created.');

                return $this->redirectToRoute('app_login');
            } catch (UserException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('security/first_run_setup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home_index');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony will intercept this and handle logout automatically
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/debug/2fa-status', name: 'debug_2fa_status')]
    public function debug2faStatus(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $token = $this->container->get('security.token_storage')->getToken();

        return $this->json([
            'user_2fa_enabled' => $user->isTotpAuthenticationEnabled(),
            'user_has_secret' => $user->getTotpAuthenticationSecret() !== null,
            'current_token' => $token::class,
            'is_fully_authenticated' => $this->isGranted('IS_AUTHENTICATED_FULLY'),
            'is_2fa_in_progress' => $this->isGranted('IS_AUTHENTICATED_2FA_IN_PROGRESS'),
        ]);
    }
}
