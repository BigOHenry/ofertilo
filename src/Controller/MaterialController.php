<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Material\Material;
use App\Domain\Material\MaterialPrice;
use App\Domain\Material\MaterialPriceRepositoryInterface;
use App\Domain\Material\MaterialRepositoryInterface;
use App\Domain\User\Role;
use App\Form\MaterialPriceType;
use App\Form\MaterialType;
use App\Infrastructure\Translation\TranslationInitializer;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class MaterialController extends AbstractController
{
    /**
     * @param array<int, string> $locales
     */
    public function __construct(
        #[Autowire('%app.supported_locales%')] private readonly array $locales,
    ) {
    }

    #[Route('/materials', name: 'material_index')]
    #[IsGranted(Role::READER->value)]
    public function index(): Response
    {
        return $this->render('material/index.html.twig');
    }

    #[Route('/material/new', name: 'material_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function new(Request $request, MaterialRepositoryInterface $materialRepository): Response
    {
        $material = new Material();
        TranslationInitializer::prepare($material, $this->locales);

        $form = $this->createForm(MaterialType::class, $material, [
            'action' => $this->generateUrl('material_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $materialRepository->save($material);
            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('material_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
            'frame_id' => 'materialModal_frame',
            'form_template' => 'components/material_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-form',
            ],
        ]);
    }

    #[Route('/api/materials', name: 'api_materials')]
    #[IsGranted(Role::READER->value)]
    public function materialsApi(Request $request, MaterialRepositoryInterface $materialRepository): JsonResponse
    {
        $page = max((int) $request->query->get('page', 1), 1);
        $size = min((int) $request->query->get('size', 10), 100);
        $offset = ($page - 1) * $size;

        $qb = $materialRepository->createQueryBuilder('m')
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
        foreach ($paginator as $material) {
            $data[] = [
                'id' => $material->getId(),
                'name' => $material->getName(),
                'description' => $material->getDescription(),
                'type' => $material->getType()->value,
            ];
        }

        return $this->json([
            'data' => $data,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ]);
    }

    #[Route('/material/{id}/edit', name: 'material_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function materialEdit(
        Request $request,
        Material $material,
        MaterialRepositoryInterface $materialRepository,
    ): Response {
        TranslationInitializer::prepare($material, $this->locales);

        $form = $this->createForm(MaterialType::class, $material, [
            'action' => $this->generateUrl('material_edit', ['id' => $material->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $materialRepository->save($material);
            $frameId = $request->request->get('frame_id');
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

            if ($frameId === 'materialModal_frame') {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            if ($frameId === 'materialDetailModal_frame') {
                return $this->render('material/_streams/material_card.stream.html.twig', [
                    'material' => $material,
                ]);
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'materialModal_frame',
            'form_template' => 'components/material_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-form',
            ],
        ]);
    }

    #[Route('/material/price/{id}', name: 'material_price_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(MaterialPrice $materialPrice, MaterialPriceRepositoryInterface $materialPriceRepository): JsonResponse
    {
        $materialPriceRepository->remove($materialPrice);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/material/{id}', name: 'material_detail', methods: ['GET'])]
    #[IsGranted(Role::READER->value)]
    public function detail(Material $material): Response
    {
        return $this->render('material/detail.html.twig', [
            'material' => $material,
        ]);
    }

    #[Route('/material/{id}/price/new', name: 'material_price_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newPrice(Request $request, Material $material, MaterialPriceRepositoryInterface $materialPriceRepository): Response
    {
        $materialPrice = new MaterialPrice($material);
        $form = $this->createForm(MaterialPriceType::class, $materialPrice, [
            'action' => $this->generateUrl('material_price_new', ['id' => $material->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $materialPriceRepository->save($materialPrice);
            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('material_detail', ['id' => $material->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
            'frame_id' => 'materialPriceModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-price-form',
            ],
        ]);
    }

    #[Route('/materials/{id}', name: 'material_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function deletePrice(Material $material, MaterialRepositoryInterface $materialRepository): JsonResponse
    {
        $materialRepository->remove($material);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/material/price/{id}/edit', name: 'material_price_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editPrice(
        Request $request,
        MaterialPrice $materialPrice,
        MaterialPriceRepositoryInterface $materialPriceRepository,
    ): Response {
        $form = $this->createForm(MaterialPriceType::class, $materialPrice, [
            'action' => $this->generateUrl('material_price_edit', ['id' => $materialPrice->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $materialPriceRepository->save($materialPrice);

            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('material_detail', ['id' => $materialPrice->getMaterial()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
            'frame_id' => 'materialPriceModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-price-form',
            ],
        ]);
    }

    #[Route('/api/material_prices/{id}', name: 'api_material_prices')]
    #[IsGranted(Role::READER->value)]
    public function materialPricesApi(Material $material, Request $request): JsonResponse
    {
        $data = [];
        foreach ($material->getPrices() as $price) {
            $data[] = [
                'id' => $price->getId(),
                'thickness' => $price->getThickness(),
                'price' => $price->getPrice(),
            ];
        }

        return $this->json([
            'data' => $data,
        ]);
    }
}
