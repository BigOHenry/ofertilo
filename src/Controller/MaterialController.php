<?php

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
        $form = $this->createForm(MaterialType::class, $material);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $materialRepository->save($material);

            if (TurboBundle::STREAM_FORMAT === $request->getPreferredFormat()) {
                // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
                return $this->renderBlock('material/index.html.twig', 'success_stream', ['material' => $material->getDescription()]);
            }

            // If the client doesn't support JavaScript, or isn't using Turbo, the form still works as usual.
            // Symfony UX Turbo is all about progressively enhancing your applications!
            return $this->redirectToRoute('material_index', [], Response::HTTP_SEE_OTHER);

        }

        return $this->render('material/form_frame.html.twig', [
            'form' => $form,
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
                   ->setMaxResults($size);

        // Volitelné: třídění
        $sortField = $request->query->get('sort')['field'] ?? null;
        $sortDir = $request->query->get('sort')['dir'] ?? 'asc';
        if (in_array($sortField, ['name', 'type', 'pricePerUnit'])) {
            $qb->orderBy("m.$sortField", strtoupper($sortDir));
        }

        $paginator = new Paginator($qb);
        $total = count($paginator);

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
}
