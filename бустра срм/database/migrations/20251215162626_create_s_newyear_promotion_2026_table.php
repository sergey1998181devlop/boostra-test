<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSNewyearPromotion2026Table extends AbstractMigration
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
        if ($this->hasTable('s_newyear_promotion_2026')) {
            return;
        }

        $table = $this->table('s_newyear_promotion_2026', [
            'id'          => 'id',
            'primary_key' => ['id'],
            'engine'      => 'InnoDB',
            'encoding'    => 'utf8mb4',
            'collation'   => 'utf8mb4_unicode_ci',
            'signed'      => false,
        ]);

        $table
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('order_id', 'integer', [
                'signed' => false,
                'null'   => false,
            ])
            ->addColumn('bucket', 'string', [
                'limit' => 20,
                'null'  => false,
            ])
            ->addColumn('send_date', 'string', [
                'limit' => 8,
                'null'  => false,
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
            ])
            ->addIndex(['user_id', 'order_id'], [
                'unique' => true,
                'name'   => 'user_order',
            ])
            ->addIndex(['bucket', 'send_date'], [
                'name' => 'idx_bucket_date',
            ])
            ->addIndex(['created_at'], [
                'name' => 'idx_created',
            ])
            ->create();
    }
}
