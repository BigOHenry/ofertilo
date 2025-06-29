<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Material\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Exception\DuplicatePriceThicknessException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\ValueObject\Type;
use PHPUnit\Framework\TestCase;

class MaterialRelationshipTest extends TestCase
{
    public function testComplexMaterialWithMultiplePrices(): void
    {
        $material = Material::create(Type::VOLUME, 'oak_wood');
        $material->setLatinName('Quercus robur');
        $material->setDryDensity(750);
        $material->setHardness(85);
        $material->setEnabled(true);

        $material->addPrice(10, '50.0');
        $material->addPrice(20, '95.0');
        $material->addPrice(30, '140.0');

        $this->assertCount(3, $material->getPrices());
        $this->assertSame('oak_wood', $material->getName());
        $this->assertSame(Type::VOLUME, $material->getType());

        foreach ($material->getPrices() as $price) {
            $this->assertSame($material, $price->getMaterial());
            $this->assertGreaterThan(0, $price->getThickness());
            $this->assertGreaterThan(0, $price->getPrice());
        }
    }

    public function testMaterialWithTranslationsAndPrices(): void
    {
        $material = Material::create(Type::VOLUME, 'beech');
        $material->setDescription('Hardwood tree', 'en');
        $material->setDescription('Listnatý strom', 'cs');
        $material->setPlaceOfOrigin('Europe', 'en');
        $material->setPlaceOfOrigin('Evropa', 'cs');

        $material->addPrice(25, '75.5');

        $this->assertSame('Hardwood tree', $material->getDescription('en'));
        $this->assertSame('Listnatý strom', $material->getDescription('cs'));
        $this->assertSame('Europe', $material->getPlaceOfOrigin('en'));
        $this->assertSame('Evropa', $material->getPlaceOfOrigin('cs'));
        $this->assertCount(1, $material->getPrices());
        $this->assertSame('75.5', $material->getPrices()->first()->getPrice());
    }

    public function testBidirectionalRelationship(): void
    {
        $material = Material::create(Type::VOLUME, 'pine_wood');
        $material->addPrice(15, '35.0');

        $price = $material->getPrices()->first();

        $this->assertSame($material, $price->getMaterial());
        $this->assertTrue($material->getPrices()->contains($price));

        $material->removePrice($price);
        $this->assertCount(0, $material->getPrices());
    }

    public function testMaterialPriceIntegrity(): void
    {
        $material = Material::create(Type::VOLUME, 'walnut');
        $material->addPrice(12, '120.0');
        $material->addPrice(18, '180.0');
        $material->addPrice(24, '240.0');

        $prices = $material->getPrices();
        $this->assertCount(3, $prices);

        foreach ($prices as $price) {
            $this->assertSame($material, $price->getMaterial());
        }

        $firstPrice = $prices->first();
        $material->removePrice($firstPrice);
        $this->assertCount(2, $material->getPrices());
        $this->assertFalse($material->getPrices()->contains($firstPrice));
    }

    public function testDuplicatePriceThicknessThrowsException(): void
    {
        $material = Material::create(Type::VOLUME, 'cherry');
        $material->addPrice(15, '80.0');

        $this->expectException(DuplicatePriceThicknessException::class);
        $this->expectExceptionMessage('Price for thickness 15mm already exists');

        $material->addPrice(15, '90.0');
    }

    public function testRemoveNonExistentPriceThrowsException(): void
    {
        $material = Material::create(Type::VOLUME, 'maple');
        $otherMaterial = Material::create(Type::VOLUME, 'birch');

        $otherMaterial->addPrice(10, '50.0');
        $otherPrice = $otherMaterial->getPrices()->first();

        $this->expectException(MaterialPriceNotFoundException::class);

        $material->removePrice($otherPrice);
    }

