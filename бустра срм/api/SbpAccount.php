<?php

class SbpAccount extends Simpla
{
    public function getSbpAccountsByUserId(int $userId)
    {
        $whereParams = [
            ['user_id', '=', $userId],
            ['deleted', '=', 0],
        ];

        return $this->get($whereParams);
    }

    public function updateSbpAccount(string $id, array $sbpAccountParams): string
    {
        $query = $this->db->placehold("
            UPDATE b2p_sbp_accounts SET ?% WHERE id = ?
        ", $sbpAccountParams, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function get(array $filters = [], array $selectFields = ['*'], ?int $limit = null, string $orderBy = '')
    {
        $selectFields = implode(',', $selectFields);
        $filtersString = $this->getWhereParams($filters);

        $query = "
            SELECT $selectFields
            FROM b2p_sbp_accounts
            WHERE 1 $filtersString
        ";

        if ($orderBy) {
            $query .= " ORDER BY $orderBy";
        }

        if ($limit) {
            $query .= " LIMIT $limit";
        }

        $query = $this->db->placehold($query);
        $this->db->query($query);

        return $this->db->results();
    }

    public function first(array $filters = [], array $selectFields = ['*'], string $orderBy = '')
    {
        $selectFields = implode(',', $selectFields);
        $filtersString = $this->getWhereParams($filters);

        $query = "
            SELECT $selectFields
            FROM b2p_sbp_accounts
            WHERE 1 $filtersString
        ";

        if ($orderBy) {
            $query .= " ORDER BY $orderBy";
        }

        $query = $this->db->placehold($query);
        $this->db->query($query);

        return $this->db->result();
    }

    /** Возвращает строку с условиями WHERE */
    public function getWhereParams(array $filters): string
    {
        $filtersString = '';
        foreach ($filters as $filter) {
            $columnName = $filter[0];
            $operator = $filter[1];
            $value = $filter[2];

            if (strtoupper($operator) === 'IN') {
                $filtersString .=
                    $filtersString
                    . $this->db->placehold(" AND {$columnName} IN (?@)", $value);

                continue;
            }

            $filtersString .= $this->db->placehold(" AND {$columnName} = ?", $value);
        }

        return $filtersString;
    }

    public function find(int $sbpAccountId)
    {
        $whereParams = [
            ['id', '=', $sbpAccountId]
        ];

        return $this->first($whereParams);
    }
}