<?php

declare(strict_types=1);

namespace App\Domain\Wood\Entity;

use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
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
    private string $name;

    #[ORM\Column(length: 300, nullable: true)]
    private ?string $latinName = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    private ?int $dryDensity = null;

    #[ORM\Column(type: 'integer', length: 8, nullable: true)]
    private ?int $hardness = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    protected function __construct()
    {
        $this->initializeTranslations();
    }

    public static function create(
        string $name,
        ?string $latin_name = null,
        ?int $dryDensity = null,
        ?int $hardness = null,
        bool $enabled = true,
    ): self {
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
        $this->name = $name;

        return $this;
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_DESCRIPTION, $locale ?? 'en');
    }

    public function setDescription(?string $description, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_DESCRIPTION, $description, $locale);
    }

    public function getPlaceOfOrigin(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_PLACE_OF_ORIGIN, $locale ?? 'en');
    }

    public function setPlaceOfOrigin(?string $placeOfOrigin, string $locale = 'en'): void
    {
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
        $this->latinName = $latinName;

        return $this;
    }

    public function getDryDensity(): ?int
    {
        return $this->dryDensity;
    }

    public function setDryDensity(?int $dryDensity): self
    {
        $this->dryDensity = $dryDensity;

        return $this;
    }

    public function getHardness(): ?int
    {
        return $this->hardness;
    }

    public function setHardness(?int $hardness): self
    {
        $this->hardness = $hardness;

        return $this;
    }

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }
}
