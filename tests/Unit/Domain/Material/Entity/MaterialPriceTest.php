<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Exception\InvalidMaterialPriceException;
use App\Domain\Material\ValueObject\Type;
use PHPUnit\Framework\TestCase;

class MaterialPriceTest extends TestCase
{
    private Material $material;

    protected function setUp(): void
    {
        $this->material = Material::create(Type::VOLUME, 'test_material');
    }

    public function testCreateEmptyMaterialPrice(): void
    {
        $price = MaterialPrice::createEmpty($this->material);

        $this->assertSame($this->material, $price->getMaterial());
        $this->assertNull($price->getId());

        $this->expectException(\LogicException::class);
        $price->getPrice();
    }

    public function testCreateEmptyMaterialPriceThickness(): void
    {
        $price = MaterialPrice::createEmpty($this->material);

        $this->expectException(\LogicException::class);
        $price->getThickness();
    }

    public function testCreateMaterialPriceWithValidData(): void
    {
        $price = MaterialPrice::create($this->material, 10, 50.0);

        $this->assertSame($this->material, $price->getMaterial());
        $this->assertSame(10, $price->getThickness());
        $this->assertSame(50.0, $price->getPrice());
        $this->assertNull($price->getId());
    }

    public function testCreateWithInvalidThicknessThrowsException(): void
    {
        $this->expectException(InvalidMaterialPriceException::class);
        $this->expectExceptionMessage('Price thickness 0 is lower than minimum allowed thickness 1');

        MaterialPrice::create($this->material, 0, 50.0);
    }

    public function testCreateWithTooHighThicknessThrowsException(): void
    {
        $this->expectException(InvalidMaterialPriceException::class);
        $this->expectExceptionMessage('Price thickness 150 exceeds maximum allowed thickness 100');

        MaterialPrice::create($this->material, 150, 50.0);
    }

    public function testCreateWithInvalidPriceThrowsException(): void
    {
        $this->expectException(InvalidMaterialPriceException::class);
        $this->expectExceptionMessage('Price 0.5 is lower than minimum allowed price 1');

        MaterialPrice::create($this->material, 10, 0.5);
    }

    public function testCreateWithTooHighPriceThrowsException(): void
    {
        $this->expectException(InvalidMaterialPriceException::class);
        $this->expectExceptionMessage('Price 1000000 exceeds maximum allowed price 999999.99');

        MaterialPrice::create($this->material, 10, 1000000.0);
    }

    public function testSetValidThickness(): void
    {
        $price = MaterialPrice::createEmpty($this->material);
        $price->setThickness(25);

        $this->assertSame(25, $price->getThickness());
    }

    public function testSetValidPrice(): void
    {
        $price = MaterialPrice::createEmpty($this->material);
        $price->setPrice(150.50);

        $this->assertSame(150.50, $price->getPrice());
    }

    public function testSetMaterial(): void
    {
        $price = MaterialPrice::createEmpty($this->material);
        $newMaterial = Material::create(Type::VOLUME, 'new_material');

        $price->setMaterial($newMaterial);

        $this->assertSame($newMaterial, $price->getMaterial());
    }

    public function testValidThicknessBoundaries(): void
    {
        $price1 = MaterialPrice::create($this->material, 1, 50.0);
        $this->assertSame(1, $price1->getThickness());

        $price2 = MaterialPrice::create($this->material, 100, 50.0);
        $this->assertSame(100, $price2->getThickness());
    }

    public function testValidPriceBoundaries(): void
    {
        $price1 = MaterialPrice::create($this->material, 10, 1.0);
        $this->assertSame(1.0, $price1->getPrice());

        $price2 = MaterialPrice::create($this->material, 10, 999999.99);
        $this->assertSame(999999.99, $price2->getPrice());
    }

    public function testFloatPriceHandling(): void
    {
        $price = MaterialPrice::createEmpty($this->material);

        $price->setPrice(99.99);
        $this->assertSame(99.99, $price->getPrice());

        $price->setPrice(1.01);
        $this->assertSame(1.01, $price->getPrice());
    }

    public function testMaterialRelationship(): void
    {
        $price = MaterialPrice::create($this->material, 10, 50.0);

        $this->assertSame($this->material, $price->getMaterial());

        $newMaterial = Material::create(Type::VOLUME, 'new_material');
        $price->setMaterial($newMaterial);

        $this->assertSame($newMaterial, $price->getMaterial());
    }

    public function testBusinessMethodsWithValidation(): void
    {
        $price = MaterialPrice::createEmpty($this->material);

        $price->setThickness(25);
        $price->setPrice(150.0);

        $this->assertSame(25, $price->getThickness());
        $this->assertSame(150.0, $price->getPrice());
    }

    public function testBusinessMethodValidationThrowsException(): void
    {
        $price = MaterialPrice::createEmpty($this->material);

        $this->expectException(InvalidMaterialPriceException::class);
        $price->setThickness(0);
    }
}
