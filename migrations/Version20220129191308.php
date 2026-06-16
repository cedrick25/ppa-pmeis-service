<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220129191308 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $currentDate = date("Y-m-d H:m:s");

        $this->addSql("INSERT INTO payment_modes (name, created_at) VALUES "
            . "('Direct Payment', '$currentDate'),"
            . "('Payment Thru Deposit', '$currentDate'),"
            . "('Payment By Consignation', '$currentDate'),"
            . "('Payment Thru Proper Office', '$currentDate')"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("TRUNCATE TABLE payment_modes");
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
