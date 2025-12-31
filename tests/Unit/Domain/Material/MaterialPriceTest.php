<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material;

use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\TestCase;

final class MaterialPriceTest extends TestCase
{
    private PlywoodMaterial $material;

    protected function setUp(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);
        $this->material = PlywoodMaterial::create($wood);
    }

    public function testCreateMaterialPrice(): void
    {
        $price = MaterialPrice::create($this->material, 18, '1500.00');

        $this->assertSame($this->material, $price->getMaterial());
        $this->assertSame(18, $price->getThickness());
        $this->assertSame('1500.00', $price->getPrice());
    }

    public function testCreateMaterialPriceWithDifferentThicknesses(): void
    {
        $price1 = MaterialPrice::create($this->material, 10, '1200.00');
        $price2 = MaterialPrice::create($this->material, 25, '2000.50');

        $this->assertSame(10, $price1->getThickness());
        $this->assertSame(25, $price2->getThickness());
    }

    public function testSetThickness(): void
    {
        $price = MaterialPrice::create($this->material, 18, '1500.00');

        $price->setThickness(20);

        $this->assertSame(20, $price->getThickness());
    }

    public function testSetPrice(): void
    {
        $price = MaterialPrice::create($this->material, 18, '1500.00');

        $price->setPrice('1800.50');

        $this->assertSame('1800.50', $price->getPrice());
    }

    public function testSetMaterial(): void
    {
        $wood2 = Wood::create('pine');
        $material2 = PlywoodMaterial::create($wood2);

        $price = MaterialPrice::create($this->material, 18, '1500.00');
        $price->setMaterial($material2);

        $this->assertSame($material2, $price->getMaterial());
    }

    public function testValidPriceFormats(): void
    {
        $price1 = MaterialPrice::create($this->material, 18, '1500');
        $this->assertSame('1500', $price1->getPrice());

        $price2 = MaterialPrice::create($this->material, 20, '1500.5');
        $this->assertSame('1500.5', $price2->getPrice());

        $price3 = MaterialPrice::create($this->material, 25, '1500.99');
        $this->assertSame('1500.99', $price3->getPrice());
    }
}
