<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260107220230 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE file (id VARCHAR(36) NOT NULL, filename VARCHAR(255) NOT NULL, original_name VARCHAR(255) NOT NULL, mime_type VARCHAR(100) NOT NULL, type VARCHAR(50) NOT NULL, description VARCHAR(500) DEFAULT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, extension VARCHAR(10) DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE product ADD image_file_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE product DROP image_filename');
        $this->addSql('ALTER TABLE product DROP image_original_name');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD6DB2EB0 FOREIGN KEY (image_file_id) REFERENCES file (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_D34A04AD6DB2EB0 ON product (image_file_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE file');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD6DB2EB0');
        $this->addSql('DROP INDEX IDX_D34A04AD6DB2EB0');
        $this->addSql('ALTER TABLE product ADD image_filename VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD image_original_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product DROP image_file_id');
    }
}
