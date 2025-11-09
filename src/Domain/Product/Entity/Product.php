<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Exception\DuplicateProductColorException;
use App\Domain\Product\Exception\ProductColorNotFoundException;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use App\Infrastructure\Persistence\Doctrine\Repository\DoctrineProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DoctrineProductRepository::class)]
#[ORM\Table(name: 'product')]
#[ORM\UniqueConstraint(
    name: 'unique_product_type_country',
    columns: ['type', 'country_id']
)]
class Product implements TranslatableInterface
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
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: Type::class)]
    #[Assert\NotNull(message: 'not_null')]
    private Type $type;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull(message: 'not_null')]
    private Country $country;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'length_max')]
    private ?string $imageFilename = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'length_max')]
    private ?string $imageOriginalName = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

    #[Assert\File(
        maxSize: '5M',
        mimeTypes: [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
            'image/svg',
        ],
        mimeTypesMessage: 'image.invalid_format'
    )]
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
    #[Assert\Valid]
    private Collection $productColors;

    protected function __construct(Type $type, Country $country)
    {
        $this->type = $type;
        $this->country = $country;
        $this->productColors = new ArrayCollection();
        $this->initializeTranslations();
    }

    public static function create(Type $type, Country $country): self
    {
        $product = new self($type, $country);
        $product->enabled = true;

        return $product;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function setType(Type $type): void
    {
        $this->type = $type;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): void
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
        if ($this->hasColor($color)) {
            throw DuplicateProductColorException::forProduct($color->getCode());
        }

        $productColor = ProductColor::create($this, $color, $description);
        $this->productColors->add($productColor);

        return $this;
    }

    public function removeColor(Color $color): self
    {
        $productColor = $this->findProductColorByColor($color);
        if (!$productColor) {
            throw ProductColorNotFoundException::forProduct($color->getCode());
        }

        $this->productColors->removeElement($productColor);

        return $this;
    }

    public function updateColorDescription(Color $color, ?string $description = null): self
    {
        $productColor = $this->findProductColorByColor($color);
        if (!$productColor) {
            throw ProductColorNotFoundException::forProduct($color->getCode());
        }

        $productColor->setDescription($description);

        return $this;
    }

    public function hasColor(Color $color): bool
    {
        return $this->findProductColorByColor($color) !== null;
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

    /**
     * @param Collection<int, ProductColor> $productColors
     */
    public function setProductColors(Collection $productColors): void
    {
        $this->productColors = $productColors;
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

    protected function setId(?int $id): void
    {
        $this->id = $id;
    }

    private function findProductColorByColor(Color $color): ?ProductColor
    {
        foreach ($this->productColors as $productColor) {
            if ($productColor->getColor()->getCode() === $color->getCode()) {
                return $productColor;
            }
        }

        return null;
    }
}
