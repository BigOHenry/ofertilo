<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\Command\Material\CreateMaterial\CreateMaterialCommand;
use App\Application\Command\Material\DeleteMaterial\DeleteMaterialCommand;
use App\Application\Command\Material\EditMaterial\EditMaterialCommand;
use App\Application\Material\Command\CreateMaterialPriceCommand;
use App\Application\Material\Command\EditMaterialPriceCommand;
use App\Application\Query\Material\GetMaterialFormData\GetMaterialFormDataQuery;
use App\Application\Query\Material\GetMaterialsForPaginatedGrid\GetMaterialsForPaginatedGridQuery;
use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Exception\DuplicatePriceThicknessException;
use App\Domain\Material\Exception\MaterialException;
use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Form\MaterialPriceType;
use App\Infrastructure\Form\MaterialType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class MaterialController extends AbstractController
{
    public function __construct(
        private readonly MaterialApplicationService $materialService,
        private readonly MessageBusInterface $bus,
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
    public function new(Request $request): Response
    {
        $form = $this->createForm(MaterialType::class, data: [], options: [
            'action' => $this->generateUrl('material_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateMaterialCommand::createFromForm($form));
                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('material_index', [], Response::HTTP_SEE_OTHER);
            } catch (MaterialException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => CreateMaterialCommand::class,
            'frame_id' => 'materialModal_frame',
            'modal_id' => 'materialModal',
            'form_template' => 'components/material_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-form',
            ],
        ]);
    }

    #[Route('/material/{id}/edit', name: 'material_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function materialEdit(Request $request, Material $material): Response
    {
        $envelope = $this->bus->dispatch(new GetMaterialFormDataQuery((int) $material->getId()));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(MaterialType::class, data: $formData, options: [
            'action' => $this->generateUrl('material_edit', ['id' => $material->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditMaterialCommand::createFromForm($form));

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
            } catch (MaterialException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => EditMaterialCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'materialModal_frame',
            'form_template' => 'components/material_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-form',
            ],
        ]);
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
            $this->bus->dispatch(DeleteMaterialCommand::create($material->getId()));

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
            $envelope = $this->bus->dispatch(GetMaterialsForPaginatedGridQuery::createFormRequest($request));
            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/material/{id}/price/new', name: 'material_price_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newPrice(Request $request, Material $material): Response
    {
        $command = $this->materialCommandFactory->createCreatePriceCommand($material);
        $form = $this->createForm(MaterialPriceType::class, $command, [
            'action' => $this->generateUrl('material_price_new', ['id' => $material->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->materialService->createPriceFromCommand($command);

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('material_detail', ['id' => $material->getId()], Response::HTTP_SEE_OTHER);
            } catch (DuplicatePriceThicknessException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => CreateMaterialPriceCommand::class,
            'frame_id' => 'materialPriceModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'material-price-form',
            ],
        ]);
    }

    #[Route('/material/price/{id}/edit', name: 'material_price_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editPrice(Request $request, MaterialPrice $materialPrice): Response
    {
        $command = $this->materialCommandFactory->createEditPriceCommand($materialPrice);
        $form = $this->createForm(MaterialPriceType::class, $command, [
            'action' => $this->generateUrl('material_price_edit', ['id' => $materialPrice->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->materialService->updatePriceFromCommand($materialPrice, $command);

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('material_detail', ['id' => $materialPrice->getMaterial()->getId()], Response::HTTP_SEE_OTHER);
            } catch (MaterialException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => EditMaterialPriceCommand::class,
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
    public function materialPricesApi(Material $material): JsonResponse
    {
        try {
            return $this->json($this->materialService->getMaterialPricesData($material));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/material/price/{id}', name: 'material_price_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function priceDelete(MaterialPrice $materialPrice): JsonResponse
    {
        try {
            $this->materialService->removePriceFromMaterial($materialPrice);

            return new JsonResponse(['success' => true]);
        } catch (MaterialException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while deleting the price'], 500);
        }
    }
}
