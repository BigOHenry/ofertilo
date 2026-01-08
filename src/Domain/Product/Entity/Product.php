<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Exception\ProductColorAlreadyExistsException;
use App\Domain\Product\Exception\ProductColorNotFoundException;
use App\Domain\Product\Exception\ProductVariantNotFoundException;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use App\Domain\Shared\File\Entity\File;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string', enumType: ProductType::class)]
#[ORM\DiscriminatorMap(ProductType::DISCRIMINATOR_MAP)]
#[ORM\UniqueConstraint(
    name: 'unique_product_type_country',
    columns: ['type', 'country_id']
)]
abstract class Product implements TranslatableInterface
{
    use TranslatableTrait;

    public const string ENTITY_FILES_FOLDER_NAME = 'products';
    public const string TRANSLATION_FIELD_NAME = 'name';
    public const string TRANSLATION_FIELD_SHORT_DESCRIPTION = 'short_description';
    public const string TRANSLATION_FIELD_DESCRIPTION = 'description';

    private const array TRANSLATION_FIELDS = [
        self::TRANSLATION_FIELD_NAME,
        self::TRANSLATION_FIELD_SHORT_DESCRIPTION,
        self::TRANSLATION_FIELD_DESCRIPTION,
    ];

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: true)]
    private ?Country $country;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private string $code;

    #[ORM\Column(type: 'string', length: 80, nullable: true)]
    private string $npn;

    #[ORM\OneToOne(targetEntity: File::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'image_file_id', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?File $imageFile = null;

    /**
     * @var Collection<string, ProductColor>
     */
    #[ORM\OneToMany(
        targetEntity: ProductColor::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $productColors;

    /**
     * @var Collection<string, ProductVariant>
     */
    #[ORM\OneToMany(
        targetEntity: ProductVariant::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $productVariants;

    protected function __construct(?Country $country)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->country = $country;
        $this->productColors = new ArrayCollection();
        $this->productVariants = new ArrayCollection();
        $this->initializeTranslations();
    }

    abstract public function getType(): ProductType;

    public function getId(): string
    {
        return $this->id;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setCode(string $code): void
    {
        $this->code = mb_strtoupper($code);
        $this->npn = str_replace('-', '', $this->code);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getNpn(): string
    {
        return $this->npn;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return string[]
     */
    public static function getTranslatableFields(): array
    {
        return self::TRANSLATION_FIELDS;
    }

    public function getName(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_NAME, $locale ?? 'en');
    }

    public function setName(?string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_NAME, $value, $locale);
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_DESCRIPTION, $locale ?? 'en');
    }

    public function setDescription(?string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_DESCRIPTION, $value, $locale);
    }

    public function getShortDescription(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_SHORT_DESCRIPTION, $locale ?? 'en');
    }

    public function setShortDescription(?string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_SHORT_DESCRIPTION, $value, $locale);
    }

    public function addColor(Color $color, ?string $description = null): self
    {
        if ($this->findProductColorByColor($color)) {
            throw ProductColorAlreadyExistsException::withCode($color->getCode());
        }

        $productColor = ProductColor::create($this, $color, $description);
        $this->productColors->add($productColor);

        return $this;
    }

    public function removeProductColor(ProductColor $productColor): self
    {
        $this->productColors->removeElement($productColor);

        return $this;
    }

    public function updateColor(ProductColor $productColor, Color $color, ?string $description = null): self
    {
        $foundProductColor = $this->findProductColorByColor($color);

        if ($productColor !== $foundProductColor) {
            throw ProductColorAlreadyExistsException::withCode($color->getCode());
        }

        $productColor->setDescription($description);
        $productColor->setColor($color);

        return $this;
    }

    public function getColorDescription(Color $color): ?string
    {
        return $this->findProductColorByColor($color)?->getDescription();
    }

    /**
     * @return Collection<string, ProductColor>
     */
    public function getProductColors(): Collection
    {
        return $this->productColors;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile): self
    {
        $this->imageFile = $imageFile;

        return $this;
    }

    public function hasImage(): bool
    {
        return $this->imageFile !== null;
    }

    public function removeImage(): self
    {
        $this->imageFile = null;

        return $this;
    }

    public function getEntityFolder(): string
    {
        return self::ENTITY_FILES_FOLDER_NAME;
    }

    public static function getProductClassByType(ProductType $type): string
    {
        return match ($type) {
            ProductType::FLAG => FlagProduct::class,
            ProductType::RELIEF_3D => Relief3dProduct::class,
            ProductType::LAYERED_2D => Layered2dProduct::class,
        };
    }

    public function findProductColorByColor(Color $color): ?ProductColor
    {
        return $this->productColors->filter(
            fn (ProductColor $f) => $f->getColor() === $color
        )->first() ?: null;
    }

    public function findProductColorById(string $id): ?ProductColor
    {
        return $this->productColors->filter(
            fn (ProductColor $f) => $f->getId() === $id
        )->first() ?: null;
    }

    /**
     * @throws ProductColorNotFoundException
     */
    public function getProductColorById(string $id): ProductColor
    {
        return $this->findProductColorById($id) ?? throw ProductColorNotFoundException::withId($id);
    }

    /**
     * @throws ProductColorNotFoundException
     */
    public function getProductVariantById(string $id): ProductVariant
    {
        return $this->findProductVariantById($id) ?? throw ProductVariantNotFoundException::withId($id);
    }

    public function addProductVariant(int $length, int $width, ?int $thickness = null): ProductVariant
    {
        $existingSize = $this->findProductVariantByDimensions($length, $width, $thickness);

        if ($existingSize !== null) {
            return $existingSize;
        }

        $productVariant = ProductVariant::create($this, $length, $width, $thickness);
        $this->productVariants->add($productVariant);

        return $productVariant;
    }

    public function removeProductVariant(ProductVariant $productVariant): self
    {
        $this->productVariants->removeElement($productVariant);

        return $this;
    }

    /**
     * @return Collection<string, ProductVariant>
     */
    public function getProductVariants(): Collection
    {
        return $this->productVariants;
    }

    public function findProductVariantByDimensions(int $height, int $width, ?int $thickness = null): ?ProductVariant
    {
        return $this->productVariants->filter(
            fn (ProductVariant $ps) => $ps->getHeight() === $height
                && $ps->getWidth() === $width
                && $ps->getThickness() === $thickness
        )->first() ?: null;
    }

    public function findProductVariantById(string $id): ?ProductVariant
    {
        return $this->productVariants->filter(
            fn (ProductVariant $ps) => $ps->getId() === $id
        )->first() ?: null;
    }
}
