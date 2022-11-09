<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221109174247 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE capability_building_participants (id INT AUTO_INCREMENT NOT NULL, capability_building_id INT NOT NULL, personnel_name VARCHAR(255) NOT NULL, personnel_id INT NOT NULL, remarks VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE capability_building_participants');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
