<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpdatePartnerApiLogsTable extends AbstractMigration
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
        $s_partner_api_logs = $this->table('s_partner_api_logs');
        if (!$s_partner_api_logs->hasColumn('phone_mobile')) {
            $s_partner_api_logs
                ->addColumn('phone_mobile', 'string', [
                    'null' => true,
                    'after' => 'partner',
                    'limit' => 32,
                ])
                ->addIndex(['phone_mobile'], [
                    'name' => 'phone_mobile_idx',
                ])
                ->update();
        }

        if (!$s_partner_api_logs->hasColumn('order_id')) {
            $s_partner_api_logs
                ->addColumn('order_id', 'integer', [
                    'null' => true,
                    'after' => 'phone_mobile',
                ])
                ->addIndex(['order_id'], [
                    'name' => 'order_id_idx',
                ])
                ->update();
        }
    }
}
