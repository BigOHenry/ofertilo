<?php

declare(strict_types=1);

namespace App\Domain\Material\Entity;

use App\Domain\Material\Exception\DuplicatePriceThicknessException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Wood\Entity\Wood;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineMaterialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DoctrineMaterialRepository::class)]
#[ORM\Table(name: 'material')]
class Material
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Wood::class, cascade: ['persist'])]
    private Wood $wood;

    #[ORM\Column(nullable: false, enumType: Type::class)]
    #[Assert\NotNull(message: 'not_null')]
    private Type $type;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    /**
     * @var Collection<int, MaterialPrice>
     */
    #[ORM\OneToMany(targetEntity: MaterialPrice::class, mappedBy: 'material', cascade: ['persist'], orphanRemoval: true)]
    private Collection $prices;

    protected function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->prices = new ArrayCollection();
    }

    public static function create(Wood $wood, Type $type, bool $enabled = true): self
    {
        $material = new self();
        $material->wood = $wood;
        $material->type = $type;
        $material->enabled = $enabled;

        return $material;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): Type
    {
        if (!isset($this->type)) {
            throw new \LogicException('Material type is not initialized');
        }

        return $this->type;
    }

    public function getWood(): Wood
    {
        return $this->wood;
    }

    public function setWood(Wood $wood): void
    {
        $this->wood = $wood;
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

    /**
     * @return Collection<int, MaterialPrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(int $thickness, string $price): void
    {
        foreach ($this->prices as $existingPrice) {
            if ($existingPrice->getThickness() === $thickness) {
                throw DuplicatePriceThicknessException::forThickness($thickness);
            }
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

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }
}
