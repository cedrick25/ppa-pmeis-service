<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240905190227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add id_number in volunteer';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE volunteer ADD id_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE volunteer DROP id_number');
    }
}
