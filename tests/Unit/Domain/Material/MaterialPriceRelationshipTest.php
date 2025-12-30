<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material;

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
        $this->assertSame(18, $prices[0]->getThickness());
        $this->assertSame('1500.00', $prices[0]->getPrice());
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
        $priceToRemove = $prices[0];

        $this->material->removePrice($priceToRemove);

        $this->assertCount(1, $this->material->getPrices());
    }

    public function testRemovePriceThrowsExceptionWhenNotFound(): void
    {
        $wood2 = Wood::create('pine');
        $otherMaterial = PlywoodMaterial::create($wood2);
        $otherMaterial->addPrice(18, '1500.00');

        $priceFromOtherMaterial = $otherMaterial->getPrices()[0];

        $this->expectException(MaterialPriceNotFoundException::class);

        $this->material->removePrice($priceFromOtherMaterial);
    }

    public function testMaterialPriceHasCorrectMaterialReference(): void
    {
        $this->material->addPrice(18, '1500.00');

        $price = $this->material->getPrices()[0];

        $this->assertSame($this->material, $price->getMaterial());
    }

    public function testGetPricesReturnsEmptyCollectionInitially(): void
    {
        $this->assertCount(0, $this->material->getPrices());
    }
}
