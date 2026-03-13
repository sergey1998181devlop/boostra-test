<?php

use Phinx\Migration\AbstractMigration;

class AddBankOfRussiaChannel extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO s_mytickets_channels (id, name, is_active)
            VALUES (8, 'Банк России', 1)
        ");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_mytickets_channels WHERE id = 8");
    }
}
