<?php

use Phinx\Migration\AbstractMigration;

final class AddAgreementJsonIndexesToMytickets extends AbstractMigration
{
    public function up(): void
    {
        $this->execute(
            "ALTER TABLE s_mytickets 
             ADD INDEX idx_source_ticket_id ((CAST(JSON_UNQUOTE(JSON_EXTRACT(data, '$.source_ticket_id')) AS UNSIGNED)))"
        );
        
        $this->execute(
            "ALTER TABLE s_mytickets 
             ADD INDEX idx_agreement_copy ((CAST(COALESCE(JSON_EXTRACT(data, '$.agreement_copy'), 0) AS UNSIGNED)))"
        );
    }

    public function down(): void
    {
        $this->execute("ALTER TABLE s_mytickets DROP INDEX idx_source_ticket_id");
        $this->execute("ALTER TABLE s_mytickets DROP INDEX idx_agreement_copy");
    }
}

