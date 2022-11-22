<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213052532 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE social_marketing (social_marketing_id INT AUTO_INCREMENT NOT NULL, social_marketing_activity_id INT NOT NULL, activity_name VARCHAR(255) NOT NULL, date DATE NOT NULL, venue VARCHAR(255) NOT NULL, remarks VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, primers INT NOT NULL, field_office_id INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(social_marketing_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE social_marketing');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
