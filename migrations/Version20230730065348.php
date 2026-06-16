<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230730065348 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rjconduct_processes DROP client_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rjconduct_processes ADD client_id INT NOT NULL');
    }
}
