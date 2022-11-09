<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220307201209 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE capability_building (capability_building_id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, subtype VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, no_of_participants INT NOT NULL, is_pwd TINYINT(1) NOT NULL, is_senior_citizen TINYINT(1) NOT NULL, not_managerial_supervisory VARCHAR(255) DEFAULT NULL, not_technical VARCHAR(255) DEFAULT NULL, not_foundation VARCHAR(255) DEFAULT NULL, no_of_training_hours INT NOT NULL, tc_in_house VARCHAR(255) DEFAULT NULL, tc_out_house VARCHAR(255) DEFAULT NULL, field_office_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(capability_building_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE capability_building');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
