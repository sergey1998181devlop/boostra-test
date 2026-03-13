<?php

require_once 'Simpla.php';

/**
 * API класс для работы с запросами ЦБ.
 *
 * CRM-данные (комментарии, статусы, темы, клиенты) хранятся локально в БД.
 * Данные из ЛК ЦБ (номер запроса, PDF, дата поступления) будут
 * приходить из сервиса парсера ЦБ по API.
 */
class CbRequests extends Simpla
{
    /**
     * Получить список запросов с фильтрами и пагинацией.
     *
     * @param array $filter
     * @return array ['data' => [], 'total_count' => 0]
     */
    public function getAllRequests(array $filter = []): array
    {
        $sort = $filter['sort'] ?? 'req.received_at DESC';
        $conditions = $this->buildConditions($filter);
        $where = implode(' AND ', $conditions);

        $limit = (int) ($filter['limit'] ?? 100);
        $limit = $limit > 0 ? $limit : 100;

        $page = (int) ($filter['page'] ?? 1);
        $page = $page > 0 ? $page : 1;

        $offset = ($page - 1) * $limit;

        $query = $this->db->placehold("
            SELECT SQL_CALC_FOUND_ROWS
                req.*,
                subj.name AS subject_name,
                org.short_name AS organization_name,
                TRIM(CONCAT(IFNULL(u.lastname, ''), ' ', IFNULL(u.firstname, ''), ' ', IFNULL(u.patronymic, ''))) AS client_fio,
                u.birth AS client_birth_date,
                u.phone_mobile AS client_phone,
                last_comment_opr.text AS comment_opr,
                last_comment_okk.text AS comment_okk,
                last_comment_measures.text AS measures,
                last_comment_lawyers.text AS comment_lawyers
            FROM __cb_requests req
            LEFT JOIN __cb_request_subjects AS subj ON subj.id = req.subject_id
            LEFT JOIN __organizations AS org ON org.id = req.organization_id
            LEFT JOIN __users AS u ON u.id = req.client_id
            LEFT JOIN (
                SELECT request_id, text
                FROM __cb_request_comments
                WHERE section = 'opr' AND id IN (
                    SELECT MAX(id) FROM __cb_request_comments WHERE section = 'opr' GROUP BY request_id
                )
            ) AS last_comment_opr ON last_comment_opr.request_id = req.id
            LEFT JOIN (
                SELECT request_id, text
                FROM __cb_request_comments
                WHERE section = 'okk' AND id IN (
                    SELECT MAX(id) FROM __cb_request_comments WHERE section = 'okk' GROUP BY request_id
                )
            ) AS last_comment_okk ON last_comment_okk.request_id = req.id
            LEFT JOIN (
                SELECT request_id, text
                FROM __cb_request_comments
                WHERE section = 'measures' AND id IN (
                    SELECT MAX(id) FROM __cb_request_comments WHERE section = 'measures' GROUP BY request_id
                )
            ) AS last_comment_measures ON last_comment_measures.request_id = req.id
            LEFT JOIN (
                SELECT request_id, text
                FROM __cb_request_comments
                WHERE section = 'lawyers' AND id IN (
                    SELECT MAX(id) FROM __cb_request_comments WHERE section = 'lawyers' GROUP BY request_id
                )
            ) AS last_comment_lawyers ON last_comment_lawyers.request_id = req.id
            WHERE $where
            ORDER BY $sort
            LIMIT ?, ?
        ", $offset, $limit);

        $this->db->query($query);
        $results = $this->db->results() ?: [];

        $this->db->query("SELECT FOUND_ROWS() AS total_count");
        $totalCount = $this->db->result('total_count');

        return [
            'data' => $results,
            'total_count' => (int) $totalCount,
        ];
    }

    /**
     * Получить запрос по ID.
     *
     * @param int $id
     * @return object|null
     */
    public function getRequestById(int $id)
    {
        $query = $this->db->placehold("
            SELECT
                req.*,
                subj.name AS subject_name,
                org.short_name AS organization_name,
                TRIM(CONCAT(IFNULL(u.lastname, ''), ' ', IFNULL(u.firstname, ''), ' ', IFNULL(u.patronymic, ''))) AS linked_user_fio,
                u.phone_mobile AS linked_user_phone,
                u.birth AS linked_user_birth,
                u.email AS linked_user_email,
                c.number AS linked_order_number,
                o.amount AS linked_order_amount,
                o.date AS linked_order_date,
                o.payment_date AS linked_order_payment_date,
                o.status AS linked_order_status,
                o.manager_id AS linked_order_manager_id,
                om.name AS linked_order_manager_name,
                GREATEST(0, DATEDIFF(CURDATE(), o.payment_date)) AS linked_order_overdue_days,
                GREATEST(0, DATEDIFF(DATE(req.created_at), o.payment_date)) AS linked_order_overdue_days_at_creation,
                ub.sale_info AS linked_order_sale_info,
                ub.buyer AS linked_order_buyer,
                ub.buyer_phone AS linked_order_buyer_phone
            FROM __cb_requests req
            LEFT JOIN __cb_request_subjects AS subj ON subj.id = req.subject_id
            LEFT JOIN __organizations AS org ON org.id = req.organization_id
            LEFT JOIN __users AS u ON u.id = req.client_id
            LEFT JOIN __orders AS o ON o.id = req.order_id
            LEFT JOIN __contracts AS c ON c.order_id = o.id
            LEFT JOIN __managers AS om ON om.id = o.manager_id
            LEFT JOIN __user_balance AS ub ON ub.zaim_number = c.number
            WHERE req.id = ?
            LIMIT 1
        ", $id);

        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Создать новый запрос.
     *
     * @param array $data
     * @return int|null ID созданного запроса
     */
    public function createRequest(array $data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');

        $query = $this->db->placehold("INSERT INTO __cb_requests SET ?%", $data);
        $this->db->query($query);

        $id = $this->db->insert_id();

        if ($id) {
            $this->logHistory($id, isset($_SESSION['manager_id']) ? (int) $_SESSION['manager_id'] : null, 'creation', 'Запрос создан');
        }

        return $id ?: null;
    }

    /**
     * Обновить запрос.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateRequest(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $query = $this->db->placehold("UPDATE __cb_requests SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);

        return true;
    }

    /**
     * Получить список тем запросов.
     *
     * @param bool $onlyActive
     * @return array
     */
    public function getSubjects(bool $onlyActive = true): array
    {
        $where = $onlyActive ? 'WHERE is_active = 1' : '';

        $this->db->query("SELECT * FROM __cb_request_subjects $where ORDER BY name ASC");
        return $this->db->results() ?: [];
    }

    /**
     * Создать тему.
     *
     * @param string $name
     * @return int|null
     */
    public function createSubject(string $name)
    {
        $query = $this->db->placehold("INSERT INTO __cb_request_subjects SET ?%", [
            'name' => $name,
            'is_active' => 1,
        ]);
        $this->db->query($query);

        return $this->db->insert_id() ?: null;
    }

    /**
     * Обновить тему запроса.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateSubject(int $id, array $data): bool
    {
        $query = $this->db->placehold("UPDATE __cb_request_subjects SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);

        return true;
    }

    /**
     * Добавить комментарий к запросу.
     *
     * @param int $requestId
     * @param int $managerId
     * @param string $section description|opr|okk|lawyers|measures|general
     * @param string $text
     * @return bool
     */
    public function addComment(int $requestId, int $managerId, string $section, string $text): bool
    {
        $allowedSections = ['description', 'opr', 'okk', 'lawyers', 'measures', 'general'];
        if (!in_array($section, $allowedSections, true)) {
            return false;
        }

        $query = $this->db->placehold("INSERT INTO __cb_request_comments SET ?%", [
            'request_id' => $requestId,
            'manager_id' => $managerId,
            'section' => $section,
            'text' => $text,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->query($query);

        return (bool) $this->db->insert_id();
    }

    /**
     * Получить комментарии запроса.
     *
     * @param int $requestId
     * @param string|null $section
     * @return array
     */
    public function getComments(int $requestId, $section = null): array
    {
        $where = "c.request_id = " . (int) $requestId;
        if ($section !== null) {
            $where .= $this->db->placehold(" AND c.section = ?", $section);
        }

        $query = "
            SELECT c.*, m.name AS manager_name
            FROM __cb_request_comments c
            LEFT JOIN __managers m ON m.id = c.manager_id
            WHERE $where
            ORDER BY c.created_at ASC
        ";

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Обновить статус запроса.
     *
     * @param int $requestId
     * @param string $statusField status_opr|status_okk|status_sent
     * @param int $value 0|1
     * @param int|null $managerId
     * @return bool
     */
    public function updateStatus(int $requestId, string $statusField, int $value, $managerId = null): bool
    {
        $allowedFields = ['status_opr', 'status_okk', 'status_sent'];
        if (!in_array($statusField, $allowedFields, true)) {
            return false;
        }

        $statusLabels = [
            'status_opr' => 'Обработан ОПР',
            'status_okk' => 'Обработан ОКК',
            'status_sent' => 'Направлен ответ',
        ];

        $data = [
            $statusField => $value,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // При первом изменении статуса — фиксируем "Взят в работу"
        if ($value == 1) {
            $request = $this->getRequestById($requestId);
            if ($request && empty($request->taken_at)) {
                $data['taken_at'] = date('Y-m-d H:i:s');
            }
        }

        $query = $this->db->placehold("UPDATE __cb_requests SET ?% WHERE id = ?", $data, $requestId);
        $this->db->query($query);

        $action = $value ? $statusLabels[$statusField] : 'Снят статус: ' . $statusLabels[$statusField];
        $this->logHistory($requestId, $managerId, 'status_change', $action);

        return true;
    }

    /**
     * Записать историю изменения.
     *
     * @param int $requestId
     * @param int|null $managerId
     * @param string $action
     * @param string $details
     */
    public function logHistory(int $requestId, $managerId, string $action, string $details): void
    {
        $query = $this->db->placehold("INSERT INTO __cb_request_history SET ?%", [
            'request_id' => $requestId,
            'manager_id' => $managerId,
            'action' => $action,
            'details' => $details,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->query($query);
    }

    /**
     * Получить историю изменений запроса.
     *
     * @param int $requestId
     * @return array
     */
    public function getHistory(int $requestId): array
    {
        $query = $this->db->placehold("
            SELECT h.*, m.name AS manager_name
            FROM __cb_request_history h
            LEFT JOIN __managers m ON m.id = h.manager_id
            WHERE h.request_id = ?
            ORDER BY h.created_at DESC
        ", $requestId);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Получить список юридических лиц.
     *
     * @return array
     */
    public function getLegalEntities(): array
    {
        return $this->organizations->getList();
    }

    /**
     * Поиск клиента по ФИО, дате рождения и email.
     *
     * @param string $fio
     * @param string|null $birthDate
     * @param string|null $email
     * @return array
     */
    public function searchClient(string $fio, $birthDate = null, $email = null): array
    {
        $fio = trim($fio);
        $conditions = ['1'];
        $hasSearchCriteria = false;

        $parts = array_filter(array_map('trim', explode(' ', $fio)));
        if (!empty($parts)) {
            $hasSearchCriteria = true;
        }

        foreach ($parts as $part) {
            $escaped = $this->db->escape($part);
            $conditions[] = "(u.lastname LIKE '%{$escaped}%' OR u.firstname LIKE '%{$escaped}%' OR u.patronymic LIKE '%{$escaped}%')";
        }

        if ($birthDate) {
            $hasSearchCriteria = true;
            $birthDate = trim($birthDate);
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $birthDate, $m)) {
                $birthDateIso = $m[3] . '-' . $m[2] . '-' . $m[1];
                $conditions[] = $this->db->placehold(
                    "(u.birth = ? OR DATE(u.birth) = ? OR DATE(STR_TO_DATE(u.birth, '%d.%m.%Y')) = ?)",
                    $birthDate,
                    $birthDateIso,
                    $birthDateIso
                );
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
                $birthDateRu = date('d.m.Y', strtotime($birthDate));
                $conditions[] = $this->db->placehold(
                    "(DATE(u.birth) = ? OR DATE(STR_TO_DATE(u.birth, '%d.%m.%Y')) = ? OR u.birth = ?)",
                    $birthDate,
                    $birthDate,
                    $birthDateRu
                );
            } else {
                $val = $this->db->escape($birthDate);
                $conditions[] = "(u.birth LIKE '%$val%' OR DATE_FORMAT(u.birth, '%d.%m.%Y') LIKE '%$val%')";
            }
        }

        $email = trim((string) $email);
        if ($email !== '') {
            $hasSearchCriteria = true;
            $emailNormalized = strtolower(preg_replace('/\s+/', '', $email));
            $normalizedEmailExpr = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(IFNULL(u.email, ''), ' ', ''), '\n', ''), '\r', ''), '\t', ''))";

            if (strpos($emailNormalized, '@') !== false) {
                $conditions[] = $this->db->placehold(
                    "(
                        $normalizedEmailExpr = ?
                        OR CONCAT(',', REPLACE($normalizedEmailExpr, ';', ','), ',') LIKE ?
                        OR $normalizedEmailExpr LIKE ?
                    )",
                    $emailNormalized,
                    '%,' . $emailNormalized . ',%',
                    '%' . $emailNormalized . '%'
                );
            } else {
                $conditions[] = $this->db->placehold(
                    "$normalizedEmailExpr LIKE ?",
                    '%' . $emailNormalized . '%'
                );
            }
        }

        if (!$hasSearchCriteria) {
            return [];
        }

        $where = implode(' AND ', $conditions);

        $query = "
            SELECT
                u.id,
                TRIM(CONCAT(IFNULL(u.lastname, ''), ' ', IFNULL(u.firstname, ''), ' ', IFNULL(u.patronymic, ''))) AS full_name,
                u.phone_mobile,
                u.birth,
                u.email
            FROM __users u
            WHERE $where
            ORDER BY u.lastname ASC
            LIMIT 20
        ";

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Поиск заказа/договора по номеру контракта.
     *
     * @param string $contractNumber
     * @return object|null
     */
    public function searchOrderByContractNumber(string $contractNumber, ?int $clientId = null)
    {
        $contractNumber = trim($contractNumber);
        if (empty($contractNumber)) {
            return null;
        }

        $whereUser = '';
        if (!empty($clientId)) {
            $whereUser = $this->db->placehold(" AND o.user_id = ?", $clientId);
        }

        $query = $this->db->placehold("
            SELECT
                o.id AS order_id,
                c.number AS contract_number,
                o.amount,
                o.date,
                o.payment_date,
                o.status,
                o.user_id,
                o.manager_id,
                o.period,
                m.name AS manager_name,
                GREATEST(0, DATEDIFF(CURDATE(), o.payment_date)) AS overdue_days,
                ub.sale_info,
                ub.buyer AS buyer_name,
                ub.buyer_phone
            FROM __contracts c
            INNER JOIN __orders o ON o.id = c.order_id
            LEFT JOIN __managers m ON m.id = o.manager_id
            LEFT JOIN __user_balance AS ub ON ub.zaim_number = c.number
            WHERE c.number = ?
            $whereUser
            LIMIT 1
        ", $contractNumber);

        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получить последние займы клиента для выбора в карточке запроса.
     *
     * @param int $clientId
     * @param int $limit
     * @return array
     */
    public function getOrdersByClientId(int $clientId, int $limit = 20): array
    {
        if ($clientId <= 0) {
            return [];
        }

        $limit = max(1, min(100, $limit));

        $query = $this->db->placehold("
            SELECT
                o.id AS order_id,
                c.number AS contract_number,
                o.amount,
                o.date,
                o.payment_date,
                o.status,
                o.user_id,
                o.manager_id,
                o.period,
                m.name AS manager_name,
                GREATEST(0, DATEDIFF(CURDATE(), o.payment_date)) AS overdue_days,
                ub.sale_info,
                ub.buyer AS buyer_name,
                ub.buyer_phone
            FROM __orders o
            LEFT JOIN __contracts c ON c.order_id = o.id
            LEFT JOIN __managers m ON m.id = o.manager_id
            LEFT JOIN __user_balance AS ub ON ub.zaim_number = c.number
            WHERE o.user_id = ?
            ORDER BY o.id DESC
            LIMIT ?
        ", $clientId, $limit);

        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Подготовить фильтры из параметров запроса.
     *
     * @param array|null $requestSearch
     * @return array
     */
    public function prepareFilters($requestSearch = []): array
    {
        $filter = [];

        if (!empty($requestSearch)) {
            $sanitizedSearch = [];
            foreach ($requestSearch as $key => $value) {
                $sanitizedKey = trim($key, "'");
                if ($value !== '' && $value !== null) {
                    if ($sanitizedKey === 'received_date_range') {
                        $dates = explode(' - ', $value);
                        if (count($dates) === 2) {
                            $sanitizedSearch['received_from'] = date('Y-m-d 00:00:00', strtotime($dates[0]));
                            $sanitizedSearch['received_to'] = date('Y-m-d 23:59:59', strtotime($dates[1]));
                        }
                    } elseif ($sanitizedKey === 'deadline_range') {
                        $dates = explode(' - ', $value);
                        if (count($dates) === 2) {
                            $sanitizedSearch['deadline_from'] = date('Y-m-d', strtotime($dates[0]));
                            $sanitizedSearch['deadline_to'] = date('Y-m-d', strtotime($dates[1]));
                        }
                    } else {
                        $sanitizedSearch[$sanitizedKey] = $value;
                    }
                }
            }
            $filter['search'] = $sanitizedSearch;
        }

        return $filter;
    }

    /**
     * Подготовить параметры сортировки.
     *
     * @param string|null $sortParam
     * @return string
     */
    public function prepareSort($sortParam = null): string
    {
        $sortOptions = [
            'id' => 'req.id',
            'request_number' => 'req.request_number',
            'organization' => 'org.short_name',
            'received_at' => 'req.received_at',
            'client_fio' => 'client_fio',
            'phone' => 'u.phone_mobile',
            'subject' => 'subj.name',
            'response_deadline' => 'req.response_deadline',
        ];

        $sortDirection = 'DESC';
        $sortField = 'req.received_at';

        if ($sortParam) {
            $isDescending = strpos($sortParam, '-') === 0;
            $cleanParam = ltrim($sortParam, '-+');

            if (array_key_exists($cleanParam, $sortOptions)) {
                $sortField = $sortOptions[$cleanParam];
                $sortDirection = $isDescending ? 'ASC' : 'DESC';
            }
        }

        return "$sortField $sortDirection";
    }

    /**
     * Построить условия WHERE из фильтров.
     *
     * @param array $filter
     * @return array
     */
    private function buildConditions(array $filter): array
    {
        $conditions = ['1'];

        if (empty($filter['search'])) {
            return $conditions;
        }

        $search = $filter['search'];

        if (!empty($search['id'])) {
            $conditions[] = $this->db->placehold("req.id = ?", (int) $search['id']);
        }

        if (!empty($search['request_number'])) {
            $val = $this->db->escape(trim($search['request_number']));
            $conditions[] = "req.request_number LIKE '%$val%'";
        }

        if (!empty($search['organization_id'])) {
            $conditions[] = $this->db->placehold("req.organization_id = ?", (int) $search['organization_id']);
        }

        if (!empty($search['received_from'])) {
            $conditions[] = $this->db->placehold("req.received_at >= ?", $search['received_from']);
        }
        if (!empty($search['received_to'])) {
            $conditions[] = $this->db->placehold("req.received_at <= ?", $search['received_to']);
        }

        if (!empty($search['client_fio'])) {
            $val = $this->db->escape(trim($search['client_fio']));
            $conditions[] = "CONCAT(IFNULL(u.lastname, ''), ' ', IFNULL(u.firstname, ''), ' ', IFNULL(u.patronymic, '')) LIKE '%$val%'";
        }

        if (!empty($search['client_birth_date'])) {
            $birthDate = trim((string) $search['client_birth_date']);
            $birthDate = preg_replace('/\s+/', '', $birthDate);
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4})$/', $birthDate, $m)) {
                $birthDateIso = $m[3] . '-' . $m[2] . '-' . $m[1];
                $conditions[] = $this->db->placehold(
                    "(u.birth = ? OR DATE(u.birth) = ? OR DATE(STR_TO_DATE(u.birth, '%d.%m.%Y')) = ?)",
                    $birthDate,
                    $birthDateIso,
                    $birthDateIso
                );
            } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthDate)) {
                $birthDateRu = date('d.m.Y', strtotime($birthDate));
                $conditions[] = $this->db->placehold(
                    "(DATE(u.birth) = ? OR DATE(STR_TO_DATE(u.birth, '%d.%m.%Y')) = ? OR u.birth = ?)",
                    $birthDate,
                    $birthDate,
                    $birthDateRu
                );
            } else {
                $val = $this->db->escape($birthDate);
                $conditions[] = "(u.birth LIKE '%$val%' OR DATE_FORMAT(u.birth, '%d.%m.%Y') LIKE '%$val%')";
            }
        }

        if (!empty($search['client_phone'])) {
            $val = $this->db->escape(trim($search['client_phone']));
            $conditions[] = "u.phone_mobile LIKE '%$val%'";
        }

        if (!empty($search['subject_id'])) {
            $conditions[] = $this->db->placehold("req.subject_id = ?", (int) $search['subject_id']);
        }

        if (!empty($search['status'])) {
            switch ((string) $search['status']) {
                case 'new':
                    $conditions[] = "req.status_opr = 0";
                    $conditions[] = "req.status_okk = 0";
                    $conditions[] = "req.status_sent = 0";
                    break;
                case 'opr':
                    $conditions[] = "req.status_opr = 1";
                    break;
                case 'okk':
                    $conditions[] = "req.status_okk = 1";
                    break;
                case 'sent':
                    $conditions[] = "req.status_sent = 1";
                    break;
            }
        }

        if (!empty($search['status_opr'])) {
            $conditions[] = $this->db->placehold("req.status_opr = ?", (int) $search['status_opr']);
        }
        if (!empty($search['status_okk'])) {
            $conditions[] = $this->db->placehold("req.status_okk = ?", (int) $search['status_okk']);
        }
        if (!empty($search['status_sent'])) {
            $conditions[] = $this->db->placehold("req.status_sent = ?", (int) $search['status_sent']);
        }

        if (!empty($search['deadline_from'])) {
            $conditions[] = $this->db->placehold("req.response_deadline >= ?", $search['deadline_from']);
        }
        if (!empty($search['deadline_to'])) {
            $conditions[] = $this->db->placehold("req.response_deadline <= ?", $search['deadline_to']);
        }

        return $conditions;
    }
}
