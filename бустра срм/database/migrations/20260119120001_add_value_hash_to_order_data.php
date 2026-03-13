<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddValueHashToOrderData extends AbstractMigration
{
    public function up(): void
    {
        // Добавляем колонку value_hash с онлайн DDL (без блокировки таблицы)
        $this->execute("
            ALTER TABLE s_order_data
            ADD COLUMN value_hash BINARY(16) NULL,
            ALGORITHM=INSTANT;
        ");

        // Создаём составной индекс на (key, value_hash)
        $this->execute("
            ALTER TABLE s_order_data
            ADD INDEX key_value_hash_idx (`key`, value_hash),
            ALGORITHM=INPLACE,
            LOCK=NONE;
        ");
    }

    public function down(): void
    {
        // Удаляем индекс
        $this->execute("DROP INDEX key_value_hash_idx ON s_order_data");

        // Удаляем колонку
        $this->execute("
            ALTER TABLE s_order_data
            DROP COLUMN value_hash,
            ALGORITHM=INPLACE,
            LOCK=NONE
        ");
    }
}