    public function testMaterialWithAllPropertiesAndMultiplePrices(): void
    {
        $material = Material::create(Type::VOLUME, 'premium_mahogany');
        $material->setLatinName('Swietenia mahagoni');
        $material->setDryDensity(850);
        $material->setHardness(95);
        $material->setDescription('Premium quality mahogany wood', 'en');
        $material->setDescription('Prémiová kvalita mahagonového dřeva', 'cs');
        $material->setPlaceOfOrigin('Central America', 'en');
        $material->setPlaceOfOrigin('Střední Amerika', 'cs');

        $material->addPrice(8, '180.0');
        $material->addPrice(12, '250.0');
        $material->addPrice(16, '320.0');
        $material->addPrice(20, '400.0');

        $this->assertSame('premium_mahogany', $material->getName());
        $this->assertSame(Type::VOLUME, $material->getType());
        $this->assertSame('Swietenia mahagoni', $material->getLatinName());
        $this->assertSame(850, $material->getDryDensity());
        $this->assertSame(95, $material->getHardness());
        $this->assertSame('Premium quality mahogany wood', $material->getDescription('en'));
        $this->assertSame('Prémiová kvalita mahagonového dřeva', $material->getDescription('cs'));
        $this->assertSame('Central America', $material->getPlaceOfOrigin('en'));
        $this->assertSame('Střední Amerika', $material->getPlaceOfOrigin('cs'));
        $this->assertTrue($material->isEnabled());
        $this->assertCount(4, $material->getPrices());

        $priceThicknesses = [];
        foreach ($material->getPrices() as $price) {
            $this->assertSame($material, $price->getMaterial());
            $priceThicknesses[] = $price->getThickness();
        }

        $this->assertContains(8, $priceThicknesses);
        $this->assertContains(12, $priceThicknesses);
        $this->assertContains(16, $priceThicknesses);
        $this->assertContains(20, $priceThicknesses);
    }

    public function testMaterialPriceCollectionOperations(): void
    {
        $material = Material::create(Type::VOLUME, 'ash_wood');

        $this->assertCount(0, $material->getPrices());
        $this->assertTrue($material->getPrices()->isEmpty());

        $material->addPrice(10, '45.0');
        $this->assertCount(1, $material->getPrices());
        $this->assertFalse($material->getPrices()->isEmpty());

        $material->addPrice(15, '67.5');
        $material->addPrice(20, '90.0');
        $this->assertCount(3, $material->getPrices());

        $priceToRemove = null;
        foreach ($material->getPrices() as $price) {
            if ($price->getThickness() === 15) {
                $priceToRemove = $price;
                break;
            }
        }

        $this->assertNotNull($priceToRemove);
        $material->removePrice($priceToRemove);
        $this->assertCount(2, $material->getPrices());

        $remainingThicknesses = [];
        foreach ($material->getPrices() as $price) {
            $remainingThicknesses[] = $price->getThickness();
        }

        $this->assertContains(10, $remainingThicknesses);
        $this->assertContains(20, $remainingThicknesses);
        $this->assertNotContains(15, $remainingThicknesses);
    }

    public function testMaterialTranslationConsistency(): void
    {
        $material = Material::create(Type::VOLUME, 'teak');

        $material->setDescription('Tropical hardwood', 'en');
        $material->setDescription('Tropické tvrdé dřevo', 'cs');
        $material->setDescription('Tropisches Hartholz', 'de');

        $material->setPlaceOfOrigin('Southeast Asia', 'en');
        $material->setPlaceOfOrigin('Jihovýchodní Asie', 'cs');
        $material->setPlaceOfOrigin('Südostasien', 'de');

        $material->addPrice(22, '450.0');

        $this->assertSame('Tropical hardwood', $material->getDescription('en'));
        $this->assertSame('Tropické tvrdé dřevo', $material->getDescription('cs'));
        $this->assertSame('Tropisches Hartholz', $material->getDescription('de'));

        $this->assertSame('Southeast Asia', $material->getPlaceOfOrigin('en'));
        $this->assertSame('Jihovýchodní Asie', $material->getPlaceOfOrigin('cs'));
        $this->assertSame('Südostasien', $material->getPlaceOfOrigin('de'));

        $this->assertCount(1, $material->getPrices());
        $this->assertSame('450.0', $material->getPrices()->first()->getPrice());
    }
}
