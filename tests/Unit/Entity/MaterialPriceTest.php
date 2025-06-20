<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use PHPUnit\Framework\TestCase;

class MaterialPriceTest extends TestCase
{
    public function testConstructor(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);
        $this->assertSame($material, $price->getMaterial());
        $this->assertNull($price->getId());
        $this->assertNull($price->getPrice());
        $this->assertNull($price->getThickness());
    }

    public function testSetAndGetThickness(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);
        $price->setThickness(25);
        $this->assertSame(25, $price->getThickness());
    }

    public function testSetAndGetPrice(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);
        $price->setPrice(150.50);

        $this->assertSame(150.50, $price->getPrice());
    }

    public function testSetAndGetMaterial(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);
        $_material = new Material();
        $price->setMaterial($_material);

        $this->assertSame($_material, $price->getMaterial());
    }

    public function testZeroThickness(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);
        $price->setThickness(0);

        $this->assertSame(0, $price->getThickness());
    }

    public function testNegativeThickness(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);
        $price->setThickness(-5);

        $this->assertSame(-5, $price->getThickness());
    }
}
