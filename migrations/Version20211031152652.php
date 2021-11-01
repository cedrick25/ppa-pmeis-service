<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211031152652 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE volunteer (volunteer_id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, suffix VARCHAR(5) DEFAULT NULL, gender VARCHAR(1) NOT NULL, date_of_birth DATE NOT NULL, is_senior_citizen TINYINT(1) DEFAULT NULL, is_pwd TINYINT(1) DEFAULT NULL, field_office_id INT DEFAULT NULL, region_id INT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(volunteer_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_sessions CHANGE role role enum(\'PS\', \'PR\', \'PD\', \'JICL\', \'FTMDO\', \'PET\', \'TERM\')');
        $this->addSql('ALTER TABLE clients CHANGE offense_category offense_category enum(\'DO\', \'NDO\')');
        $this->addSql('ALTER TABLE phases CHANGE name name enum(\'I\', \'II\', \'III\', \'IV\')');
        $this->addSql('ALTER TABLE quarters CHANGE name name enum(\'FIRST\', \'SECOND\', \'THIRD\', \'FOURTH\')');
        $this->addSql('ALTER TABLE sessions CHANGE period period enum(\'AM\', \'PM\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE volunteer');
        $this->addSql('ALTER TABLE client_sessions CHANGE role role VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE clients CHANGE offense_category offense_category VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE phases CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE quarters CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE sessions CHANGE period period VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
