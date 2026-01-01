<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Wood\CommandHandler;

use App\Application\Wood\Command\CreateWood\CreateWoodCommand;
use App\Application\Wood\Command\CreateWood\CreateWoodCommandHandler;
use App\Application\Wood\Service\WoodApplicationService;
use App\Domain\Translation\TranslationDto\TranslationDto;
use App\Domain\Wood\Entity\Wood;
use App\Domain\Wood\Exception\WoodAlreadyExistsException;
use App\Domain\Wood\Exception\WoodValidationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateWoodCommandHandlerTest extends TestCase
{
    private WoodApplicationService&MockObject $woodApplicationService;
    private CreateWoodCommandHandler $handler;

    protected function setUp(): void
    {
        $this->woodApplicationService = $this->createMock(WoodApplicationService::class);
        $this->handler = new CreateWoodCommandHandler($this->woodApplicationService);
    }

    public function testHandleCreatesWoodSuccessfully(): void
    {
        $command = new CreateWoodCommand(
            name: 'oak',
            latinName: 'Quercus',
            dryDensity: 750,
            hardness: 6000,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('findByName')
            ->with('oak')
            ->willReturn(null)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenWoodAlreadyExists(): void
    {
        $command = new CreateWoodCommand(
            name: 'oak',
            latinName: null,
            dryDensity: null,
            hardness: null,
            translations: []
        );

        $existingWood = Wood::create('oak');

        $this->woodApplicationService
            ->expects($this->once())
            ->method('findByName')
            ->with('oak')
            ->willReturn($existingWood)
        ;

        $this->expectException(WoodAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationExceptionForInvalidName(): void
    {
        $command = new CreateWoodCommand(
            name: 'Invalid Name',
            latinName: null,
            dryDensity: null,
            hardness: null,
            translations: []
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('findByName')
            ->willReturn(null)
        ;

        $this->expectException(WoodValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleCreatesWoodWithTranslations(): void
    {
        $descriptionTranslation = $this->createStub(TranslationDto::class);
        $descriptionTranslation->method('getField')->willReturn('description');
        $descriptionTranslation->method('getValue')->willReturn('Beautiful hardwood');
        $descriptionTranslation->method('getLocale')->willReturn('en');

        $originTranslation = $this->createStub(TranslationDto::class);
        $originTranslation->method('getField')->willReturn('place_of_origin');
        $originTranslation->method('getValue')->willReturn('Europe');
        $originTranslation->method('getLocale')->willReturn('en');

        $translations = [$descriptionTranslation, $originTranslation];

        $command = new CreateWoodCommand(
            name: 'oak',
            latinName: 'Quercus',
            dryDensity: 750,
            hardness: 6000,
            translations: $translations
        );

        $this->woodApplicationService
            ->expects($this->once())
            ->method('findByName')
            ->willReturn(null)
        ;

        $this->woodApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);
    }
}
