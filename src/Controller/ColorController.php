<?php

declare(strict_types=1);

namespace App\Controller;

use App\Application\Color\ColorService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorException;
use App\Domain\User\ValueObject\Role;
use App\Form\ColorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ColorController extends AbstractController
{
    public function __construct(private readonly ColorService $colorService)
    {
    }

    #[Route('/colors', name: 'color_index')]
    #[IsGranted(Role::READER->value)]
    public function index(): Response
    {
        return $this->render('color/index.html.twig');
    }

    #[Route('/color/new', name: 'color_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function new(Request $request): Response
    {
        $color = $this->colorService->createEmpty();

        $form = $this->createForm(ColorType::class, $color, [
            'action' => $this->generateUrl('color_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->colorService->save($color);
                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('color_index', [], Response::HTTP_SEE_OTHER);
            } catch (ColorException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'frame_id' => 'colorModal_frame',
            'form_template' => 'components/color_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'color-form',
            ],
        ]);
    }

    #[Route('/color/{id}/edit', name: 'color_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function colorEdit(Request $request, Color $color): Response
    {
        $form = $this->createForm(ColorType::class, $color, [
            'action' => $this->generateUrl('color_edit', ['id' => $color->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->colorService->save($color);

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'colorModal_frame') {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                if ($frameId === 'colorDetailModal_frame') {
                    return $this->render('color/_streams/color_card.stream.html.twig', [
                        'color' => $color,
                    ]);
                }
            } catch (ColorException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'colorModal_frame',
            'form_template' => 'components/color_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'color-form',
            ],
        ]);
    }

    #[Route('/api/colors', name: 'api_colors')]
    #[IsGranted(Role::READER->value)]
    public function colorsApi(Request $request): JsonResponse
    {
        try {
            return $this->json($this->colorService->getPaginatedColors($request));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/color/{id}', name: 'color_detail', methods: ['GET'])]
    #[IsGranted(Role::READER->value)]
    public function detail(Color $color): Response
    {
        return $this->render('color/detail.html.twig', [
            'color' => $color,
        ]);
    }

    #[Route('/color/{id}', name: 'color_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function deletePrice(Color $color): JsonResponse
    {
        try {
            $this->colorService->delete($color);

            return new JsonResponse(['success' => true]);
        } catch (ColorException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/colors/out-of-stock', name: 'api_colors_out_of_stock', methods: ['GET'])]
    public function getOutOfStockColors(Request $request): JsonResponse
    {
        try {
            return $this->json($this->colorService->getOutOfStockColors($request));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
