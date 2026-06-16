<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220926161722 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE system_code_settings CHANGE system_code_id system_code_id VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE system_code_settings CHANGE system_code_id system_code_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
