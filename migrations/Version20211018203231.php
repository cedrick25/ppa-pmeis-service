<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018203231 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_account (user_account_id INT AUTO_INCREMENT NOT NULL, email_address VARCHAR(255) NOT NULL, contact_number VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, user_type VARCHAR(2) NOT NULL, field_office_id INT DEFAULT NULL, status INT NOT NULL, created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(user_account_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_details (user_detail_id INT AUTO_INCREMENT NOT NULL, user_account_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, middle_name VARCHAR(255) DEFAULT NULL, last_name VARCHAR(255) NOT NULL, suffix VARCHAR(5) DEFAULT NULL, gender VARCHAR(1) NOT NULL, date_of_birth DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', is_senior_citizen TINYINT(1) NOT NULL, is_pwd TINYINT(1) NOT NULL, position_id INT NOT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(user_detail_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE user_account');
        $this->addSql('DROP TABLE user_details');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
