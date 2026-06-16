<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220201140839 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE volunteer_operations CHANGE dropped_by dropped_by INT DEFAULT NULL');
        $this->addSql('ALTER TABLE volunteer_id CHANGE id_no id_no VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE volunteer_operations CHANGE dropped_by dropped_by INT NOT NULL');
        $this->addSql('ALTER TABLE volunteer_id CHANGE id_no id_no INT AUTO_INCREMENT NOT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
