<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\User\Role;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Form\FirstRunSetupType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Cache\CacheInterface;

class SecurityController extends AbstractController
{
    #[Route('/first-run', name: 'app_first_run_setup')]
    public function setup(
        Request $request,
        UserRepositoryInterface $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        CacheInterface $cache,
    ): Response {
        $is_installed = $cache->get('ofertilo.first_run_done', function () use ($userRepository) {
            return $userRepository->hasSuperAdmin();
        });

        if ($is_installed) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(FirstRunSetupType::class);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = new User($data['email']);
            $user->setName('SuperAdmin');
            $user->setPassword(
                $passwordHasher->hashPassword($user, $data['password'])
            );
            $user->setRoles([Role::SUPER_ADMIN]);
            $user->setForceEmailChange(false);
            $user->setForcePasswordChange(false);
            $userRepository->save($user);

            $cache->delete('ofertilo.first_run_done');

            $this->addFlash('success', 'SuperAdmin has been successfully created.');

            return $this->redirectToRoute('app_login');
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

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
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
