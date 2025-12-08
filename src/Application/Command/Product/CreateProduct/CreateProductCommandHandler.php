<?php

declare(strict_types=1);

namespace App\Application\Command\Product\CreateProduct;

use App\Application\Service\CountryService;
use App\Application\Service\ProductApplicationService;
use App\Domain\Product\Entity\Product;
use App\Domain\Product\Exception\ProductAlreadyExistsException;
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
        $type = $command->getType();
        $country = $this->countryService->getEnabledCountryById($command->getCountryId());

        if ($this->productApplicationService->findByTypeAndCountry($type, $country)) {
            throw ProductAlreadyExistsException::withTypeAndCountry($type, $country);
        }

        $product = Product::create($type, $country);

        if ($command->getImageFile()) {
            $this->productApplicationService->handleImageUpload($product, $command->getImageFile());
        }

        foreach ($command->getTranslations() as $translation) {
            $value = mb_trim($translation->getValue() ?? '');
            $product->addOrUpdateTranslation($translation->getField(), $value, $translation->getLocale());
        }

        $this->productApplicationService->save($product);
    }
}
