<?php

require_once 'Simpla.php';

class UserBankRequisites extends Simpla
{
    /**
     * Получить все банковские реквизиты пользователя
     * @param int $userId
     * @return array
     */
    public function getByUserId(int $userId): array
    {
        $this->db->query(
            "SELECT * FROM __user_bank_requisites 
             WHERE user_id = ? 
             ORDER BY is_default DESC, created DESC", 
            $userId
        );
        
        $result = [];
        foreach ($this->db->results() as $row) {
            $fioPart = !empty($row->recipient_fio) ? ' — ' . $row->recipient_fio : '';
            $result[] = [
                'id' => $row->id,
                'display_name' => $row->account_number . ' (' . $row->bank_name . ')' . $fioPart . ($row->is_default ? ' [по умолчанию]' : ''),
                'account_number' => $row->account_number,
                'bik' => $row->bik,
                'bank_name' => $row->bank_name,
                'recipient_fio' => $row->recipient_fio ?? '',
            ];
        }
        return $result;
    }
    
    /**
     * Проверить существование реквизитов
     * @param int $userId
     * @param string $accountNumber
     * @param string $bik
     * @return int|null ID реквизитов или null
     */
    public function exists(int $userId, string $accountNumber, string $bik): ?int
    {
        $this->db->query(
            "SELECT id FROM __user_bank_requisites 
             WHERE user_id = ? AND account_number = ? AND bik = ?",
            $userId, $accountNumber, $bik
        );
        $result = $this->db->result();
        return $result ? $result->id : null;
    }
    
    /**
     * Сбросить флаг is_default у всех реквизитов пользователя
     * @param int $userId
     * @return void
     */
    public function resetDefault(int $userId): void
    {
        $this->db->query(
            "UPDATE __user_bank_requisites SET is_default = 0 WHERE user_id = ?", 
            $userId
        );
    }
    
    /**
     * Создать или вернуть существующие банковские реквизиты
     * @param array $data
     * @return int ID реквизитов
     */
    public function create(array $data): int
    {
        $existingId = $this->exists($data['user_id'], $data['account_number'], $data['bik']);
        if ($existingId) {
            return $existingId;
        }
        
        if (!empty($data['is_default'])) {
            $this->resetDefault($data['user_id']);
        }
        
        $this->db->query("INSERT INTO __user_bank_requisites SET ?%", $data);
        return $this->db->insert_id();
    }
}
