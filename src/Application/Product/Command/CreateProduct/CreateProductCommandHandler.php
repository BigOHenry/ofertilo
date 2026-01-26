<?php

declare(strict_types=1);

namespace App\Application\Product\Command\CreateProduct;

use App\Application\Product\Service\ProductApplicationService;
use App\Application\Shared\Country\Service\CountryService;
use App\Domain\Product\Entity\FlagProduct;
use App\Domain\Product\Entity\Layered2dProduct;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Entity\Relief3dProduct;
use App\Domain\Product\Exception\ProductAlreadyExistsException;
use App\Domain\Product\Exception\ProductValidationException;
use App\Domain\Product\Validator\ProductValidator;
use App\Domain\Product\ValueObject\ProductType;
use App\Domain\Shared\Country\Entity\Country;
use App\Domain\Shared\File\Entity\File;
use App\Domain\Shared\File\ValueObject\FileType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateProductCommandHandler
{
    public function __construct(
        private ProductApplicationService $productApplicationService,
        private CountryService $countryService,
    ) {
    }

    public function __invoke(CreateProductCommand $command): void
    {
        $type = $command->type;
        $country = null;

        if ($command->countryId !== null) {
            $country = $this->countryService->getEnabledCountryById($command->countryId);
        }

        if ($country !== null && $this->productApplicationService->findByTypeAndCountry($type, $country) !== null) {
            throw ProductAlreadyExistsException::withTypeAndCountry($type, $country);
        }

        $errors = ProductValidator::validate($command->code, $command->translations, $command->imageFile);
        if (!empty($errors)) {
            throw ProductValidationException::withErrors($errors);
        }

        $product = $this->createProductByType($type, $country);
        $product->setCode($command->code);

        if ($command->imageFile) {
            $file = File::createFromUploadedFile(
                $command->imageFile,
                FileType::IMAGE
            );
            $product->setImageFile($file);
        }

        foreach ($command->translations as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $product->addOrUpdateTranslation(
                $translation->getField(),
                $value,
                $translation->getLocale()
            );
        }

        $this->productApplicationService->save($product);
    }

    private function createProductByType(ProductType $type, ?Country $country): Product
    {
        return match ($type) {
            ProductType::FLAG => FlagProduct::create($country),
            ProductType::RELIEF_3D => Relief3dProduct::create($country),
            ProductType::LAYERED_2D => Layered2dProduct::create($country),
        };
    }
}
