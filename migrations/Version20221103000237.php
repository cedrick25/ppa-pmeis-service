<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221103000237 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE volunteer_supervision_clients (volunteer_supervision_client_id INT AUTO_INCREMENT NOT NULL, volunteer_supervision_id INT NOT NULL, client_id INT NOT NULL, PRIMARY KEY(volunteer_supervision_client_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE volunteer_supervision_clients');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
