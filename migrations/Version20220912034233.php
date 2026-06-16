<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220912034233 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audit_trail ADD email VARCHAR(255) NOT NULL, ADD first_name VARCHAR(255) NOT NULL, ADD middle_name VARCHAR(255) DEFAULT NULL, ADD last_name VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audit_trail DROP email, DROP first_name, DROP middle_name, DROP last_name');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
