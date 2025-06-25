<?php

declare(strict_types=1);

namespace App\Domain\Material\Entity;

use App\Domain\Material\Exception\DuplicatePriceThicknessException;
use App\Domain\Material\Exception\InvalidMaterialException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\ValueObject\Type;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use App\Infrastructure\Persistence\Doctrine\DoctrineMaterialRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;
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

    #[ORM\Column(length: 50, nullable: false)]
    #[Assert\NotBlank(message: 'Material name is required')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'Name must be at least {{ limit }} characters',
        maxMessage: 'Name cannot exceed {{ limit }} characters'
    )]
    #[Assert\Regex(
        pattern: '/^[a-z\s\-_]+$/',
        message: 'Name contains invalid characters'
    )]
    private string $name;

    #[ORM\Column(nullable: false, enumType: Type::class)]
    #[Assert\NotNull]
    private Type $type;

    #[ORM\Column(length: 300, nullable: true)]
    #[Assert\Length(max: 300)]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'Latin name can only contain letters and spaces'
    )]
    private ?string $latin_name = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    #[Assert\Range(
        notInRangeMessage: 'Dry density must be between {{ min }} and {{ max }} kg/m³',
        min: 10,
        max: 2000
    )]
    private ?int $dry_density = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    #[Assert\Range(
        notInRangeMessage: 'Hardness must be between {{ min }} and {{ max }}',
        min: 1,
        max: 9999
    )]
    private ?int $hardness = null;

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
        $this->initTranslations();
    }

    public static function create(Type $type, string $name): self
    {
        $product = new self();
        $product->setType($type);
        $product->setName($name);
        $product->enabled = true;

        return $product;
    }

    public static function createEmpty(): self
    {
        return new self();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        self::validateName($name);
        $this->name = $name;
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationFromMemory('description', $locale ?? 'en');
    }

    public function setDescription(string $value, string $locale = 'en'): void
    {
        self::validateDescription($value);
        $this->addOrUpdateTranslation('description', $value, $locale);
    }

    public function getPlaceOfOrigin(?string $locale = null): ?string
    {
        return $this->getTranslationFromMemory('place_of_origin', $locale ?? 'en');
    }

    public function setPlaceOfOrigin(string $value, string $locale = 'en'): void
    {
        self::validatePlaceOfOrigin($value);
        $this->addOrUpdateTranslation('place_of_origin', $value, $locale);
    }

    public function getType(): Type
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
        if ($latin_name !== null) {
            self::validateLatinName($latin_name);
        }
        $this->latin_name = $latin_name;
    }

    public function getDryDensity(): ?int
    {
        return $this->dry_density;
    }

    public function setDryDensity(?int $dry_density): void
    {
        if ($dry_density !== null) {
            self::validateDryDensity($dry_density);
        }
        $this->dry_density = $dry_density;
    }

    public function getHardness(): ?int
    {
        return $this->hardness;
    }

    public function setHardness(?int $hardness): void
    {
        if ($hardness !== null) {
            self::validateHardness($hardness);
        }
        $this->hardness = $hardness;
    }

    /**
     * @return Collection<int, MaterialPrice>
     */
    public function getPrices(): Collection
    {
        return $this->prices;
    }

    public function addPrice(int $thickness, float $price): void
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

    private static function validateName(string $name): void
    {
        $trimmed = trim($name);
        if (empty($trimmed)) {
            throw InvalidMaterialException::emptyName();
        }

        if (strlen($trimmed) < 2) {
            throw InvalidMaterialException::nameTooShort(2);
        }

        if (strlen($trimmed) > 100) {
            throw InvalidMaterialException::nameTooLong(100);
        }

        if (!preg_match('/^[a-z\s\-_]+$/', $trimmed)) {
            throw InvalidMaterialException::nameInvalidCharacters();
        }
    }

    private static function validateLatinName(string $latinName): void
    {
        $trimmed = trim($latinName);
        if (strlen($trimmed) > 300) {
            throw InvalidMaterialException::latinNameTooLong(300);
        }

        if (!preg_match('/^[a-zA-Z\s]+$/u', $trimmed)) {
            throw InvalidMaterialException::latinNameInvalidCharacters();
        }
    }

    private static function validateDryDensity(int $density): void
    {
        if ($density < 10) {
            throw InvalidMaterialException::dryDensityTooLow(10);
        }

        if ($density > 2000) {
            throw InvalidMaterialException::dryDensityTooHigh(2000);
        }
    }

    private static function validateHardness(int $hardness): void
    {
        if ($hardness < 1) {
            throw InvalidMaterialException::hardnessTooLow(1);
        }

        if ($hardness > 9999) {
            throw InvalidMaterialException::hardnessTooHigh(9999);
        }
    }

    private static function validateDescription(string $description): void
    {
        $trimmed = trim($description);
        if (strlen($trimmed) > 100) {
            throw InvalidMaterialException::descriptionTooLong(100);
        }
    }

    private static function validatePlaceOfOrigin(string $placeOfOrigin): void
    {
        $trimmed = trim($placeOfOrigin);
        if (strlen($trimmed) > 200) {
            throw InvalidMaterialException::placeOfOriginTooLong(200);
        }

        if (!preg_match('/^[\p{L}\s\-,]+$/u', $trimmed)) {
            throw InvalidMaterialException::placeOfOriginInvalidCharacters();
        }
    }

}
