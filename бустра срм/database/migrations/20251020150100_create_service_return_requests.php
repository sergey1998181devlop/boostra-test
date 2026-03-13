<?php

use Phinx\Migration\AbstractMigration;

/**
 * Миграция для функционала "Возврат по реквизитам"
 * - Создает таблицу банковских реквизитов клиентов
 * - Создает таблицу реестра заявок на возврат
 * - Добавляет новые типы транзакций в b2p_transactions
 */
class CreateServiceReturnRequests extends AbstractMigration
{
    public function up()
    {
        $requisites = $this->table('s_user_bank_requisites', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'comment' => 'Банковские реквизиты клиентов для возвратов'
        ]);
        
        $requisites->addColumn('id', 'integer', ['identity' => true])
              ->addColumn('user_id', 'integer', ['null' => false, 'comment' => 'ID пользователя'])
              ->addColumn('account_number', 'string', ['limit' => 20, 'null' => false, 'comment' => 'Номер счета (р/с)'])
              ->addColumn('bik', 'string', ['limit' => 9, 'null' => false, 'comment' => 'БИК банка'])
              ->addColumn('bank_name', 'string', ['limit' => 255, 'null' => false, 'comment' => 'Наименование банка'])
              ->addColumn('is_default', 'boolean', ['default' => 0, 'comment' => 'Флаг реквизитов по умолчанию'])
              ->addColumn('created', 'datetime', ['null' => false])
              ->addColumn('updated', 'datetime', ['null' => false])
              ->addIndex(['user_id'], ['name' => 'idx_user_id'])
              ->addIndex(['user_id', 'account_number', 'bik'], ['unique' => true, 'name' => 'unique_user_requisites'])
              ->create();

        $requests = $this->table('s_service_return_requests', [
            'id' => false,
            'primary_key' => 'id',
            'engine' => 'InnoDB',
            'encoding' => 'utf8',
            'comment' => 'Реестр заявок на возврат услуг по реквизитам'
        ]);
        
        $requests->addColumn('id', 'integer', ['identity' => true])
              ->addColumn('user_id', 'integer', ['null' => false])
              ->addColumn('order_id', 'integer', ['null' => false])
              ->addColumn('service_type', 'enum', [
                  'values' => ['credit_doctor', 'multipolis', 'tv_medical', 'star_oracle'],
                  'null' => false,
                  'comment' => 'Тип услуги'
              ])
              ->addColumn('service_pk', 'integer', ['null' => false, 'comment' => 'ID записи услуги в таблице услуги'])
              ->addColumn('operation_id', 'integer', ['null' => false, 'comment' => 'OperationID покупки услуги для отправки в 1С'])
              ->addColumn('amount', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false, 'comment' => 'Сумма возврата'])
              ->addColumn('requisites_id', 'integer', ['null' => true, 'comment' => 'FK на s_user_bank_requisites (если выбраны сохраненные)'])
              ->addColumn('requisites_payload', 'json', ['null' => false, 'comment' => 'JSON копия реквизитов на момент отправки'])
              ->addColumn('status', 'enum', [
                  'values' => ['sent', 'approved', 'rejected', 'error'],
                  'default' => 'approved',
                  'comment' => 'Статус заявки'
              ])
              ->addColumn('error_text', 'text', ['null' => true, 'comment' => 'Текст ошибки от 1С'])
              ->addColumn('manager_id', 'integer', ['null' => false, 'comment' => 'Менеджер, оформивший возврат'])
              ->addColumn('return_transaction_id', 'integer', ['null' => true, 'comment' => 'FK на b2p_transactions (фейковая транзакция для аудита)'])
              ->addColumn('created', 'datetime', ['null' => false])
              ->addColumn('updated', 'datetime', ['null' => false])
              ->addIndex(['user_id'], ['name' => 'idx_user_id'])
              ->addIndex(['order_id'], ['name' => 'idx_order_id'])
              ->addIndex(['status'], ['name' => 'idx_status'])
              ->addIndex(['service_type', 'service_pk'], ['name' => 'idx_service_lookup'])
              ->create();

        $this->execute("
            ALTER TABLE `b2p_transactions` 
            MODIFY COLUMN `type` ENUM(
                'REFUND_CREDIT_DOCTOR',
                'REFUND_MULTIPOLIS',
                'REFUND_TV_MEDICAL',
                'REFUND_STAR_ORACLE',
                'RECOMPENSE_CREDIT_DOCTOR',
                'RECOMPENSE_MULTIPOLIS',
                'RECOMPENSE_TV_MEDICAL',
                'RECOMPENSE_STAR_ORACLE',
                'REFUND_CREDIT_DOCTOR_REQUISITES',
                'REFUND_MULTIPOLIS_REQUISITES',
                'REFUND_TV_MEDICAL_REQUISITES',
                'REFUND_STAR_ORACLE_REQUISITES'
            ) NULL DEFAULT NULL
        ");
    }

    public function down()
    {
        $this->execute("
            ALTER TABLE `b2p_transactions` 
            MODIFY COLUMN `type` ENUM(
                'REFUND_CREDIT_DOCTOR',
                'REFUND_MULTIPOLIS',
                'REFUND_TV_MEDICAL',
                'REFUND_STAR_ORACLE',
                'RECOMPENSE_CREDIT_DOCTOR',
                'RECOMPENSE_MULTIPOLIS',
                'RECOMPENSE_TV_MEDICAL',
                'RECOMPENSE_STAR_ORACLE'
            ) NULL DEFAULT NULL
        ");

        $this->table('s_service_return_requests')->drop()->save();
        $this->table('s_user_bank_requisites')->drop()->save();
    }
}

