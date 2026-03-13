<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateYmLogsTable extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_ym_logs')) {
            return;
        }

        $table = $this->table('s_ym_logs', [
            'id'          => false,
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'encoding'    => 'utf8mb4',
            'collation'   => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('id', 'biginteger', [
                'signed'   => false,
                'identity' => true,
                'null'     => false,
            ])
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('ym_uid', 'biginteger', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['ym_uid'], [
                'name' => 's_ym_logs_ym_uid_index',
            ])
            ->addForeignKey('user_id', 's_users', 'id', [
                'constraint' => 's_ym_logs_s_users_id_fk',
                'delete'     => 'NO_ACTION',
                'update'     => 'NO_ACTION',
            ])
            ->create();
    }

    public function down(): void
    {
        $this->table('s_ym_logs')->drop()->save();
    }
}