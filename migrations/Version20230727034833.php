<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230727034833 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");

        $this->addSql("INSERT INTO session_activities (name, created_at, phase_id, treatment_category_id) VALUES ('Special Activity', '$currentDate', null, 0)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DELETE FROM session_activities WHERE name = 'Special Activity' ");
    }
}
