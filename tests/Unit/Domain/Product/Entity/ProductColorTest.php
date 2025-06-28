<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Product\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use App\Domain\Product\Exception\InvalidProductColorException;
use App\Domain\Product\ValueObject\Type;
use App\Domain\Shared\Entity\Country;
use PHPUnit\Framework\TestCase;

class ProductColorTest extends TestCase
{
    private Product $product;
    private Color $color;

    protected function setUp(): void
    {
        $type = Type::FLAG;
        $country = new Country('Czech Republic', 'cz', 'cze');
        $this->product = Product::create($type, $country);
        $this->color = Color::create(3025);
    }

    public function testCreateEmptyProductColor(): void
    {
        $productColor = ProductColor::createEmpty($this->product);

        $this->assertNull($productColor->getId());
        $this->assertSame($this->product, $productColor->getProduct());

        $this->expectException(\LogicException::class);
        $productColor->getColor();
    }

    public function testCreateEmptyProductColorDescription(): void
    {
        $productColor = ProductColor::createEmpty($this->product);

        $this->assertNull($productColor->getDescription());
    }

    public function testCreateProductColorWithValidData(): void
    {
        $productColor = ProductColor::create($this->product, $this->color, 'Red variant');

        $this->assertNull($productColor->getId());
        $this->assertSame($this->product, $productColor->getProduct());
        $this->assertSame($this->color, $productColor->getColor());
        $this->assertSame('Red variant', $productColor->getDescription());
    }

    public function testCreateProductColorWithoutDescription(): void
    {
        $productColor = ProductColor::create($this->product, $this->color);

        $this->assertSame($this->product, $productColor->getProduct());
        $this->assertSame($this->color, $productColor->getColor());
        $this->assertNull($productColor->getDescription());
    }

    public function testCreateWithTooLongDescriptionThrowsException(): void
    {
        $longDescription = str_repeat('A', 501);

        $this->expectException(InvalidProductColorException::class);
        $this->expectExceptionMessage('ProductColor description must be maximum 500 characters');

        ProductColor::create($this->product, $this->color, $longDescription);
    }

    public function testGetProduct(): void
    {
        $productColor = ProductColor::createEmpty($this->product);

        $this->assertSame($this->product, $productColor->getProduct());
    }

    public function testSetAndGetColor(): void
    {
        $productColor = ProductColor::createEmpty($this->product);
        $productColor->setColor($this->color);

        $this->assertSame($this->color, $productColor->getColor());
    }

    public function testSetAndGetDescription(): void
    {
        $productColor = ProductColor::createEmpty($this->product);

        $result = $productColor->setDescription('Shield red color');

        $this->assertSame('Shield red color', $productColor->getDescription());
        $this->assertSame($productColor, $result);
    }

    public function testSetDescriptionWithNull(): void
    {
        $productColor = ProductColor::createEmpty($this->product);

        $result = $productColor->setDescription(null);

        $this->assertNull($productColor->getDescription());
        $this->assertSame($productColor, $result);
    }

    public function testSetDescriptionTooLongThrowsException(): void
    {
        $productColor = ProductColor::createEmpty($this->product);
        $longDescription = str_repeat('A', 501);

        $this->expectException(InvalidProductColorException::class);
        $this->expectExceptionMessage('ProductColor description must be maximum 500 characters');

        $productColor->setDescription($longDescription);
    }

    public function testCompleteProductColorConfiguration(): void
    {
        $productColor = ProductColor::create($this->product, $this->color, 'Lion body');

        $this->assertNull($productColor->getId()); // ID bude nastaveno při persist
        $this->assertSame($this->product, $productColor->getProduct());
        $this->assertSame($this->color, $productColor->getColor());
        $this->assertSame('Lion body', $productColor->getDescription());
    }

    public function testFluentInterface(): void
    {
        $productColor = ProductColor::createEmpty($this->product);

        $result = $productColor
            ->setDescription('Blue light')
            ->setDescription('Blue dark')
        ;

        $this->assertSame($productColor, $result);
        $this->assertSame('Blue dark', $productColor->getDescription());
    }

    public function testValidDescriptionBoundaries(): void
    {
        $productColor = ProductColor::createEmpty($this->product);

        $productColor->setDescription('');
        $this->assertSame('', $productColor->getDescription());

        $maxDescription = str_repeat('A', 500);
        $productColor->setDescription($maxDescription);
        $this->assertSame($maxDescription, $productColor->getDescription());
    }

    public function testColorRelationship(): void
    {
        $productColor = ProductColor::create($this->product, $this->color, 'Test color');

        $this->assertSame($this->color, $productColor->getColor());

        $newColor = Color::create(3026);
        $productColor->setColor($newColor);

        $this->assertSame($newColor, $productColor->getColor());
    }

    public function testProductRelationship(): void
    {
        $productColor = ProductColor::create($this->product, $this->color, 'Test color');

        $this->assertSame($this->product, $productColor->getProduct());

        $this->assertFalse(method_exists($productColor, 'setProduct'));
    }
}
