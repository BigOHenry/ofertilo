<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Color\Entity\Color;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\ProductColor;
use PHPUnit\Framework\TestCase;

class ProductColorTest extends TestCase
{
    public function testConstructorWithProduct(): void
    {
        $product = new Product();
        $productColor = new ProductColor($product);

        $this->assertNull($productColor->getId());
        $this->assertSame($product, $productColor->getProduct());
        $this->assertNull($productColor->getColor());
        $this->assertNull($productColor->getDescription());
    }

    public function testSetAndGetId(): void
    {
        $product = new Product();
        $productColor = new ProductColor($product);
        $productColor->setId(123);

        $this->assertSame(123, $productColor->getId());
    }

    public function testSetIdWithNull(): void
    {
        $product = new Product();
        $productColor = new ProductColor($product);
        $productColor->setId(null);

        $this->assertNull($productColor->getId());
    }

    public function testGetProduct(): void
    {
        $product = new Product();
        $productColor = new ProductColor($product);

        $this->assertSame($product, $productColor->getProduct());
    }

    public function testSetAndGetColor(): void
    {
        $product = new Product();
        $color = new Color();
        $productColor = new ProductColor($product);

        $productColor->setColor($color);

        $this->assertSame($color, $productColor->getColor());
    }

    public function testSetAndGetDescription(): void
    {
        $product = new Product();
        $productColor = new ProductColor($product);

        $result = $productColor->setDescription('Shield red color');

        $this->assertSame('Shield red color', $productColor->getDescription());
        $this->assertSame($productColor, $result); // Test fluent interface
    }

    public function testSetDescriptionWithNull(): void
    {
        $product = new Product();
        $productColor = new ProductColor($product);

        $result = $productColor->setDescription(null);

        $this->assertNull($productColor->getDescription());
        $this->assertSame($productColor, $result); // Test fluent interface
    }

    public function testCompleteProductColorConfiguration(): void
    {
        $product = new Product();
        $color = new Color();
        $productColor = new ProductColor($product);

        $productColor->setId(1);
        $productColor->setColor($color);
        $productColor->setDescription('Lion body');

        $this->assertSame(1, $productColor->getId());
        $this->assertSame($product, $productColor->getProduct());
        $this->assertSame($color, $productColor->getColor());
        $this->assertSame('Lion body', $productColor->getDescription());
    }

    public function testFluentInterface(): void
    {
        $product = new Product();
        $productColor = new ProductColor($product);

        $result = $productColor
            ->setDescription('Blue light')
            ->setDescription('Blue dark')
        ;

        $this->assertSame($productColor, $result);
        $this->assertSame('Blue dark', $productColor->getDescription());
    }
}
