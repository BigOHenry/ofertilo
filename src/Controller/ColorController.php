<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Color\Color;
use App\Domain\Color\ColorRepositoryInterface;
use App\Domain\User\Role;
use App\Form\ColorType;
use App\Infrastructure\Translation\TranslationInitializer;
use App\Infrastructure\Translation\TranslationLoader;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ColorController extends AbstractController
{
    /**
     * @param array<int, string> $locales
     */
    public function __construct(
        #[Autowire('%app.supported_locales%')] private readonly array $locales,
    ) {
    }

    #[Route('/colors', name: 'color_index')]
    #[IsGranted(Role::READER->value)]
    public function index(): Response
    {
        return $this->render('color/index.html.twig');
    }

    #[Route('/color/new', name: 'color_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function new(Request $request, ColorRepositoryInterface $colorRepository): Response
    {
        $color = new Color();
        TranslationInitializer::prepare($color, $this->locales);

        $form = $this->createForm(ColorType::class, $color, [
            'action' => $this->generateUrl('color_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $colorRepository->save($color);
            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('color_index', [], Response::HTTP_SEE_OTHER);
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

    #[Route('/api/colors', name: 'api_colors')]
    #[IsGranted(Role::READER->value)]
    public function colorsApi(
        Request $request,
        ColorRepositoryInterface $colorRepository,
        TranslationLoader $translationLoader,
        TranslatorInterface $translator,
    ): JsonResponse {
        $page = max((int) $request->query->get('page', 1), 1);
        $size = min((int) $request->query->get('size', 10), 100);
        $offset = ($page - 1) * $size;

        $qb = $colorRepository->createQueryBuilder('m')
                   ->setFirstResult($offset)
                   ->setMaxResults($size)
        ;

        $sortField = $request->query->get('sort')['field'] ?? null;
        $sortDir = $request->query->get('sort')['dir'] ?? 'asc';
        if (\in_array($sortField, ['name', 'type', 'pricePerUnit'], true)) {
            $qb->orderBy("m.$sortField", mb_strtoupper($sortDir));
        }

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        /** @var Color $color */
        foreach ($paginator as $color) {
            $translationLoader->loadTranslations($color);
            $data[] = [
                'id' => $color->getId(),
                'code' => $color->getCode(),
                'description' => $color->getDescription($request->getLocale()),
                'in_stock' => $translator->trans($color->isInStock() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        return $this->json([
            'data' => $data,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ]);
    }

    #[Route('/color/{id}/edit', name: 'color_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function colorEdit(
        Request $request,
        Color $color,
        ColorRepositoryInterface $colorRepository,
    ): Response {
        TranslationInitializer::prepare($color, $this->locales);

        $form = $this->createForm(ColorType::class, $color, [
            'action' => $this->generateUrl('color_edit', ['id' => $color->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $colorRepository->save($color);
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
    public function deletePrice(Color $color, ColorRepositoryInterface $colorRepository): JsonResponse
    {
        $colorRepository->remove($color);

        return new JsonResponse(['success' => true]);
    }
}
