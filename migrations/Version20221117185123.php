<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117185123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE res_mob_cash (id INT AUTO_INCREMENT NOT NULL, res_mob_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, source_name VARCHAR(255) NOT NULL, source_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE res_mob_materials (id INT AUTO_INCREMENT NOT NULL, res_mob_id INT NOT NULL, particular_quantity INT NOT NULL, particular_type VARCHAR(255) NOT NULL, estimated_amount INT NOT NULL, source_name VARCHAR(255) NOT NULL, source_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE res_mob_secured_by (id INT AUTO_INCREMENT NOT NULL, res_mob_id INT NOT NULL, secured_by_id INT NOT NULL, type VARCHAR(255) NOT NULL, others_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE res_mob_technical_assistance (id INT AUTO_INCREMENT NOT NULL, res_mob_id INT NOT NULL, particular_quantity INT NOT NULL, particular_type VARCHAR(255) NOT NULL, estimated_amount INT NOT NULL, source_name VARCHAR(255) NOT NULL, source_type VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE res_mob_cash');
        $this->addSql('DROP TABLE res_mob_materials');
        $this->addSql('DROP TABLE res_mob_secured_by');
        $this->addSql('DROP TABLE res_mob_technical_assistance');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
