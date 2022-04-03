<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211101203019 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE resource_facilitator_session (resource_facilitator_session_id INT AUTO_INCREMENT NOT NULL, session_id INT NOT NULL, resource_facilitator_id INT NOT NULL, resource_facilitator_type enum(\'PPO\', \'VPA\', \'ERP\'), role text NULL, PRIMARY KEY(resource_facilitator_session_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE resource_facilitator_session');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
