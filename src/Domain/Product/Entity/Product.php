<?php

declare(strict_types=1);

namespace App\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use App\Domain\Translation\Interface\TranslatableInterface;
use App\Domain\Translation\Trait\TranslatableTrait;
use App\Infrastructure\Persistence\Doctrine\DoctrineProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: DoctrineProductRepository::class)]
#[ORM\Table(name: 'product')]
#[ORM\UniqueConstraint(
    name: 'unique_product_type_country',
    columns: ['type', 'country_id']
)]
class Product implements TranslatableInterface
{
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', enumType: Type::class)]
    private Type $type;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: false)]
    private Country $country;

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

    protected function __construct()
    {
        $this->productColors = new ArrayCollection();
        $this->initTranslations();
    }

    /**
     * @param Type    $type
     * @param Country $country
     * @return self
     */
    public static function create(Type $type, Country $country): self
    {
        $product = new self();
        $product->type = $type;
        $product->country = $country;
        $product->enabled = true;
        return $product;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
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
        return ['description'];
    }

    public function getDescription(?string $locale = null): ?string
    {
        return $this->getTranslationFromMemory('description', $locale ?? 'en');
    }

    public function setDescription(string $value, string $locale = 'en'): void
    {
        $this->addOrUpdateTranslation('description', $value, $locale);
    }

    public function addColor(Color $color, string $description): self
    {
        if ($this->hasColor($color)) {
            throw new \InvalidArgumentException('Color is already assigned to this Product');
        }

        $productColor = new ProductColor($this);
        $this->productColors->add($productColor);

        return $this;
    }

    public function removeColor(Color $color): self
    {
        $productColor = $this->findProductColorByColor($color);
        if ($productColor) {
            $this->productColors->removeElement($productColor);
        }

        return $this;
    }

    public function updateColorDescription(Color $color, string $description): self
    {
        $productColor = $this->findProductColorByColor($color);
        if (!$productColor) {
            throw new \InvalidArgumentException('Color is not assigned to this product');
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

    public function getEncodedFilename(): string
    {
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
        return 'products';
    }

    private function findProductColorByColor(Color $color): ?ProductColor
    {
        foreach ($this->productColors as $productColor) {
            if ($productColor->getColor()->getId() === $color->getId()) {
                return $productColor;
            }
        }

        return null;
    }
}
