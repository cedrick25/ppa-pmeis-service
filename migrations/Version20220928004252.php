<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220928004252 extends AbstractMigration
{

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE phases SET name = 'IV-Ongoing' WHERE phase_id = 4 ");
        $this->addSql("INSERT INTO phases (name, sort_order, created_at) VALUES ('Prep', 1, now())");
        $this->addSql("UPDATE phases SET sort_order = 2 WHERE name = 'I' ");
        $this->addSql("UPDATE phases SET sort_order = 3 WHERE name = 'II' ");
        $this->addSql("UPDATE phases SET sort_order = 4 WHERE name = 'III' ");
        $this->addSql("UPDATE phases SET sort_order = 5 WHERE name = 'IV-Ongoing' ");
        $this->addSql("INSERT INTO phases (name, sort_order, created_at) VALUES ('IV-Completed', 6, now())");
        $this->addSql("INSERT INTO session_activities (name,phase_id,is_community_service,is_tree_planting,is_cooperative_self_help,is_cooperative_self_help_activities,created_at)
                        VALUES ('St. Valentines Day', 1, false, false, false, false, now()),
                            ('Initial Implementation of Grant Denial of Probation', 1, false, false, false, false, now()),
                            ('Women s Role in History Month', 1, false, false, true, true, now()),
                            ('Month Fire Prevention Month', 1, false, false, true, true, now()),
                            ('Observance of Holy Week', 1, false, false, false, false, now()),
                            ('Araw ng kagitingan', 1, false, false, false, false, now()),
                            ('Philippines Earth Day', 1, true, false, false, false, now()),
                            ('Independence Day', 1, false, false, false, false, now()),
                            ('Philippines Arbor Day', 1, false, false, false, false, now()),
                            ('Philippines Environment Month', 1, false, false, false, false, now())");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE phases");
        $this->addSql("TRUNCATE TABLE session_activities");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
