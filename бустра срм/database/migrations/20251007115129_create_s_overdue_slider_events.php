<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSOverdueSliderEvents extends AbstractMigration
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
        $table = $this->table('s_overdue_slider_events', [
            'id'         => false,
            'primary_key'=> 'id',
            'engine'     => 'InnoDB',
            'encoding'   => 'utf8mb4',
            'collation'  => 'utf8mb4_general_ci',
        ]);

        $table
            ->addColumn('id',           'biginteger', ['identity' => true, 'signed' => false])
            ->addColumn('created_at',   'datetime',   ['null' => false])
            ->addColumn('user_id',      'integer',    ['null' => false, 'signed' => false])
            ->addColumn('order_id',     'integer',    ['null' => false, 'signed' => false])
            ->addColumn('nk_pk',        'enum',       ['values' => ['НК','ПК'], 'default' => 'НК'])
            ->addColumn('action',       'enum',       ['values' => ['slider_first','info_click','paid_after'], 'null' => false])
            ->addColumn('overdue_day',  'integer',    ['null' => true, 'signed' => false]) // 0..31, для paid_after может быть NULL
            ->addColumn('meta',         'json',       ['null' => true])

            ->addIndex(['user_id','created_at'],  ['name' => 'idx_user_time'])
            ->addIndex(['order_id','created_at'], ['name' => 'idx_order_time'])
            ->addIndex(['action','created_at'],   ['name' => 'idx_action_time'])
            ->addIndex(['user_id','order_id'],   ['name' => 'idx_user_id_order_id'])
            ->create();
    }
}
