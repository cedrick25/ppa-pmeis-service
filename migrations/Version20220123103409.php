<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220123103409 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");

        $this->addSql("INSERT INTO rjprocess_status (name, created_at) VALUES "
            . "('Shelved', '$currentDate'),"
            . "('Deferred', '$currentDate'),"
            . "('On-Going', '$currentDate'),"
            . "('Completed', '$currentDate'),"
            . "('Agreement', '$currentDate'),"
            . "('Reached', '$currentDate')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE rjprocess_status");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
