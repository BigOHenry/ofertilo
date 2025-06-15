<?php

declare(strict_types=1);

namespace App\Domain\Material;

use App\Domain\Translation\TranslatableInterface;
use App\Domain\Translation\TranslatableTrait;
use App\Infrastructure\Persistence\Doctrine\DoctrineMaterialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineMaterialRepository::class)]
#[ORM\Table(name: 'material')]
class Material implements TranslatableInterface
{
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 200, nullable: false)]
    private ?string $name = null;

    #[ORM\Column(nullable: false, enumType: Type::class)]
    private ?Type $type = null;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $latin_name = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    private ?int $dry_density = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    private ?int $hardness = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    /**
     * @var Collection<int, MaterialPrice>
     */
    #[ORM\OneToMany(targetEntity: MaterialPrice::class, mappedBy: 'material', cascade: ['persist'], orphanRemoval: true)]
    private Collection $prices;

    public function __construct()
    {
        $this->prices = new ArrayCollection();
    }

    /**
     * @return string[]
     */
    public static function getTranslatableFields(): array
    {
        return ['description', 'place_of_origin'];
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

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationFromMemory('description', $locale ?? 'en');
    }

    public function setDescription(string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation('description', $value, $locale);
    }

    public function getPlaceOfOrigin(?string $locale = null): ?string
    {
        return $this->getTranslationFromMemory('place_of_origin', $locale ?? 'en');
    }

    public function setPlaceOfOrigin(string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation('place_of_origin', $value, $locale);
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

    public function getDryDensity(): ?int
    {
        return $this->dry_density;
    }

    public function setDryDensity(?int $dry_density): void
    {
        $this->dry_density = $dry_density;
    }

    public function getHardness(): ?int
    {
        return $this->hardness;
    }

    public function setHardness(?int $hardness): void
    {
        $this->hardness = $hardness;
    }

    /**
     * @return Collection<int, MaterialPrice>
     */
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
