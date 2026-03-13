<?php

use Phinx\Migration\AbstractMigration;

class ChangeRclContractsDateFields extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `rcl_contracts` 
                MODIFY `date_create` DATETIME NOT NULL,
                MODIFY `sent_onec_date` DATETIME DEFAULT NULL
        ");

        $this->execute("ALTER TABLE `rcl_contracts` AUTO_INCREMENT = 1000003");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `rcl_contracts` 
                MODIFY `date_create` DATE NOT NULL,
                MODIFY `sent_onec_date` DATE DEFAULT NULL
        ");
    }
}