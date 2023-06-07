<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230607195201 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $positions = [
            'SYSTEM ADMINISTRATOR', 'CENTRAL OFFICE', 'REGIONAL OFFICE CLERK', 'FIELD OFFICER', 'FIELD OFFICE CLERK',
            'REGIONAL DIRECTOR', 'ASSISTANT REGIONAL DIRECTOR', 'ADMINISTRATOR', 'DEPUTY ADMINISTRATOR', 'CMRU (REGIONAL OFFICE)',
            'ADMINISTRATIVE AIDE I', 'ADMINISTRATIVE AIDE II', 'ADMINISTRATIVE AIDE III', 'ADMINISTRATIVE AIDE IV',
            'ADMINISTRATIVE AIDE IV  /IT OFFICER', 'ADMINISTRATIVE AIDE V', 'ADMINISTRATIVE AIDE VI', 'ADMINISTRATIVE AIDE VI / ACTING PROPERTY OFFICER',
            'ADMINISTRATIVE ASSISTANT I', 'ADMINISTRATIVE ASSISTANT II', 'ADMINISTRATIVE ASSISTANT II / IT PERSONNEL', 'ADMINISTRATIVE ASSISTANT III',
            'ADMINISTRATIVE OFFICER I', 'ADMINISTRATIVE OFFICER I / IT', 'ADMINISTRATIVE OFFICER II', 'ADMINISTRATIVE OFFICER II / BUDGET OFFICER',
            'ADMINISTRATIVE OFFICER IV', 'ADMINISTRATIVE OFFICER V', 'ASSISTANT REGIONAL DIRECTOR / ROIC',
            'ATTORNEY V', 'CHIEF ADMINISTRATIVE OFFICER', 'CHIEF PROBATION OFFICER', 'CHIEF PROBATION OFFICER / ARD-OIC','CHIEF PROBATION OFFICER / CMRU',
            'CLERK ACCOUNT', 'CMRU', 'CMRU ADMINISTRATIVE OFFICER IV', 'CMRU HEAD', 'CPPO OIC ACCOUNT', 'CPPO / CLERK ACCOUNT', 'CPPO / FIELD OFFICER ACCOUNT',
            'EXECUTIVE ASSISTANT II', 'FIELD OFFICER ACCOUNT', 'LEGAL ASSISTANT I', 'LEGAL ASSISTANT II', 'OFFICER ACCOUNT', 'OIC - ADMINISTRATOR',
            'OIC - ARD ACCOUNT', 'OIC - CPPO', 'OIC - CPPO ACCOUNT', 'OIC - DEPUTY DIRECTOR', 'PHOTOGRAPHER 1', 'PLANNING OFFICER I', 'PLANNING OFFICER III',
            'PLANNING OFFICER V', 'PO / CLERK ACCOUNT', 'PROBATION OFFICER I', 'PROBATION OFFICER I / CSU HEAD', 'PROBATION OFFICER II', 'PROBATION OFFICER II / CMRU',
            'SENIOR PROBATION OFFICER', 'SUPERVISING PROBATION OFFICER', 'SUPERVISING PROBATION OFFICER / CPPO OIC', 'SUPERVISING PROBATION OFFICER / CMRU HEAD',
            'ACCOUNTANT', 'ACCOUNTANT I', 'ACCOUNTANT II', 'ACCOUNTANT III', 'TECHNICAL PERSON', 'CMRD Analyst','PLANNING OFFICER II',
        ];
        
        $this->addSql($this->buildSql($positions));
    }

    public function down(Schema $schema): void
    {
    
    }

    /**
     * @param string[] $data
     * @return string
     */
    private function buildSql(array $data): string
    {
        $currentDate = date("Y-m-d H:m:s");
        $sql = "INSERT INTO `position` (name, created_at) VALUES ";
        foreach ($data as $item) {
            $sql .= "('{$item}', '{$currentDate}'),";
        }
        return rtrim($sql, ',');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
