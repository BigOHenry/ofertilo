<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250619053951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX unique_product_type_country
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP name
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_product_type_country ON product (type, country_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX unique_product_type_country
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD name VARCHAR(100) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_product_type_country ON product (type, country_id, name)
        SQL);
    }
}
