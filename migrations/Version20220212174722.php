<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220212174722 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE id_support (id INT AUTO_INCREMENT NOT NULL, type enum(\'VPA\', \'Personnel\'), vpa_personnel_id INT NOT NULL, program VARCHAR(255) NOT NULL, field_office_id INT NOT NULL, activity VARCHAR(255) NOT NULL, date DATE NOT NULL, venue VARCHAR(255) NOT NULL, assistance_rendered VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE id_support');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
