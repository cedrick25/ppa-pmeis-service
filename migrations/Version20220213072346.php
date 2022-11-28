<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213072346 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE program_materials_development (program_materials_development_id INT AUTO_INCREMENT NOT NULL, particulars VARCHAR(255) NOT NULL, date DATE NOT NULL, program enum(\'TC\', \'RJ\', \'VPA\', \'GAD\', \'OTHERS\'), utilized_for VARCHAR(255) NOT NULL, remarks VARCHAR(255) NOT NULL, field_office_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(program_materials_development_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE program_materials_development');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
