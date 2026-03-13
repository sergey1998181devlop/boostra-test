<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateVoxRobotCallsTable extends AbstractMigration
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
        $table = $this->table('s_vox_robot_calls', [
            'id'          => 'id',
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'encoding'    => 'utf8mb4',
            'collation'   => 'utf8mb4_unicode_ci',
            'signed'      => false,
        ]);

        $table->addColumn('user_id', 'integer', [
            'signed' => false,
            'null'   => false,
        ])
            ->addColumn('order_id', 'biginteger', [
                'signed' => false,
                'null'   => true,
            ])
            ->addColumn('client_phone', 'string', [
                'limit' => 30,
                'null'  => false,
            ])
            ->addColumn('vox_call_id', 'biginteger', [
                'null' => true,
            ])
            ->addColumn('status', 'integer', [
                'null' => true,
            ])
            ->addColumn('is_redirected_manager', 'boolean', [
                'null' => true,
            ])
            ->addColumn('type', 'string', [
                'limit' => 255,
                'null'  => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'null' => false,
            ])
            ->addColumn('updated_at', 'datetime', [
                'null' => true,
            ])
            // Индексы
            ->addIndex(['user_id'], ['name' => 'idx_user_id'])
            ->addIndex(['status'], ['name' => 'idx_status'])
            ->addIndex(['client_phone'], ['name' => 'idx_client_phone'])
            // Внешние ключи
            ->addForeignKey('user_id', 's_users', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'RESTRICT',
            ])
            ->addForeignKey('order_id', 's_orders', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'RESTRICT',
            ])
            ->create();
    }
}