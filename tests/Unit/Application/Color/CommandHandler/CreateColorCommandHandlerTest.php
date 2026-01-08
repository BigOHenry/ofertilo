<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Color\CommandHandler;

use App\Application\Color\Command\CreateColor\CreateColorCommand;
use App\Application\Color\Command\CreateColor\CreateColorCommandHandler;
use App\Application\Color\Service\ColorApplicationService;
use App\Domain\Color\Entity\Color;
use App\Domain\Color\Exception\ColorAlreadyExistsException;
use App\Domain\Color\Exception\ColorValidationException;
use App\Domain\Translation\DTO\TranslationDto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateColorCommandHandlerTest extends TestCase
{
    private ColorApplicationService&MockObject $colorApplicationService;
    private CreateColorCommandHandler $handler;

    protected function setUp(): void
    {
        $this->colorApplicationService = $this->createMock(ColorApplicationService::class);
        $this->handler = new CreateColorCommandHandler($this->colorApplicationService);
    }

    public function testHandleCreatesColorSuccessfully(): void
    {
        $command = new CreateColorCommand(
            code: 5000,
            inStock: true,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findByCode')
            ->with(5000)
            ->willReturn(null)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionWhenColorAlreadyExists(): void
    {
        $existingColor = Color::create(5000);

        $command = new CreateColorCommand(
            code: 5000,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findByCode')
            ->with(5000)
            ->willReturn($existingColor)
        ;

        $this->expectException(ColorAlreadyExistsException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsValidationExceptionForInvalidCode(): void
    {
        $command = new CreateColorCommand(
            code: 999,
            inStock: false,
            enabled: true,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findByCode')
            ->willReturn(null)
        ;

        $this->expectException(ColorValidationException::class);

        $this->handler->__invoke($command);
    }

    public function testHandleCreatesColorWithTranslations(): void
    {
        $descriptionTranslation = $this->createStub(TranslationDto::class);
        $descriptionTranslation->method('getField')->willReturn('description');
        $descriptionTranslation->method('getValue')->willReturn('Beautiful red color');
        $descriptionTranslation->method('getLocale')->willReturn('en');

        $translations = [$descriptionTranslation];

        $command = new CreateColorCommand(
            code: 5000,
            inStock: true,
            enabled: true,
            translations: $translations
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findByCode')
            ->willReturn(null)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);
    }

    public function testHandleCreatesColorWithDefaultValues(): void
    {
        $command = new CreateColorCommand(
            code: 5000,
            inStock: false,
            enabled: false,
            translations: []
        );

        $this->colorApplicationService
            ->expects($this->once())
            ->method('findByCode')
            ->willReturn(null)
        ;

        $this->colorApplicationService
            ->expects($this->once())
            ->method('save')
        ;

        $this->handler->__invoke($command);
    }
}
