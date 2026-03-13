<?php

use Phinx\Migration\AbstractMigration;

class AddAutoRecalcToPdn extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_pdn_calculation')) {
            $table = $this->table('s_pdn_calculation');
            if (!$table->hasColumn('auto_recalc')) {
                $table->addColumn('auto_recalc', 'boolean', [
                    'default' => 0,
                    'null' => true,
                    'comment' => 'Пометить для автопересчета (например, при HTTP 3xx/5xx)'
                ])->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_pdn_calculation')) {
            $table = $this->table('s_pdn_calculation');
            if ($table->hasColumn('auto_recalc')) {
                $table->removeColumn('auto_recalc')->update();
            }
        }
    }
}