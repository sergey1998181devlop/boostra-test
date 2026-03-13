<?php

use Phinx\Migration\AbstractMigration;

class AddLowNbkiScoreReason extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT IGNORE INTO s_reasons (id, admin_name, client_name, type, maratory, refusal_note)
            VALUES (70, 'Низкий балл NBKI', 'Недостаточный кредитный рейтинг', 'reject', 1, null)
        ");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_reasons WHERE id = 70");
    }
}
