<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220927162256 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_activities ADD phase_id INT DEFAULT NULL, ADD is_community_service TINYINT(1) NOT NULL, ADD is_tree_planting TINYINT(1) NOT NULL, ADD is_cooperative_self_help TINYINT(1) NOT NULL, ADD is_cooperative_self_help_activities TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_activities DROP phase_id, DROP is_community_service, DROP is_tree_planting, DROP is_cooperative_self_help, DROP is_cooperative_self_help_activities');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
