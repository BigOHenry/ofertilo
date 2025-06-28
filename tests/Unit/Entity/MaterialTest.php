<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Domain\Material\Entity\Material;
use App\Domain\Material\Entity\MaterialPrice;
use App\Domain\Material\Exception\DuplicatePriceThicknessException;
use App\Domain\Material\Exception\InvalidMaterialException;
use App\Domain\Material\Exception\MaterialPriceNotFoundException;
use App\Domain\Material\ValueObject\Type;
use PHPUnit\Framework\TestCase;

class MaterialTest extends TestCase
{
    public function testConstructor(): void
    {
        $material = Material::createEmpty();

        $this->assertNull($material->getId());
        $this->assertNull($material->getLatinName());
        $this->assertNull($material->getDryDensity());
        $this->assertNull($material->getHardness());
        $this->assertNull($material->getDescription());
        $this->assertNull($material->getPlaceOfOrigin());
        $this->assertTrue($material->isEnabled());
        $this->assertEmpty($material->getPrices());
    }

    public function testCreateMaterialWithTypeAndName(): void
    {
        $material = Material::create(Type::VOLUME, 'Oak Wood');

        $this->assertNull($material->getId());
        $this->assertSame('Oak Wood', $material->getName());
        $this->assertSame(Type::VOLUME, $material->getType());
        $this->assertTrue($material->isEnabled());
        $this->assertEmpty($material->getPrices());
    }


    public function testSetValidName(): void
    {
        $material = Material::createEmpty();
        $material->setName('Oak Wood');

        $this->assertSame('Oak Wood', $material->getName());
    }

    public function testSetInvalidNameThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material name cannot be empty');

