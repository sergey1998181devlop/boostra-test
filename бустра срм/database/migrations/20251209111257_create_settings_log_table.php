<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSettingsLogTable extends AbstractMigration
{
    public function up(): void
    {
        $table = $this->table('s_settings_log', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $table->addColumn('id', 'biginteger', [
            'null' => false,
            'identity' => true,
        ])
        ->addColumn('manager_id', 'integer', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('setting_id', 'integer', [
            'null' => false,
        ])
        ->addColumn('old_value', 'text', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('new_value', 'text', [
            'null' => true,
            'default' => null,
        ])
        ->addColumn('date_added', 'datetime', [
            'null' => false,
            'default' => 'CURRENT_TIMESTAMP',
        ])
        ->addIndex(['setting_id'], [
            'name' => 'idx_setting_id'
        ])
        ->create();
    }

    public function down(): void
    {
        $this->table('s_settings_log')->drop()->save();
    }
}
