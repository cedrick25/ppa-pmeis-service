<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211018203300 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $birthDate = date("Y-m-d H:m:s");
        // Note: Use symfony console security:hash-password command to generate hashed password
        $password = '$2y$13$XECx8Q7xtXFtJ7KvEbNYyO28m7YmUSiz0NhBNzgUtRI7Bt5dM2t5G';
        $this->addSql("INSERT INTO user_account (email_address, contact_number, password, user_type, field_office_id, region_id, status)" .
                    "VALUES ('juan.delcruz@test.com', '09884522345', '{$password}', 'RD', 1, 1, 1)");
        $this->addSql("INSERT INTO user_details (user_account_id, first_name, last_name, gender, date_of_birth, is_senior_citizen, is_pwd)" .
                    "VALUES (1, 'Juan', 'Dela Cruz', 'M', '{$birthDate}', false, false)");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user_details where user_detail_id = 1');
        $this->addSql('DELETE FROM user_account where user_account_id = 1');
    }
}
