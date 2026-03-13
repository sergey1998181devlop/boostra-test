<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSNewyearPromotion2026EventsTable extends AbstractMigration
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
        if ($this->hasTable('s_newyear_promotion_2026_events')) {
            return;
        }

        $table = $this->table('s_newyear_promotion_2026_events', [
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
            ->addColumn('promotion_id', 'integer', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('order_id', 'integer', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('event', 'string', [
                'limit' => 64,
                'null'  => false,
            ])
            ->addColumn('meta', 'json', [
                'null' => true,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['promotion_id'], [
                'name' => 'idx_promotion',
            ])
            ->addIndex(['user_id', 'order_id'], [
                'name' => 'idx_user_order',
            ])
            ->addIndex(['event', 'created_at'], [
                'name' => 'idx_event_created',
            ])
            ->addIndex(['created_at'], [
                'name' => 'idx_created',
            ])
            ->addForeignKey('promotion_id', 's_newyear_promotion_2026', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
                'constraint' => 'fk_promotion_events',
            ])
            ->create();
    }
}
