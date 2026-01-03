<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Material\Command\CreateMaterial\CreateMaterialCommand;
use App\Application\Material\Command\CreateMaterialPrice\CreateMaterialPriceCommand;
use App\Application\Material\Command\DeleteMaterial\DeleteMaterialCommand;
use App\Application\Material\Command\DeleteMaterialPrice\DeleteMaterialPriceCommand;
use App\Application\Material\Command\EditMaterial\EditMaterialCommand;
use App\Application\Material\Command\EditMaterialPrice\EditMaterialPriceCommand;
use App\Application\Material\Query\CalculateMaterialPricePerUnit\CalculateMaterialPricePerUnitQuery;
use App\Application\Material\Query\GetMaterialFormData\GetMaterialFormDataQuery;
use App\Application\Material\Query\GetMaterialPriceFormData\GetMaterialPriceFormDataQuery;
use App\Application\Material\Query\GetMaterialPricesGrid\GetMaterialPricesGridQuery;
use App\Application\Material\Query\GetMaterialsPaginatedGrid\GetMaterialsPaginatedGridQuery;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Exception\MaterialException;
use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Web\Form\MaterialFormType;
use App\Infrastructure\Web\Form\MaterialPriceCalculationFormType;
use App\Infrastructure\Web\Form\MaterialPriceFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class MaterialController extends BaseController
{
    #[Route('/materials', name: 'material_index')]
    #[IsGranted(Role::READER->value)]
    public function index(): Response
    {
        return $this->render('material/index.html.twig');
    }

    #[Route('/material/new', name: 'material_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function new(Request $request): Response
    {
        $form = $this->createForm(MaterialFormType::class, data: [], options: [
            'action' => $this->generateUrl('material_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateMaterialCommand::createFromForm($form));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('material_index', [], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render(
            'components/form_frame.html.twig',
            [
                'data_class' => CreateMaterialCommand::class,
                'frame_id' => $request->headers->get('Turbo-Frame') ?? 'materialModal_frame',
                'form_template' => 'components/_form.html.twig',
                'form_context' => [
                    'form' => $form->createView(),
                    'form_id' => 'material-form',
                ],
            ]
        );

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/material/{id}/edit', name: 'material_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function materialEdit(Request $request, Material $material): Response
    {
        $envelope = $this->bus->dispatch(new GetMaterialFormDataQuery((int) $material->getId()));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(MaterialFormType::class, data: $formData, options: [
            'action' => $this->generateUrl('material_edit', ['id' => $material->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditMaterialCommand::createFromForm($form));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $referer = $request->headers->get('referer') ?? '';
                $isDetailPage = str_contains($referer, '/material/');

                if ($isDetailPage) {
                    return $this->render('material/_streams/_card.stream.html.twig', [
                        'material' => $material,
                    ]);
                }

                if ($frameId === 'materialModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => null,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'materialModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-form',
                'material' => $material,
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/material/{id}', name: 'material_detail', methods: ['GET'])]
    #[IsGranted(Role::READER->value)]
    public function detail(Material $material): Response
    {
        return $this->render('material/detail.html.twig', [
            'material' => $material,
        ]);
    }

    #[Route('/material/{id}', name: 'material_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function deleteMaterial(Material $material): JsonResponse
    {
        try {
            $this->bus->dispatch(DeleteMaterialCommand::create((int) $material->getId()));

            return new JsonResponse(['success' => true]);
        } catch (MaterialException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/api/materials', name: 'api_materials')]
    #[IsGranted(Role::READER->value)]
    public function materialsApi(Request $request): JsonResponse
    {
        try {
            $envelope = $this->bus->dispatch(GetMaterialsPaginatedGridQuery::createFormRequest($request));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/material/{id}/price/new', name: 'material_price_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newPrice(Request $request, Material $material): Response
    {
        $form = $this->createForm(MaterialPriceFormType::class, [], [
            'action' => $this->generateUrl('material_price_new', ['id' => $material->getId()]),
            'method' => 'POST',
            'material' => $material,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateMaterialPriceCommand::createFromForm($form, $material));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'materialPriceModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('material_detail', ['id' => $material->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CreateMaterialPriceCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'materialPriceModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-price-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/material/price/{id}/edit', name: 'material_price_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editPrice(Request $request, MaterialPrice $materialPrice): Response
    {
        $envelope = $this->bus->dispatch(GetMaterialPriceFormDataQuery::create($materialPrice));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(MaterialPriceFormType::class, $formData, [
            'action' => $this->generateUrl('material_price_edit', ['id' => $materialPrice->getId()]),
            'method' => 'POST',
            'material' => $materialPrice->getMaterial(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditMaterialPriceCommand::createFromForm($form, $materialPrice->getMaterial()));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'materialPriceModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('material_detail', ['id' => $materialPrice->getMaterial()->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => EditMaterialPriceCommand::class,
            'frame_id' => 'materialPriceModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-price-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/api/material_prices/{id}', name: 'api_material_prices')]
    #[IsGranted(Role::READER->value)]
    public function materialPricesApi(Material $material): JsonResponse
    {
        try {
            \assert($material->getId() !== null, 'Material must have an ID when loaded from database');
            $envelope = $this->bus->dispatch(GetMaterialPricesGridQuery::create($material->getId()));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/material/price/{id}', name: 'material_price_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function priceDelete(MaterialPrice $materialPrice): JsonResponse
    {
        try {
            $this->bus->dispatch(DeleteMaterialPriceCommand::create((int) $materialPrice->getMaterial()->getId(), (int) $materialPrice->getId()));
            $this->addFlash('success', $this->translator->trans('message.item_deleted'));

            return new JsonResponse(['success' => true]);
        } catch (MaterialException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/material/{id}/calculation', name: 'material_price_calculation', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function calculation(Request $request, Material $material): Response
    {
        $form = $this->createForm(MaterialPriceCalculationFormType::class, [], [
            'action' => $this->generateUrl('material_price_calculation', ['id' => $material->getId()]),
            'method' => 'POST',
            'material' => $material,
        ]);

        $form->handleRequest($request);
        $calculatedPrice = null;

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $calculatedPrice = $this->bus->dispatch(CalculateMaterialPricePerUnitQuery::createFromForm($form, $material))
                                             ->last(HandledStamp::class)
                                             ?->getResult()
                ;
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CalculateMaterialPricePerUnitQuery::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'calculationModal_frame',
            'form_template' => 'material/components/_form_calculation.html.twig',
            'material' => $material,
            'calculatedPrice' => $calculatedPrice,
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'calculation-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }
}
