<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220129190456 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");

        $this->addSql("INSERT INTO payment_forms (name, created_at) VALUES "
            . "('Cash', '$currentDate'),"
            . "('Check', '$currentDate'),"
            . "('Transfer', '$currentDate'),"
            . "('Services', '$currentDate')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE payment_forms");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
