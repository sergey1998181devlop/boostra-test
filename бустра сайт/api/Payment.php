<?php

/**
 * Simpla CMS
 *
 * @copyright    2011 Denis Pikusov
 * @link        http://simplacms.ru
 * @author        Denis Pikusov
 *
 */

require_once('Simpla.php');

class Payment extends Simpla {
    public function getHasCancelledPaymentRs(int $userId): bool {
        $this->db->query("
        SELECT contract_id
        FROM __payments_rs
        WHERE user_id = ? AND status = ?
        ORDER BY id DESC
        LIMIT 1",
            $userId, 'cancelled'
        );
        $contractId = $this->db->result('contract_id');
        $contract = $this->contracts->get_contract_by_params(['id' => $contractId]);

        if (empty($contractId) || empty($contract->number)) {
            return false;
        }

        $userBalance = $this->users->get_user_balance($userId, ['zaim_number' => $contract->number]);

        if (!$userBalance || $userBalance->ostatok_od == 0) {
            return false;
        }

        return true;
    }

    public function getPaymentRsData(int $userId): ?object {
        $this->db->query("
        SELECT status, updated_at, reject_reason
        FROM __payments_rs
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 1
    ", $userId);

        $data = $this->db->result();

        if (!$data || empty($data->updated_at)) {
            return null;
        }

        $updatedAt = strtotime($data->updated_at);
        $now = time();

        $data->is_recent = ($now - $updatedAt) < 86400;

        return $data;
    }
}
