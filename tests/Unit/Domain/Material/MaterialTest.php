<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material;

use App\Domain\Material\Entity\EdgeGluedPanelMaterial;
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
        $this->assertSame(18, $prices[0]->getThickness());
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
        $priceToRemove = $prices[0];

        $material->removePrice($priceToRemove);

        $this->assertCount(1, $material->getPrices());
    }

    public function testRemovePriceThrowsExceptionWhenPriceNotFound(): void
    {
        $material = PlywoodMaterial::create($this->wood);
        $otherMaterial = PlywoodMaterial::create($this->wood);
        $otherMaterial->addPrice(18, '1500.00');

        $priceFromOtherMaterial = $otherMaterial->getPrices()[0];

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
}
