<?php

use Phinx\Migration\AbstractMigration;

class CreateRclTables extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            CREATE TABLE `rcl_contracts` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `organization_id` INT(11) NOT NULL,
                `user_id` INT(11) NOT NULL,
                `number` VARCHAR(16) DEFAULT NULL,
                `max_amount` INT(8) NOT NULL,
                `status` VARCHAR(24) NOT NULL,
                `uid` VARCHAR(40) DEFAULT NULL,
                `asp_code` VARCHAR(12) DEFAULT NULL,
                `date_create` DATE NOT NULL,
                `date_start` DATE NOT NULL,
                `date_end` DATE NOT NULL,
                `sent_onec` TINYINT(1) NOT NULL DEFAULT 0,
                `sent_onec_date` DATE DEFAULT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_status_sent_onec` (`status`, `sent_onec`),
                INDEX `idx_organization_id` (`organization_id`),
                INDEX `idx_user_id` (`user_id`),
                INDEX `idx_number` (`number`),
                INDEX `idx_date_create` (`date_create`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $this->execute("
            CREATE TABLE `rcl_tranches` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `order_id` INT(11) NOT NULL,
                `rcl_contract_id` INT(11) NOT NULL,
                PRIMARY KEY (`id`),
                INDEX `idx_order_contract` (`order_id`, `rcl_contract_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        $this->execute("DROP TABLE IF EXISTS `rcl_tranches`");
        $this->execute("DROP TABLE IF EXISTS `rcl_contracts`");
    }
}
