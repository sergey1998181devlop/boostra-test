<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddProlongationDayToB2pPayments extends AbstractMigration
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
        // 1. Добавляем поле prolongation_day в b2p_payments
        $this->table('b2p_payments')
            ->addColumn('prolongation_day', 'smallinteger', [
                'default' => 0,
                'after' => 'prolongation',
                'signed'  => false,
            ])
            ->update();
    }
}
