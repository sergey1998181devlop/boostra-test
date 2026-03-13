<?php

use Phinx\Migration\AbstractMigration;

class AddFinalPdnBeforeIssuanceToPdnCalculation extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_pdn_calculation')) {
            $table = $this->table('s_pdn_calculation');
            if (!$table->hasColumn('final_pdn_before_issuance')) {
                $table->addColumn('final_pdn_before_issuance', 'boolean', [
                    'default' => 0,
                    'null' => false,
                    'after' => 'auto_recalc',
                ])->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_pdn_calculation')) {
            $table = $this->table('s_pdn_calculation');
            if ($table->hasColumn('final_pdn_before_issuance')) {
                $table->removeColumn('final_pdn_before_issuance')->update();
            }
        }
    }
}
