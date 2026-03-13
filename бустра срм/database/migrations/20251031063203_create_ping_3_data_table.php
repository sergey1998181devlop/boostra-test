<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePing3DataTable extends AbstractMigration
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
        $phonePartnerApiTable = $this->table('s_partner_api_data', [
            'id' => true,
            'comment' => 'Информация о результатах проверок',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $phonePartnerApiTable
            ->addColumn('key_name', 'string', ['limit' => 512, 'comment' => 'Имя ключа'])
            ->addColumn('key_value', 'string', ['limit' => 512, 'comment' => 'Значение ключа'])
            ->addColumn('value', 'integer', ['comment' => 'Значение'])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['key_name', 'key_value'], ['unique' => true])
            ->create();
    }
}
