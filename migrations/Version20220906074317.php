<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220906074317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Audit Trail';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE audit_trail (audit_trail_id INT AUTO_INCREMENT NOT NULL, action VARCHAR(255) NOT NULL, user_id INT NOT NULL, action_details JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(audit_trail_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE audit_trail');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
