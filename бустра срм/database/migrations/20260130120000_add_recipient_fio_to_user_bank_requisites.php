<?php

use Phinx\Migration\AbstractMigration;

/**
 * Добавление поля ФИО получателя в реквизиты для возврата по реквизитам
 */
class AddRecipientFioToUserBankRequisites extends AbstractMigration
{
    public function up()
    {
        $this->table('s_user_bank_requisites')
            ->addColumn('recipient_fio', 'string', [
                'limit' => 255,
                'null' => true,
                'comment' => 'ФИО получателя перевода',
                'after' => 'bank_name',
            ])
            ->update();
    }

    public function down()
    {
        $this->table('s_user_bank_requisites')
            ->removeColumn('recipient_fio')
            ->update();
    }
}
