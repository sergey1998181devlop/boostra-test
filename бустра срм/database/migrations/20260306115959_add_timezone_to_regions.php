<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTimezoneToRegions extends AbstractMigration
{
    public function up(): void
    {
        if (!$this->hasTable('regions')) {
            return;
        }

        $table = $this->table('regions');

        if (!$table->hasColumn('timezone')) {
            $table->addColumn('timezone', 'string', [
                'limit'   => 64,
                'null'    => true,
                'default' => null,
                'after'   => 'name',
            ])->update();
        }
    }

    public function down(): void
    {
        if (!$this->hasTable('regions')) {
            return;
        }

        $table = $this->table('regions');

        if ($table->hasColumn('timezone')) {
            $table->removeColumn('timezone')->update();
        }
    }
}
