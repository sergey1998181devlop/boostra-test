<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePhoneApiTable extends AbstractMigration
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
        // Создание таблицы s_partner_api_users
        $phonePartnerApiTable = $this->table('s_phone_partner_api', [
            'id' => true,
            'comment' => 'История принятия телефонов по апи',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $phonePartnerApiTable
            ->addColumn('phone', 'string', ['limit' => 32])
            ->addColumn('client_type', 'string', ['limit' => 32, 'comment' => 'Тип клиента после проверки'])
            ->addColumn('cron_status', 'string', ['limit' => 32, 'comment' => 'Статус выполнения cron'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('cron_finished_at', 'datetime', ['null' => true, 'comment' => 'Дата завершения задачи по крону'])
            ->addIndex(['phone'], ['unique' => true])
            ->addIndex(['cron_status'])
            ->create();
    }
}
