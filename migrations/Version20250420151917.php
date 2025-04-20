<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250420151917 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
      $this->addSql('ALTER TABLE vpa_association_initiated_activities DROP volunteer_id, DROP role');
    }

    public function down(Schema $schema): void
    {
      $this->addSql('ALTER TABLE vpa_association_initiated_activities ADD volunteer_id INT NOT NULL, ADD role VARCHAR(255) NOT NULL');
    }
}
