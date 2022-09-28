<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220928003658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('TRUNCATE TABLE social_marketing_activities');
        $this->addSql('ALTER TABLE quarters CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE social_marketing_activities CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE client_sessions CHANGE role role VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE clients CHANGE offense_category offense_category VARCHAR(3) NOT NULL');
        $this->addSql('ALTER TABLE id_support CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE offenses CHANGE type type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE phases CHANGE name name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE program_materials_development CHANGE utilized_for utilized_for VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE resource_facilitator_session CHANGE resource_facilitator_type resource_facilitator_type VARCHAR(3) NOT NULL');
        $this->addSql('ALTER TABLE resource_mobilization CHANGE category category VARCHAR(255) NOT NULL, CHANGE cash_source_type cash_source_type VARCHAR(255) NOT NULL, CHANGE material_source_type material_source_type VARCHAR(255) NOT NULL, CHANGE technical_assistance_type technical_assistance_type VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rj_related_restitutions CHANGE rj_group rj_group VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rjconduct_processes CHANGE rj_group rj_group VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rjrelated_activities CHANGE rj_group rj_group VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE sessions CHANGE period period VARCHAR(255) NOT NULL, CHANGE li_lo li_lo VARCHAR(255) NOT NULL, CHANGE role role VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE support_of_region_to_field_office CHANGE category category VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE status status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE social_marketing_activities CHANGE name name TEXT NOT NULL');
        $this->addSql('ALTER TABLE client_sessions CHANGE role role VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE clients CHANGE offense_category offense_category VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE id_support CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE offenses CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE phases CHANGE name name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE program_materials_development CHANGE utilized_for utilized_for VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_facilitator_session CHANGE resource_facilitator_type resource_facilitator_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE resource_mobilization CHANGE category category VARCHAR(255) DEFAULT NULL, CHANGE cash_source_type cash_source_type VARCHAR(255) DEFAULT NULL, CHANGE material_source_type material_source_type VARCHAR(255) DEFAULT NULL, CHANGE technical_assistance_type technical_assistance_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rj_related_restitutions CHANGE rj_group rj_group VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rjconduct_processes CHANGE rj_group rj_group VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rjrelated_activities CHANGE rj_group rj_group VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE sessions CHANGE period period VARCHAR(255) DEFAULT NULL, CHANGE li_lo li_lo VARCHAR(255) DEFAULT NULL, CHANGE role role VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE support_of_region_to_field_office CHANGE category category VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE volunteer_operations CHANGE status status VARCHAR(255) DEFAULT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
