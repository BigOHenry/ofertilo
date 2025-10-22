<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller\Web;

use App\Application\Product\Command\CreateProductColorCommand;
use App\Application\Product\Command\EditProductColorCommand;
use App\Application\Product\Command\EditProductCommand;
use App\Application\Product\Factory\ProductCommandFactory;
use App\Application\Product\ProductApplicationService;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Exception\ProductException;
use App\Domain\User\ValueObject\Role;
use App\Form\ProductColorType;
use App\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductApplicationService $productService,
        private readonly ProductCommandFactory $commandFactory,
    ) {
    }

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
        $command = $this->commandFactory->createCreateCommand();
        $form = $this->createForm(ProductType::class, $command, [
            'action' => $this->generateUrl('product_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $product = $this->productService->createFromCommand($command);

                if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
            } catch (ProductException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductCommand::class,
            'frame_id' => 'productModal_frame',
            'form_template' => 'components/product_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-form',
                'product' => null,
            ],
        ]);
    }

    #[Route('/api/products', name: 'api_products')]
    #[IsGranted(Role::READER->value)]
    public function productsApi(Request $request): JsonResponse
    {
        try {
            return $this->json($this->productService->getPaginatedProducts($request));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/product/{id}/edit', name: 'product_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function productEdit(Request $request, Product $product): Response
    {
        $command = $this->commandFactory->createEditCommand($product);
        $form = $this->createForm(ProductType::class, $command, [
            'action' => $this->generateUrl('product_edit', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->productService->updateFromCommand($product, $command);

                $frameId = $request->request->get('frame_id');
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                if ($frameId === 'productModal_frame') {
                    $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                    return $this->render('components/stream_modal_cleanup.html.twig');
                }

                if ($frameId === 'productDetailModal_frame') {
                    return $this->render('product/_streams/product_card.stream.html.twig', [
                        'product' => $product,
                    ]);
                }
            } catch (ProductException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductCommand::class,
            'frame_id' => $request->headers->get('Turbo-Frame') ?? 'productModal_frame',
            'form_template' => 'components/product_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'product' => $product,
                'form_id' => 'product-form',
            ],
        ]);
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
            $this->productService->delete($product);

            return new JsonResponse(['success' => true]);
        } catch (ProductException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/product/{id}/color/new', name: 'product_color_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newColor(Request $request, Product $product): Response
    {
        $command = $this->commandFactory->createCreateColorCommand($product);
        $availableColors = $this->productService->getAvailableColorsForProduct($product);

        $form = $this->createForm(ProductColorType::class, $command, [
            'action' => $this->generateUrl('product_color_new', ['id' => $product->getId()]),
            'available_colors' => $availableColors,
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->productService->createColorFromCommand($command);
            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('product_detail', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => CreateProductColorCommand::class,
            'frame_id' => 'productColorModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-color-form',
            ],
        ]);
    }

    #[Route('/product/color/{id}/edit', name: 'product_color_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function editColor(Request $request, ProductColor $productColor): Response
    {
        $command = $this->commandFactory->createEditColorCommand($productColor);

        $availableColors = $this->productService->getAvailableColorsForProduct($productColor->getProduct(), $productColor->getColor());

        $form = $this->createForm(ProductColorType::class, $command, [
            'action' => $this->generateUrl('product_color_edit', ['id' => $productColor->getId()]),
            'available_colors' => $availableColors,
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->productService->updateColorFromCommand($productColor, $command);

            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('product_detail', ['id' => $productColor->getProduct()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
            'data_class' => EditProductColorCommand::class,
            'frame_id' => 'productColorModal_frame',
            'form_template' => 'components/_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-color-form',
            ],
        ]);
    }

    #[Route('/api/product_colors/{id}', name: 'api_product_colors')]
    #[IsGranted(Role::READER->value)]
    public function productColorsApi(Product $product): JsonResponse
    {
        try {
            return $this->json($this->productService->getProductColorsData($product));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid parameters'], 400);
        }
    }

    #[Route('/product/color/{id}', name: 'product_color_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(ProductColor $productColor): JsonResponse
    {
        try {
            $this->productService->removeColorFromProduct($productColor->getProduct(), $productColor->getColor());

            return new JsonResponse(['success' => true]);
        } catch (ProductException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'An error occurred while deleting the price'], 500);
        }
    }
}
