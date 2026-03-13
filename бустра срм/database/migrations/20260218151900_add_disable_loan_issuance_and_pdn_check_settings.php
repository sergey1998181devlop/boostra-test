<?php

use Phinx\Migration\AbstractMigration;

class AddDisableLoanIssuanceAndPdnCheckSettings extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO s_settings (site_id, name, value)
            VALUES ('soyaplace', 'disable_loan_issuance', '0')
            ON DUPLICATE KEY UPDATE value = value
        ");

        $this->execute("
            INSERT INTO s_settings (site_id, name, value)
            VALUES ('soyaplace', 'disable_pdn_check', '1')
            ON DUPLICATE KEY UPDATE value = value
        ");
    }

    public function down(): void
    {
        $this->execute("
            DELETE FROM s_settings 
            WHERE name IN ('disable_loan_issuance', 'disable_pdn_check')
        ");
    }
}
