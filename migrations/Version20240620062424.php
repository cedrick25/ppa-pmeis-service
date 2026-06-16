<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240620062424 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
      $currentDate = date("Y-m-d H:m:s");

      $this->addSql("INSERT INTO rjprocesses (name, created_at) VALUES 
          ('Pre-Encounter Activities', '$currentDate')"
      );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM rjprocesses WHERE name = 'Pre-Encounter Activities' ");
    }
}
