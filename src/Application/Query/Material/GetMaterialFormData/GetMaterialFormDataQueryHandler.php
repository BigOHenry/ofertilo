<?php

declare(strict_types=1);

namespace App\Application\Query\Material\GetMaterialFormData;

use App\Application\Service\MaterialApplicationService;
use App\Domain\Material\Exception\MaterialNotFoundException;
use App\Domain\Wood\Entity\Wood;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetMaterialFormDataQueryHandler
{
    public function __construct(
        private MaterialApplicationService $materialApplicationService,
    ) {
    }

    /**
     * @return array{id: int|null, wood: Wood|null, type: 'area'|'piece'|'volume', enabled: bool}
     */
    public function __invoke(GetMaterialFormDataQuery $query): array
    {
        $material = $this->materialApplicationService->findById($query->getId());

        if ($material === null) {
            throw MaterialNotFoundException::withId($query->getId());
        }

        return [
            'id' => $material->getId(),
            'wood' => $material->getWood(),
            'type' => $material->getType()->value,
            'enabled' => $material->isEnabled(),
        ];
    }
}
