<?php

use Phinx\Migration\AbstractMigration;

final class AddIndexesForRefundExtraServicesReport extends AbstractMigration
{
    public function up(): void
    {

        $b2pTable = $this->table('b2p_transactions');

        if (!$b2pTable->hasIndex(['operation_date'])) {
            $b2pTable->addIndex(['operation_date'], ['name' => 'idx_operation_date']);
        }

        if (!$b2pTable->hasIndex(['type', 'reason_code'])) {
            $b2pTable->addIndex(['type', 'reason_code'], ['name' => 'idx_type_reason_code']);
        }

        if (!$b2pTable->hasIndex(['reference', 'type'])) {
            $b2pTable->addIndex(['reference', 'type'], ['name' => 'idx_reference_type']);
        }
        
        $b2pTable->save();

        $multipolisTable = $this->table('s_multipolis');
        if (!$multipolisTable->hasIndex(['payment_id', 'status'])) {
            $multipolisTable->addIndex(['payment_id', 'status'], ['name' => 'idx_payment_id_status']);
        }
        $multipolisTable->save();

        $tvMedicalTable = $this->table('s_tv_medical_payments');
        if (!$tvMedicalTable->hasIndex(['payment_id', 'status'])) {
            $tvMedicalTable->addIndex(['payment_id', 'status'], ['name' => 'idx_payment_id_status']);
        }
        $tvMedicalTable->save();

        $creditDoctorTable = $this->table('s_credit_doctor_to_user');
        if (!$creditDoctorTable->hasIndex(['transaction_id', 'status'])) {
            $creditDoctorTable->addIndex(['transaction_id', 'status'], ['name' => 'idx_transaction_id_status']);
        }
        $creditDoctorTable->save();

        $starOracleTable = $this->table('s_star_oracle');
        if (!$starOracleTable->hasIndex(['transaction_id', 'status'])) {
            $starOracleTable->addIndex(['transaction_id', 'status'], ['name' => 'idx_transaction_id_status']);
        }
        $starOracleTable->save();

        $this->execute('ANALYZE TABLE b2p_transactions');
        $this->execute('ANALYZE TABLE s_multipolis');
        $this->execute('ANALYZE TABLE s_tv_medical_payments');
        $this->execute('ANALYZE TABLE s_credit_doctor_to_user');
        $this->execute('ANALYZE TABLE s_star_oracle');
    }

    public function down(): void
    {
        $b2pTable = $this->table('b2p_transactions');
        
        if ($b2pTable->hasIndex(['operation_date'])) {
            $b2pTable->removeIndex(['operation_date']);
        }
        
        if ($b2pTable->hasIndex(['type', 'reason_code'])) {
            $b2pTable->removeIndex(['type', 'reason_code']);
        }
        
        if ($b2pTable->hasIndex(['reference', 'type'])) {
            $b2pTable->removeIndex(['reference', 'type']);
        }
        
        $b2pTable->save();

        $multipolisTable = $this->table('s_multipolis');
        if ($multipolisTable->hasIndex(['payment_id', 'status'])) {
            $multipolisTable->removeIndex(['payment_id', 'status']);
        }
        $multipolisTable->save();
        
        $tvMedicalTable = $this->table('s_tv_medical_payments');
        if ($tvMedicalTable->hasIndex(['payment_id', 'status'])) {
            $tvMedicalTable->removeIndex(['payment_id', 'status']);
        }
        $tvMedicalTable->save();
        
        $creditDoctorTable = $this->table('s_credit_doctor_to_user');
        if ($creditDoctorTable->hasIndex(['transaction_id', 'status'])) {
            $creditDoctorTable->removeIndex(['transaction_id', 'status']);
        }
        $creditDoctorTable->save();
        
        $starOracleTable = $this->table('s_star_oracle');
        if ($starOracleTable->hasIndex(['transaction_id', 'status'])) {
            $starOracleTable->removeIndex(['transaction_id', 'status']);
        }
        $starOracleTable->save();
    }
}

