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
        // password: 1234567890
        // $password = '$2y$13$XECx8Q7xtXFtJ7KvEbNYyO28m7YmUSiz0NhBNzgUtRI7Bt5dM2t5G';
        // password: 123
        $password = '$2y$13$2N7Gd8k0P44.5iMj3/eDgeHn3Tg0DhvitLXirTmALisOEDV.ZAyXy';
        $this->addSql(
            "INSERT INTO user_account "
                . "(email_address, contact_number, password, user_type, field_office_id, status)"
            . "VALUES "
                . "('nd@probation.gov.ph', '09884522345', '{$password}', 'ND', 1, 1),"
                . "('rd@probation.gov.ph', '09884522345', '{$password}', 'RD', 1, 1),"
                . "('fo@probation.gov.ph', '09884522345', '{$password}', 'FO', 1, 1)"
        );
        $this->addSql(
            "INSERT INTO user_details (user_account_id, first_name, last_name, gender, date_of_birth, is_senior_citizen, is_pwd, position_id)" 
                . "VALUES "
                    ."(1, 'Simon', 'Dela Cruz', 'M', '{$birthDate}', false, false, 1),"
                    ."(2, 'Pedro', 'Dela Cruz', 'M', '{$birthDate}', false, false, 1),"
                    ."(3, 'Juan', 'Dela Cruz', 'M', '{$birthDate}', false, false, 1)"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DELETE FROM user_details where user_detail_id = 1');
        $this->addSql('DELETE FROM user_account where user_account_id = 1');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
