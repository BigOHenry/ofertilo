<?php

declare(strict_types=1);

namespace App\Domain\Material\Entity;

use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Material\ValueObject\MeasurementType;
use App\Domain\Wood\Entity\Wood;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'material')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string', enumType: MaterialType::class)]
#[ORM\DiscriminatorMap(MaterialType::DISCRIMINATOR_MAP)]
abstract class Material
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Wood::class, cascade: ['persist'])]
    private Wood $wood;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    /**
     * @var Collection<int, MaterialPrice>
     */
    #[ORM\OneToMany(targetEntity: MaterialPrice::class, mappedBy: 'material', cascade: ['persist'], orphanRemoval: true)]
    private Collection $prices;

    protected function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    abstract public function getMeasurementType(): MeasurementType;

    public function getId(): ?int
    {
        return $this->id;
    }

    abstract public function getType(): MaterialType;

    public function getWood(): Wood
    {
        return $this->wood;
    }

    public function setWood(Wood $wood): void
    {
        $this->wood = $wood;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getDescription(?string $locale = null): string
    {
        return \sprintf('%s - %s', $this->wood->getDescription($locale), $this->getType()->value);
    }

    public function getName(): string
    {
        return \sprintf('%s_%s', $this->wood->getName(), $this->getType()->name);
    }

    /**
     * @return Collection<int, MaterialPrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * @throws MaterialPriceAlreadyExistsException
     */
    public function addPrice(int $thickness, string $price): void
    {
        if ($this->findPriceByThickness($thickness)) {
            throw MaterialPriceAlreadyExistsException::withThickness($thickness);
        }
        $materialPrice = MaterialPrice::create($this, $thickness, $price);
        $this->prices->add($materialPrice);
    }

    public function removePrice(MaterialPrice $price): void
    {
        if (!$this->prices->contains($price)) {
            throw MaterialPriceNotFoundException::withThickness($price->getThickness());
        }

        $this->prices->removeElement($price);
    }

    public static function getMaterialClassByType(MaterialType $type): string
    {
        return match ($type) {
            MaterialType::PIECE => PieceMaterial::class,
            MaterialType::PLYWOOD => PlywoodMaterial::class,
            MaterialType::EDGE_GLUED_PANEL => EdgeGluedPanelMaterial::class,
            MaterialType::SOLID_WOOD => SolidWoodMaterial::class,
        };
    }

    public function findPriceById(int $id): ?MaterialPrice
    {
        return $this->prices->filter(
            fn (MaterialPrice $f) => $f->getId() === $id
        )->first() ?: null;
    }

    public function findPriceByThickness(int $thickness): ?MaterialPrice
    {
        return $this->prices->filter(
            fn (MaterialPrice $f) => $f->getThickness() === $thickness
        )->first() ?: null;
    }

    /**
     * @throws MaterialPriceNotFoundException
     */
    public function getPriceById(int $id): MaterialPrice
    {
        return $this->findPriceById($id) ?? throw MaterialPriceNotFoundException::withId($id);
    }
}
