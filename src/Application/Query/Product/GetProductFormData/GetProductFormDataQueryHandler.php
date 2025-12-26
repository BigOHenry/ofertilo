<?php

declare(strict_types=1);

namespace App\Application\Query\Product\GetProductFormData;

use App\Application\Service\ProductApplicationService;
use App\Domain\Product\Exception\ProductNotFoundException;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Entity\Country;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Infrastructure\Web\Form\Helper\TranslationFormHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetProductFormDataQueryHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
        private TranslationFormHelper $translationHelper,
    ) {
    }

    /**
     * @return array{id: int|null, type: ProductType, country: Country|null, enabled: bool, translations: ArrayCollection<int, TranslationEntity>}
     */
    public function __invoke(GetProductFormDataQuery $query): array
    {
        $product = $this->productApplicationService->findById($query->getId());

        if (!$product) {
            throw ProductNotFoundException::withId($query->getId());
        }

        return [
            'id' => $product->getId(),
            'type' => $product->getType(),
            'country' => $product->getCountry(),
            'enabled' => $product->isEnabled(),
            'translations' => $this->translationHelper->prepareTranslationsFromEntity($product),
        ];
    }
}
