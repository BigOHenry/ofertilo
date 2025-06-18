<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Repository\ProductColorRepositoryInterface;
use App\Domain\Product\Repository\ProductRepositoryInterface;
use App\Domain\Translation\Service\TranslationInitializer;
use App\Domain\User\ValueObject\Role;
use App\Form\ProductColorType;
use App\Form\ProductType;
use App\Infrastructure\Persistence\Doctrine\DoctrineTranslationLoader;
use App\Infrastructure\Service\FileUploader;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Turbo\TurboBundle;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class ProductController extends AbstractController
{
    /**
     * @param array<int, string> $locales
     */
    public function __construct(
        #[Autowire('%app.supported_locales%')] private readonly array $locales,
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
    public function new(Request $request, ProductRepositoryInterface $productRepository, FileUploader $fileUploader): Response
    {
        $product = new Product();
        TranslationInitializer::prepare($product, $this->locales);

        $form = $this->createForm(ProductType::class, $product, [
            'action' => $this->generateUrl('product_new'),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $uploadResult = $fileUploader->upload($imageFile, $product->getEntityFolder());
                $product->setImageFilename($uploadResult['filename']);
                $product->setImageOriginalName($uploadResult['originalName']);
            }

            $productRepository->save($product);

            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
            'frame_id' => 'productModal_frame',
            'form_template' => 'components/product_form.html.twig',
            'form_context' => [
                'form' => $form->createView(),
                'form_id' => 'product-form',
                'product' => $product,
            ],
        ]);
    }

    #[Route('/api/products', name: 'api_products')]
    #[IsGranted(Role::READER->value)]
    public function productsApi(
        Request $request,
        ProductRepositoryInterface $productRepository,
        DoctrineTranslationLoader $translationLoader,
        TranslatorInterface $translator,
    ): JsonResponse {
        $page = max((int) $request->query->get('page', 1), 1);
        $size = min((int) $request->query->get('size', 10), 100);
        $offset = ($page - 1) * $size;

        $qb = $productRepository->createQueryBuilder('m')
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
        /** @var Product $product */
        foreach ($paginator as $product) {
            $translationLoader->loadTranslations($product);
            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'description' => $product->getDescription($request->getLocale()),
                'country' => $product->getCountry()->getName(),
                'type' => $product->getType()->value,
                'enabled' => $translator->trans($product->isEnabled() ? 'boolean.yes' : 'boolean.no', domain: 'messages'),
            ];
        }

        return $this->json([
            'data' => $data,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ]);
    }

    #[Route('/product/{id}/edit', name: 'product_edit')]
    #[IsGranted(Role::WRITER->value)]
    public function productEdit(
        Request $request,
        Product $product,
        ProductRepositoryInterface $productRepository,
    ): Response {
        TranslationInitializer::prepare($product, $this->locales);

        $form = $this->createForm(ProductType::class, $product, [
            'action' => $this->generateUrl('product_edit', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product);
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
        }

        return $this->render('components/form_frame.html.twig', [
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
    public function deleteProduct(Product $product, ProductRepositoryInterface $productRepository): JsonResponse
    {
        $productRepository->remove($product);

        return new JsonResponse(['success' => true]);
    }



    #[Route('/product/{id}/color/new', name: 'product_color_new', methods: ['GET', 'POST'])]
    #[IsGranted(Role::WRITER->value)]
    public function newColor(Request $request, Product $product, ProductColorRepositoryInterface $productColorRepository): Response
    {
        $productColor = new ProductColor($product);
        $form = $this->createForm(ProductColorType::class, $productColor, [
            'action' => $this->generateUrl('product_color_new', ['id' => $product->getId()]),
            'method' => 'POST',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productColorRepository->save($productColor);
            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('product_detail', ['id' => $product->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
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
    public function editColor(
        Request $request,
        ProductColor $productColor,
        ProductColorRepositoryInterface $productColorRepository,
    ): Response {
        $form = $this->createForm(ProductColorType::class, $productColor, [
            'action' => $this->generateUrl('product_color_edit', ['id' => $productColor->getId()]),
            'method' => 'POST',
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productColorRepository->save($productColor);

            if ($request->getPreferredFormat() === TurboBundle::STREAM_FORMAT) {
                $request->setRequestFormat(TurboBundle::STREAM_FORMAT);

                return $this->render('components/stream_modal_cleanup.html.twig');
            }

            return $this->redirectToRoute('product_detail', ['id' => $productColor->getProduct()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('components/form_frame.html.twig', [
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
    public function productColorsApi(Product $product, Request $request): JsonResponse
    {
        $data = [];
        foreach ($product->getProductColors() as $productColor) {
            $data[] = [
                'id' => $productColor->getId(),
                'color' => $productColor->getColor()->getCode(),
                'description' => $productColor->getDescription(),
            ];
        }

        return $this->json([
            'data' => $data,
        ]);
    }


    #[Route('/product/color/{id}', name: 'product_color_delete', methods: ['DELETE'])]
    #[IsGranted(Role::WRITER->value)]
    public function delete(ProductColor $productColor, ProductColorRepositoryInterface $productColorRepository): JsonResponse
    {
        $productColorRepository->remove($productColor);

        return new JsonResponse(['success' => true]);
    }

}
