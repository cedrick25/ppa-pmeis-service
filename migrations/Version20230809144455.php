<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230809144455 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_activities DROP activity_detail');
        $this->addSql('ALTER TABLE sessions ADD activity_detail VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_activities ADD activity_detail VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sessions DROP activity_detail');
    }
}
