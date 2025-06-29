<?php

declare(strict_types=1);

namespace App\Application\Material;

use App\Application\Material\Command\CreateMaterialCommand;
use App\Application\Material\Command\CreateMaterialPriceCommand;
use App\Application\Material\Command\EditMaterialCommand;
use App\Application\Material\Command\EditMaterialPriceCommand;
use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Exception\InvalidMaterialException;
use App\Domain\Material\Exception\InvalidMaterialPriceException;
use App\Domain\Material\Exception\MaterialAlreadyExistsException;
use App\Domain\Material\Factory\MaterialFactory;
use App\Domain\Material\Repository\MaterialRepositoryInterface;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Translation\Repository\TranslationLoaderInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class MaterialService
{
    public function __construct(
        private MaterialRepositoryInterface $materialRepository,
        private MaterialFactory $materialFactory,
        private TranslationLoaderInterface $translationLoader,
        private TranslatorInterface $translator,
    ) {
    }

    public function createFromCommand(CreateMaterialCommand $command): Material
    {
        $type = $command->getType();
        $name = $command->getName();

        if ($type === null) {
            throw InvalidMaterialException::emptyType();
        }

        if ($name === null || mb_trim($name) === '') {
            throw InvalidMaterialException::emptyName();
        }

        if ($this->materialRepository->findByTypeAndName($type, $name)) {
            throw MaterialAlreadyExistsException::withTypeAndName($type, $name);
        }

        $material = $this->materialFactory->create($type, $name);
        $material->setLatinName($command->getLatinName());
        $material->setDryDensity($command->getDryDensity());
        $material->setHardness($command->getHardness());
        $material->setEnabled($command->isEnabled());

        foreach ($command->getTranslations() as $translation) {
            $value = $translation->getValue();
            if ($value !== null && !empty(mb_trim($value))) {
                if ($translation->getField() === 'description') {
                    $material->setDescription($value, $translation->getLocale());
                } elseif ($translation->getField() === 'place_of_origin') {
                    $material->setPlaceOfOrigin($value, $translation->getLocale());
                }
            }
        }

        $this->materialRepository->save($material);

        return $material;
    }

    public function updateFromCommand(Material $material, EditMaterialCommand $command): void
    {
        if ($material->getName() !== $command->getName()) {
            if ($this->materialRepository->findByTypeAndName($command->getType(), $command->getName())) {
                throw MaterialAlreadyExistsException::withTypeAndName($command->getType(), $command->getName());
            }
        }

        $material->setName($command->getName());
        $material->setType($command->getType());
        $material->setLatinName($command->getLatinName());
        $material->setDryDensity($command->getDryDensity());
        $material->setHardness($command->getHardness());
        $material->setEnabled($command->isEnabled());

        foreach ($command->getTranslations() as $translation) {
            $value = $translation->getValue();
            if ($value !== null) {
                $trimmedValue = mb_trim($value);
                if ($translation->getField() === 'description') {
                    $material->setDescription(!empty($trimmedValue) ? $trimmedValue : null, $translation->getLocale());
                } elseif ($translation->getField() === 'place_of_origin') {
                    $material->setPlaceOfOrigin(!empty($trimmedValue) ? $trimmedValue : null, $translation->getLocale());
                }
            }
        }

        $this->materialRepository->save($material);
    }

    public function createPriceFromCommand(CreateMaterialPriceCommand $command): void
    {
        $thickness = $command->getThickness();
        $price = $command->getPrice();

        if ($thickness === null) {
            throw InvalidMaterialPriceException::emptyThickness();
        }

        if ($price === null || mb_trim($price) === '') {
            throw InvalidMaterialPriceException::emptyPrice();
        }

        $command->getMaterial()->addPrice($thickness, $price);
        $this->materialRepository->save($command->getMaterial());
    }

    public function updatePriceFromCommand(MaterialPrice $materialPrice, EditMaterialPriceCommand $command): void
    {
        $materialPrice->setThickness($command->getThickness());
        $materialPrice->setPrice($command->getPrice());
        $this->materialRepository->save($materialPrice->getMaterial());
    }

    public function create(Type $type, string $name): Material
    {
        if ($this->materialRepository->findByTypeAndName($type, $name)) {
            throw MaterialAlreadyExistsException::withTypeAndName($type, $name);
        }

        return $this->materialFactory->create($type, $name);
    }

    public function save(Material $material): void
    {
        $this->materialRepository->save($material);
    }

    public function delete(Material $material): void
    {
        $this->materialRepository->remove($material);
    }

    /**
     * @return array<string, mixed>
     */
    public function getPaginatedMaterials(Request $request): array
    {
        $page = max((int) $request->query->get('page', 1), 1);
        $size = min((int) $request->query->get('size', 10), 100);
        $offset = ($page - 1) * $size;

        $qb = $this->materialRepository->createQueryBuilder('m')
                                       ->setFirstResult($offset)
                                       ->setMaxResults($size)
        ;

        $sortData = $request->query->all('sort');
        $sortField = $sortData['field'] ?? null;
        $sortDir = $sortData['dir'] ?? 'asc';

        $allowedFields = ['name', 'type'];
        $allowedDirections = ['asc', 'desc'];

        if (
            \in_array($sortField, $allowedFields, true)
            && \in_array(mb_strtolower($sortDir), $allowedDirections, true)
        ) {
            $qb->orderBy("m.$sortField", mb_strtoupper($sortDir));
        }

        $paginator = new Paginator($qb);
        $total = \count($paginator);

        $data = [];
        /** @var Material $material */
        foreach ($paginator as $material) {
            $this->translationLoader->loadTranslations($material);
            $data[] = [
                'id' => $material->getId(),
                'name' => $material->getName(),
                'description' => $material->getDescription($request->getLocale()),
                'type' => $this->translator->trans(
                    'material.type.' . $material->getType()->value,
                    domain: 'enum'
                ),
            ];
        }

        return [
            'data' => $data,
            'last_page' => ceil($total / $size),
            'total' => $total,
        ];
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

    public function createEmptyPrice(Material $material): MaterialPrice
    {
        return MaterialPrice::createEmpty($material);
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
            'material_name' => $material->getName(),
            'total_prices' => \count($data),
        ];
    }
}
