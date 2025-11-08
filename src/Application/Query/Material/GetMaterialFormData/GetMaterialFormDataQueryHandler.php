<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialFormData;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialNotFoundException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetMaterialFormDataQueryHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    /**
     * @param GetMaterialFormDataQuery $query
     * @return array{id: int|null, wood: int, type: string, enabled: bool}
     */
    public function __invoke(GetMaterialFormDataQuery $query): array
    {
        $material = $this->materialApplicationService->findById($query->getId());

        if ($material === null) {
            throw MaterialNotFoundException::withId($query->getId());
        }

        return [
            'id' => $material->getId(),
            'wood' => $material->getWood()->getId(),
            'type' => $material->getType()->value,
            'enabled' => $material->isEnabled(),
        ];
    }
}
