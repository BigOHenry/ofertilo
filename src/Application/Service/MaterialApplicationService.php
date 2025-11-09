<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Exception\InvalidMaterialPriceException;
use App\Domain\Material\Repository\MaterialPriceRepositoryInterface;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;

final readonly class MaterialApplicationService
{
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
        private MaterialPriceRepositoryInterface $materialPriceRepository,
    ) {
    }

    public function findByWoodAndType(Wood $wood, Type $type): ?Material
    {
        return $this->materialRepository->findByWoodAndType($wood, $type);
    }

    public function findById(int $id): ?Material
    {
        return $this->materialRepository->findById($id);
    }

    public function findMaterialPriceByMaterialAndThickness(Material $material, int $thickness): ?MaterialPrice
    {
        return $this->materialPriceRepository->findByMaterialAndThickness($material, $thickness);
    }

    public function findMaterialPriceById(int $id): ?MaterialPrice
    {
        return $this->materialPriceRepository->findById($id);
    }

    public function updatePriceFromCommand(MaterialPrice $materialPrice, EditMaterialPriceCommand $command): void
    {
        $materialPrice->setThickness($command->getThickness());
        $materialPrice->setPrice($command->getPrice());
        $this->materialRepository->save($materialPrice->getMaterial());
    }

    public function save(Material $material): void
    {
        $this->materialRepository->save($material);
    }

    public function delete(Material $material): void
    {
        $this->materialRepository->remove($material);
    }

    public function addPriceToMaterial(Material $material, int $thickness, string $price): void
    {
        $material->addPrice($thickness, $price);
        $this->materialRepository->save($material);
    }

    public function updateMaterialPrice(MaterialPrice $materialPrice, int $thickness, string $price): void
    {
        $materialPrice->setThickness($thickness);
        $materialPrice->setPrice($price);
        $this->materialRepository->save($materialPrice->getMaterial());
    }

    public function removePriceFromMaterial(MaterialPrice $materialPrice): void
    {
        $material = $materialPrice->getMaterial();

        $material->removePrice($materialPrice);
        $this->materialRepository->save($material);
    }

    /**
     * @return array<string, mixed>
     */
    public function getMaterialPricesData(Material $material): array
    {
        $data = [];
        foreach ($material->getPrices() as $price) {
            $data[] = [
                'id' => $price->getId(),
                'thickness' => $price->getThickness(),
                'price' => $price->getPrice(),
                'formatted_price' => $price->getPrice() . ' Kč',
                'formatted_thickness' => $price->getThickness() . ' mm',
            ];
        }

        usort($data, static fn ($a, $b) => $a['thickness'] <=> $b['thickness']);

        return [
            'data' => $data,
            'material_id' => $material->getId(),
            'material_name' => $material->getDescription(),
            'total_prices' => \count($data),
        ];
    }
}
