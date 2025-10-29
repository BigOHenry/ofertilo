<?php

declare(strict_types=1);

namespace App\Application\Query\Color\GetColorFormData;

use App\Application\Color\ColorApplicationService;
use App\Domain\Color\Exception\ColorNotFoundException;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Infrastructure\Form\Helper\TranslationFormHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetColorFormDataQueryHandler
{
    public function __construct(
        private ColorApplicationService $colorApplicationService,
        private TranslationFormHelper $translationHelper,
    ) {
    }

    /**
     * @param GetColorFormDataQuery $query
     * @return array{id: int|null, code: int, inStock: bool, enabled: bool, translations: ArrayCollection<int, TranslationEntity>}
     */
    public function __invoke(GetColorFormDataQuery $query): array
    {
        $color = $this->colorApplicationService->findById($query->getId());

        if (!$color) {
            throw ColorNotFoundException::withId($query->getId());
        }

        return [
            'id' => $color->getId(),
            'code' => $color->getCode(),
            'inStock' => $color->isInStock(),
            'enabled' => $color->isEnabled(),
            'translations' => $this->translationHelper->prepareTranslationsFromEntity($color),
        ];
    }
}
