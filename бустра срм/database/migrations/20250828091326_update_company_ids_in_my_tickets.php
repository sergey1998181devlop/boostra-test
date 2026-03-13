<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdateCompanyIdsInMyTickets extends AbstractMigration
{
    public function up(): void
    {
        $this->updateCompanyIds([
            '4'  => '1',
            '5'  => '6',
            '6'  => '7',
            '7'  => '11',
            '8'  => '15',
            '9'  => '12',
            '10' => '14',
            '11' => '13',
        ]);
    }

    public function down(): void
    {
        $this->updateCompanyIds([
            '1'  => '4',
            '6'  => '5',
            '7'  => '6',
            '11' => '7',
            '15' => '8',
            '12' => '9',
            '14' => '10',
            '13' => '11',
        ]);
    }

    private function updateCompanyIds(array $mapping): void
    {
        $caseSql = "CASE company_id\n";
        foreach ($mapping as $oldId => $newId) {
            $caseSql .= "    WHEN $oldId THEN $newId\n";
        }
        $caseSql .= "    ELSE company_id END";

        $ids = implode(',', array_keys($mapping));
        $this->execute("UPDATE s_mytickets SET company_id = $caseSql WHERE company_id IN ($ids)");
    }
}
