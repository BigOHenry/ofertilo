<?php

declare(strict_types=1);

namespace App\Domain\Wood\Entity;

use App\Domain\Material\Exception\InvalidMaterialException;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineWoodRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DoctrineWoodRepository::class)]
#[ORM\Table(name: 'wood')]
class Wood implements TranslatableInterface
{
    use TranslatableTrait;
    public const string TRANSLATION_FIELD_PLACE_OF_ORIGIN = 'placeOfOrigin';
    public const string TRANSLATION_FIELD_DESCRIPTION = 'description';

    private const array TRANSLATION_FIELDS = [
        self::TRANSLATION_FIELD_DESCRIPTION,
        self::TRANSLATION_FIELD_PLACE_OF_ORIGIN,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true, nullable: false)]
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

    #[ORM\Column(length: 300, nullable: true)]
    #[Assert\Length(max: 300, maxMessage: 'length_max')]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'invalid_characters'
    )]
    private ?string $latinName = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    #[Assert\Range(
        notInRangeMessage: 'range³',
        min: 10,
        max: 2000
    )]
    private ?int $dryDensity = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    #[Assert\Range(
        notInRangeMessage: 'range',
        min: 1,
        max: 9999
    )]
    private ?int $hardness = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    protected function __construct()
    {
        $this->initializeTranslations();
    }

    public static function create(string $name, ?string $latin_name = null, ?int $dryDensity = null, ?int $hardness = null, bool $enabled = true): self
    {
        $product = new self();
        $product->name = $name;
        $product->latinName = $latin_name;
        $product->dryDensity = $dryDensity;
        $product->hardness = $hardness;
        $product->enabled = $enabled;

        return $product;
    }

    /**
     * @return string[]
     */
    public static function getTranslatableFields(): array
    {
        return self::TRANSLATION_FIELDS;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        if (!isset($this->name)) {
            throw new \LogicException('Material name is not initialized');
        }

        return $this->name;
    }

    public function setName(string $name): self
    {
        self::validateName($name);
        $this->name = $name;
        return $this;
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_DESCRIPTION, $locale ?? 'en');
    }

    public function setDescription(?string $description, string $locale = 'en'): void
    {
        if ($description !== null) {
            self::validateDescription($description);
        }
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_DESCRIPTION, $description, $locale);
    }

    public function getPlaceOfOrigin(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_PLACE_OF_ORIGIN, $locale ?? 'en');
    }

    public function setPlaceOfOrigin(?string $placeOfOrigin, string $locale = 'en'): void
    {
        if ($placeOfOrigin !== null) {
            self::validatePlaceOfOrigin($placeOfOrigin);
        }
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_PLACE_OF_ORIGIN, $placeOfOrigin, $locale);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getLatinName(): ?string
    {
        return $this->latinName;
    }

    public function setLatinName(?string $latinName): self
    {
        if ($latinName !== null) {
            self::validateLatinName($latinName);
        }
        $this->latinName = $latinName;
        return $this;
    }

    public function getDryDensity(): ?int
    {
        return $this->dryDensity;
    }

    public function setDryDensity(?int $dryDensity): self
    {
        if ($dryDensity !== null) {
            self::validateDryDensity($dryDensity);
        }
        $this->dryDensity = $dryDensity;
        return $this;
    }

    public function getHardness(): ?int
    {
        return $this->hardness;
    }

    public function setHardness(?int $hardness): self
    {
        if ($hardness !== null) {
            self::validateHardness($hardness);
        }
        $this->hardness = $hardness;
        return $this;
    }

    protected function setId(?int $id): void
    {
        $this->id = $id;
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
