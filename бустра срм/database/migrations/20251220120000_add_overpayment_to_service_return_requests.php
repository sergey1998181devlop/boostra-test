<?php

use Phinx\Migration\AbstractMigration;

class AddOverpaymentToServiceReturnRequests extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `s_service_return_requests` 
            MODIFY COLUMN `service_type` ENUM(
                'credit_doctor',
                'multipolis',
                'tv_medical',
                'star_oracle',
                'overpayment'
            ) NOT NULL COMMENT 'Тип услуги/переплаты'
        ");

        $this->execute("
            ALTER TABLE `s_service_return_requests` 
            MODIFY COLUMN `operation_id` BIGINT UNSIGNED NULL 
            COMMENT 'OperationID покупки услуги (NULL для переплат)'
        ");

        $this->execute("
            ALTER TABLE `b2p_transactions` 
            MODIFY COLUMN `type` ENUM(
                'REFUND_CREDIT_DOCTOR',
                'REFUND_MULTIPOLIS',
                'REFUND_TV_MEDICAL',
                'REFUND_STAR_ORACLE',
                'RECOMPENSE_CREDIT_DOCTOR',
                'RECOMPENSE_MULTIPOLIS',
                'RECOMPENSE_TV_MEDICAL',
                'RECOMPENSE_STAR_ORACLE',
                'REFUND_CREDIT_DOCTOR_REQUISITES',
                'REFUND_MULTIPOLIS_REQUISITES',
                'REFUND_TV_MEDICAL_REQUISITES',
                'REFUND_STAR_ORACLE_REQUISITES',
                'REFUND_OVERPAYMENT_REQUISITES'
            ) NULL DEFAULT NULL
        ");
    }

    public function down()
    {
        $this->execute("
            ALTER TABLE `s_service_return_requests` 
            MODIFY COLUMN `service_type` ENUM(
                'credit_doctor',
                'multipolis',
                'tv_medical',
                'star_oracle'
            ) NOT NULL COMMENT 'Тип услуги'
        ");

        $this->execute("
            ALTER TABLE `s_service_return_requests` 
            MODIFY COLUMN `operation_id` BIGINT UNSIGNED NOT NULL 
            COMMENT 'OperationID покупки услуги для отправки в 1С'
        ");

        $this->execute("
            ALTER TABLE `b2p_transactions` 
            MODIFY COLUMN `type` ENUM(
                'REFUND_CREDIT_DOCTOR',
                'REFUND_MULTIPOLIS',
                'REFUND_TV_MEDICAL',
                'REFUND_STAR_ORACLE',
                'RECOMPENSE_CREDIT_DOCTOR',
                'RECOMPENSE_MULTIPOLIS',
                'RECOMPENSE_TV_MEDICAL',
                'RECOMPENSE_STAR_ORACLE',
                'REFUND_CREDIT_DOCTOR_REQUISITES',
                'REFUND_MULTIPOLIS_REQUISITES',
                'REFUND_TV_MEDICAL_REQUISITES',
                'REFUND_STAR_ORACLE_REQUISITES'
            ) NULL DEFAULT NULL
        ");
    }
}

