<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Product\Command\CreateProduct\CreateProductCommand;
use App\Application\Product\Command\CreateProductColor\CreateProductColorCommand;
use App\Application\Product\Command\DeleteProduct\DeleteProductCommand;
use App\Application\Product\Command\DeleteProductColor\DeleteProductColorCommand;
use App\Application\Product\Command\EditProduct\EditProductCommand;
use App\Application\Product\Command\EditProductColor\EditProductColorCommand;
use App\Application\Product\Query\GetProductColorFormData\GetProductColorFormDataQuery;
use App\Application\Product\Query\GetProductColorsGrid\GetProductColorsGridQuery;
use App\Application\Product\Query\GetProductFormData\GetProductFormDataQuery;
use App\Application\Product\Query\GetProductsPaginatedGrid\GetProductsPaginatedGridQuery;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Exception\ProductException;
use App\Domain\User\ValueObject\Role;
use App\Infrastructure\Web\Form\ProductColorFormType;
use App\Infrastructure\Web\Form\ProductFormType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ProductController extends BaseController
{
    #[Route('/products', name: 'product_index')]
    #[IsGranted(Role::READER->value)]
    public function index(): Response
    {
        return $this->render('product/index.html.twig');
    }

    #[Route('/product/new', name: 'product_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function new(Request $request): Response
    {
        $form = $this->createForm(ProductFormType::class, data: $this->formHelper->prepareFormData(Product::class), options: [
            'action' => $this->generateUrl('product_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateProductCommand::createFromForm($form));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CreateProductCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productModal_frame',
            'form_template' => '/components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-form',
                'product' => null,
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/product/{id}/edit', name: 'product_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function productEdit(Request $request, Product $product): Response
    {
        $envelope = $this->bus->dispatch(new GetProductFormDataQuery((int) $product->getId()));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(ProductFormType::class, $formData, [
            'action' => $this->generateUrl('product_edit', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditProductCommand::createFromForm($form));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                $referer = $request->headers->get('referer') ?? '';
                $isDetailPage = str_contains($referer, '/product/');

                if ($isDetailPage) {
                    return $this->render('product/_streams/_card.stream.html.twig', [
                        'product' => $product,
                    ]);
                }

                if ($frameId === 'productModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productModal_frame',
            'form_template' => '/components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'product' => $product,
                'form_id' => 'product-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/api/products', name: 'api_products')]
    #[IsGranted(Role::READER->value)]
    public function productsApi(Request $request): JsonResponse
    {
        try {
            $envelope = $this->bus->dispatch(GetProductsPaginatedGridQuery::createFormRequest($request));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/product/{id}', name: 'product_detail', methods: ['GET'])]
    #[IsGranted(Role::READER->value)]
    public function detail(Product $product): Response
    {
        return $this->render('product/detail.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}', name: 'product_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function deleteProduct(Product $product): JsonResponse
    {
        try {
            $this->bus->dispatch(DeleteProductCommand::create((int) $product->getId()));

            return new JsonResponse(['success' => true]);
        } catch (ProductException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/product/{id}/color/new', name: 'product_color_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newColor(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductColorFormType::class, ['product' => $product], [
            'action' => $this->generateUrl('product_color_new', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(CreateProductColorCommand::createFromForm($form, $product));
                $this->addFlash('success', $this->translator->trans('message.item_created'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productColorModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_detail', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => CreateProductColorCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productColorModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-color-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/product/color/{id}/edit', name: 'product_color_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editColor(Request $request, ProductColor $productColor): Response
    {
        $envelope = $this->bus->dispatch(GetProductColorFormDataQuery::create($productColor));
        $formData = $envelope->last(HandledStamp::class)?->getResult();

        $form = $this->createForm(ProductColorFormType::class, $formData, [
            'action' => $this->generateUrl('product_color_edit', ['id' => $productColor->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->bus->dispatch(EditProductColorCommand::createFromForm($form, $productColor->getProduct()));
                $this->addFlash('success', $this->translator->trans('message.item_updated'));

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productColorModal_frame') {
                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_detail', ['id' => $productColor->getProduct()->getId()], Response::HTTP_SEE_OTHER);
            } catch (HandlerFailedException $e) {
                $this->handleHandlerException($e, $form);
            }
        }

        $response = $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductColorCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productColorModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-color-form',
            ],
        ]);

        if ($form->isSubmitted() && !$form->isValid()) {
            $response->setStatusCode(422);
        }

        return $response;
    }

    #[Route('/api/product_colors/{id}', name: 'api_product_colors')]
    #[IsGranted(Role::READER->value)]
    public function productColorsApi(Product $product): JsonResponse
    {
        try {
            \assert($product->getId() !== null, 'Product must have an ID when loaded from database');
            $envelope = $this->bus->dispatch(GetProductColorsGridQuery::create($product->getId()));

            return $this->json($envelope->last(HandledStamp::class)?->getResult());
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/product/color/{id}', name: 'product_color_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(ProductColor $productColor): JsonResponse
    {
        try {
            \assert($productColor->getId() !== null, 'ProductColor must have an ID when loaded from database');
            $this->bus->dispatch(DeleteProductColorCommand::create($productColor->getId(), $productColor->getId()));

            return new JsonResponse(['success' => true]);
        } catch (ProductException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while deleting the price'], 500);
        }
    }
}
