<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material;

use App\Domain\Material\Entity\EdgeGluedPanelMaterial;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Entity\PieceMaterial;
use App\Domain\Material\Entity\PlywoodMaterial;
use App\Domain\Material\Entity\SolidWoodMaterial;
use App\Domain\Material\Exception\MaterialPriceAlreadyExistsException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\ValueObject\MaterialType;
use App\Domain\Material\ValueObject\MeasurementType;
use App\Domain\Wood\Entity\Wood;
use PHPUnit\Framework\TestCase;

final class MaterialTest extends TestCase
{
    private Wood $wood;

    protected function setUp(): void
    {
        $this->wood = Wood::create('oak', 'Quercus', 750, 6000);
    }

    public function testCreatePieceMaterial(): void
    {
        $material = PieceMaterial::create($this->wood);

        $this->assertInstanceOf(PieceMaterial::class, $material);
        $this->assertSame($this->wood, $material->getWood());
        $this->assertTrue($material->isEnabled());
        $this->assertSame(MaterialType::PIECE, $material->getType());
        $this->assertSame(MeasurementType::PIECE, $material->getMeasurementType());
    }

    public function testCreatePlywoodMaterial(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $this->assertInstanceOf(PlywoodMaterial::class, $material);
        $this->assertSame($this->wood, $material->getWood());
        $this->assertTrue($material->isEnabled());
        $this->assertSame(MaterialType::PLYWOOD, $material->getType());
        $this->assertSame(MeasurementType::AREA, $material->getMeasurementType());
    }

    public function testCreateEdgeGluedPanelMaterial(): void
    {
        $material = EdgeGluedPanelMaterial::create($this->wood);

        $this->assertInstanceOf(EdgeGluedPanelMaterial::class, $material);
        $this->assertSame($this->wood, $material->getWood());
        $this->assertTrue($material->isEnabled());
        $this->assertSame(MaterialType::EDGE_GLUED_PANEL, $material->getType());
        $this->assertSame(MeasurementType::AREA, $material->getMeasurementType());
    }

    public function testCreateSolidWoodMaterial(): void
    {
        $material = SolidWoodMaterial::create($this->wood);

        $this->assertInstanceOf(SolidWoodMaterial::class, $material);
        $this->assertSame($this->wood, $material->getWood());
        $this->assertTrue($material->isEnabled());
        $this->assertSame(MaterialType::SOLID_WOOD, $material->getType());
        $this->assertSame(MeasurementType::VOLUME, $material->getMeasurementType());
    }

    public function testCreateMaterialWithDisabledState(): void
    {
        $material = PieceMaterial::create($this->wood, false);

        $this->assertFalse($material->isEnabled());
    }

    public function testSetWood(): void
    {
        $material = PieceMaterial::create($this->wood);
        $newWood = Wood::create('pine', 'Pinus', 500, 4000);

        $material->setWood($newWood);

        $this->assertSame($newWood, $material->getWood());
    }

    public function testSetEnabled(): void
    {
        $material = PieceMaterial::create($this->wood);

        $material->setEnabled(false);
        $this->assertFalse($material->isEnabled());

        $material->setEnabled(true);
        $this->assertTrue($material->isEnabled());
    }

    public function testGetName(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $this->assertSame('oak_PLYWOOD', $material->getName());
    }

    public function testGetDescription(): void
    {
        $this->wood->setDescription('Oak wood', 'en');
        $material = SolidWoodMaterial::create($this->wood);

        $description = $material->getDescription('en');

        $this->assertStringContainsString('Oak wood', $description);
        $this->assertStringContainsString('solid_wood', $description);
    }

    public function testAddPrice(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $material->addPrice(18, '1500.00');

        $prices = $material->getPrices();
        $this->assertCount(1, $prices);

        $firstPrice = $prices->first();
        $this->assertInstanceOf(MaterialPrice::class, $firstPrice);
        $this->assertSame(18, $firstPrice->getThickness());
    }

    public function testAddMultiplePrices(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $material->addPrice(18, '1500.00');
        $material->addPrice(20, '1700.00');
        $material->addPrice(25, '2000.00');

        $this->assertCount(3, $material->getPrices());
    }

