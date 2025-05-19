<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\User\Role;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use App\Form\FirstRunSetupType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/first-run', name: 'app_first_run_setup')]
    public function setup(
        Request $request,
        UserRepositoryInterface $userRepo,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        if ($userRepo->hasSuperAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(FirstRunSetupType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $user = new User($data['email']);
            $user->setName('SuperAdmin');
            $user->setPassword(
                $passwordHasher->hashPassword($user, $data['password'])
            );
            $user->setRoles([Role::SUPER_ADMIN]);
            $user->setForceEmailChange(false);
            $user->setForcePasswordChange(false);
            $userRepo->save($user);

            $this->addFlash('success', 'SuperAdmin has been successfully created.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/first_run_setup.html.twig', [
            'form' => $form->createView(),
        ], new Response(null, 422));
    }

    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
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
}
