<?php

declare(strict_types=1);

namespace App\Application\Product\Query\GetProductSizeComponentsGrid;

use App\Application\Product\Service\ProductApplicationService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsMessageHandler]
final readonly class GetProductSizeComponentsGridQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @return list<array{id: string, material: non-falsy-string, length: int|null, width: int|null, thickness: int, description: string|null}>
     */
    public function __invoke(GetProductSizeComponentsGridQuery $query): array
    {
        $product = $this->productApplicationService->getById($query->productId);
        $productSize = $product->getProductSizeById($query->productSizeId);

        $data = [];
        foreach ($productSize->getProductComponents() as $productComponent) {
            $material = $productComponent->getMaterial();
            $materialName = \sprintf('%s - %s', $this->translator->trans($material->getType()->label()), $material->getWood()->getDescription($this->translator->getLocale()));

            $data[] = [
                'id' => $productComponent->getId(),
                'material' => $materialName,
                'length' => $productComponent->getLength(),
                'width' => $productComponent->getWidth(),
                'thickness' => $productComponent->getThickness(),
                'description' => $productComponent->getShapeDescription()
            ];
        }

        usort($data, static fn ($a, $b) => $a['length'] <=> $b['length']);

        return $data;
    }
}