    public function testAddPriceThrowsExceptionForDuplicateThickness(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $material->addPrice(18, '1500.00');

        $this->expectException(MaterialPriceAlreadyExistsException::class);

        $material->addPrice(18, '1600.00');
    }

    public function testRemovePrice(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $material->addPrice(18, '1500.00');
        $material->addPrice(20, '1700.00');

        $prices = $material->getPrices();
        $priceToRemove = $prices->first();

        $this->assertInstanceOf(MaterialPrice::class, $priceToRemove);
        $material->removePrice($priceToRemove);

        $this->assertCount(1, $material->getPrices());
    }

    public function testRemovePriceThrowsExceptionWhenPriceNotFound(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $otherMaterial = PlywoodMaterial::create($this->wood);
        $otherMaterial->addPrice(18, '1500.00');

        $priceFromOtherMaterial = $otherMaterial->getPrices()->first();

        $this->assertInstanceOf(MaterialPrice::class, $priceFromOtherMaterial);
        $this->expectException(MaterialPriceNotFoundException::class);

        $material->removePrice($priceFromOtherMaterial);
    }

    public function testGetMaterialClassByType(): void
    {
        $this->assertSame(
            PieceMaterial::class,
            PieceMaterial::getMaterialClassByType(MaterialType::PIECE)
        );

        $this->assertSame(
            PlywoodMaterial::class,
            PlywoodMaterial::getMaterialClassByType(MaterialType::PLYWOOD)
        );

        $this->assertSame(
            EdgeGluedPanelMaterial::class,
            EdgeGluedPanelMaterial::getMaterialClassByType(MaterialType::EDGE_GLUED_PANEL)
        );

        $this->assertSame(
            SolidWoodMaterial::class,
            SolidWoodMaterial::getMaterialClassByType(MaterialType::SOLID_WOOD)
        );
    }

    public function testFindPriceById(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $material->addPrice(18, '1500.00');

        $prices = $material->getPrices();
        $price = $prices->first();

        $this->assertInstanceOf(MaterialPrice::class, $price);

        $priceId = $price->getId();

        $foundPrice = $material->findPriceById($priceId);

        $this->assertSame($price, $foundPrice);
    }

    public function testFindPriceByIdReturnsNullWhenNotFound(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';
        $this->assertNull($material->findPriceById($nonExistentId));
    }

    public function testFindPriceByThickness(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $material->addPrice(18, '1500.00');
        $material->addPrice(20, '1700.00');

        $foundPrice = $material->findPriceByThickness(18);

        $this->assertNotNull($foundPrice);
        $this->assertSame(18, $foundPrice->getThickness());
        $this->assertSame('1500.00', $foundPrice->getPrice());
    }

    public function testFindPriceByThicknessReturnsNullWhenNotFound(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $this->assertNull($material->findPriceByThickness(99));
    }

    public function testGetPriceByIdThrowsExceptionWhenNotFound(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $this->expectException(MaterialPriceNotFoundException::class);

        $nonExistentId = '00000000-0000-0000-0000-000000000000';
        $material->getPriceById($nonExistentId);
    }

    public function testGetPriceById(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $material->addPrice(18, '1500.00');

        $prices = $material->getPrices();
        $price = $prices->first();

        $this->assertInstanceOf(MaterialPrice::class, $price);

        $priceId = $price->getId();

        $foundPrice = $material->getPriceById($priceId);

        $this->assertSame($price, $foundPrice);
    }

    public function testMaterialHasUuidId(): void
    {
        $material = PlywoodMaterial::create($this->wood);

        $id = $material->getId();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $id,
            'ID should be a valid UUID v4'
        );
    }

    public function testMaterialPriceHasUuidId(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $material->addPrice(18, '1500.00');

        $prices = $material->getPrices();
        $price = $prices->first();

        $this->assertInstanceOf(MaterialPrice::class, $price);

        $id = $price->getId();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $id,
            'MaterialPrice ID should be a valid UUID v4'
        );
    }
}
