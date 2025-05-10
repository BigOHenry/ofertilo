<?php

namespace App\Domain\Material;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'material')]
class Material
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 200, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(length: 400, nullable: false)]
    private ?string $description = null;

    #[ORM\Column(nullable: false, enumType: Type::class)]
    private ?Type $type = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $latin_name = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $place_of_origin = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    private ?string $dry_density = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    private ?string $hardness = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\OneToMany(targetEntity: MaterialPrice::class, mappedBy: 'material', cascade: ['persist'], orphanRemoval: true)]
    private Collection $prices;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): void
    {
        $this->type = $type;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getLatinName(): ?string
    {
        return $this->latin_name;
    }

    public function setLatinName(?string $latin_name): void
    {
        $this->latin_name = $latin_name;
    }

    public function getPlaceOfOrigin(): ?string
    {
        return $this->place_of_origin;
    }

    public function setPlaceOfOrigin(?string $place_of_origin): void
    {
        $this->place_of_origin = $place_of_origin;
    }

    public function getDryDensity(): ?string
    {
        return $this->dry_density;
    }

    public function setDryDensity(?string $dry_density): void
    {
        $this->dry_density = $dry_density;
    }

    public function getHardness(): ?string
    {
        return $this->hardness;
    }

    public function setHardness(?string $hardness): void
    {
        $this->hardness = $hardness;
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(MaterialPrice $price): void
    {
        $this->prices[] = $price;
        $price->setMaterial($this);
    }

    public function removePrice(MaterialPrice $price): void
    {
        $this->prices->removeElement($price);
    }
}
