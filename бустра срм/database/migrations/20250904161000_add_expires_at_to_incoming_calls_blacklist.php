<?php

use Phinx\Migration\AbstractMigration;

final class AddExpiresAtToIncomingCallsBlacklist extends AbstractMigration
{
    public function up(): void
    {
        if ($this->hasTable('s_incoming_calls_blacklist')) {
            $table = $this->table('s_incoming_calls_blacklist');

            if (!$table->hasColumn('expires_at')) {
                $table->addColumn('expires_at', 'datetime', [
                    'null' => true,
                    'after' => 'last_call_date',
                ]);
            }

            if (!$table->hasIndex(['is_active', 'expires_at'])) {
                $table->addIndex(['is_active', 'expires_at']);
            }

            $table->update();

            $this->execute("
                UPDATE s_incoming_calls_blacklist
                SET expires_at = DATE_ADD(created_at, INTERVAL 24 HOUR)
                WHERE is_active = 1 AND (expires_at IS NULL OR expires_at < created_at)
            ");
        }
    }

    public function down(): void
    {
        if ($this->hasTable('s_incoming_calls_blacklist')) {
            $table = $this->table('s_incoming_calls_blacklist');

            if ($table->hasIndex(['is_active', 'expires_at'])) {
                $table->removeIndex(['is_active', 'expires_at']);
            }

            if ($table->hasColumn('expires_at')) {
                $table->removeColumn('expires_at');
            }

            $table->update();
        }
    }
}


