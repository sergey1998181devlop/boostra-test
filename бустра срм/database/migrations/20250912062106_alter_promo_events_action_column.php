<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AlterPromoEventsActionColumn extends AbstractMigration
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
        $this->table('promo_events')
            ->changeColumn('action', 'string', [
                'limit'     => 191,
                'null'      => false,
                'encoding'  => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ])
            ->update();
    }
}
