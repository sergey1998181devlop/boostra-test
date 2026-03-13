<?php

use Phinx\Migration\AbstractMigration;

final class AddOrganizationIdToMissedCalls extends AbstractMigration
{
    /**
     * Добавление organization_id в таблицу missed_calls
     * Это необходимо для поддержки перезвона для разных МКК
     */
    public function change(): void
    {
        $table = $this->table('missed_calls');
        
        if (!$table->hasColumn('organization_id')) {
            $table
                ->addColumn('organization_id', 'integer', [
                    'null' => true,
                    'default' => 6,
                    'after' => 'robo_number',
                ])
                ->addIndex(['organization_id', 'created'], [
                    'name' => 'idx_missed_calls_organization_created',
                ])
                ->update();
        }
    }
}

