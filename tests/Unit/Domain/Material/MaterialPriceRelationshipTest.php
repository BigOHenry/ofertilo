<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material;

use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\TestCase;

final class MaterialPriceRelationshipTest extends TestCase
{
    private PlywoodMaterial $material;

    protected function setUp(): void
    {
        $wood = Wood::create('oak', 'Quercus', 750, 6000);
        $this->material = PlywoodMaterial::create($wood);
    }

    public function testAddPriceToMaterial(): void
    {
        $this->material->addPrice(18, '1500.00');

        $prices = $this->material->getPrices();
        $this->assertCount(1, $prices);

        $firstPrice = $prices->first();
        $this->assertInstanceOf(MaterialPrice::class, $firstPrice);
        $this->assertSame(18, $firstPrice->getThickness());
        $this->assertSame('1500.00', $firstPrice->getPrice());
    }

    public function testAddMultiplePricesToMaterial(): void
    {
        $this->material->addPrice(10, '1200.00');
        $this->material->addPrice(18, '1500.00');
        $this->material->addPrice(25, '2000.00');

        $this->assertCount(3, $this->material->getPrices());
    }

    public function testAddPriceThrowsExceptionForDuplicateThickness(): void
    {
        $this->material->addPrice(18, '1500.00');

        $this->expectException(MaterialPriceAlreadyExistsException::class);

        $this->material->addPrice(18, '1600.00');
    }

    public function testRemovePriceFromMaterial(): void
    {
        $this->material->addPrice(18, '1500.00');
        $this->material->addPrice(20, '1700.00');

        $prices = $this->material->getPrices();
        $priceToRemove = $prices->first();

        $this->assertInstanceOf(MaterialPrice::class, $priceToRemove);
        $this->material->removePrice($priceToRemove);

        $this->assertCount(1, $this->material->getPrices());
    }

    public function testRemovePriceThrowsExceptionWhenNotFound(): void
    {
        $wood2 = Wood::create('pine');
        $otherMaterial = PlywoodMaterial::create($wood2);
        $otherMaterial->addPrice(18, '1500.00');

        $priceFromOtherMaterial = $otherMaterial->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $priceFromOtherMaterial);
        $this->expectException(MaterialPriceNotFoundException::class);

        $this->material->removePrice($priceFromOtherMaterial);
    }

    public function testMaterialPriceHasCorrectMaterialReference(): void
    {
        $this->material->addPrice(18, '1500.00');

        $price = $this->material->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $price);
        $this->assertSame($this->material, $price->getMaterial());
    }

    public function testGetPricesReturnsEmptyCollectionInitially(): void
    {
        $this->assertCount(0, $this->material->getPrices());
    }

    public function testPriceCanBeFoundByThickness(): void
    {
        $this->material->addPrice(18, '1500.00');
        $this->material->addPrice(20, '1700.00');

        $foundPrice = $this->material->findPriceByThickness(18);

        $this->assertNotNull($foundPrice);
        $this->assertInstanceOf(MaterialPrice::class, $foundPrice);
        $this->assertSame(18, $foundPrice->getThickness());
        $this->assertSame('1500.00', $foundPrice->getPrice());
    }

    public function testFindPriceByThicknessReturnsNullWhenNotFound(): void
    {
        $this->material->addPrice(18, '1500.00');

        $foundPrice = $this->material->findPriceByThickness(25);

        $this->assertNull($foundPrice);
    }

    public function testPriceCanBeFoundById(): void
    {
        $this->material->addPrice(18, '1500.00');

        $price = $this->material->getPrices()->first();
        $this->assertInstanceOf(MaterialPrice::class, $price);

        $priceId = $price->getId();
        $foundPrice = $this->material->findPriceById($priceId);

        $this->assertSame($price, $foundPrice);
    }

    public function testGetPriceByIdThrowsExceptionWhenNotFound(): void
    {
        $this->expectException(MaterialPriceNotFoundException::class);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';
        $this->material->getPriceById($nonExistentId);
    }

    public function testMultiplePricesCanBeAddedAndRetrieved(): void
    {
        $thicknesses = [10, 12, 15, 18, 20, 25, 30];

        foreach ($thicknesses as $thickness) {
            $price = (string) ($thickness * 100);
            $this->material->addPrice($thickness, $price);
        }

        $prices = $this->material->getPrices();
        $this->assertCount(\count($thicknesses), $prices);

        foreach ($prices as $price) {
            $this->assertInstanceOf(MaterialPrice::class, $price);
            $this->assertContains($price->getThickness(), $thicknesses);
        }
    }

    public function testPriceCollectionIsIterable(): void
    {
        $this->material->addPrice(18, '1500.00');
        $this->material->addPrice(20, '1700.00');

        $count = 0;
        foreach ($this->material->getPrices() as $price) {
            $this->assertInstanceOf(MaterialPrice::class, $price);
            ++$count;
        }

        $this->assertSame(2, $count);
    }
}
