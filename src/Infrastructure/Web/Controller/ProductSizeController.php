<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Product\Command\CreateProductSize\CreateProductSizeCommand;
use App\Application\Product\Command\DeleteProductSize\DeleteProductSizeCommand;
use App\Application\Product\Command\EditProductSize\EditProductSizeCommand;
use App\Application\Product\Query\GetProductSizeFormData\GetProductSizeFormDataQuery;
use App\Application\Product\Query\GetProductSizesGrid\GetProductSizesGridQuery;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductSize;
use App\Domain\Product\Exception\ProductException;
use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Web\Form\ProductSizeFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ProductSizeController extends BaseController
{
    #[Route('/product/{id}/size/new', name: 'product_size_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newSize(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductSizeFormType::class, ['product' => $product], [
            'action' => $this->generateUrl('product_size_new', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateProductSizeCommand::createFromForm($form, $product));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productSizeModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_detail', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CreateProductSizeCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productSizeModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-size-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/product/size/{id}/edit', name: 'product_size_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editSize(Request $request, ProductSize $productSize): Response
    {
        $envelope = $this->bus->dispatch(GetProductSizeFormDataQuery::create($productSize));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(ProductSizeFormType::class, $formData, [
            'action' => $this->generateUrl('product_size_edit', ['id' => $productSize->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditProductSizeCommand::createFromForm($form, $productSize->getProduct()));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productSizeModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_detail', ['id' => $productSize->getProduct()->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductSizeCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productSizeModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-size-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/api/product/{id}/sizes', name: 'api_product_sizes')]
    #[IsGranted(Role::READER->value)]
    public function productSizesApi(Product $product): JsonResponse
    {
        try {
            $envelope = $this->bus->dispatch(GetProductSizesGridQuery::create($product->getId()));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/product/size/{id}', name: 'product_size_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(ProductSize $productSize): JsonResponse
    {
        try {
            $this->bus->dispatch(DeleteProductSizeCommand::create($productSize->getProduct()->getId(), $productSize->getId()));

            return new JsonResponse(['success' => true]);
        } catch (ProductException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while deleting the price'], 500);
        }
    }
}
