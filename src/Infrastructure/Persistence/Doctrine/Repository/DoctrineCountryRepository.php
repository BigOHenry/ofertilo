<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Shared\Entity\Country;
use App\Domain\Shared\Repository\CountryRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @phpstan-extends ServiceEntityRepository<Country>
 */
class DoctrineCountryRepository extends ServiceEntityRepository implements CountryRepositoryInterface
{
    private const int CACHE_TTL = 86400; // 24 hours

    public function __construct(
        ManagerRegistry $registry,
        private readonly CacheInterface $cache,
    ) {
        parent::__construct($registry, Country::class);
    }

    public function findById(int $id): ?Country
    {
        return $this->find($id);
    }

    public function findByAlpha2(string $alpha2): ?Country
    {
        return $this->findOneBy(['alpha2' => mb_strtoupper($alpha2)]);
    }

    public function findByAlpha3(string $alpha3): ?Country
    {
        return $this->findOneBy(['alpha3' => mb_strtoupper($alpha3)]);
    }

    /**
     * @return Country[]
     */
    public function findAllEnabled(): array
    {
        return $this->findBy(['enabled' => true], ['name' => 'ASC']);
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return array<string, int>
     */
    public function findAllAsChoices(): array
    {
        return $this->cache->get(
            'countries_choices',
            function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TTL);
                $countries = $this->findAllEnabled();
                $choices = [];
                foreach ($countries as $country) {
                    $choices[$country->getName()] = $country->getId();
                }

                return $choices;
            }
        );
    }

    /**
     * @throws InvalidArgumentException
     *
     * @return array<string, string>
     */
    public function findAllAsApiChoices(): array
    {
        return $this->cache->get(
            'countries_api_choices',
            function (ItemInterface $item) {
                $item->expiresAfter(self::CACHE_TTL);
                $countries = $this->findAllEnabled();
                $choices = [];
                foreach ($countries as $country) {
                    $choices[$country->getAlpha2()] = $country->getName();
                }

                return $choices;
            }
        );
    }

    public function existsEnabledByAlpha2(string $alpha2): bool
    {
        $country = $this->findByAlpha2($alpha2);

        return $country !== null && $country->isEnabled();
    }

    public function existsEnabledByAlpha3(string $alpha3): bool
    {
        $country = $this->findByAlpha3($alpha3);

        return $country !== null && $country->isEnabled();
    }
}
