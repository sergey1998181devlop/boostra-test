<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCbRequests extends AbstractMigration
{
    public function up(): void
    {
        // Темы запросов ЦБ
        if (!$this->hasTable('s_cb_request_subjects')) {
            $table = $this->table('s_cb_request_subjects', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table
                ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
                ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
                ->create();

            // Начальные темы из ТЗ
            $subjects = [
                'Навязанный доп', 'Не вернули доп', 'Мошенничество', 'Срок охлаждения',
                'Рекурренты', 'ШКД', 'Иное', 'Бот', 'Не верное взаимодействие',
                'Не предоставлен ответ на запрос клиента', 'Более 5-ти пролонгаций',
                'Комиссия', 'Размер начисленных процентов', 'Не прислал заявление на возврат',
                'Не верно учтены средства при оплате по реквизитам', 'КК',
                'Жалоба на взыск', 'Переуступка прав требования', 'Перерасчет',
                'Расторжение договора', 'Самозапрет', 'Инстолмент', 'БКИ',
                'Более одного действующего займа', 'Автовыдача', 'Сумма больше запрашиваемой',
            ];

            $rows = [];
            foreach ($subjects as $name) {
                $rows[] = ['name' => $name, 'is_active' => true];
            }
            $this->table('s_cb_request_subjects')->insert($rows)->saveData();
        }

        // Основная таблица запросов ЦБ
        if (!$this->hasTable('s_cb_requests')) {
            $table = $this->table('s_cb_requests', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table
                ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('external_id', 'string', ['limit' => 36, 'null' => true, 'comment' => 'UUID запроса в парсере'])
                ->addColumn('request_number', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Номер запроса из ЛК ЦБ'])
                ->addColumn('organization_id', 'integer', ['null' => true, 'signed' => false, 'comment' => 'FK organizations'])
                ->addColumn('client_id', 'integer', ['null' => true, 'signed' => false, 'comment' => 'FK users'])
                ->addColumn('order_id', 'integer', ['null' => true, 'signed' => false, 'comment' => 'FK orders'])
                ->addColumn('message_text', 'text', ['null' => true, 'comment' => 'Основной текст запроса из ЛК ЦБ (заполняется парсером)'])
                ->addColumn('file_links', 'text', ['null' => true, 'comment' => 'JSON-массив S3-ссылок на файлы (заполняется парсером)'])
                ->addColumn('subject_id', 'integer', ['null' => true, 'signed' => false, 'comment' => 'FK cb_request_subjects'])
                ->addColumn('status_opr', 'boolean', ['null' => false, 'default' => false, 'comment' => 'Обработан ОПР'])
                ->addColumn('status_okk', 'boolean', ['null' => false, 'default' => false, 'comment' => 'Обработан ОКК'])
                ->addColumn('status_sent', 'boolean', ['null' => false, 'default' => false, 'comment' => 'Направлен ответ'])
                ->addColumn('response_deadline', 'date', ['null' => true, 'comment' => 'Срок ответа'])
                ->addColumn('request_after_opr', 'text', ['null' => true, 'comment' => 'Запрос после проработки ОПР'])
                ->addColumn('opr_contacted_client', 'boolean', ['null' => false, 'default' => false, 'comment' => 'ОПР взаимодействовал с клиентом до запроса'])
                ->addColumn('received_at', 'datetime', ['null' => true, 'comment' => 'Дата поступления из ЛК ЦБ'])
                ->addColumn('taken_at', 'datetime', ['null' => true, 'comment' => 'Взят в работу'])
                ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('updated_at', 'datetime', ['null' => true])
                ->addIndex(['external_id'], ['unique' => true])
                ->addIndex(['organization_id'])
                ->addIndex(['client_id'])
                ->addIndex(['order_id'])
                ->addIndex(['subject_id'])
                ->addIndex(['status_opr'])
                ->addIndex(['status_okk'])
                ->addIndex(['status_sent'])
                ->addIndex(['received_at'])
                ->addIndex(['response_deadline'])
                ->create();
        }

        // Комментарии к запросам
        if (!$this->hasTable('s_cb_request_comments')) {
            $table = $this->table('s_cb_request_comments', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table
                ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('request_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('manager_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('section', 'string', ['limit' => 50, 'null' => false, 'comment' => 'description, opr, okk, measures, lawyers'])
                ->addColumn('text', 'text', ['null' => false])
                ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['request_id'])
                ->addIndex(['manager_id'])
                ->create();
        }

        // История изменений запросов
        if (!$this->hasTable('s_cb_request_history')) {
            $table = $this->table('s_cb_request_history', [
                'id' => false,
                'primary_key' => ['id'],
                'engine' => 'InnoDB',
                'encoding' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]);
            $table
                ->addColumn('id', 'integer', ['null' => false, 'identity' => true, 'signed' => false])
                ->addColumn('request_id', 'integer', ['null' => false, 'signed' => false])
                ->addColumn('manager_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('action', 'string', ['limit' => 100, 'null' => false])
                ->addColumn('details', 'text', ['null' => true])
                ->addColumn('created_at', 'datetime', ['null' => false, 'default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['request_id'])
                ->create();
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_cb_request_history')) {
            $this->table('s_cb_request_history')->drop()->save();
        }
        if ($this->hasTable('s_cb_request_comments')) {
            $this->table('s_cb_request_comments')->drop()->save();
        }
        if ($this->hasTable('s_cb_requests')) {
            $this->table('s_cb_requests')->drop()->save();
        }
        if ($this->hasTable('s_cb_request_subjects')) {
            $this->table('s_cb_request_subjects')->drop()->save();
        }
    }
}
