<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
final class Version20230729034953 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rj_related_restitutions ADD created_by INT NOT NULL, CHANGE remarks remarks VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rjconduct_processes ADD created_by INT NOT NULL');
        $this->addSql('ALTER TABLE rjrelated_activities ADD created_by INT NOT NULL');;
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rj_related_restitutions DROP created_by, CHANGE remarks remarks VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rjconduct_processes DROP created_by');
        $this->addSql('ALTER TABLE rjrelated_activities DROP created_by');
    }
}
