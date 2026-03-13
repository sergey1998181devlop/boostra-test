<?php

use Phinx\Migration\AbstractMigration;

class AddIsHighlightedToMytickets extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_mytickets')) {
            $table = $this->table('s_mytickets');
            if (!$table->hasColumn('is_highlighted')) {
                $table->addColumn('is_highlighted', 'boolean', [
                    'default' => 0,
                    'null' => false,
                    'after' => 'direction_id',
                ])->update();
            }
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_mytickets')) {
            $table = $this->table('s_mytickets');
            if ($table->hasColumn('is_highlighted')) {
                $table->removeColumn('is_highlighted')->update();
            }
        }
    }
}
