<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213161241 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE resource_mobilization (resource_mobilization_id INT AUTO_INCREMENT NOT NULL, category enum(\'TC\', \'RJ\', \'VPA\', \'GAD\', \'OTHERS\'), activity_name VARCHAR(255) NOT NULL, date DATE NOT NULL, venue VARCHAR(255) NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, cash_source_name VARCHAR(255) NOT NULL, cash_source_type enum(\'GO\', \'NGO\', \'IND\'), materials_id INT NOT NULL, materials_qty INT NOT NULL, material_source_name VARCHAR(255) NOT NULL, material_source_type enum(\'GO\', \'NGO\', \'IND\'), technical_assistance_particulars VARCHAR(255) NOT NULL, technical_assistance_amount DOUBLE PRECISION DEFAULT NULL, technical_assistance_name VARCHAR(255) NOT NULL, technical_assistance_type enum(\'GO\', \'NGO\', \'IND\'), resources_secured_by VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', field_office_id INT NOT NULL, PRIMARY KEY(resource_mobilization_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE resource_mobilization');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
