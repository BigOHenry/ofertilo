<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\ValueObject\Type;
use PHPUnit\Framework\TestCase;

class MaterialRelationshipTest extends TestCase
{
    public function testComplexMaterialWithMultiplePrices(): void
    {
        $material = new Material();
        $material->setName('Oak Wood');
        $material->setLatinName('Quercus');
        $material->setType(Type::VOLUME);
        $material->setDryDensity(750);
        $material->setHardness(85);
        $material->setEnabled(true);

        // Přidání více cen pro různé tloušťky
        $price1 = new MaterialPrice($material);
        $price1->setThickness(10);
        $price1->setPrice(50.0);

        $price2 = new MaterialPrice($material);
        $price2->setThickness(20);
        $price2->setPrice(95.0);

        $price3 = new MaterialPrice($material);
        $price3->setThickness(30);
        $price3->setPrice(140.0);

        $material->addPrice($price1);
        $material->addPrice($price2);
        $material->addPrice($price3);

        $this->assertCount(3, $material->getPrices());
        $this->assertSame('Oak Wood', $material->getName());
        $this->assertSame(Type::VOLUME, $material->getType());

        foreach ($material->getPrices() as $price) {
            $this->assertSame($material, $price->getMaterial());
            $this->assertNotNull($price->getThickness());
            $this->assertNotNull($price->getPrice());
        }
    }

    public function testMaterialWithTranslationsAndPrices(): void
    {
        $material = new Material();
        $material->setName('Beech');
        $material->setDescription('Hardwood tree', 'en');
        $material->setDescription('Listnatý strom', 'cs');
        $material->setPlaceOfOrigin('Europe', 'en');
        $material->setPlaceOfOrigin('Evropa', 'cs');

        $price = new MaterialPrice($material);
        $price->setThickness(25);
        $price->setPrice(75.5);

        $material->addPrice($price);

        $this->assertSame('Hardwood tree', $material->getDescription('en'));
        $this->assertSame('Listnatý strom', $material->getDescription('cs'));
        $this->assertSame('Europe', $material->getPlaceOfOrigin('en'));
        $this->assertSame('Evropa', $material->getPlaceOfOrigin('cs'));
        $this->assertCount(1, $material->getPrices());
        $this->assertSame(75.5, $material->getPrices()->first()->getPrice());
    }
}
