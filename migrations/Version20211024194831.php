<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211024194831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_sessions (client_session_id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, session_id INT NOT NULL, role enum(\'PS\', \'PR\', \'PD\', \'JICL\', \'FTMDO\', \'PET\', \'TERM\'), PRIMARY KEY(client_session_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE client_types (client_type_id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(client_type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE clients (client_id INT AUTO_INCREMENT NOT NULL, cmis_id INT NOT NULL, client_type_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, suffix VARCHAR(255) DEFAULT NULL, gender VARCHAR(1) NOT NULL, date_of_birth DATE NOT NULL, offense_category enum(\'DO\', \'NDO\'), field_office_id INT DEFAULT NULL, is_senior_citizen TINYINT(1) NOT NULL, is_pwd TINYINT(1) NOT NULL, supervision_start DATE NOT NULL, supervision_end DATE NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(client_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE phases CHANGE name name enum(\'I\', \'II\', \'III\', \'IV\')');
        $this->addSql('ALTER TABLE quarters CHANGE name name enum(\'FIRST\', \'SECOND\', \'THIRD\', \'FOURTH\')');
        $this->addSql('ALTER TABLE sessions CHANGE period period enum(\'AM\', \'PM\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE client_sessions');
        $this->addSql('DROP TABLE client_types');
        $this->addSql('DROP TABLE clients');
        $this->addSql('ALTER TABLE phases CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE quarters CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE sessions CHANGE period period VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
