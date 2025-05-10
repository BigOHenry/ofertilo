<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250510174110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ADD latin_name VARCHAR(300) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ADD place_of_origin VARCHAR(300) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ADD dry_density INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ADD hardness INT DEFAULT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE appuser ALTER roles TYPE JSONB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material DROP latin_name
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material DROP place_of_origin
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material DROP dry_density
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material DROP hardness
        SQL);
    }
}
