<?php

use Phinx\Migration\AbstractMigration;

class AddHighPdnReason extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT IGNORE INTO s_reasons (id, admin_name, client_name, type, maratory, refusal_note)
            VALUES (69, 'Высокий ПДН', '-', 'reject', 0, null)
        ");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_reasons WHERE id = 69");
    }
}
