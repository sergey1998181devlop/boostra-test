<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSAbEventsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        if ($this->hasTable('s_ab_events')) {
            return;
        }

        $table = $this->table('s_ab_events', [
            'id'        => false,
            'primary_key' => ['id'],
            'engine'    => 'InnoDB',
            'encoding'  => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('id', 'integer', [
                'signed'   => false,
                'identity' => true,
                'null'     => false,
            ])
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('ab_key', 'string', [
                'limit' => 64,
                'null'  => false,
            ])
            ->addColumn('ab_group', 'enum', [
                'values' => ['control', 'test'],
                'null'   => false,
            ])
            ->addColumn('event', 'enum', [
                'values' => ['lk_open', 'shown', 'suppressed', 'click', 'paid'],
                'null'   => false,
            ])
            ->addColumn('meta', 'json', [
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['user_id'], [
                'name' => 'idx_user',
            ])
            ->addIndex(['ab_key', 'event', 'created_at'], [
                'name' => 'idx_key_event',
            ])
            ->addIndex(['user_id', 'ab_group'], [
                'name' => 'idx_user_group',
            ])
            ->create();
    }
}
