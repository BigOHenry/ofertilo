<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Product\Command\CreateProductVariant\CreateProductVariantCommand;
use App\Application\Product\Command\DeleteProductVariant\DeleteProductVariantCommand;
use App\Application\Product\Command\EditProductVariant\EditProductVariantCommand;
use App\Application\Product\Query\GetProductVariantFormData\GetProductVariantFormDataQuery;
use App\Application\Product\Query\GetProductVariantsGrid\GetProductVariantsGridQuery;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductVariant;
use App\Domain\Product\Exception\ProductException;
use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Web\Form\ProductVariantFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ProductVariantController extends BaseController
{
    #[Route('/product/{id}/variant/new', name: 'product_variant_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newVariant(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductVariantFormType::class, ['product' => $product], [
            'action' => $this->generateUrl('product_variant_new', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateProductVariantCommand::createFromForm($form, $product));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productVariantModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_detail', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CreateProductVariantCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productVariantModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-variant-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/product/variant/{id}/edit', name: 'product_variant_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editVariant(Request $request, ProductVariant $productVariant): Response
    {
        $envelope = $this->bus->dispatch(GetProductVariantFormDataQuery::create($productVariant));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(ProductVariantFormType::class, $formData, [
            'action' => $this->generateUrl('product_variant_edit', ['id' => $productVariant->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditProductVariantCommand::createFromForm($form, $productVariant->getProduct()));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productVariantModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_detail', ['id' => $productVariant->getProduct()->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductVariantCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productVariantModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-variant-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/api/product/{id}/variants', name: 'api_product_variants')]
    #[IsGranted(Role::READER->value)]
    public function productVariantsApi(Product $product): JsonResponse
    {
        try {
            $envelope = $this->bus->dispatch(GetProductVariantsGridQuery::create($product->getId()));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/product/variant/{id}', name: 'product_variant_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(ProductVariant $productVariant): JsonResponse
    {
        try {
            $this->bus->dispatch(DeleteProductVariantCommand::create($productVariant->getProduct()->getId(), $productVariant->getId()));

            return new JsonResponse(['success' => true]);
        } catch (ProductException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while deleting the price'], 500);
        }
    }

    #[Route('/product/variant/{id}', name: 'product_variant_detail', methods: ['GET'])]
    #[IsGranted(Role::READER->value)]
    public function detail(ProductVariant $productVariant): Response
    {
        return $this->render('product/variant.html.twig', [
            'productVariant' => $productVariant,
            'product' => $productVariant->getProduct(),
        ]);
    }
}
