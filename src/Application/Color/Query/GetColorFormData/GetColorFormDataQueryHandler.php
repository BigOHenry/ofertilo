<?php

declare(strict_types=1);

namespace App\Application\Color\Query\GetColorFormData;

use App\Application\Color\Service\ColorApplicationService;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Infrastructure\Web\Form\Helper\TranslationFormHelper;
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
     * @return array{id: string, code: int, inStock: bool, enabled: bool, translations: array<int, TranslationEntity>}
     */
    public function __invoke(GetColorFormDataQuery $query): array
    {
        $color = $this->colorApplicationService->getById($query->getId());

        return [
            'id' => $color->getId(),
            'code' => $color->getCode(),
            'inStock' => $color->isInStock(),
            'enabled' => $color->isEnabled(),
            'translations' => $this->translationHelper->prepareTranslationsFromEntity($color),
        ];
    }
}
