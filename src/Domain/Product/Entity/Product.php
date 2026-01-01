<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Exception\ProductColorAlreadyExistsException;
use App\Domain\Product\Exception\ProductColorNotFoundException;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
    public const string TRANSLATION_FIELD_DESCRIPTION = 'description';

    private const array TRANSLATION_FIELDS = [
        self::TRANSLATION_FIELD_DESCRIPTION,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: true)]
    private ?Country $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageFilename = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $imageOriginalName = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    private ?UploadedFile $imageFile = null;

    /**
     * @var Collection<int, ProductColor>
     */
    #[ORM\OneToMany(
        targetEntity: ProductColor::class,
        mappedBy: 'product',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    private Collection $productColors;

    protected function __construct(?Country $country)
    {
        $this->id = null;
        $this->country = $country;
        $this->productColors = new ArrayCollection();
        $this->initializeTranslations();
    }

    abstract public function getType(): ProductType;

    public function getId(): ?int
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

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationValue(self::TRANSLATION_FIELD_DESCRIPTION, $locale ?? 'en');
    }

    public function setDescription(?string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation(self::TRANSLATION_FIELD_DESCRIPTION, $value, $locale);
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
     * @return Collection<int, ProductColor>
     */
    public function getProductColors(): Collection
    {
        return $this->productColors;
    }

    public function getImageFilename(): ?string
    {
        return $this->imageFilename;
    }

    public function setImageFilename(?string $imageFilename): self
    {
        $this->imageFilename = $imageFilename;

        return $this;
    }

    public function hasImage(): bool
    {
        return $this->imageFilename !== null;
    }

    public function removeImage(): self
    {
        $this->imageFilename = null;
        $this->imageOriginalName = null;

        return $this;
    }

    public function getEncodedFilename(): ?string
    {
        if (empty($this->imageFilename)) {
            return null;
        }

        return base64_encode($this->imageFilename);
    }

    public function getImageOriginalName(): ?string
    {
        return $this->imageOriginalName;
    }

    public function setImageOriginalName(?string $imageOriginalName): self
    {
        $this->imageOriginalName = $imageOriginalName;

        return $this;
    }

    public function getImageFile(): ?UploadedFile
    {
        return $this->imageFile;
    }

    public function setImageFile(?UploadedFile $imageFile): self
    {
        $this->imageFile = $imageFile;

        if ($imageFile) {
            $this->setImageOriginalName($imageFile->getClientOriginalName());
        }

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

    public function findProductColorById(int $id): ?ProductColor
    {
        return $this->productColors->filter(
            fn (ProductColor $f) => $f->getId() === $id
        )->first() ?: null;
    }

    /**
     * @throws ProductColorNotFoundException
     */
    public function getProductColorById(int $id): ProductColor
    {
        return $this->findProductColorById($id) ?? throw ProductColorNotFoundException::withId($id);
    }
}
