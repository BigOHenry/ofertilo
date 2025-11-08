<?php

declare(strict_types=1);

namespace App\Application\Query\Wood\GetWoodFormData;

use App\Application\Service\WoodApplicationService;
use App\Domain\Translation\Entity\TranslationEntity;
use App\Domain\Wood\Exception\WoodNotFoundException;
use App\Infrastructure\Form\Helper\TranslationFormHelper;
use Doctrine\Common\Collections\ArrayCollection;
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
     * @param GetWoodFormDataQuery $query
     * @return array{id: int|null, name: string, latinName: string, dryDensity: int, hardness: int, enabled: bool, translations: ArrayCollection<int, TranslationEntity>}
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
