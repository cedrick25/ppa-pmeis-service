<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230724040125 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_activities ADD treatment_category_id INT NOT NULL, DROP is_community_service, DROP is_tree_planting, DROP is_cooperative_self_help, DROP is_cooperative_self_help_activities');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE session_activities ADD is_community_service TINYINT(1) NOT NULL, ADD is_tree_planting TINYINT(1) NOT NULL, ADD is_cooperative_self_help TINYINT(1) NOT NULL, ADD is_cooperative_self_help_activities TINYINT(1) NOT NULL, DROP treatment_category_id');
    }
}
