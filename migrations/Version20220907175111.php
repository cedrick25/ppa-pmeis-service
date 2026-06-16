<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220907175111 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audit_trail CHANGE audit_trail_id audit_trail_id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE audit_trail CHANGE audit_trail_id audit_trail_id INT AUTO_INCREMENT NOT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
