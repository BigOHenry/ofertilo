<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\ValueObject\Type;
use PHPUnit\Framework\TestCase;

class MaterialTest extends TestCase
{
    public function testConstructor(): void
    {
        $material = new Material();
        $this->assertNull($material->getId());
        $this->assertNull($material->getName());
        $this->assertNull($material->getType());
        $this->assertNull($material->getLatinName());
        $this->assertNull($material->getDryDensity());
        $this->assertNull($material->getHardness());
        $this->assertNull($material->getDescription());
        $this->assertNull($material->getPlaceOfOrigin());
        $this->assertTrue($material->isEnabled());
        $this->assertEmpty($material->getPrices());
    }

    public function testSetId(): void
    {
        $material = new Material();
        $material->setId(123);

        $this->assertSame(123, $material->getId());
    }

    public function testSetAndGetName(): void
    {
        $material = new Material();
        $material->setName('oak');
        $this->assertSame('oak', $material->getName());
    }

    public function testSetAndGetLatinName(): void
    {
        $material = new Material();
        $material->setLatinName('Quercus');
        $this->assertSame('Quercus', $material->getLatinName());
    }

    public function testSetAndGetDryDensity(): void
    {
        $material = new Material();
        $material->setDryDensity(500);
        $this->assertSame(500, $material->getDryDensity());
    }

    public function testSetAndGetHardness(): void
    {
        $material = new Material();
        $material->setHardness(85);
        $this->assertSame(85, $material->getHardness());
    }

    public function testSetAndGetEnabled(): void
    {
        $material = new Material();
        $material->setEnabled(false);
        $this->assertFalse($material->isEnabled());
    }

    public function testSetAndGetType(): void
    {
        $material = new Material();
        $material->setType(Type::VOLUME);
        $this->assertSame(Type::VOLUME, $material->getType());
    }

    public function testSetAndGetDescriptionTranslation(): void
    {
        $material = new Material();
        $material->setDescription('Oak', 'en');
        $material->setDescription('Dub', 'cs');
        $this->assertSame('Oak', $material->getDescription('en'));
        $this->assertSame('Dub', $material->getDescription('cs'));
    }

    public function testSetAndGetPlaceOfOriginTranslation(): void
    {
        $material = new Material();
        $material->setPlaceOfOrigin('Czech republic', 'en');
        $material->setPlaceOfOrigin('Česká republika', 'cs');
        $this->assertSame('Czech republic', $material->getPlaceOfOrigin('en'));
        $this->assertSame('Česká republika', $material->getPlaceOfOrigin('cs'));
    }

    public function testAddPrice(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);

        $material->addPrice($price);

        $this->assertTrue($material->getPrices()->contains($price));
        $this->assertSame($material, $price->getMaterial());
    }

    public function testRemovePrice(): void
    {
        $material = new Material();
        $price = new MaterialPrice($material);

        $material->addPrice($price);
        $material->removePrice($price);

        $this->assertFalse($material->getPrices()->contains($price));
    }

    public function testMultiplePrices(): void
    {
        $material = new Material();
        $price1 = new MaterialPrice($material);
        $price2 = new MaterialPrice($material);

        $material->addPrice($price1);
        $material->addPrice($price2);

        $this->assertCount(2, $material->getPrices());
        $this->assertTrue($material->getPrices()->contains($price1));
        $this->assertTrue($material->getPrices()->contains($price2));
    }

    public function testGetTranslatableFields(): void
    {
        $fields = Material::getTranslatableFields();

        $this->assertIsArray($fields);
        $this->assertContains('description', $fields);
        $this->assertContains('place_of_origin', $fields);
        $this->assertCount(2, $fields);
    }

    public function testTranslationWithDefaultLocale(): void
    {
        $material = new Material();
        $material->setDescription('Default description');

        $this->assertSame('Default description', $material->getDescription());
        $this->assertSame('Default description', $material->getDescription('en'));
    }

    public function testTranslationWithNullLocale(): void
    {
        $material = new Material();
        $material->setDescription('English description', 'en');

        $this->assertSame('English description', $material->getDescription(null));
    }

    public function testMultipleTranslationsForSameField(): void
    {
        $material = new Material();
        $material->setDescription('English description', 'en');
        $material->setDescription('Czech description', 'cs');
        $material->setDescription('German description', 'de');

        $this->assertSame('English description', $material->getDescription('en'));
        $this->assertSame('Czech description', $material->getDescription('cs'));
        $this->assertSame('German description', $material->getDescription('de'));
    }

    public function testPlaceOfOriginWithMultipleLocales(): void
    {
        $material = new Material();
        $material->setPlaceOfOrigin('United States', 'en');
        $material->setPlaceOfOrigin('Spojené státy', 'cs');
        $material->setPlaceOfOrigin('Deutschland', 'de');

        $this->assertSame('United States', $material->getPlaceOfOrigin('en'));
        $this->assertSame('Spojené státy', $material->getPlaceOfOrigin('cs'));
        $this->assertSame('Deutschland', $material->getPlaceOfOrigin('de'));
    }

}
