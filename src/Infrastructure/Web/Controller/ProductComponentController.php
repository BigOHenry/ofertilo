<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Product\Command\CreateProductComponent\CreateProductComponentCommand;
use App\Application\Product\Command\DeleteProductComponent\DeleteProductComponentCommand;
use App\Application\Product\Command\EditProductComponent\EditProductComponentCommand;
use App\Application\Product\Query\GetProductComponentFormData\GetProductComponentFormDataQuery;
use App\Application\Product\Query\GetProductComponentsGrid\GetProductComponentsGridQuery;
use App\Domain\Product\Entity\ProductComponent;
use App\Domain\Product\Entity\ProductVariant;
use App\Domain\Product\Exception\ProductException;
use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Web\Form\ProductComponentFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ProductComponentController extends BaseController
{
    #[Route('/product/variant/{id}/component/new', name: 'product_component_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newComponent(Request $request, ProductVariant $productVariant): Response
    {
        $form = $this->createForm(ProductComponentFormType::class, ['productVariant' => $productVariant], [
            'action' => $this->generateUrl('product_component_new', ['id' => $productVariant->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateProductComponentCommand::createFromForm($form, $productVariant));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productComponentModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_detail', ['id' => $productVariant->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CreateProductComponentCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productComponentModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-component-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/product/component/{id}/edit', name: 'product_component_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editComponent(Request $request, ProductComponent $productComponent): Response
    {
        $envelope = $this->bus->dispatch(GetProductComponentFormDataQuery::create($productComponent));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(ProductComponentFormType::class, $formData, [
            'action' => $this->generateUrl('product_component_edit', ['id' => $productComponent->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditProductComponentCommand::createFromForm($form, $productComponent));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productComponentModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute(
                    'product_variant_detail',
                    ['id' => $productComponent->getProductVariant()->getId()],
                    Response::HTTP_SEE_OTHER
                );
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductComponentCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productComponentModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-component-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/api/product/variant/{id}/components', name: 'api_product_variant_components')]
    #[IsGranted(Role::READER->value)]
    public function productComponentsApi(ProductVariant $productVariant): JsonResponse
    {
        try {
            $envelope = $this->bus->dispatch(GetProductComponentsGridQuery::createFromProductVariant($productVariant));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/product/component/{id}', name: 'product_component_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(ProductComponent $productComponent): JsonResponse
    {
        try {
            $this->bus->dispatch(DeleteProductComponentCommand::create($productComponent->getId()));

            return new JsonResponse(['success' => true]);
        } catch (ProductException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while deleting the price'], 500);
        }
    }
}
