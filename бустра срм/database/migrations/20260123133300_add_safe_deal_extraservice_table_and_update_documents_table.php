<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSafeDealExtraserviceTableAndUpdateDocumentsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        // 1. Добавляем типы документов
        $documentTypes = [
            [
                'type' => 'OFFER_SAFE_DEAL',
                'template' => 'offer_safe_deal.tpl',
                'name' => 'Оферта на оказание услуги "Безопасная сделка"',
                'client_visible' => '1',
                'organization_id' => '17'
            ],
            [
                'type' => 'ORDER_FOR_EXECUTION_SAFE_DEAL',
                'template' => 'order_for_execution_safe_deal.tpl',
                'name' => 'Поручение об исполнении обязательства услуги "Безопасная сделка"',
                'client_visible' => '1',
                'organization_id' => '17'
            ],
            [
                'type' => 'REPORT_SAFE_DEAL',
                'template' => 'report_safe_deal.tpl',
                'name' => 'Отчет по проверке "Безопасная сделка"',
                'client_visible' => '1',
                'organization_id' => '17'
            ],
            [
                'type' => 'NOTIFICATION_SAFE_DEAL',
                'template' => 'notification_safe_deal.tpl',
                'name' => 'Уведомление "Безопасная сделка"',
                'client_visible' => '1',
                'organization_id' => '17'
            ],
            [
                'type' => 'CONTRACT_SAFE_DEAL',
                'template' => 'contract_safe_deal.tpl',
                'name' => 'Заявление о предоставлении услуги "Безопасная сделка"',
                'client_visible' => '1',
                'organization_id' => '17'
            ]
        ];

        $this->table('s_document_types')->insert($documentTypes)->saveData();

        // 2. Создаем таблицу s_safe_deal
        $safeDealTable = $this->table('s_safe_deal', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb3',
            'collation' => 'utf8mb3_general_ci',
            'comment' => 'Список купленных услуг Безопасная сделка'
        ]);

        $safeDealTable
            ->addColumn('id', 'integer', [
                'identity' => true,
                'signed' => false,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('order_id', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('amount', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('amount_total_returned', 'integer', [
                'signed' => false,
                'null' => false,
                'default' => 0,
                'comment' => 'total returned amount'
            ])
            ->addColumn('payment_method', 'string', [
                'limit' => 32,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('transaction_id', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('status', 'string', [
                'limit' => 32,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('organization_id', 'integer', [
                'signed' => false,
                'null' => false,
                'default' => 1,
            ])
            ->addColumn('return_sent', 'boolean', [
                'null' => false,
                'default' => false,
            ])
            ->addColumn('return_transaction_id', 'integer', [
                'signed' => false,
                'null' => false,
                'default' => 0,
            ])
            ->addColumn('return_status', 'boolean', [
                'null' => false,
                'default' => false,
            ])
            ->addColumn('return_date', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('return_amount', 'decimal', [
                'precision' => 10,
                'scale' => 2,
                'null' => false,
                'default' => 0.00,
            ])
            ->addColumn('return_by_user', 'boolean', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('return_by_manager_id', 'integer', [
                'signed' => false,
                'null' => true,
                'default' => null,
            ])
            ->addColumn('date_added', 'datetime', [
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('date_edit', 'datetime', [
                'null' => true,
                'default' => null,
            ])
            ->addColumn('dop1c_sent', 'boolean', [
                'null' => false,
                'default' => false,
                'comment' => 'отправлена ли доп услуга в доповую 1с'
            ])
            ->addColumn('dop1c_sent_return', 'boolean', [
                'null' => false,
                'default' => false,
                'comment' => 'отправлен ли возврат доп услуги в доповую 1с'
            ])
            ->addIndex(['dop1c_sent'], [
                'name' => 'dop1c_sent',
                'type' => 'btree',
            ])
            ->addIndex(['dop1c_sent_return'], [
                'name' => 'dop1c_sent_return',
                'type' => 'btree',
            ])
            ->addIndex(['return_date'], [
                'name' => 'return_date',
                'type' => 'btree',
            ])
            ->addIndex(['return_sent'], [
                'name' => 'return_sent',
                'type' => 'btree',
            ])
            ->addIndex(['return_status'], [
                'name' => 'return_status',
                'type' => 'btree',
            ])
            ->addIndex(['return_transaction_id'], [
                'name' => 'return_transaction_id',
                'type' => 'btree',
            ])
            ->addIndex(['order_id'], [
                'name' => 's_safe_deal_order_id_index',
                'type' => 'btree',
            ])
            ->addIndex(['payment_method'], [
                'name' => 's_safe_deal_payment_method_index',
                'type' => 'btree',
            ])
            ->addIndex(['status'], [
                'name' => 's_safe_deal_status_index',
                'type' => 'btree',
            ])
            ->addIndex(['transaction_id'], [
                'name' => 's_safe_deal_transaction_id_index',
                'type' => 'btree',
            ])
            ->addIndex(['user_id'], [
                'name' => 's_safe_deal_user_id_index',
                'type' => 'btree',
            ])
            ->create();

        // Добавляем триггер для автоматического обновления date_edit
        $this->execute("
            CREATE TRIGGER update_s_safe_deal_date_edit
            BEFORE UPDATE ON s_safe_deal
            FOR EACH ROW
            SET NEW.date_edit = CURRENT_TIMESTAMP
        ");

        // 3. Добавляем тип операции
        $operationTypes = [
            [
                'type' => 'SAFE_DEAL',
                'title' => 'Оплата безопасной сделки'
            ]
        ];

        $this->table('s_operation_types')->insert($operationTypes)->saveData();

        // 4. Изменяем тип столбца в b2p_transactions
        // Получаем текущее определение ENUM
        $result = $this->fetchRow("SHOW COLUMNS FROM b2p_transactions WHERE Field = 'type'");

        if ($result && isset($result['Type'])) {
            $currentType = $result['Type'];

            // Проверяем, содержит ли уже ENUM новые значения
            if (strpos($currentType, 'RECOMPENSE_SAFE_DEAL') === false) {
                // Если нет, добавляем новые значения к существующим
                $enumValues = $this->extractEnumValues($currentType);
                $newEnumValues = array_merge($enumValues, [
                    'RECOMPENSE_SAFE_DEAL',
                    'REFUND_SAFE_DEAL',
                    'REFUND_SAFE_DEAL_REQUISITES'
                ]);

                $this->execute(sprintf(
                    "ALTER TABLE b2p_transactions MODIFY COLUMN `type` ENUM('%s')",
                    implode("','", $newEnumValues)
                ));
            }
        }
    }

    /**
     * Метод для отката миграции
     */
    public function down(): void
    {
        // 1. Удаляем триггер
        $this->execute("DROP TRIGGER IF EXISTS update_s_safe_deal_date_edit");

        // 2. Удаляем таблицу s_safe_deal
        $this->table('s_safe_deal')->drop()->save();

        // 3. Удаляем добавленные типы операций
        $this->getQueryBuilder()
            ->delete('s_operation_types')
            ->where(['type' => 'SAFE_DEAL'])
            ->execute();

        // 4. Удаляем добавленные типы документов
        $typesToDelete = [
            'OFFER_SAFE_DEAL',
            'ORDER_FOR_EXECUTION_SAFE_DEAL',
            'REPORT_SAFE_DEAL',
            'NOTIFICATION_SAFE_DEAL',
            'CONTRACT_SAFE_DEAL'
        ];

        $this->getQueryBuilder()
            ->delete('s_document_types')
            ->where(['type IN' => $typesToDelete])
            ->execute();

        // 5. Восстанавливаем оригинальный ENUM в b2p_transactions
        // Получаем текущее определение ENUM
        $result = $this->fetchRow("SHOW COLUMNS FROM b2p_transactions WHERE Field = 'type'");

        if ($result && isset($result['Type'])) {
            $currentType = $result['Type'];
            $enumValues = $this->extractEnumValues($currentType);

            // Удаляем SAFE_DEAL значения из ENUM
            $safeDealValues = [
                'RECOMPENSE_SAFE_DEAL',
                'REFUND_SAFE_DEAL',
                'REFUND_SAFE_DEAL_REQUISITES'
            ];

            $originalValues = array_diff($enumValues, $safeDealValues);

            if (!empty($originalValues)) {
                $this->execute(sprintf(
                    "ALTER TABLE b2p_transactions MODIFY COLUMN `type` ENUM('%s')",
                    implode("','", $originalValues)
                ));
            }
        }
    }

    /**
     * Извлекает значения ENUM из строки определения столбца
     */
    private function extractEnumValues(string $columnType): array
    {
        // Пример строки: enum('value1','value2','value3')
        if (preg_match("/^enum\((.*)\)$/i", $columnType, $matches)) {
            $values = str_getcsv($matches[1], ",", "'");
            return array_filter($values, function($value) {
                return $value !== '';
            });
        }
        return [];
    }
}