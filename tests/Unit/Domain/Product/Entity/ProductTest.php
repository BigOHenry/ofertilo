<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Exception\DuplicateProductColorException;
use App\Domain\Product\Exception\ProductColorNotFoundException;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductTest extends TestCase
{
    private Type $type;
    private Country $country;

    protected function setUp(): void
    {
        $this->type = Type::FLAG;
        $this->country = new Country('Czech Republic', 'cz', 'cze');
    }

    public function testCreateEmptyProduct(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertNull($product->getId());
        $this->assertTrue($product->isEnabled());
        $this->assertInstanceOf(ArrayCollection::class, $product->getProductColors());
        $this->assertCount(0, $product->getProductColors());
        $this->assertNull($product->getImageFilename());
        $this->assertNull($product->getImageOriginalName());
        $this->assertNull($product->getImageFile());

        $this->assertSame($this->type, $product->getType());
    }

    public function testCreateProductWithTypeAndCountry(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertNull($product->getId());
        $this->assertSame($this->type, $product->getType());
        $this->assertSame($this->country, $product->getCountry());
        $this->assertTrue($product->isEnabled());
        $this->assertCount(0, $product->getProductColors());
    }

    public function testSetAndGetType(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setType(Type::FLAG);

        $this->assertSame(Type::FLAG, $product->getType());

        $product->setType(Type::COAT_OF_ARMS);
        $this->assertSame(Type::COAT_OF_ARMS, $product->getType());
    }

    public function testSetAndGetCountry(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setCountry($this->country);

        $this->assertSame($this->country, $product->getCountry());
    }

    public function testIsEnabledByDefault(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertTrue($product->isEnabled());
    }

    public function testSetEnabled(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setEnabled(false);

        $this->assertFalse($product->isEnabled());
    }

    public function testToggleEnabled(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertTrue($product->isEnabled());
        $product->setEnabled(false);
        $this->assertFalse($product->isEnabled());
        $product->setEnabled(true);
        $this->assertTrue($product->isEnabled());
    }

    public function testGetTranslatableFields(): void
    {
        $fields = Product::getTranslatableFields();

        $this->assertIsArray($fields);
        $this->assertContains('description', $fields);
        $this->assertCount(1, $fields);
    }

    public function testSetAndGetDescription(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setDescription('Product description', 'en');

        $this->assertSame('Product description', $product->getDescription('en'));
    }

    public function testDescriptionWithDefaultLocale(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setDescription('Default description');

        $this->assertSame('Default description', $product->getDescription());
        $this->assertSame('Default description', $product->getDescription('en'));
    }

    public function testMultipleTranslationsForDescription(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setDescription('English description', 'en');
        $product->setDescription('Czech description', 'cs');
        $product->setDescription('German description', 'de');

        $this->assertSame('English description', $product->getDescription('en'));
        $this->assertSame('Czech description', $product->getDescription('cs'));
        $this->assertSame('German description', $product->getDescription('de'));
    }

    public function testAddColor(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);

        $result = $product->addColor($color, 'Red variant');

        $this->assertSame($product, $result); // Test fluent interface
        $this->assertTrue($product->hasColor($color));
        $this->assertSame('Red variant', $product->getColorDescription($color));
        $this->assertCount(1, $product->getProductColors());
    }

    public function testAddColorThrowsExceptionWhenColorAlreadyExists(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);
        $product->addColor($color, 'Red variant');

        $this->expectException(DuplicateProductColorException::class);
        $this->expectExceptionMessage("Color '3025' is already assigned to product");

        $product->addColor($color, 'Another description');
    }

    public function testRemoveColor(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);
        $product->addColor($color, 'Red variant');

        $this->assertTrue($product->hasColor($color));

        $result = $product->removeColor($color);

        $this->assertSame($product, $result);
        $this->assertFalse($product->hasColor($color));
        $this->assertCount(0, $product->getProductColors());
    }

    public function testRemoveColorWhenColorNotExistsThrowsException(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);

        $this->expectException(ProductColorNotFoundException::class);
        $this->expectExceptionMessage("Color '3025' is not assigned to product");

        $product->removeColor($color);
    }

    public function testUpdateColorDescription(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);
        $product->addColor($color, 'Original description');

        $result = $product->updateColorDescription($color, 'Updated description');

        $this->assertSame($product, $result);
        $this->assertSame('Updated description', $product->getColorDescription($color));
    }

    public function testUpdateColorDescriptionThrowsExceptionWhenColorNotExists(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);

        $this->expectException(ProductColorNotFoundException::class);
        $this->expectExceptionMessage("Color '3025' is not assigned to product");

        $product->updateColorDescription($color, 'New description');
    }

    public function testHasColor(): void
    {
        $product = Product::create($this->type, $this->country);
        $color1 = Color::create(3025);
        $color2 = Color::create(3026);

        $product->addColor($color1, 'Red variant');

        $this->assertTrue($product->hasColor($color1));
        $this->assertFalse($product->hasColor($color2));
    }

    public function testGetColorDescription(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);
        $product->addColor($color, 'Red variant');

        $this->assertSame('Red variant', $product->getColorDescription($color));
    }

    public function testGetColorDescriptionReturnsNullWhenColorNotExists(): void
    {
        $product = Product::create($this->type, $this->country);
        $color = Color::create(3025);

        $this->assertNull($product->getColorDescription($color));
    }

    public function testGetProductColors(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertInstanceOf(ArrayCollection::class, $product->getProductColors());
        $this->assertCount(0, $product->getProductColors());
    }

    public function testSetProductColors(): void
    {
        $product = Product::create($this->type, $this->country);
        $productColors = new ArrayCollection();

        $product->setProductColors($productColors);

        $this->assertSame($productColors, $product->getProductColors());
    }

    public function testSetAndGetImageFilename(): void
    {
        $product = Product::create($this->type, $this->country);

        $result = $product->setImageFilename('image.jpg');

        $this->assertSame('image.jpg', $product->getImageFilename());
        $this->assertSame($product, $result);
    }

    public function testSetImageFilenameWithNull(): void
    {
        $product = Product::create($this->type, $this->country);

        $result = $product->setImageFilename(null);

        $this->assertNull($product->getImageFilename());
        $this->assertSame($product, $result);
    }

    public function testHasImage(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertFalse($product->hasImage());

        $product->setImageFilename('image.jpg');
        $this->assertTrue($product->hasImage());

        $product->setImageFilename(null);
        $this->assertFalse($product->hasImage());
    }

    public function testRemoveImage(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setImageFilename('image.jpg');
        $product->setImageOriginalName('original.jpg');

        $result = $product->removeImage();

        $this->assertSame($product, $result);
        $this->assertNull($product->getImageFilename());
        $this->assertNull($product->getImageOriginalName());
        $this->assertFalse($product->hasImage());
    }

    public function testGetEncodedFilename(): void
    {
        $product = Product::create($this->type, $this->country);
        $product->setImageFilename('test.jpg');

        $encoded = $product->getEncodedFilename();

        $this->assertSame(base64_encode('test.jpg'), $encoded);
    }

    public function testSetAndGetImageOriginalName(): void
    {
        $product = Product::create($this->type, $this->country);

        $result = $product->setImageOriginalName('original.jpg');

        $this->assertSame('original.jpg', $product->getImageOriginalName());
        $this->assertSame($product, $result);
    }

    public function testSetImageOriginalNameWithNull(): void
    {
        $product = Product::create($this->type, $this->country);

        $result = $product->setImageOriginalName(null);

        $this->assertNull($product->getImageOriginalName());
        $this->assertSame($product, $result);
    }

    public function testGetImageFile(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertNull($product->getImageFile());
    }

    /**
     * @throws Exception
     */
    public function testSetImageFile(): void
    {
        $product = Product::create($this->type, $this->country);
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')->willReturn('uploaded.jpg');

        $result = $product->setImageFile($uploadedFile);

        $this->assertSame($uploadedFile, $product->getImageFile());
        $this->assertSame('uploaded.jpg', $product->getImageOriginalName());
        $this->assertSame($product, $result);
    }

    public function testSetImageFileWithNull(): void
    {
        $product = Product::create($this->type, $this->country);

        $result = $product->setImageFile(null);

        $this->assertNull($product->getImageFile());
        $this->assertSame($product, $result);
    }

    public function testGetEntityFolder(): void
    {
        $product = Product::create($this->type, $this->country);

        $this->assertSame('products', $product->getEntityFolder());
    }

    public function testMultipleColorsManagement(): void
    {
        $product = Product::create($this->type, $this->country);
        $color1 = Color::create(3025);
        $color2 = Color::create(3026);
        $color3 = Color::create(3027);

        // Adding multiple colours
        $product->addColor($color1, 'Red');
        $product->addColor($color2, 'Blue');
        $product->addColor($color3, 'Green');

        $this->assertCount(3, $product->getProductColors());
        $this->assertTrue($product->hasColor($color1));
        $this->assertTrue($product->hasColor($color2));
        $this->assertTrue($product->hasColor($color3));

        // Removing one colour
        $product->removeColor($color2);

        $this->assertCount(2, $product->getProductColors());
        $this->assertTrue($product->hasColor($color1));
        $this->assertFalse($product->hasColor($color2));
        $this->assertTrue($product->hasColor($color3));

        // Update color description
        $product->updateColorDescription($color1, 'Bright Red');
        $this->assertSame('Bright Red', $product->getColorDescription($color1));
    }

    public function testCompleteProductConfiguration(): void
    {
        $product = Product::create(Type::COAT_OF_ARMS, $this->country);
        $color = Color::create(3025);

        $product->setEnabled(true);
        $product->setDescription('Test product', 'en');
        $product->setImageFilename('product.jpg');
        $product->setImageOriginalName('original_product.jpg');
        $product->addColor($color, 'Default color');

        // Verification of all properties
        $this->assertNull($product->getId());
        $this->assertSame(Type::COAT_OF_ARMS, $product->getType());
        $this->assertSame($this->country, $product->getCountry());
        $this->assertTrue($product->isEnabled());
        $this->assertSame('Test product', $product->getDescription('en'));
        $this->assertSame('product.jpg', $product->getImageFilename());
        $this->assertSame('original_product.jpg', $product->getImageOriginalName());
        $this->assertTrue($product->hasImage());
        $this->assertTrue($product->hasColor($color));
        $this->assertSame('Default color', $product->getColorDescription($color));
        $this->assertCount(1, $product->getProductColors());
    }
}