        $material->setName('');
    }

    public function testSetNameTooShortThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material name must be at least 2 characters');

        $material->setName('A');
    }

    public function testSetNameTooLongThrowsException(): void
    {
        $material = Material::createEmpty();
        $longName = str_repeat('A', 101);

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material name must be maximum 100 characters');

        $material->setName($longName);
    }

    public function testSetNameWithInvalidCharactersThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material name contains invalid characters');

        $material->setName('Oak@Wood!');
    }

    public function testSetValidLatinName(): void
    {
        $material = Material::createEmpty();
        $material->setLatinName('Quercus robur');

        $this->assertSame('Quercus robur', $material->getLatinName());
    }

    public function testSetLatinNameTooLongThrowsException(): void
    {
        $material = Material::createEmpty();
        $longLatinName = str_repeat('A', 301);

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material latin name must be maximum 300 characters');

        $material->setLatinName($longLatinName);
    }

    public function testSetLatinNameWithInvalidCharactersThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material latin name contains invalid characters');

        $material->setLatinName('Quercus123');
    }

    public function testSetValidDryDensity(): void
    {
        $material = Material::createEmpty();
        $material->setDryDensity(750);

        $this->assertSame(750, $material->getDryDensity());
    }

    public function testSetDryDensityTooLowThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material dry density is lower than minimum allowed value 10 kg/m³');

        $material->setDryDensity(5);
    }

    public function testSetDryDensityTooHighThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material dry density exceeds maximum allowed value 2000 kg/m³');

        $material->setDryDensity(2500);
    }

    public function testSetValidHardness(): void
    {
        $material = Material::createEmpty();
        $material->setHardness(85);

        $this->assertSame(85, $material->getHardness());
    }

    public function testSetHardnessTooLowThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material hardness is lower than minimum allowed value 1');

        $material->setHardness(0);
    }

    public function testSetHardnessTooHighThrowsException(): void
    {
        $material = Material::createEmpty();

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material hardness exceeds maximum allowed value 9999');

        $material->setHardness(10000);
    }

    public function testSetDescriptionTooLongThrowsException(): void
    {
        $material = Material::createEmpty();
        $longDescription = str_repeat('A', 101);

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material description must be maximum 100 characters');

        $material->setDescription($longDescription);
    }

    public function testSetAndGetDescriptionTranslation(): void
    {
        $material = Material::createEmpty();
        $material->setDescription('Oak', 'en');
        $material->setDescription('Dub', 'cs');
        $this->assertSame('Oak', $material->getDescription('en'));
        $this->assertSame('Dub', $material->getDescription('cs'));
    }

    public function testSetAndGetPlaceOfOriginTranslation(): void
    {
        $material = Material::createEmpty();
        $material->setPlaceOfOrigin('Czech republic', 'en');
        $material->setPlaceOfOrigin('Česká republika', 'cs');
        $this->assertSame('Czech republic', $material->getPlaceOfOrigin('en'));
        $this->assertSame('Česká republika', $material->getPlaceOfOrigin('cs'));
    }

    public function testSetPlaceOfOriginTooLongThrowsException(): void
    {
        $material = Material::createEmpty();
        $longPlace = str_repeat('A', 201);

        $this->expectException(InvalidMaterialException::class);
        $this->expectExceptionMessage('Material place of origin must be maximum 200 characters');

        $material->setPlaceOfOrigin($longPlace);
    }

    public function testAddPriceWithValidData(): void
    {
        $material = Material::create(Type::VOLUME, 'Oak Wood');
        $material->addPrice(10, 50.0);

        $this->assertCount(1, $material->getPrices());
        $price = $material->getPrices()->first();
        $this->assertSame(10, $price->getThickness());
        $this->assertSame(50.0, $price->getPrice());
        $this->assertSame($material, $price->getMaterial());
    }

    public function testAddDuplicateThicknessThrowsException(): void
    {
        $material = Material::create(Type::VOLUME, 'Oak Wood');
        $material->addPrice(10, 50.0);

        $this->expectException(DuplicatePriceThicknessException::class);
        $this->expectExceptionMessage('Price for thickness 10mm already exists');

        $material->addPrice(10, 60.0);
    }

    public function testRemovePriceSuccessfully(): void
    {
        $material = Material::create(Type::VOLUME, 'Oak Wood');
        $material->addPrice(10, 50.0);
        $price = $material->getPrices()->first();

        $material->removePrice($price);

        $this->assertCount(0, $material->getPrices());
    }

    public function testRemoveNonExistentPriceThrowsException(): void
    {
        $material = Material::create(Type::VOLUME, 'Oak Wood');
        $otherMaterial = Material::create(Type::VOLUME, 'Beech Wood');
        $price = MaterialPrice::create($otherMaterial, 10, 50.0);

        $this->expectException(MaterialPriceNotFoundException::class);

        $material->removePrice($price);
    }

    public function testGetTranslatableFields(): void
    {
        $fields = Material::getTranslatableFields();

        $this->assertIsArray($fields);
        $this->assertContains('description', $fields);
        $this->assertContains('place_of_origin', $fields);
        $this->assertCount(2, $fields);
    }

    public function testSetAndGetType(): void
    {
        $material = Material::createEmpty();
        $material->setType(Type::VOLUME);

        $this->assertSame(Type::VOLUME, $material->getType());
    }

    public function testSetAndGetEnabled(): void
    {
        $material = Material::createEmpty();
        $this->assertTrue($material->isEnabled());

        $material->setEnabled(false);
        $this->assertFalse($material->isEnabled());

        $material->setEnabled(true);
        $this->assertTrue($material->isEnabled());
    }

    public function testComplexMaterialWithAllProperties(): void
    {
        $material = Material::create(Type::VOLUME, 'Premium Oak');
        $material->setLatinName('Quercus robur');
        $material->setDryDensity(750);
        $material->setHardness(85);
        $material->setDescription('Premium quality oak wood', 'en');
        $material->setDescription('Prémiová kvalita dubového dřeva', 'cs');
        $material->setPlaceOfOrigin('Czech Republic', 'en');
        $material->setPlaceOfOrigin('Česká republika', 'cs');
        $material->addPrice(10, 50.0);
        $material->addPrice(20, 95.0);

        $this->assertSame('Premium Oak', $material->getName());
        $this->assertSame(Type::VOLUME, $material->getType());
        $this->assertSame('Quercus robur', $material->getLatinName());
        $this->assertSame(750, $material->getDryDensity());
        $this->assertSame(85, $material->getHardness());
        $this->assertSame('Premium quality oak wood', $material->getDescription('en'));
        $this->assertSame('Czech Republic', $material->getPlaceOfOrigin('en'));
        $this->assertCount(2, $material->getPrices());
        $this->assertTrue($material->isEnabled());
    }
}
