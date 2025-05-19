<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Material\Material;
use App\Domain\Material\MaterialRepositoryInterface;
use App\Form\MaterialType;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboBundle;

final class MaterialController extends AbstractController
{
    #[Route('/materials', name: 'material_index')]
    public function index(): Response
    {
        return $this->render('material/index.html.twig');
    }

    #[Route('/materials/new', name: 'material_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MaterialRepositoryInterface $materialRepository): Response
    {
        $material = new Material();
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
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-form',
            ],
        ]);
    }

    #[Route('/api/materials', name: 'api_materials')]
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
    public function edit(
        Request $request,
        Material $material,
        MaterialRepositoryInterface $materialRepository,
    ): Response {
        $form = $this->createForm(MaterialType::class, $material, [
            'action' => $this->generateUrl('material_edit', ['id' => $material->getId()]),
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
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-form',
            ],
        ]);
    }

    #[Route('/materials/{id}', name: 'material_delete', methods: ['DELETE'])]
    public function delete(Material $material, MaterialRepositoryInterface $materialRepository): JsonResponse
    {
        $materialRepository->delete($material);

        return new JsonResponse(['success' => true]);
    }

    #[Route('/material/{id}', name: 'material_show', methods: ['GET'])]
    public function show(Material $material): Response
    {
        return $this->render('material/show.html.twig', [
            'material' => $material,
        ]);
    }

    #[Route('/api/material_prices/{id}', name: 'api_material_prices')]
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

    public function wrongStyle(): void
    {
        echo 'wrong style';
    }
}
