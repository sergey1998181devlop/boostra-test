<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCrossOrdersTable extends AbstractMigration
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
        $table = $this->table('s_cross_orders', [
            'id'          => 'id',
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'encoding'    => 'utf8mb3',
            'collation'   => 'utf8mb3_general_ci',
            'signed'      => false,
        ]);

        $table->addColumn('parent_order_id', 'integer', [
            'signed' => false,
            'null'   => true,
            'comment'=> 'Родительская заявка для создания кросс-ордера',
        ])
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null'   => true,
            ])
            ->addColumn('status', 'string', [
                'limit'  => 32,
                'null'   => true,
                'default'=> 'NEW',
            ])
            ->addColumn('reason', 'string', [
                'limit' => 255,
                'null'  => true,
            ])
            ->addColumn('date_added', 'datetime', [
                'null'    => true,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('date_cron', 'datetime', [
                'null'    => true,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('date_edit', 'datetime', [
                'null'    => true,
                'default' => null,
                'update'  => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['parent_order_id'], [
                'name' => 's_cross_orders_parent_order_id_index',
            ])
            ->addIndex(['user_id'], [
                'name' => 's_cross_orders_user_id_index',
            ])
            ->addIndex(['status'], [
                'name' => 's_cross_orders_status_index',
            ])
            ->addIndex(['date_cron'], [
                'name' => 's_cross_orders_date_cron_index',
            ])
            ->create();
    }
}