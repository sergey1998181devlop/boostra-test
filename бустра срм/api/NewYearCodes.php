<?php

require_once 'Simpla.php';
class NewYearCodes extends Simpla
{
    /**
     * Получить коды и дополнительные данные участников акции
     *
     * @param array $filter
     * @return array
     */
    public function getAllParticipantCodesWithUserInfo(array $filter = []): array
    {
        $conditions = '';

        if (!empty($filter['search'])) {
            foreach ($filter['search'] as $key => $value) {
                $conditions .= $this->getCondition($key, $value, 'string') . ' ';
            }
        }

        if (!empty($filter['level'])) {
            $levelCondition = $this->db->placehold(" AND su.generated_codes_count = ? ", $filter['level']);
            $conditions .= $levelCondition;
        }

        $limit = isset($filter['limit']) ? max(1, intval($filter['limit'])) : 50;
        $page = isset($filter['page']) ? max(1, intval($filter['page'])) : 1;
        $offset = ($page - 1) * $limit;
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', $offset, $limit);

        $query = $this->db->placehold("
        SELECT pc.id, pc.user_id, pc.code, su.lastname, su.firstname, su.patronymic, su.phone_mobile, su.generated_codes_count, pc.updated_at
        FROM __participant_codes pc
        LEFT JOIN __users su ON pc.user_id = su.id
        WHERE 1 $conditions
        ORDER BY pc.id DESC
        $sql_limit
    ");

        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * Получить общее количество кодов участников акции с учетом фильтров
     *
     * @param array $filter
     * @return int
     */
    public function countParticipantCodes(array $filter = []): int
    {
        $conditions = '';

        if (!empty($filter['search'])) {
            foreach ($filter['search'] as $key => $value) {
                $conditions .= $this->getCondition($key, $value, 'like') . ' ';
            }
        }

        $query = $this->db->placehold("
        SELECT COUNT(id) as count
        FROM __participant_codes
        WHERE 1 $conditions
    ");

        $this->db->query($query);
        return $this->db->result('count');
    }

    /**
     * Вывести количество кодов каждого уровня
     * @return array
     */
    public function getAllParticipantCodesForDisplay(): array
    {
        $level1 = '1';
        $level2 = '2';

        $queryLevel1 = $this->db->placehold("
        SELECT COUNT(*) as count
        FROM __participant_codes pc
        LEFT JOIN __users su ON pc.user_id = su.id
        WHERE LEFT(pc.code, 1) = ?
    ");

        $queryLevel2 = $this->db->placehold("
        SELECT COUNT(*) as count
        FROM __participant_codes pc
        LEFT JOIN __users su ON pc.user_id = su.id
        WHERE LEFT(pc.code, 1) = ?
    ");

        $this->db->query($queryLevel1, $level1);
        $countLevel1 = $this->db->result('count');

        $this->db->query($queryLevel2, $level2);
        $countLevel2 = $this->db->result('count');

        $result = [
            'level1' => $countLevel1,
            'level2' => $countLevel2,
        ];

        return $result;
    }


    /**
     * Получаем условие для фильтрации
     * @param string $option
     * @param mixed $value
     * @param string $type
     * @return string
     */
    private function getCondition(string $option, $value, string $type): string
    {
        if ($type == 'string') {
            return $this->db->placehold("AND $option LIKE '%$value%'");
        } else {
            return '';
        }
    }

    /**
     * Получить все коды и дополнительные данные участников акции для выгрузки
     *
     * @param string|null $level
     * @return array
     */
    public function getAllParticipantCodesForExport(?string $level = null): array
    {
        $whereCondition = ($level !== null && $level !== "0") ? "WHERE LEFT(pc.code, 1) = $level" : "";

        $query = $this->db->placehold("
        SELECT pc.id, pc.user_id, pc.code, su.lastname, su.firstname, su.patronymic, su.phone_mobile, su.generated_codes_count, pc.updated_at
        FROM __participant_codes pc
        LEFT JOIN __users su ON pc.user_id = su.id
        $whereCondition
        ORDER BY pc.id DESC
    ");

        $this->db->query($query);
        return $this->db->results();
    }

}