<?php

require_once 'Simpla.php';

class B2pBankList extends Simpla
{
    /**
     * @param array $where
     * @return array|false
     */
    public function get(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            if (is_array($value)) {
                $conditions[] = $this->db->placehold("`$condition` IN(?@)", $value);
            } else {
                $conditions[] = $this->db->placehold("`$condition` = ?", $value);
            }
        }

        $conditions = implode(' AND ', $conditions);

        $query = "SELECT * FROM b2p_bank_list WHERE 1 AND $conditions ORDER BY id DESC";
        $this->db->query($query);

        return $this->db->results();
    }
    /**
     * @param array $where
     * @return stdClass|false|int|null
     */
    public function getOne(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            if (is_array($value)) {
                $conditions[] = $this->db->placehold("`$condition` IN(?@)", $value);
            } else {
                $conditions[] = $this->db->placehold("`$condition` = ?", $value);
            }
        }

        $conditions = implode(' AND ', $conditions);

        $query = "SELECT * FROM b2p_bank_list WHERE 1 AND $conditions LIMIT 1";
        $this->db->query($query);

        return $this->db->result();
    }

    /**
     * Добавление новой записи
     * @param array $row
     * @return int
     */
    public function add($row)
    {
        $query = $this->db->placehold('INSERT INTO b2p_bank_list SET ?%', (array)$row);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление записи
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data)
    {
        $query = $this->db->placehold("UPDATE b2p_bank_list SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    public function getSbpBanks(): array
    {
        $this->db->query("SELECT * FROM b2p_bank_list WHERE has_sbp = 1");
        $banks = $this->db->results();
        if (empty($banks)) {
            return [];
        }

        // Сортировка
        $priority = [
            'Sberbank' => 1,
            'Tinkoff Bank' => 2,
            'Alfa-Bank' => 3,
            'Gazprombank' => 4,
            'Raiffeisenbank' => 5,
        ];

        usort($banks, function ($a, $b) use ($priority) {
            $pa = $priority[$a->latinTitle] ?? PHP_INT_MAX;
            $pb = $priority[$b->latinTitle] ?? PHP_INT_MAX;
            if ($pa === $pb) {
                return strcasecmp($a->latinTitle, $b->latinTitle);
            }
            return $pa <=> $pb;
        });

        return $banks;
    }

    public function canShowSbpBanks(): bool
    {
        return true;
    }

    /**
     * Извлекает bin_issuer из XML ответа B2P и сохраняет соответствующий bank_id
     *
     * @param string $operation_info XML ответ от B2P с информацией об операции
     * @param int $user_id ID пользователя
     * @param int|null $order_id ID заявки (опционально)
     * @return bool true если банк успешно определен и сохранен, false в противном случае
     */
    public function saveBankIdFromBinIssuer(string $operation_info, int $user_id, ?int $order_id = null): bool
    {
        if (empty($operation_info)) {
            return false;
        }

        $xml = simplexml_load_string($operation_info);
        if ($xml === false || empty($xml->bin_issuer)) {
            return false;
        }

        $bin_issuer = trim((string)$xml->bin_issuer);
        $bank = $this->getBankByBinIssuer($bin_issuer);
        
        if (empty($bank)) {
            return false;
        }

        $this->user_data->set(
            $user_id,
            $this->user_data::DEFAULT_BANK_ID_FOR_SBP_ISSUANCE,
            (int)$bank->b2p_bank_id
        );

        if (!empty($order_id)) {
            $this->order_data->set(
                (int)$order_id,
                $this->order_data::BANK_ID_FOR_SBP_ISSUANCE,
                (int)$bank->b2p_bank_id
            );
        }

        return true;
    }

    /**
     * Поиск банка в таблице b2p_credit_card_bank_list по названию bin_issuer
     *
     * @param string $bin_issuer Название банка-эмитента из B2P
     * @return stdClass|null Найденный банк или null
     */
    public function getBankByBinIssuer(string $bin_issuer): ?stdClass
    {
        if (empty($bin_issuer)) {
            return null;
        }

        $query = $this->db->placehold("
            SELECT b2p_bank_id, bank_name, has_sbp
            FROM b2p_credit_card_bank_list
            WHERE UPPER(TRIM(bank_name)) = UPPER(TRIM(?))
            AND has_sbp = 1
            LIMIT 1
        ", $bin_issuer);

        $this->db->query($query);
        $result = $this->db->result();

        return $result ?: null;
    }
}