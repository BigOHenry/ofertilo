<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Service\TwoFactorService;
use App\Domain\User\User;
use App\Domain\User\UserRepositoryInterface;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class TwoFactorSetupController extends AbstractController
{
    public function __construct(
        private readonly TwoFactorService $twoFactorService,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
        private readonly UserRepositoryInterface $userRepository,
    ) {
    }

    #[Route('/2fa/setup', name: 'app_2fa_setup')]
    public function setup(Request $request, SessionInterface $session): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $secret = $session->get('temp_totp_secret');

        if (!$secret) {
            $secret = $this->twoFactorService->generateSecret();
            $session->set('temp_totp_secret', $secret);
        }

        $user->setTotpSecret($secret);

        if ($request->isMethod('POST')) {
            $code = (string) $request->request->get('_auth_code');

            if ($this->totpAuthenticator->checkCode($user, $code)) {
                $this->userRepository->save($user);
                $session->remove('temp_totp_secret');
                $this->addFlash('success', '2FA bylo úspěšně aktivováno!');

                return $this->redirectToRoute('app_home_index');
            }

            $this->addFlash('error', 'Neplatný ověřovací kód!');
        }

        return $this->render('security/2fa_setup.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/2fa/qr-code', name: 'app_2fa_qr_code')]
    public function qrCode(SessionInterface $session): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $secret = $session->get('temp_totp_secret');
        if (!$secret) {
            throw $this->createNotFoundException('2FA setup nebyl zahájen');
        }

        $user->setTotpSecret($secret);

        $qrCodeContent = $this->totpAuthenticator->getQRContent($user);

        $qrCode = new QrCode($qrCodeContent);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return new Response($result->getString(), 200, [
            'Content-Type' => 'image/png',
        ]);
    }
}
