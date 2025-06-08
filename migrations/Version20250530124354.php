<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250530124354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE translation (id SERIAL NOT NULL, object_class VARCHAR(100) NOT NULL, object_id INT NOT NULL, locale VARCHAR(2) NOT NULL, field VARCHAR(200) NOT NULL, value TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material DROP description
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material DROP place_of_origin
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE translation
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ADD description VARCHAR(400) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE material ADD place_of_origin VARCHAR(300) DEFAULT NULL
        SQL);
    }
}
