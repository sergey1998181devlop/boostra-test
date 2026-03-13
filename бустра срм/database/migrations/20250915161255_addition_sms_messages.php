<?php

use Phinx\Migration\AbstractMigration;

final class AdditionSmsMessages extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_addition_sms_messages')) {
            $table = $this->table('s_addition_sms_messages');

            $changed = false;

            if (!$table->hasColumn('code')) {
                $table->addColumn('code', 'string', [
                    'limit' => 16,
                    'null' => false,
                    'comment' => 'Код подтверждения',
                ]);
                $changed = true;
            }

            if (!$table->hasIndex(['message_id', 'phone'])) {
                $table->addIndex(['message_id', 'phone'], ['unique' => true, 'name' => 'uniq_message_phone']);
                $changed = true;
            }
            if (!$table->hasIndex(['user_uid'])) {
                $table->addIndex(['user_uid'], ['name' => 'idx_user_uid']);
                $changed = true;
            }
            if (!$table->hasIndex(['phone'])) {
                $table->addIndex(['phone'], ['name' => 'idx_phone']);
                $changed = true;
            }
            if (!$table->hasIndex(['type'])) {
                $table->addIndex(['type'], ['name' => 'idx_type']);
                $changed = true;
            }

            if ($changed) {
                $table->update();
            }

            return;
        }

        // 2) Таблицы нет — создаём
        $table = $this->table('s_addition_sms_messages', [
            'id'         => false,
            'primary_key'=> ['id'],
            'engine'     => 'InnoDB',
            'encoding'   => 'utf8mb4',
            'collation'  => 'utf8mb4_unicode_ci',
        ]);

        $table
            ->addColumn('id', 'integer', [
                'identity' => true,
                'signed'   => false,
                'null'     => false,
            ])
            ->addColumn('message_id', 'string', [
                'limit'   => 64,
                'null'    => false,
                'comment' => 'ID SMS-сообщения',
            ])
            ->addColumn('phone', 'string', [
                'limit'   => 20,
                'null'    => false,
                'comment' => 'Номер телефона',
            ])
            ->addColumn('user_uid', 'string', [
                'limit'   => 40,
                'null'    => false,
                'comment' => 'UID пользователя',
            ])
            ->addColumn('type', 'string', [
                'limit'   => 32,
                'null'    => false,
                'comment' => 'Тип SMS (строкой)',
            ])
            ->addColumn('code', 'string', [
                'limit'   => 16,
                'null'    => false,
                'comment' => 'Код подтверждения',
            ])
            ->addColumn('used', 'boolean', [
                'default' => 0,
                'null'    => false,
                'comment' => 'Флаг: использован',
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null'    => false,
                'comment' => 'Создано',
            ])
            ->addColumn('updated_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'update'  => 'CURRENT_TIMESTAMP',
                'null'    => false,
                'comment' => 'Обновлено',
            ])
            ->addIndex(['message_id', 'phone'], ['unique' => true, 'name' => 'uniq_message_phone'])
            ->addIndex(['user_uid'], ['name' => 'idx_user_uid'])
            ->addIndex(['phone'], ['name' => 'idx_phone'])
            ->addIndex(['type'], ['name' => 'idx_type'])
            ->create();
    }

    public function down(): void
    {
        if ($this->hasTable('s_addition_sms_messages')) {
            $this->table('s_addition_sms_messages')->drop()->save();
        }
    }
}
