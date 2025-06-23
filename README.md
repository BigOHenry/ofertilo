# Ofertilo

**Ofertilo** je interní nástroj pro správu zakázek, evidenci materiálů a tvorbu cenových nabídek. Byl vytvořen jako podpora výrobního procesu mého e-shopu [woodflag.eu](https://woodflag.eu) a zároveň jako ukázka programátorských dovedností v PHP.

![Coverage](https://github.com/BigOHenry/ofertilo/blob/image-data/coverage.svg)

## ✨ Funkce
- Evidence klientů a zakázek
- Výpočet spotřeby a ceny materiálů (masiv, MDF, překližka, spárovky...) (plánováno)
- Tvorba cenových nabídek s různými variantami (materiál, rozměr, povrchová úprava) (plánováno)
- Víceúrovňové výpočty a varianty provedení (plánováno)
- Šablony cenových nabídek (plánováno)
- Export do PDF (plánováno)
- Link pro zobrazení nacenění klientovi (plánováno)

## 🛠️ Technologie
- PHP 8.4.x
- Bootstrap 5.3
- PostgreSQL 17.4

## 📦 Instalace

- vytvoření env.docker z env.docker.example a úprava hesla
- spuštění `docker compose up -d --build`
- vytvoření `env.local` z `env.local.example`
- `composer install`
- `npm run dev`
- vygenerování migrací `php bin/console make:migration`
- spuštění mígrací `php bin/console doctrine:migrations:migrate`

## 📦 CS + PHPStan

- kvalita kódu je kontrolována dle PSR-12 pomocí CodeSniffer `vendor/bin/phpcs` nebo automatický fix `PHP_CS_FIXER_IGNORE_ENV=1 vendor/bin/php-cs-fixer fix`
- statickou analýzu kódu provádí PHPStan `vendor/bin/phpstan analyse`
