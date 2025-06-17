<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250617065211 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE product (id SERIAL NOT NULL, country_id INT NOT NULL, type VARCHAR(255) NOT NULL, name VARCHAR(100) NOT NULL, enabled BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D34A04ADF92F3E70 ON product (country_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_product_type_country ON product (type, country_id, name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE product_color (id SERIAL NOT NULL, product_id INT NOT NULL, color_id INT NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C70A33B54584665A ON product_color (product_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_C70A33B57ADA1FB5 ON product_color (color_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX unique_product_color ON product_color (product_id, color_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product ADD CONSTRAINT FK_D34A04ADF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_color ADD CONSTRAINT FK_C70A33B54584665A FOREIGN KEY (product_id) REFERENCES product (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_color ADD CONSTRAINT FK_C70A33B57ADA1FB5 FOREIGN KEY (color_id) REFERENCES color (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country ADD enabled BOOLEAN DEFAULT true NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_country_alpha2 ON country (alpha2)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_country_alpha3 ON country (alpha3)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX idx_country_enabled ON country (enabled)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product DROP CONSTRAINT FK_D34A04ADF92F3E70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_color DROP CONSTRAINT FK_C70A33B54584665A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE product_color DROP CONSTRAINT FK_C70A33B57ADA1FB5
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE product_color
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_country_alpha2
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_country_alpha3
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX idx_country_enabled
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE country DROP enabled
        SQL);
    }
}
