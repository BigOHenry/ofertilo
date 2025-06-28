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
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[Assert\NotBlank(message: 'not_null')]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: 'length_min',
        maxMessage: 'length_max',
    )]
    #[Assert\Regex(
        pattern: '/^[a-z\s\-_]+$/',
        message: 'invalid_characters'
    )]
    private string $name;

    #[ORM\Column(nullable: false, enumType: Type::class)]
    #[Assert\NotNull(message: 'not_null')]
    private Type $type;

    #[ORM\Column(length: 300, nullable: true)]
    #[Assert\Length(max: 300, maxMessage: 'length_max')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'invalid_characters'
    )]
    private ?string $latin_name = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    #[Assert\Range(
        notInRangeMessage: 'range³',
        min: 10,
        max: 2000
    )]
    private ?int $dry_density = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    #[Assert\Range(
        notInRangeMessage: 'range',
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

    protected function __construct(?int $id = null)
    {
        $this->id = $id;
        $this->prices = new ArrayCollection();
        $this->initTranslations();
    }

    public static function create(Type $type, string $name): self
    {
        self::validateName($name);
        $product = new self();
        $product->setType($type);
        $product->setName($name);
        return $product;
    }

    public static function createEmpty(): self
    {
        return new self();
    }

    public static function createFromDatabase(
        int $id,
        Type $type,
        string $name,
        bool $enabled = true
    ): self {
        $material = new self($id);
        $material->type = $type;
        $material->name = $name;
        $material->enabled = $enabled;

        return $material;
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

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        if (!isset($this->name)) {
            throw new \LogicException('Material name is not initialized');
        }
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

    public function setDescription(?string $description, string $locale = 'en'): void
    {
        if ($description !== null) {
            self::validateDescription($description);
        }
        $this->addOrUpdateTranslation('description', $description, $locale);
    }

    public function getPlaceOfOrigin(?string $locale = null): ?string
    {
        return $this->getTranslationFromMemory('place_of_origin', $locale ?? 'en');
    }

    public function setPlaceOfOrigin(?string $place_of_origin, string $locale = 'en'): void
    {
        if ($place_of_origin !== null) {
            self::validatePlaceOfOrigin($place_of_origin);
        }
        $this->addOrUpdateTranslation('place_of_origin', $place_of_origin, $locale);
    }

    public function getType(): Type
    {
        if (!isset($this->type)) {
            throw new \LogicException('Material type is not initialized');
        }
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
        $trimmed = mb_trim($name);
        if (empty($trimmed)) {
            throw InvalidMaterialException::emptyName();
        }

        if (mb_strlen($trimmed) < 2) {
            throw InvalidMaterialException::nameTooShort(2);
        }

        if (mb_strlen($trimmed) > 100) {
            throw InvalidMaterialException::nameTooLong(100);
        }

        if (!preg_match('/^[a-z\s\-_]+$/', $trimmed)) {
            throw InvalidMaterialException::nameInvalidCharacters();
        }
    }

    private static function validateLatinName(string $latinName): void
    {
        $trimmed = mb_trim($latinName);
        if (mb_strlen($trimmed) > 300) {
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
        $trimmed = mb_trim($description);
        if (mb_strlen($trimmed) > 100) {
            throw InvalidMaterialException::descriptionTooLong(100);
        }
    }

    private static function validatePlaceOfOrigin(string $placeOfOrigin): void
    {
        $trimmed = mb_trim($placeOfOrigin);
        if (mb_strlen($trimmed) > 200) {
            throw InvalidMaterialException::placeOfOriginTooLong(200);
        }

        if (!preg_match('/^[\p{L}\s\-,]+$/u', $trimmed)) {
            throw InvalidMaterialException::placeOfOriginInvalidCharacters();
        }
    }
}
