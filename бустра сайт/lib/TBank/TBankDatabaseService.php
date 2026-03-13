<?php

require_once 'Simpla.php';

class TBankDatabaseService extends Simpla
{
    public function addAccount(array $data): int
    {
        try {
            $query = $this->db->placehold("INSERT INTO t_sbp_accounts SET ?%", $data);
            $this->db->query($query);

            return (int) $this->db->insert_id();
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, 'addAccount', $data, $e->getMessage(), date('d-m-Y').'-t-bank-error.txt');

            throw new \Exception('Ошибка добавления аккаунта в БД');
        }
    }

    public function getAccountByRequestKey(string $requestKey): array
    {
        try {
            if (!$this->checkExists(['request_key' => $requestKey])) throw new \Exception('Аккаунт не найден');

            $query = $this->db->placehold("SELECT * FROM t_sbp_accounts WHERE request_key = ? AND deleted = 0 ORDER BY id DESC LIMIT 1", $requestKey);
            $this->db->query($query);

            return $this->db->result();
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, 'getAccountByRequestKey', $requestKey, $e->getMessage(), date('d-m-Y').'-t-bank-error.txt');

            throw new \Exception('Ошибка получения аккаунта из БД');
        }
    }

    public function updateAccount(array $data, array $where): bool
    {
        try {
            if (!$data || !$where) return false;
            if (!$this->checkExists($where)) throw new \Exception('Запись не найдена');

            $conditions = [];
            foreach ($where as $condition => $value) {
                $conditions[] = $this->db->placehold("`$condition` = ?", $value);
            }

            $where = implode(" AND ", $conditions);

            $query = $this->db->placehold("UPDATE t_sbp_accounts SET ?% WHERE ".$where." AND deleted = 0", $data);
            $this->db->query($query);
            return (bool) $this->db->affected_rows();
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, 'updateAccount', $data, $e->getMessage(), date('d-m-Y').'-t-bank-error.txt');

            throw new \Exception('Ошибка обновления аккаунта в БД');
        }
    }

    public function checkExists(array $where): bool
    {
        try {
            if (!$where) return false;

            $conditions = [];
            foreach ($where as $condition => $value) {
                $conditions[] = $this->db->placehold("`$condition` = ?", $value);
            }

            $where = implode(" AND ", $conditions);

            $query = $this->db->placehold("SELECT * FROM t_sbp_accounts WHERE ".$where." AND deleted = 0");
            $this->db->query($query);

            return (bool) $this->db->result();
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, 'checkExists', $where, $e->getMessage(), date('d-m-Y').'-t-bank-error.txt');

            throw new \Exception('Ошибка проверки существования аккаунта в БД');
        }
    }

    public function checkActiveAccountByUserId(int $userId): bool
    {
        try {
            return $this->checkExists(['user_id' => $userId, 'status' => 'ACTIVE']);
        } catch (\Throwable $e) {
            $this->logging(__METHOD__, 'checkActiveAccountByUserId', $userId, $e->getMessage(), date('d-m-Y').'-t-bank-error.txt');

            throw new \Exception('Ошибка проверки существования активного аккаунта в БД');
        }
    }
}