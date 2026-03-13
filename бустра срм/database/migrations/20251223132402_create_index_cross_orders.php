<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateIndexCrossOrders extends AbstractMigration
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
        // Получаем объект таблицы
        $table = $this->table('s_cross_orders');

        $table->addIndex('parent_order_id', [
                'name' => 's_cross_orders_parent_order_id_index',
                'unique' => false, // обычный индекс, не уникальный
            ])
            ->addIndex('reason', [
                'name' => 's_cross_orders_reason_index',
                'unique' => false,
            ])
            ->update();
    }
}
