<?php

use Phinx\Migration\AbstractMigration;

class AddOrganizationColumnsToManagerRelations extends AbstractMigration
{
    public function change(): void
    {
        $managerSchedule = $this->table('managers_schedule');
        if (!$managerSchedule->hasColumn('organization_id')) {
            $managerSchedule
                ->addColumn('organization_id', 'integer', [
                    'null' => true,
                    'after' => 'plus',
                    'default' => 6, // По умолчанию 'Аквариус' для обратной совместимости
                ])
                ->addIndex(['organization_id'], [
                    'name' => 'managers_schedule_organization_idx',
                ])
                ->update();
        }

        $managerCompany = $this->table('manager_company');
        if (!$managerCompany->hasColumn('organization_id')) {
            $managerCompany
                ->addColumn('organization_id', 'integer', [
                    'null' => true,
                    'after' => 'company',
                    'default' => 6, // По умолчанию 'Аквариус' для обратной совместимости
                ])
                ->addIndex(['organization_id', 'manager_id'], [
                    'name' => 'manager_company_organization_manager_idx',
                ])
                ->update();
        }
    }
}

