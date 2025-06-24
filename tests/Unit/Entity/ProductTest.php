<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductTest extends TestCase
{
    public function testConstructor(): void
    {
        $product = new Product();

        $this->assertNull($product->getId());
        $this->assertTrue($product->isEnabled()); // default true
        $this->assertInstanceOf(ArrayCollection::class, $product->getProductColors());
        $this->assertCount(0, $product->getProductColors());
        $this->assertNull($product->getImageFilename());
        $this->assertNull($product->getImageOriginalName());
        $this->assertNull($product->getImageFile());
    }

    public function testSetAndGetId(): void
    {
        $product = new Product();
        $product->setId(123);

        $this->assertSame(123, $product->getId());
    }

    public function testSetIdWithNull(): void
    {
        $product = new Product();
        $product->setId(null);

        $this->assertNull($product->getId());
    }

    public function testSetAndGetType(): void
    {
        $product = new Product();
        $product->setType(Type::FLAG);
        $this->assertSame(Type::FLAG, $product->getType());

        $product->setType(Type::COAT_OF_ARMS);
        $this->assertSame(Type::COAT_OF_ARMS, $product->getType());
    }

    public function testSetAndGetCountry(): void
    {
        $product = new Product();
        $country = new Country('Czech Republic', 'cz', 'cze');

        $product->setCountry($country);

        $this->assertSame($country, $product->getCountry());
    }

    public function testIsEnabledByDefault(): void
    {
        $product = new Product();

        $this->assertTrue($product->isEnabled());
    }

    public function testSetEnabled(): void
    {
        $product = new Product();
        $product->setEnabled(false);

        $this->assertFalse($product->isEnabled());
    }

    public function testToggleEnabled(): void
    {
        $product = new Product();

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
        $product = new Product();
        $product->setDescription('Product description', 'en');

        $this->assertSame('Product description', $product->getDescription('en'));
    }

    public function testDescriptionWithDefaultLocale(): void
    {
        $product = new Product();
        $product->setDescription('Default description');

        $this->assertSame('Default description', $product->getDescription());
        $this->assertSame('Default description', $product->getDescription('en'));
    }

    public function testMultipleTranslationsForDescription(): void
    {
        $product = new Product();
        $product->setDescription('English description', 'en');
        $product->setDescription('Czech description', 'cs');
        $product->setDescription('German description', 'de');

        $this->assertSame('English description', $product->getDescription('en'));
        $this->assertSame('Czech description', $product->getDescription('cs'));
        $this->assertSame('German description', $product->getDescription('de'));
    }

    public function testAddColor(): void
    {
        $product = new Product();
        $color = new Color();

        $result = $product->addColor($color, 'Red variant');

        $this->assertSame($product, $result); // Test fluent interface
        $this->assertTrue($product->hasColor($color));
        $this->assertSame('Red variant', $product->getColorDescription($color));
        $this->assertCount(1, $product->getProductColors());
    }

    public function testAddColorThrowsExceptionWhenColorAlreadyExists(): void
    {
        $product = new Product();
        $color = new Color();

        $product->addColor($color, 'Red variant');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Color is already assigned to this Product');

        $product->addColor($color, 'Another description');
    }

    public function testRemoveColor(): void
    {
        $product = new Product();
        $color = new Color();

        $product->addColor($color, 'Red variant');
        $this->assertTrue($product->hasColor($color));

        $result = $product->removeColor($color);

        $this->assertSame($product, $result);
        $this->assertFalse($product->hasColor($color));
        $this->assertCount(0, $product->getProductColors());
    }

    public function testRemoveColorWhenColorNotExists(): void
    {
        $product = new Product();
        $color = new Color();

        $result = $product->removeColor($color);

        $this->assertSame($product, $result);
        $this->assertFalse($product->hasColor($color));
    }

    public function testUpdateColorDescription(): void
    {
        $product = new Product();
        $color = new Color();

        $product->addColor($color, 'Original description');

        $result = $product->updateColorDescription($color, 'Updated description');

        $this->assertSame($product, $result);
        $this->assertSame('Updated description', $product->getColorDescription($color));
    }

    public function testUpdateColorDescriptionThrowsExceptionWhenColorNotExists(): void
    {
        $product = new Product();
        $color = new Color();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Color is not assigned to this product');

        $product->updateColorDescription($color, 'New description');
    }

    public function testHasColor(): void
    {
        $product = new Product();
        $color1 = new Color();
        $color2 = new Color();

        $product->addColor($color1, 'Red variant');

        $this->assertTrue($product->hasColor($color1));
        $this->assertFalse($product->hasColor($color2));
    }

    public function testGetColorDescription(): void
    {
        $product = new Product();
        $color = new Color();

        $product->addColor($color, 'Red variant');

        $this->assertSame('Red variant', $product->getColorDescription($color));
    }

    public function testGetColorDescriptionReturnsNullWhenColorNotExists(): void
    {
        $product = new Product();
        $color = new Color();

        $this->assertNull($product->getColorDescription($color));
    }

    public function testGetProductColors(): void
    {
        $product = new Product();

        $this->assertInstanceOf(ArrayCollection::class, $product->getProductColors());
        $this->assertCount(0, $product->getProductColors());
    }

    public function testSetProductColors(): void
    {
        $product = new Product();
        $productColors = new ArrayCollection();

        $product->setProductColors($productColors);

        $this->assertSame($productColors, $product->getProductColors());
    }

    public function testSetAndGetImageFilename(): void
    {
        $product = new Product();

        $result = $product->setImageFilename('image.jpg');

        $this->assertSame('image.jpg', $product->getImageFilename());
        $this->assertSame($product, $result); // Test fluent interface
    }

    public function testSetImageFilenameWithNull(): void
    {
        $product = new Product();

        $result = $product->setImageFilename(null);

        $this->assertNull($product->getImageFilename());
        $this->assertSame($product, $result);
    }

    public function testHasImage(): void
    {
        $product = new Product();

        $this->assertFalse($product->hasImage());

        $product->setImageFilename('image.jpg');
        $this->assertTrue($product->hasImage());

        $product->setImageFilename(null);
        $this->assertFalse($product->hasImage());
    }

    public function testRemoveImage(): void
    {
        $product = new Product();
        $product->setImageFilename('image.jpg');
        $product->setImageOriginalName('original.jpg');

        $result = $product->removeImage();

        $this->assertSame($product, $result); // Test fluent interface
        $this->assertNull($product->getImageFilename());
        $this->assertNull($product->getImageOriginalName());
        $this->assertFalse($product->hasImage());
    }

    public function testGetEncodedFilename(): void
    {
        $product = new Product();
        $product->setImageFilename('test.jpg');

        $encoded = $product->getEncodedFilename();

        $this->assertSame(base64_encode('test.jpg'), $encoded);
    }

    public function testSetAndGetImageOriginalName(): void
    {
        $product = new Product();

        $result = $product->setImageOriginalName('original.jpg');

        $this->assertSame('original.jpg', $product->getImageOriginalName());
        $this->assertSame($product, $result); // Test fluent interface
    }

    public function testSetImageOriginalNameWithNull(): void
    {
        $product = new Product();

        $result = $product->setImageOriginalName(null);

        $this->assertNull($product->getImageOriginalName());
        $this->assertSame($product, $result);
    }

    public function testGetImageFile(): void
    {
        $product = new Product();

        $this->assertNull($product->getImageFile());
    }

    /**
     * @throws Exception
     */
    public function testSetImageFile(): void
    {
        $product = new Product();
        $uploadedFile = $this->createMock(UploadedFile::class);
        $uploadedFile->method('getClientOriginalName')->willReturn('uploaded.jpg');

        $result = $product->setImageFile($uploadedFile);

        $this->assertSame($uploadedFile, $product->getImageFile());
        $this->assertSame('uploaded.jpg', $product->getImageOriginalName());
        $this->assertSame($product, $result);
    }

    public function testSetImageFileWithNull(): void
    {
        $product = new Product();

        $result = $product->setImageFile(null);

        $this->assertNull($product->getImageFile());
        $this->assertSame($product, $result);
    }

    public function testGetEntityFolder(): void
    {
        $product = new Product();

        $this->assertSame('products', $product->getEntityFolder());
    }

    public function testMultipleColorsManagement(): void
    {
        $product = new Product();
        $color1 = new Color();
        $color2 = new Color();
        $color3 = new Color();

        // Adding more colours
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
        $product = new Product();
        $type = Type::COAT_OF_ARMS;
        $country = new Country('Czech Republic', 'cz', 'cze');
        $color = new Color();

        $product->setId(1);
        $product->setType($type);
        $product->setCountry($country);
        $product->setEnabled(true);
        $product->setDescription('Test product', 'en');
        $product->setImageFilename('product.jpg');
        $product->setImageOriginalName('original_product.jpg');
        $product->addColor($color, 'Default color');

        // Verification of all properties
        $this->assertSame(1, $product->getId());
        $this->assertSame($type, $product->getType());
        $this->assertSame($country, $product->getCountry());
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
