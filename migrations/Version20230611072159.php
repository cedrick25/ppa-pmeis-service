<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230611072159 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_of_region_to_field_office CHANGE attributable_cost attributable_cost VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_of_region_to_field_office CHANGE attributable_cost attributable_cost VARCHAR(255) NOT NULL');
    }
}
