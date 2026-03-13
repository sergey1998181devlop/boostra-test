<?php

use Phinx\Migration\AbstractMigration;

class AddPskAndPdnToRclContracts extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            ALTER TABLE `rcl_contracts`
                ADD COLUMN `psk` DECIMAL(10,4) DEFAULT NULL AFTER `max_amount`,
                ADD COLUMN `psk_rub` DECIMAL(12,2) DEFAULT NULL AFTER `psk`,
                ADD COLUMN `pdn_calculation_id` INT(11) DEFAULT NULL AFTER `psk_rub`,
                ADD COLUMN `without_ch` TINYINT(1) DEFAULT NULL AFTER `pdn_calculation_id`
        ");
    }

    public function down(): void
    {
        $this->execute("
            ALTER TABLE `rcl_contracts`
                DROP COLUMN `psk`,
                DROP COLUMN `psk_rub`,
                DROP COLUMN `pdn_calculation_id`,
                DROP COLUMN `without_ch`
        ");
    }
}
