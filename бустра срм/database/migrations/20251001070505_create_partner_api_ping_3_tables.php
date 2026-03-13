<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePartnerApiPing3Tables extends AbstractMigration
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
    public function change()
    {
        // Создание таблицы s_partner_api_logs
        $partnerApiLogs = $this->table('s_partner_api_logs', [
            'id' => false,
            'primary_key' => ['id'],
            'comment' => 'Лог запросов с апи партнеров',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $partnerApiLogs->addColumn('id', 'integer', [
            'identity' => true,
            'signed' => false,
            'null' => false,
        ])
            ->addColumn('action', 'string', [
                'limit' => 128,
                'null' => true,
                'comment' => 'Роут запроса'
            ])
            ->addColumn('request_uid', 'string', [
                'limit' => 128,
                'null' => true,
                'comment' => 'Uid запроса'
            ])
            ->addColumn('partner', 'string', [
                'limit' => 128,
                'null' => true,
                'comment' => 'Id партнера'
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true
            ])
            ->addIndex(['created_at', 'action'], [
                'name' => 's_partner_api_logs_created_at_action_index'
            ])
            ->addIndex(['partner'], [
                'name' => 's_partner_api_logs_partner_index'
            ])
            ->create();

        // Создание таблицы s_partner_api_users
        $partnerApiUsers = $this->table('s_partner_api_users', [
            'id' => false,
            'primary_key' => ['id'],
            'comment' => 'история проверки пользователей',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $partnerApiUsers->addColumn('id', 'integer', [
            'identity' => true,
            'signed' => false,
            'null' => false,
        ])
            ->addColumn('phone', 'string', [
                'limit' => 32,
                'null' => true
            ])
            ->addColumn('status', 'string', [
                'limit' => 128,
                'null' => true
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true
            ])
            ->addIndex(['status', 'created_at'], [
                'name' => 's_partner_api_users_status_created_at_index'
            ])
            ->addIndex(['phone'], [
                'name' => 's_partner_api_users_phone_index'
            ])
            ->create();

        // Создание таблицы s_partner_api_orders
        $partnerApiOrders = $this->table('s_partner_api_orders_log', [
            'id' => false,
            'primary_key' => ['id'],
            'comment' => 'история возврата статусов и заявок',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $partnerApiOrders->addColumn('id', 'integer', [
            'identity' => true,
            'signed' => false,
            'null' => false,
        ])
            ->addColumn('order_id', 'integer', [
                'null' => false
            ])
            ->addColumn('status', 'string', [
                'limit' => 128,
                'null' => true
            ])
            ->addColumn('created_at', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => true
            ])
            ->addIndex(['order_id', 'status'], [
                'name' => 's_partner_api_orders_log_order_id_status_index'
            ])
            ->create();
    }

    /**
     * Откат изменений
     * @return void
     */
    public function down(): void
    {
        // Удаление таблиц в обратном порядке (с учетом возможных внешних ключей)
        $this->table('s_partner_api_users')->drop()->save();
        $this->table('s_partner_api_orders_log')->drop()->save();
        $this->table('s_partner_api_logs')->drop()->save();
    }
}
