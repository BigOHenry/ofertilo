<?php

declare(strict_types=1);

namespace App\Application\Wood\Query\GetWoodFormData;

use App\Application\Wood\Service\WoodApplicationService;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Wood\Exception\WoodNotFoundException;
use App\Infrastructure\Web\Form\Helper\TranslationFormHelper;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetWoodFormDataQueryHandler
{
    public function __construct(
        private WoodApplicationService $woodApplicationService,
        private TranslationFormHelper $translationHelper,
    ) {
    }

    /**
     * @return array{id: int|null, name: string, latinName: string|null, dryDensity: int|null,
     *      hardness: int|null, enabled: bool, translations: array<int, TranslationEntity>}
     */
    public function __invoke(GetWoodFormDataQuery $query): array
    {
        $color = $this->woodApplicationService->findById($query->getId());

        if (!$color) {
            throw WoodNotFoundException::withId($query->getId());
        }

        return [
            'id' => $color->getId(),
            'name' => $color->getName(),
            'latinName' => $color->getLatinName(),
            'dryDensity' => $color->getDryDensity(),
            'hardness' => $color->getHardness(),
            'enabled' => $color->isEnabled(),
            'translations' => $this->translationHelper->prepareTranslationsFromEntity($color),
        ];
    }
}
