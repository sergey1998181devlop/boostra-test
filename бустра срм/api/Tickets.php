<?php

use api\traits\TicketDuplicatesHandlerTrait;
use App\Containers\DomainSection\Tickets\Tables\TsTicketTable;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;

require_once 'Simpla.php';

class Tickets extends Simpla
{
    use TicketDuplicatesHandlerTrait;
    
    public const NEW          = 1; // Новая
    public const UNRESOLVED   = 2; // Не урегулирован
    public const ON_HOLD      = 3; // Ожидание
    public const RESOLVED     = 4; // Урегулирован
    public const IN_WORK      = 5; // В работе
    public const REQUEST_DETAILS      = 7; // Запрос в контролирующий орган
    public const DUPLICATE      = 8; // Дубликат
    public const DISPUTED_COMPLAINT = 9; // Спорная жалоба
    public const AGREEMENT_REACHED  = 10; // Достигнуты договоренности

    private const AUTO_DISABLE_ADDITIONAL_SERVICES = 'auto_disable_additional_services';
    private const SUBJECT_COMPLAINT_ADDITIONAL_SERVICES_ID = 2;
    private const SUBJECT_COMPLAINT_RECALCULATION_ID = 3;

    // Константы для тем взыскания и допов
    private const SUBJECT_COLLECTION_ID = 9;
    private const SUBJECT_DOPY_ID = 10;
    // Константы для каналов обращения
    private const CHANNEL_PHONE = 1;    // Телефония
    private const CHANNEL_CHAT = 2;     // Чат
    private const CHANNEL_EMAIL = 3;    // Эл. Почта

    // ID менеджеров для спорных жалоб
    private const MANAGER_SMIRNOVA_TATIANA = 335;
    private const MANAGER_SMIRNOV_DANIIL = 85;
    private const MANAGER_SHEVTSOVA_OKSANA = 196;
    private const MANAGER_OMAROVA_DILARA = 357;
    private const MANAGER_SUVVY_BOT = 360;

    public const PUSH_TITLE =  'Не удалось дозвониться до вас';
    public const PUSH_DESCRIPTION = 'Мы не смогли с вами связаться по вашему номеру. Пожалуйста, перезвоните нам +74951804205 (Отдел по работе с претензиями), мы поможем в решении вашего вопроса.';
    public const PUSH_SUCCESS_COMMENT = 'Отправлено уведомление в ЛК на сайте и мобильное пуш-уведомление клиенту о необходимости перезвонить по тикету, push_id = ';
    public const PUSH_FAIL_COMMENT = 'Отправлено уведомление в ЛК на сайте о необходимости перезвонить по тикету, но мобильное пуш-уведомление не отправлено, так как клиент не пользуется приложением';
    public const RESOLVED_PUSH_TITLE = 'Ваше обращение урегулировано';
    public const RESOLVED_PUSH_DESCRIPTION = 'Если у вас остались дополнительные вопросы, позвоните нам +74951804205 (Отдел по работе с претензиями)';
    public const RESOLVED_PUSH_SUCCESS_COMMENT = 'Отправлено уведомление в ЛК на сайте и мобильное пуш-уведомление клиенту о возможности повторного обращения, push_id = ';
    public const RESOLVED_PUSH_FAIL_COMMENT = 'Отправлено уведомление в ЛК на сайте о возможности повторного обращения, но мобильное пуш-уведомление не отправлено, так как клиент не пользуется приложением';
    public const RESOLVED_PUSH_DESCRIPTION_CLAIMS = 'Если у вас остались дополнительные вопросы, позвоните нам +74952042438 (Отдел по работе с претензиями)';

    private array $filter_args = [
        'id' => "assign",
        'chanel_id' => "assign",
        'subject_id' => "assign",
        'subject_parent_id' => "assign",
        'status_id' => "assign",
        'priority_id' => "assign",
        'company_id' => "assign",
        'manager_id' => "assign",
        'initiator_id' => "assign",

        'created_at' => "between",

        'client_id' => "like",
        'phone' => "like",
        'description' => "like",
    ];

    /**
     * Подготовка фильтров для поиска
     * @param array $requestSearch Массив параметров из запроса
     * @return array Подготовленные фильтры
     */
    public function prepareFilters($requestSearch = []): array
    {
        $filter = [];

        if (!empty($requestSearch)) {
            $sanitizedSearch = [];
            foreach ($requestSearch as $key => $value) {
                $sanitizedKey = trim($key, "'");
                if (!empty($value)) {
                    if ($sanitizedKey === 'date_range') {
                        $dates = explode(' - ', $value);
                        if (count($dates) == 2) {
                            $sanitizedSearch['date_from'] = date('Y-m-d H:i:s', strtotime($dates[0]));
                            $sanitizedSearch['date_to'] = date('Y-m-d H:i:s', strtotime($dates[1] . ' 23:59:59'));
                        }
                    } elseif ($sanitizedKey === 'accepted_date_range') {
                        $dates = explode(' - ', $value);
                        if (count($dates) == 2) {
                            $sanitizedSearch['accepted_from'] = date('Y-m-d H:i:s', strtotime($dates[0]));
                            $sanitizedSearch['accepted_to'] = date('Y-m-d H:i:s', strtotime($dates[1] . ' 23:59:59'));
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
     * Подготовка сортировки
     * @param string|null $sortParam Параметр сортировки
     * @return string Строка сортировки для SQL
     */
    public function prepareSort(?string $sortParam): string
    {
        $sortOptions = [
            'id' => 'id',
            'chanel' => 'tick.chanel_id',
            'client' => 'us.lastname',
            'date' => 'tick.created_at',
            'accepted_at' => 'tick.accepted_at',
            'subject' => 'tick.subject_id',
            'status' => 'tick.status_id',
            'priority' => 'tick.priority_id',
            'manager' => 'man.name',
            'initiator' => 'initiator.name',
            'company' => 'tick.company_id',
            'repeat' => 'tick.is_repeat',
        ];

        $sortDirection = 'DESC';
        $sortField = 'tick.created_at';

        if ($sortParam) {
            $isDescending = strpos($sortParam, '-') === 0;
            $sortParam = ltrim($sortParam, '-');

            if (array_key_exists($sortParam, $sortOptions)) {
                $sortField = $sortOptions[$sortParam];
                $sortDirection = $isDescending ? 'ASC' : 'DESC';
            }
        }

        return "$sortField $sortDirection";
    }

    /**
     * Создает новый тикет
     * @param array $data
     * @return mixed
     */
    public function createNewTicket(array $data)
    {
        $changedBy = ($data['initiator_id'] == self::MANAGER_SUVVY_BOT) ? self::MANAGER_SUVVY_BOT : ($this->getManagerId() ?: $data['initiator_id']);

        $tsTable = TsTicketTable::getInstance();
        if ($tsTable->getSubjectId() === intval($data['subject_id'])) {
            //Смена приоритета тикета от сотрудника ОПР
            if (
                $this->isInitiatorOpr($data['initiator_id'])
                && !empty($newPriorityId = $this->getPriorityIdByName('Высокий'))
            ) {
                $data['priority_id'] = $newPriorityId;
            }
            //Запрет на критический приоритет для тикетов ТП
            elseif (!in_array($data['priority_id'], $this->getValidPrioritiesForTechnicalSupport())) {
                $data['priority_id'] = $this->getPriorityIdByName('Средний');
            }
        }


        $query = $this->db->placehold("
            INSERT INTO __mytickets SET ?%
        ", (array)$data);
        $this->db->query($query);

        $newTicketId = $this->db->insert_id();

        $this->handleDuplicatesOnCreation($newTicketId, $data);

        $this->logTicketHistory($newTicketId, 'creation', '', '', $changedBy, 'Тикет создан');

        $this->addComment($data);
        $tsTable->initTsTicket($newTicketId);

        $this->addClientToWhitelist($data, $newTicketId);

        return $newTicketId;
    }

    private function addComment(array $data)
    {
        $managerId = ($data['initiator_id'] == self::MANAGER_SUVVY_BOT) ? self::MANAGER_SUVVY_BOT : ($data['manager_id'] ?? null);

        $query = $this->db->placehold("
            INSERT INTO __comments SET ?%
        ", [
            'manager_id' => $managerId,
            'user_id' => $data['client_id'],
            'text' => 'ticket: ' . $data['description'],
            'block' => 'personal',
            'created' => date('Y-m-d H:i:s'),
        ]);

        $this->db->query($query);

        if ($data['client_id']) {
            $this->syncCommentWith1C($data);
        }
    }

    private function syncCommentWith1C($data)
    {
        if (!$data['client_id']) {
            return;
        }

        $manager_id = $data['manager_id'] ?? $this->managers::MANAGER_SYSTEM_ID;

        $manager = $this->managers->get_manager($manager_id);
        $user = $this->users->get_user($data['client_id']);

        if (!$user) {
            return;
        }

        $this->soap->send_comment([
            'manager' => $manager->name_1c,
            'text' => $data['description'],
            'created' => date('Y-m-d H:i:s'),
            'number' => '',
            'user_uid' => $user->UID
        ]);
    }
    
    /**
     * Получить уникальные имена ответственных лиц из таблицы mytickets.
     *
     * @return array Массив уникальных имен ответственных лиц.
     */
    public function getUniqueResponsiblePersonNames(): array
    {
        $query = $this->db->placehold("
            SELECT uid, code
            FROM s_responsible_persons
            WHERE uid IS NOT NULL AND code IS NOT NULL
            GROUP BY uid, code
            ORDER BY code ASC
        ");

        $this->db->query($query);
        $results = $this->db->results() ?: [];

        $keyValuePairs = [];
        foreach ($results as $row) {
            $keyValuePairs[$row->uid] = $row->code;
        }

        return $keyValuePairs;
    }

    /**
     * Получить уникальные связки UUID и названий групп из таблицы mytickets.
     *
     * @return array Массив уникальных связок UUID и названий групп.
     */
    public function getUniqueGroups(): array
    {
        $query = $this->db->placehold("
            SELECT group_uid, MAX(group_name) AS group_name
            FROM s_responsible_persons
            WHERE group_uid IS NOT NULL AND group_name IS NOT NULL
            GROUP BY group_uid
            ORDER BY group_name ASC
        ");

        $this->db->query($query);
        $results = $this->db->results() ?: [];

        $keyValuePairs = [];
        foreach ($results as $row) {
            if (!empty($row->group_name)) {
                $keyValuePairs[$row->group_uid] = $row->group_name;
            }
        }

        return $keyValuePairs;
    }

    /**
     * Создает или обновляет запись об ответственном лице.
     * Приоритет поиска: сначала по uid, затем по code.
     *
     * @param array $data Данные для сохранения (могут содержать uid, code, group_name и т.д.)
     * @return int|null ID созданной или обновленной записи
     */
    public function createOrUpdateResponsiblePerson(array $data = []): ?int
    {
        $record = null;

        if (!empty($data['uid'])) {
            $record = $this->getResponsiblePersonByUid($data['uid']);
        }

        if (!$record && !empty($data['code'])) {
            $record = $this->getResponsiblePersonByCode($data['code']);
        }

        if ($record) {
            $this->db->query("UPDATE __responsible_persons SET ?% WHERE id = ?", $data, $record->id);
            return $record->id;
        } else {
            $this->db->query("INSERT INTO __responsible_persons SET ?%", $data);
            return $this->db->insert_id();
        }
    }
    
    private function getResponsiblePersonByUid($uid)
    {
        $this->db->query("SELECT * FROM __responsible_persons WHERE uid = ?", $uid);
        return $this->db->result();
    }

    /**
     * Получение списка тикетов с фильтрацией и пагинацией
     * @param array $filter
     * @return array
     */
    public function getAllTickets(array $filter = []): array
    {
        $sort = $filter['sort'] ?? 'tick.created_at DESC';
        $conditions = $this->buildConditions($filter);

        $where = implode(' AND ', $conditions);

        // Пагинация
        $limit = (int)($filter['limit'] ?? 1000);
        $limit = $limit > 0 ? $limit : 1000;

        $page = (int)($filter['page'] ?? 1);
        $page = $page > 0 ? $page : 1;

        $offset = ($page - 1) * $limit;

        $query = $this->db->placehold("
            SELECT SQL_CALC_FOUND_ROWS
                tick.*,
                IF(tick.client_id IS NOT NULL,
                   CONCAT(us.lastname, ' ', us.firstname, ' ', us.patronymic, ' ', us.birth),
                   JSON_UNQUOTE(JSON_EXTRACT(tick.data,'$.fio'))
                ) as client_name,
                IF(tick.client_id IS NOT NULL,
                   us.phone_mobile,
                   JSON_UNQUOTE(JSON_EXTRACT(tick.data,'$.phone'))
                ) AS client_phone,
                man.name AS name_manager,
                initiator.name as name_initiator,
                stat.name AS status_name,
                stat.color AS status_color,
                prior.name AS priority_name,
                prior.color AS priority_color,
                company.short_name AS company_name,
                chan.name AS chanel_name,
                subj.name AS ticket_subject,
                comment.text AS last_comment,
                parent.name AS subject_parent_name,
                us.Regregion AS client_region,
                IF(tick.status_id IN (3, 5)
                    AND DATE(tick.created_at) < DATE(NOW() - INTERVAL 1 DAY), 1, 0) as is_overdue,
                IF(tick.status_id = 1, 1, 0) as is_new,
                tick.responsible_person_id,
                s_responsible_persons.code AS responsible_person_name,
                s_responsible_persons.group_uid AS responsible_group_uid,
                s_responsible_persons.group_name AS responsible_group_name,
                direction.name AS direction_name,
                us.loan_history AS user_loan_history
            FROM __mytickets tick
            LEFT JOIN __mytickets_statuses AS stat ON stat.id = tick.status_id
            LEFT JOIN __managers AS man ON man.id = tick.manager_id
            LEFT JOIN __managers AS initiator ON initiator.id = tick.initiator_id
            LEFT JOIN __mytickets_subjects AS subj ON subj.id = tick.subject_id AND subj.is_active = TRUE
            LEFT JOIN __mytickets_subjects AS parent ON parent.id = subj.parent_id
            LEFT JOIN __mytickets_channels AS chan ON chan.id = tick.chanel_id
            LEFT JOIN __mytickets_priority AS prior ON prior.id = tick.priority_id
            LEFT JOIN __organizations AS company ON company.id = tick.company_id
            LEFT JOIN __users AS us ON us.id = tick.client_id
            LEFT JOIN (
                SELECT ticket_id, text
                FROM __mytickets_comments
                WHERE id IN (
                    SELECT MAX(id)
                    FROM __mytickets_comments
                    GROUP BY ticket_id
                )
            ) AS comment ON comment.ticket_id = tick.id
            LEFT JOIN s_responsible_persons ON s_responsible_persons.id = tick.responsible_person_id
            LEFT JOIN __mytickets_directions AS direction ON direction.id = tick.direction_id
            WHERE $where
            ORDER BY $sort
            LIMIT ?, ?
        ", $offset, $limit);

        // Выполняем запрос
        $this->db->query($query);
        $results = $this->db->results() ?: [];

        // Получаем общее количество записей
        $this->db->query("SELECT FOUND_ROWS() AS total_count");
        $totalCount = $this->db->result('total_count');

        $results = $this->enrichTicketsWithOverdueDays($results);

        return [
            'data' => $results,
            'total_count' => $totalCount
        ];
    }

    private function buildConditions(array $filter): array
    {
        $conditions = ['1'];

        if (!empty($filter['search'])) {
            foreach ($filter['search'] as $key => $value) {
                if (in_array($key, ['client_name', 'overdue_range', 'subject_parent_id', 'phone','responsible_person_name','responsible_group_name', 'date_from', 'date_to', 'accepted_from', 'accepted_to'])) {
                    continue;
                }
                if (!empty($value)) {
                    $conditions[] = $this->getCondition($key, $value, $this->filter_args[$key] ?? 'assign');
                }
            }

            // Добавляем условия для фильтрации по датам
            if (!empty($filter['search']['date_from'])) {
                $conditions[] = $this->db->placehold("tick.created_at >= ?", $filter['search']['date_from']);
            }
            if (!empty($filter['search']['date_to'])) {
                $conditions[] = $this->db->placehold("tick.created_at <= ?", $filter['search']['date_to']);
            }

            if (!empty($filter['search']['accepted_from'])) {
                $conditions[] = $this->db->placehold("tick.accepted_at >= ?", $filter['search']['accepted_from']);
            }
            if (!empty($filter['search']['accepted_to'])) {
                $conditions[] = $this->db->placehold("tick.accepted_at <= ?", $filter['search']['accepted_to']);
            }

            if (!empty($filter['search']['description'])) {
                $description = $this->db->escape(trim($filter['search']['description']));
                $conditions[] = "tick.description LIKE '%$description%'";
            }

            if (!empty($filter['search']['phone'])) {
                $phone = $this->db->escape(trim($filter['search']['phone']));
                $conditions[] = "(us.phone_mobile LIKE '%$phone%' OR JSON_UNQUOTE(JSON_EXTRACT(data, '$.phone')) LIKE '%$phone%')";
            }

            if (!empty($filter['search']['client_name'])) {
                $conditions[] = $this->buildClientNameCondition($filter['search']['client_name']);
            }

            if (!empty($filter['search']['overdue_range'])) {
                $conditions[] = $this->buildOverdueCondition($filter['search']['overdue_range']);
            }

            if (!empty($filter['search']['subject_parent_id'])) {
                $parentId = (int)$filter['search']['subject_parent_id'];
                $conditions[] = $this->db->placehold("(subj.parent_id = ? OR subj.id = ?)", $parentId, $parentId);
            }

            // фильтрации по responsible_group_name
            if (!empty($filter['search']['responsible_group_name'])) {
                $groupName = $this->db->escape(trim($filter['search']['responsible_group_name']));
                $conditions[] = $this->db->placehold("s_responsible_persons.group_name LIKE ?", "%$groupName%");
            }

            // фильтрации по responsible_person_name (s_responsible_persons.code)
            if (!empty($filter['search']['responsible_person_name'])) {
                $personName = $this->db->escape(trim($filter['search']['responsible_person_name']));
                $conditions[] = $this->db->placehold("s_responsible_persons.code LIKE ?", "%$personName%");
            }
        }

        // фильтрации по исключениям из выборки
        if (!empty($filter['exclude'])) {
            foreach ($filter['exclude'] as $key => $values) {
                $conditions[] = $this->getCondition($key, implode(', ', $values), 'exclude');
            }
        }

        if (!empty($filter['ticket_type']) && $filter['ticket_type'] === 'support') {
            $conditions[] = $this->db->placehold("tick.status_id != ?", self::AGREEMENT_REACHED);
        }

        return $conditions;
    }

    private function buildClientNameCondition(string $clientName): string
    {
        $expls = array_map('trim', explode(' ', $clientName));
        $fioConditions = array_map(function($expl) {
            $escaped = $this->db->escape($expl);
            return "(us.lastname LIKE '%$escaped%' 
                 OR us.firstname LIKE '%$escaped%' 
                 OR us.patronymic LIKE '%$escaped%')";
        }, $expls);
        return '(' . implode(' AND ', $fioConditions) . ')';
    }

    private function buildOverdueCondition(string $overdueRange): string
    {
        switch ($overdueRange) {
            case '1':
                return "JSON_EXTRACT(tick.data, '$.overdue_days') <= 8";
            case '2':
                return "JSON_EXTRACT(tick.data, '$.overdue_days') BETWEEN 9 AND 30";
            case '3':
                return "JSON_EXTRACT(tick.data, '$.overdue_days') >= 31";
            default:
                if (preg_match('/^(-?\d+)\s*-\s*(-?\d+)$/', $overdueRange, $matches)) {
                    $min = (int)$matches[1];
                    $max = (int)$matches[2];

                    // Ограничиваем диапазон: от -16 до 500
                    if ($min < -16) {
                        $min = -16;
                    }
                    if ($max > 500) {
                        $max = 500;
                    }
                    if ($min <= $max) {
                        return "JSON_EXTRACT(tick.data, '$.overdue_days') BETWEEN $min AND $max";
                    }
                }
                return '';
        }
    }

    /**
     * Генерация условий для поиска
     * @param $key
     * @param $value
     * @param $type
     * @return array|false|string|string[]|null
     */
    private function getCondition($key, $value, $type)
    {
        if ($key === 'subject_parent_id') {
            $key = 'subj.parent_id';
        }
        
        if ($key === 'id') {
            $key = 'tick.id';
        }

        switch ($type) {
            case 'like':
                return $this->db->placehold("$key LIKE ?", '%' . $this->db->escape(trim($value)) . '%');
            case 'assign':
                return $this->db->placehold("$key = ?", $value);
            case 'exclude':
                return $this->db->placehold("$key NOT IN (?)", $this->db->escape(trim($value)));
            default:
                return '';
        }
    }

    /**
     * Получаем тикет + доп инфу
     * @param int $id
     * @return mixed
     */
    public function getTicketById(int $id)
    {
        $query = $this->db->placehold("
            SELECT 
                tick.*,
                CONCAT(us.lastname, ' ', us.firstname, ' ', us.patronymic) AS client_full_name,
                us.phone_mobile AS client_phone,
                us.birth AS client_birth,
                us.email AS client_email,
                man.name AS manager_name,
                stat.name AS status_name,
                stat.color AS status_color,
                chan.name AS channel_name,
                subj.name AS subject,
                priority.name AS priority_name,
                priority.color AS priority_color,
                company.short_name AS company_name,
                tick.responsible_person_id,
                responsible_persons.code AS responsible_person_name,
                responsible_persons.group_uid AS responsible_group_uid,
                responsible_persons.group_name AS responsible_group_name,
                direction.name AS direction_name
            FROM __mytickets AS tick
            LEFT JOIN __mytickets_statuses AS stat ON stat.id = tick.status_id
            LEFT JOIN __managers AS man ON man.id = tick.manager_id
            LEFT JOIN __mytickets_subjects AS subj ON subj.id = tick.subject_id AND subj.is_active = TRUE
            LEFT JOIN __mytickets_channels AS chan ON chan.id = tick.chanel_id
            LEFT JOIN __users AS us ON us.id = tick.client_id
            LEFT JOIN __mytickets_priority AS priority ON priority.id = tick.priority_id
            LEFT JOIN __organizations AS company ON company.id = tick.company_id
            LEFT JOIN __responsible_persons AS responsible_persons ON responsible_persons.id = tick.responsible_person_id
            LEFT JOIN __mytickets_directions AS direction ON direction.id = tick.direction_id
            WHERE tick.id = ?
            GROUP BY tick.id
            ORDER BY tick.id DESC
        ", $id);

        $this->db->query($query);
        $result = $this->db->result();

        if ($result && !empty($result->data)) {
            $result->data = json_decode($result->data, true);
        }

        return $result;
    }

    /**
     * Получить тикеты для карточки клиента.
     *
     * @param int $clientId
     * @param int $page
     * @param int $limit
     * @return array{data: array, total_count: int}
     */
    public function getClientTicketsForClientCard(int $clientId, int $page = 1, int $limit = 20, array $filter = []): array
    {
        $page = max(1, $page);
        $limit = max(1, $limit);
        $offset = ($page - 1) * $limit;

        $conditions = $this->buildConditions($filter);
        $conditions[] = $this->db->placehold("tick.client_id = ?", $clientId);
        $whereClause = implode(' AND ', $conditions);

        $this->db->query("
        SELECT COUNT(*) AS total_count
        FROM __mytickets AS tick
        LEFT JOIN __mytickets_subjects AS subj
            ON subj.id = tick.subject_id AND subj.is_active = TRUE
        LEFT JOIN __users AS us
            ON us.id = tick.client_id
        LEFT JOIN s_responsible_persons
            ON s_responsible_persons.id = tick.responsible_person_id
        WHERE $whereClause
        ");

        $totalCount = (int)$this->db->result('total_count');

        if ($totalCount === 0) {
            return [
                'data' => [],
                'total_count' => 0,
            ];
        }

        $query = $this->db->placehold("
        SELECT
            tick.id,
            tick.client_id,
            tick.created_at,
            tick.accepted_at,
            tick.status_id,
            tick.subject_id,
            tick.priority_id,
            tick.company_id,
            tick.chanel_id,
            tick.order_id,
            tick.is_repeat,
            tick.description,
            tick.final_comment,
            tick.data,
            IF(tick.client_id IS NOT NULL,
               us.phone_mobile,
               JSON_UNQUOTE(JSON_EXTRACT(tick.data,'$.phone'))
            ) AS client_phone,
            us.Regregion AS client_region,
            man.name        AS name_manager,
            initiator.name  AS name_initiator,
            stat.name       AS status_name,
            stat.color      AS status_color,
            prior.name      AS priority_name,
            prior.color     AS priority_color,
            company.short_name AS company_name,
            chan.name       AS chanel_name,
            subj.name       AS ticket_subject,
            parent.name     AS subject_parent_name,
            s_responsible_persons.code      AS responsible_person_name,
            s_responsible_persons.group_name AS responsible_group_name,
            direction.name                  AS direction_name,
            (
                SELECT c.text
                FROM __mytickets_comments AS c
                WHERE c.ticket_id = tick.id
                ORDER BY c.id DESC
                LIMIT 1
            ) AS last_comment,
            us.loan_history AS user_loan_history
        FROM __mytickets AS tick
        LEFT JOIN __mytickets_statuses AS stat
            ON stat.id = tick.status_id
        LEFT JOIN __managers AS man
            ON man.id = tick.manager_id
        LEFT JOIN __managers AS initiator
            ON initiator.id = tick.initiator_id
        LEFT JOIN __mytickets_subjects AS subj
            ON subj.id = tick.subject_id AND subj.is_active = TRUE
        LEFT JOIN __mytickets_subjects AS parent
            ON parent.id = subj.parent_id
        LEFT JOIN __mytickets_channels AS chan
            ON chan.id = tick.chanel_id
        LEFT JOIN __mytickets_priority AS prior
            ON prior.id = tick.priority_id
        LEFT JOIN __organizations AS company
            ON company.id = tick.company_id
        LEFT JOIN __users AS us
            ON us.id = tick.client_id
        LEFT JOIN s_responsible_persons
            ON s_responsible_persons.id = tick.responsible_person_id
        LEFT JOIN __mytickets_directions AS direction
            ON direction.id = tick.direction_id
        WHERE $whereClause
        ORDER BY tick.created_at DESC
        LIMIT ?, ?
        ", $offset, $limit);

        $this->db->query($query);
        $results = $this->db->results() ?: [];

        $results = $this->enrichTicketsWithOverdueDays($results);

        return [
            'data' => $results,
            'total_count' => $totalCount,
        ];
    }

    /**
     * Обогащает тикеты полем data['overdue_days'] на основе loan_history.
     *
     * @param array $results Массив объектов тикетов (как вернул $this->db->results()).
     * @return array Тот же массив, с обновлёнными полями data и без user_loan_history.
     */
    private function enrichTicketsWithOverdueDays(array $results): array
    {
        foreach ($results as $result) {
            $data = [];
            if (!empty($result->data)) {
                $decoded = json_decode($result->data, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                }
            }

            if (!empty($result->user_loan_history) && !empty($result->order_id)) {
                $loanHistory = json_decode($result->user_loan_history, true);
                if (is_array($loanHistory)) {
                    $orderId = (string)$result->order_id;

                    foreach ($loanHistory as $loan) {
                        $numberParts = explode('-', $loan['number'] ?? '');
                        $numberSuffix = end($numberParts);

                        if ($numberSuffix === $orderId && isset($loan['days_overdue'])) {
                            $data['overdue_days'] = (int)$loan['days_overdue'];
                            break;
                        }
                    }
                }
            }

            $result->data = $data;
            unset($result->user_loan_history);
        }

        return $results;
    }
    
    /**
     * Добавление комментария к тикету
     *`
     * @param string $comment
     * @param int $ticketID
     * @param int $managerID
     * @return void
     */
    public function addCommentToTicket(string $comment, int $ticketID, int $managerID): array
    {
        $dateToday = date('Y-m-d H:i:s');
        $ticket = $this->getTicketById($ticketID);

        $queryInsert = $this->db->placehold(
            "INSERT INTO __mytickets_comments (`manager_id`, `ticket_id`, `text`, `is_show`, `created_at`) VALUES (?, ?, ?, 1, ?)",
            $managerID,
            $ticketID,
            $comment,
            $dateToday
        );

        $this->db->query($queryInsert);

        if (in_array($ticket->status_id, [self::RESOLVED, self::UNRESOLVED, self::REQUEST_DETAILS])) {
            $ticket->data['has_comments_after_closing'] = true;

            $updatedData = json_encode($ticket->data);
            
            $this->db->query("UPDATE __mytickets SET data = ? WHERE id = ?", $updatedData, $ticketID);
        }

        if ($ticket->client_id) {
            $this->syncCommentWith1C([
                'manager_id' => $managerID,
                'client_id' => $ticket->client_id,
                'description' => 'ticket: ' . $comment
            ]);
        }
        
        return [
            'manager_id' => $managerID,
            'ticket_id' => $ticketID,
            'text' => $comment,
            'is_show' => 1,
            'created_at' => $dateToday
        ];
    }

    /**
     * Получить родительские темы
     *
     * @return array|false
     */
    public function getParentSubjects()
    {
        $query = $this->db->placehold("
            SELECT s.*
            FROM __mytickets_subjects s
            WHERE s.parent_id = 0 AND s.is_active = TRUE
            ORDER BY id ASC
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить темы обращения
     *
     * @return array Темы
     */
    public function getSubjects(): array
    {
        $this->db->query("
            SELECT 
                s.id, 
                s.name, 
                s.parent_id, 
                p.id as parent_id_value,
                p.name as parent_name,
                s.uid,
                s.yandex_goal_id
            FROM __mytickets_subjects s
            LEFT JOIN __mytickets_subjects p ON s.parent_id = p.id
            WHERE s.is_active = TRUE
            ORDER BY 
                s.id
        ");
        
        return $this->db->results() ?: [];
    }

    /**
     * Получить темы обращения с группировкой по родительским темам
     *
     * @return array Структурированный массив тем для выпадающего списка
     */
    public function getSubjectsGroupedByParent(): array
    {
        $query = $this->db->placehold("
            SELECT 
                s.id, 
                s.name, 
                s.parent_id, 
                p.name as parent_name
            FROM __mytickets_subjects s
            LEFT JOIN __mytickets_subjects p ON s.parent_id = p.id
            WHERE s.is_active = TRUE AND (p.is_active = TRUE OR p.id IS NULL)
            ORDER BY p.name, s.name ASC
        ");

        $this->db->query($query);
        $results = $this->db->results();

        $groupedSubjects = [];
        foreach ($results as $result) {
            // Если элемент является родительским (parent_id = NULL или 0)
            if (empty($result->parent_id)) {
                $groupedSubjects[$result->id] = [
                    'id' => $result->id,
                    'name' => $result->name,
                    'children' => []
                ];
            }
            // Если элемент является дочерним
            else {
                $parentId = $result->parent_id;
                // Создаем родительскую группу, если еще не существует
                if (!isset($groupedSubjects[$parentId])) {
                    $groupedSubjects[$parentId] = [
                        'id' => $parentId,
                        'name' => $result->parent_name,
                        'children' => []
                    ];
                }
                // Добавляем дочерний элемент
                $groupedSubjects[$parentId]['children'][] = [
                    'id' => $result->id,
                    'name' => $result->name
                ];
            }
        }

        return array_values($groupedSubjects);
    }
    
    /**
     * Получить тему обращения по ID
     *
     * @params int $id
     * @return array|false
     */
    public function getSubjectById(int $id)
    {
        $this->db->query('SELECT * FROM __mytickets_subjects WHERE id = ?', $id);

        return $this->db->result();
    }

    /**
     * Получаем родительские и дочерние темы обращений
     *
     * @return array{main: array<int, string>, child: array<int, string>}
     */
    public function getMainAndChildSubjects(): array
    {
        $subjects = $this->getSubjects();

        $mainSubjects  = [];
        $childSubjects = [];

        foreach ($subjects as $subject) {
            $id = (int)$subject->id;
            $name = $subject->name;

            if (empty($subject->parent_id)) {
                $mainSubjects[$id] = $name;
            } else {
                // Только если есть parent_id добавляем в дочерние
                $childSubjects[$id] = $name;
            }
        }

        return [
            'main'  => $mainSubjects,
            'child' => $childSubjects,
        ];
    }
    
    /**
     * Получить приоритеты тикетов
     *
     * @return array|false
     */
    public function getPriorities()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __mytickets_priority
            ORDER BY id ASC
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить приоритеты тикетов ТП
     *
     * @return array|false
     */
    public function getTsPriorities()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __mytickets_priority
            WHERE name != 'Критический'
            ORDER BY id ASC
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить каналы коммуникации
     *
     * @return array|false
     */
    public function getChannels()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM s_mytickets_channels
            ORDER BY id
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить направления обращения
     *
     * @return array|false
     */
    public function getDirections()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM s_mytickets_directions
            ORDER BY id
        ");

        $this->db->query($query);
        return $this->db->results();
    }


    public function getDirectionIdByCode(string $code): ?int
    {
        $query = $this->db->placehold('SELECT id FROM s_mytickets_directions WHERE code = ?', $code);
        $this->db->query($query);
        return $this->db->result('id') ?? null;
    }

    /**
     * Получить статусы тикета
     *
     * @return array|false
     */
    public function getStatuses()
    {
        $query = $this->db->placehold("
            SELECT *
            FROM __mytickets_statuses
            ORDER BY id ASC
        ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получить комментарии тикета
     *
     * @return array|false
     */
    public function getCommentsTicket(int $id)
    {
        $query = $this->db->placehold("
        SELECT com.id, com.text, man.name as manager_name, com.created_at
        FROM __mytickets_comments com
            
        LEFT JOIN __managers AS man
        ON man.id = com.manager_id

        WHERE com.ticket_id = $id AND com.is_show = 1 
        ORDER BY com.id DESC ");

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получение информации о клиенте на основе данных тикета
     *
     * @param object $ticketData Данные тикета
     * @return object|null Информация о клиенте или null
     */
    public function getUserInfo($ticketData)
    {
        // Если у тикета нет клиента, пытаемся найти его по номеру телефона
        if (!$ticketData->client_id) {
            $user_phone = $ticketData->data['phone'] ?? null;
            if ($user_phone) {
                return $this->getUserByPhone(preg_replace("/[^0-9]/", '', $user_phone));
            }
        }

        // Если клиент уже привязан, получаем его данные
        if ($ticketData->client_id) {
            return $this->users->get_user($ticketData->client_id);
        }

        return null;
    }

    /**
     * Получение прикреплённого займа на основе истории клиента
     *
     * @param object $ticketData Данные тикета
     * @param object|null $userInfo Информация о клиенте
     * @return object|null Информация о займе или null
     */
    public function getAttachedOrder($ticketData, $userInfo): ?object
    {
        if ($ticketData->order_id && $userInfo) {
            return $this->users->getLoanFromHistory($userInfo, $ticketData->order_id);
        }

        return null;
    }

    /**
     * @param $user_phone
     * TODO: удалить дубл getUserInfo()
     * @return false|int
     */
    public function getUserByPhone($user_phone)
    {
        $query = $this->db->placehold("
            SELECT 
                id,
                concat(lastname, ' ', firstname, ' ', patronymic) as fio_client,
                phone_mobile,
                birth,
                UID
            FROM __users 
            WHERE `phone_mobile` = ?", $user_phone);
        $this->db->query($query);
        return $this->db->result();
    }

    public function getCompanies()
    {
        $query = $this->db->placehold("SELECT * FROM __organizations WHERE use_in_tickets = 1");
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Принятие тикета в работу
     *
     * @param int $ticketID ID тикета
     * @param int $manager_id ID менеджера
     * @return array Результат операции
     */
    public function accept(int $ticketID, int $manager_id): array
    {
        $this->db->query('SELECT * FROM __mytickets WHERE id = ?', $ticketID);
        $ticket = $this->db->result();
        $oldManager = $ticket->manager_id;
        $oldStatus  = (int) $ticket->status_id;

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Тикет не найден.'
            ];
        }

        if (!$this->hasAccessToSubject($manager_id, $ticket->subject_id)) {
            return [
                'success' => false,
                'message' => 'У менеджера нет доступа к теме данного тикета.'
            ];
        }

        $query = "UPDATE __mytickets SET manager_id = ?, accepted_at = NOW(), status_id = ?, is_highlighted = 0 WHERE id = ?";
        $this->db->query($query, $manager_id, self::IN_WORK, $ticketID);
        
        if ($this->db->affected_rows() <= 0) {
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении тикета.'
            ];
        }

        if ($this->db->affected_rows() > 0) {
            $phone = null;
            if ($ticket->client_id) {
                $user = $this->users->get_user($ticket->client_id);
                $phone = $user->phone_mobile;
            } elseif (!empty($ticket->data)) {
                $data = json_decode($ticket->data, true);
                $phone = $data['phone'] ?? null;
            }

            if ($phone && (int)$ticket->company_id !== 10) {
                $this->sendTicketSmsNotification($phone, $ticket->client_id, $ticket->order_id, $ticket->subject_id, 'open');
            }
            
            if ($oldManager != $manager_id) {
                $this->logTicketHistory($ticketID, 'manager_id', $oldManager, $manager_id, $this->getManagerId(), 'Тикет принят: назначен менеджер');
            }
            if ($oldStatus !== self::IN_WORK) {
                $this->logTicketHistory($ticketID, 'status_id', $oldStatus, self::IN_WORK, $this->getManagerId(), 'Тикет принят: статус изменён');
            }

            TsTicketTable::getInstance()->initTsTicket($ticketID);
            $this->notifyTicketInitiatorAfterChangeStatus($ticketID);
        }
        
        return [
            'success' => true,
            'message' => 'Тикет успешно принят в работу.',
        ];
    }

    /**
     * Устанавливает статус подсветки тикета
     *
     * @param int $ticketId
     * @param int $status (1 - подсвечен, 0 - нет)
     * @return bool
     */
    public function setHighlightStatus(int $ticketId, int $status = 1): bool
    {
        $query = $this->db->placehold("UPDATE __mytickets SET is_highlighted = ? WHERE id = ?", $status, $ticketId);
        $this->db->query($query);
        return $this->db->affected_rows() > 0;
    }

    /**
     * Обновляет статус тикета и при необходимости:
     * - записывает информацию о получении обратной связи;
     * - назначает менеджера при переводе из нового в работу;
     * - отправляет SMS при переводе тикета в работу или урегулирован;
     * - сохраняет историю изменений;
     * - рассчитывает общее время работы по тикету;
     * - отправляет уведомления менеджерам при некоторых статусах.
     * - фиксирует последний комментарий для тем "взыскание"
     *
     * @param int $ticketId ID тикета
     * @param int $statusId Новый ID статуса тикета
     * @param int|null $feedbackReceived Признак получения обратной связи (1 - получена, 0 - не получена, null - без изменений)
     * @param string $historyMessage Сообщение для истории
     * @return array Ассоциативный массив с результатом выполнения и сообщением
     * @throws Exception
     */
    public function updateTicketStatus(int $ticketId, int $statusId, int $feedbackReceived = null, string $historyMessage = 'Статус тикета обновлён'): array
    {
        $this->db->query('SELECT * FROM __mytickets WHERE id = ?', $ticketId);
        $ticket = $this->db->result();

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Тикет не найден.'
            ];
        }

        if ($ticket->status_id == $statusId) {
            return [
                'success' => false,
                'message' => 'Статус уже установлен.'
            ];
        }

        if ($ticket->status_id == self::DUPLICATE) {
            return [
                'success' => false,
                'message' => 'Невозможно сменить статус у дублирующего тикета. Работайте с основным тикетом.'
            ];
        }

        $ticketData = json_decode($ticket->data, true) ?: [];

        if ($statusId === self::REQUEST_DETAILS) {
            if (!empty($ticketData['request_details_used'])) {
                return [
                    'success' => false,
                    'message' => 'Статус запроса уже был использован ранее.'
                ];
            }

            $ticketData['request_details_used'] = true;
        }

        $updatedData = json_encode($ticketData);
        $oldStatus = $ticket->status_id;

        $query = "UPDATE __mytickets SET status_id = ?, data = ?, is_highlighted = 0";
        $params = [$statusId, $updatedData];

        if ($oldStatus == self::NEW && $statusId == self::IN_WORK) {
            $currentManagerId = $this->getManagerId();
            $query .= ", manager_id = ?, accepted_at = NOW()";
            $params[] = $currentManagerId;
        }

        // Обновление даты закрытия при RESOLVED или UNRESOLVED
        if (in_array($statusId, [self::RESOLVED, self::UNRESOLVED])) {
            $query .= ", closed_at = NOW()";
        } else {
            $query .= ", closed_at = NULL";
        }

        if ($feedbackReceived !== null) {
            $query .= ", feedback_received = ?";
            $params[] = $feedbackReceived;
        }

        $query .= " WHERE id = ?";
        $params[] = $ticketId;

        // Обработка возвращения в работу
        $manager = $this->managers->get_manager($this->getManagerId());

        if ($statusId == self::IN_WORK && in_array($ticket->status_id, [self::RESOLVED, self::UNRESOLVED])) {
            $linkToTicket = "{$this->config->back_url}/tickets/{$ticketId}";
            $this->notificationsManagers->sendNotification([
                'from_user' => $manager->id,
                'to_user' => $ticket->manager_id,
                'subject' => "Тикет система",
                'message' => "{$manager->name} вернул тикет в работу. Тикет: {$linkToTicket}"
            ]);
        }

        $this->db->query($query, ...$params);

        if ($this->db->affected_rows() > 0) {
            $this->logTicketHistory($ticketId, 'status_id', $oldStatus, $statusId, $this->getManagerId(), $historyMessage);

            if (isset($ticketData['agreement_copy']) && $ticketData['agreement_copy'] == 1 && 
                in_array($statusId, [self::RESOLVED, self::UNRESOLVED])) {
                
                $this->removeAgreementHighlight($ticketId);
            }

            // Обработка тикета с родительской темой "взыскание"
            if (in_array($statusId, [self::RESOLVED, self::UNRESOLVED])) {
                $this->db->query("SELECT parent_id FROM __mytickets_subjects WHERE id = ?", $ticket->subject_id);
                $parentId = $this->db->result('parent_id');

                if ($parentId) {
                    $this->db->query("SELECT name FROM __mytickets_subjects WHERE id = ?", $parentId);
                    $parentName = mb_strtolower(trim($this->db->result('name')));

                    if ($parentName === 'взыскание') {
                        // Проверяем, не установлен ли уже финальный комментарий (не перезаписываем!)
                        if (empty($ticket->final_comment)) {
                            // Получаем последний комментарий
                            $this->db->query("
                                SELECT text FROM __mytickets_comments
                                WHERE ticket_id = ? AND is_show = 1
                                ORDER BY id DESC
                                LIMIT 1
                            ", $ticketId);
                            $lastComment = $this->db->result('text');

                            if ($lastComment) {
                                $this->db->query("
                                    UPDATE __mytickets 
                                    SET final_comment = ?
                                    WHERE id = ?
                                ", $lastComment, $ticketId);
                            }
                        }
                    }
                }
            }

            // Принятие тикета в работу и отправка SMS
            if ($oldStatus == self::NEW && $statusId == self::IN_WORK) {
                $currentManagerId = $this->getManagerId();
                if ($ticket->manager_id != $currentManagerId) {
                    $this->logTicketHistory($ticketId, 'manager_id', $ticket->manager_id, $currentManagerId, $currentManagerId, 'Тикет принят в работу: назначен менеджер');
                }

                $phone = null;
                if ($ticket->client_id) {
                    $user = $this->users->get_user($ticket->client_id);
                    if ($user) {
                        $phone = $user->phone_mobile;
                    }
                } elseif (!empty($ticketData)) {
                    $phone = $ticketData['phone'] ?? null;
                }

                if ($phone && (int)$ticket->company_id !== 10) {
                    $this->sendTicketSmsNotification(
                        $phone,
                        $ticket->client_id,
                        $ticket->order_id,
                        $ticket->subject_id,
                        'open'
                    );
                }
            }

            if ($oldStatus == self::IN_WORK) {
                $timeData = $this->calculateWorkingTime($ticketId);
                $totalTime = $timeData['closed_time'];
                if ($timeData['open_start'] !== null) {
                    // Если тикет всё ещё в работе, прибавляем время от начала текущего интервала до текущего момента
                    $totalTime += (time() - $timeData['open_start']);
                }

                $this->db->query("UPDATE s_mytickets SET working_time = ? WHERE id = ?", $totalTime, $ticketId);
            }

            if ($statusId === self::UNRESOLVED) {
                $managers = [381, 272];
                $linkToTicket = "{$this->config->back_url}/tickets/{$ticketId}";
                
                $notificationData = [
                    'subject' => 'Тикет система',
                    'message' => "У вас появился новый неурегулированный тикет: {$linkToTicket}"
                ];

                array_walk($managers, function ($manager) use ($notificationData) {
                    $this->notificationsManagers->sendNotification(array_merge($notificationData, ['to_user' => $manager]));
                });
            }

            TsTicketTable::getInstance()->initTsTicket($ticketId);
            $this->notifyTicketInitiatorAfterChangeStatus($ticketId);

            return [
                'success' => true,
                'message' => 'Статус тикета успешно обновлён.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении статуса тикета.'
            ];
        }
    }

    /**
     * Обновляет исполнителя тикета
     *
     * @param int $ticketId ID тикета
     * @param int $managerId ID нового менеджера
     * @return array Результат операции
     */
    public function updateManager(int $ticketId, int $managerId): array
    {
        $this->db->query('SELECT * FROM __mytickets WHERE id = ?', $ticketId);
        $ticket = $this->db->result();

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Тикет не найден.'
            ];
        }

        if ($ticket->manager_id === $managerId) {
            return [
                'success' => false,
                'message' => 'Этот исполнитель уже привязан к этому тикету'
            ];
        }

        $oldManager = $ticket->manager_id;

        $this->db->query("UPDATE __mytickets SET manager_id = ? WHERE id = ?", $managerId, $ticketId);

        if ($this->db->affected_rows() > 0) {
            $this->logTicketHistory($ticketId, 'manager_id', $oldManager, $managerId, $this->getManagerId(), 'Исполнитель тикета обновлён');

            $manager = $this->managers->get_manager($this->getManagerId());
            
            $linkToTicket = "{$this->config->back_url}/tickets/{$ticketId}";
            $this->notificationsManagers->sendNotification([
                'from_user' => $manager->id,
                'to_user' => $managerId,
                'subject' => "Тикет система",
                'message' => "{$manager->name} назначил вас исполнителем тикета. Тикет: {$linkToTicket}"
            ]);

            return [
                'success' => true,
                'message' => 'Исполнитель успешно обновлён'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при смене менеджера.'
            ];
        }
    }
    
    public function getTicketHistory(int $id)
    {
        $this->db->query('SELECT * FROM s_tickets_history WHERE ticket_id = ? ORDER BY changed_at DESC', $id);

        return $this->db->results();
    }

    /**
     * Записывает изменение в историю тикета
     *
     * @param int    $ticketId   ID тикета
     * @param string $fieldName  Изменённое поле (например, manager_id, status_id, creation)
     * @param mixed  $oldValue   Старое значение
     * @param mixed  $newValue   Новое значение
     * @param int    $changedBy  ID пользователя (или менеджера), совершившего изменение
     * @param string $comment    Комментарий к изменению
     * @return void
     */
    public function logTicketHistory(int $ticketId, string $fieldName, $oldValue, $newValue, $changedBy, string $comment = '')
    {
        $query = $this->db->placehold("
            INSERT INTO s_tickets_history 
            SET ticket_id = ?, field_name = ?, old_value = ?, new_value = ?, changed_by = ?, changed_at = NOW(), comment = ?
        ", $ticketId, $fieldName, $oldValue, $newValue, $changedBy, $comment);

        $this->db->query($query);
    }

    /**
     * Рассчитывает суммарное время, в течение которого тикет находился в статусе "В работе".
     * Если последний интервал ещё не завершён, возвращает время его начала.
     *
     * @param int $ticketId
     * @return array ['closed_time' => int, 'open_start' => int|null]
     * @throws Exception
     */
    public function calculateWorkingTime(int $ticketId): array
    {
        $this->db->query("
            SELECT changed_at, old_value, new_value 
            FROM s_tickets_history 
            WHERE ticket_id = ? AND field_name = 'status_id'
            ORDER BY changed_at ASC
        ", $ticketId);
        $history = $this->db->results();

        $totalTime = 0;
        $openStart = null;

        $tz = new DateTimeZone('Europe/Moscow');

        foreach ($history as $entry) {
            $changedAt = new DateTime($entry->changed_at, $tz);

            // Начало интервала: перевод в статус "В работе" (status_id = 5)
            if ((int)$entry->new_value === 5) {
                $openStart = $changedAt->getTimestamp();
            }
            // Конец интервала: выход из статуса "В работе"
            if ((int)$entry->old_value === 5 && $openStart !== null) {
                $endTime = $changedAt->getTimestamp();
                $totalTime += ($endTime - $openStart);
                $openStart = null;
            }
        }

        return [
            'closed_time' => $totalTime,
            'open_start' => $openStart
        ];
    }

    /**
     * Получить ответственное лицо (папку) для темы обращения
     *
     * @param int $subjectId ID темы обращения
     * @return object|bool Информация о папке или false, если не найдена
     */
    public function getResponsiblePersonForSubject(int $subjectId)
    {
        $this->db->query("
            SELECT rp.* 
            FROM s_mytickets_subjects AS subj
            LEFT JOIN s_responsible_persons AS rp ON subj.responsible_person_id = rp.id
            WHERE subj.id = ? AND subj.responsible_person_id IS NOT NULL
        ", $subjectId);

        return $this->db->result();
    }

    public function updateSubject($ticketId, $subjectId)
    {
        $this->db->query('SELECT * FROM __mytickets WHERE id = ?', $ticketId);
        $ticket = $this->db->result();

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Тикет не найден.'
            ];
        }

        $this->db->query('SELECT * FROM __mytickets_subjects WHERE id = ? AND parent_id > 0', $subjectId);
        $subject = $this->db->result();

        if (!$subject) {
            return [
                'success' => false,
                'message' => 'Некорректная тема. Выберите дочернюю тему.'
            ];
        }

        if ($ticket->subject_id === $subjectId) {
            return [
                'success' => false,
                'message' => 'Эта тема уже установлена для тикета'
            ];
        }

        $oldSubject = $ticket->subject_id;

        $this->db->query("UPDATE __mytickets SET subject_id = ? WHERE id = ?", $subjectId, $ticketId);

        if ($this->db->affected_rows() > 0) {
            $this->logTicketHistory($ticketId, 'subject_id', $oldSubject, $subjectId, $this->getManagerId(), 'Тема тикета обновлена');

            // Инициализация тикета ТП
            $tsTable = TsTicketTable::getInstance();
            $tsTable->initTsTicket($ticketId);
            $ticket = $tsTable->getByPrimary($ticketId);
            $highPriorityId = $this->getPriorityIdByName('Высокий');
            $newPriorityId = false;
            if ($ticket->getSubjectId() === $tsTable->getSubjectId()) {
                //Смена приоритета тикета от сотрудника ОПР
                if (
                    $this->isInitiatorOpr($ticket->getInitiatorId())
                    && $ticket->getPriorityId() !== $highPriorityId
                ) {
                    $newPriorityId = $highPriorityId;
                }
                //Запрет на критический приоритет для тикетов ТП
                elseif (!in_array($ticket->getPriorityId(), $this->getValidPrioritiesForTechnicalSupport())) {
                    $newPriorityId = $this->getPriorityIdByName('Средний');
                }
            }
            if ($newPriorityId !== false) {
                $this->db->query("UPDATE __mytickets SET priority_id = ? WHERE id = ?", $newPriorityId, $ticketId);
            }

            $manager = $this->managers->get_manager($this->getManagerId());

            $linkToTicket = "{$this->config->back_url}/tickets/{$ticketId}";
            $this->notificationsManagers->sendNotification([
                'from_user' => $manager->id,
                'to_user' => $ticket->manager_id,
                'subject' => "Тикет система",
                'message' => "{$manager->name} сменил тему тикета. Тикет: {$linkToTicket}"
            ]);

            return [
                'success' => true,
                'message' => 'Тема успешно обновлена',
                'new_subject_name' => $subject->name
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Ошибка при смене темы.'
            ];
        }
    }
    
    /**
     * Поиск тем обращений по названию
     *
     * @param string $term Поисковый запрос
     * @return array Отформатированный массив результатов
     */
    public function searchSubjects(string $term): array
    {
        $likeTerm = "%" . $term . "%";

        $this->db->query("
            SELECT s.id, s.name, s.parent_id, p.name as parent_name
            FROM __mytickets_subjects s
            LEFT JOIN __mytickets_subjects p ON s.parent_id = p.id
            WHERE (s.name LIKE ? OR p.name LIKE ?) AND s.parent_id > 0
            AND s.is_active = TRUE
            ORDER BY p.name, s.name
        ", $likeTerm, $likeTerm);

        $results = $this->db->results();

        $formattedResults = [];
        foreach ($results as $result) {
            $formattedResults[] = [
                'id' => $result->id,
                'name' => $result->name,
                'parent_id' => $result->parent_id
            ];
        }

        return $formattedResults;
    }

    /**
     * Получение тикетов клиента по ID клиента
     *
     * @param int $clientId ID клиента
     * @return array Массив тикетов клиента
     */
    public function getClientTickets(int $clientId): array
    {
        $query = $this->db->placehold("
            SELECT 
                tick.id,
                tick.created_at,
                tick.status_id,
                stat.name AS status_name,
                tick.subject_id,
                subj.name AS subject_name,
                tick.manager_id,
                man.name AS manager_name
            FROM __mytickets AS tick
            LEFT JOIN __mytickets_statuses AS stat ON stat.id = tick.status_id
            LEFT JOIN __mytickets_subjects AS subj ON subj.id = tick.subject_id
            LEFT JOIN __managers AS man ON man.id = tick.manager_id
            WHERE tick.client_id = ?
            ORDER BY tick.created_at DESC
        ", $clientId);

        $this->db->query($query);

        return $this->db->results() ?: [];
    }

    /**
     * Получить активный тикет клиента
     *
     * @param int $clientId
     * @return array|null
     */
    public function getClientMainTicket(int $clientId): ?object
    {
        $activeStatuses = [self::NEW, self::ON_HOLD, self::IN_WORK];
        $placeholders = implode(',', array_fill(0, count($activeStatuses), '?'));

        $query = $this->db->placehold("
            SELECT
                tick.id,
                tick.created_at,
                tick.status_id,
                stat.name AS status_name,
                stat.color AS status_color,
                subj.name AS subject_name,
                tick.duplicates_count,
                tick.is_highlighted
            FROM __mytickets AS tick
            LEFT JOIN __mytickets_statuses AS stat ON stat.id = tick.status_id
            LEFT JOIN __mytickets_subjects AS subj ON subj.id = tick.subject_id
            WHERE tick.client_id = ?
              AND tick.status_id IN ($placeholders)
              AND IFNULL(tick.is_duplicate, 0) = 0
            ORDER BY tick.created_at DESC
            LIMIT 1
    ", $clientId, ...$activeStatuses);

        $this->db->query($query);
        $result = $this->db->result();

        return $result ?: null;
    }

    /**
     * Отправка SMS-уведомления для тикета
     *
     * @param string $phone Номер телефона клиента
     * @param int|null $userId ID пользователя
     * @param int|null $orderId ID заказа
     * @param int|null $subjectId ID темы тикета
     * @param string $action Действие (create - создание, open - открытие)
     * @return bool Результат отправки
     */
    public function sendTicketSmsNotification(string $phone, int $userId = null, int $orderId = null, int $subjectId = null, string $action = 'create'): bool
    {
        if (!$phone || !$subjectId) {
            return false;
        }

        $actionToSmsTypeMap = [
            'create' => 'ticket_created',
            'open' => 'ticket_in_work'
        ];

        $smsType = $actionToSmsTypeMap[$action] ?? null;
        if (!$smsType) {
            return false;
        }


        if ($this->sms->isSmsAlreadySentToday($phone, $smsType)) {
            return false;
        }

        $allowedParentSubjects = [10, 9];

        $subject = $this->getSubjectById($subjectId);
        if (!$subject) {
            return false;
        }

        $parentId = ($subject->parent_id > 0) ? (int)$subject->parent_id : (int)$subject->id;

        if (!in_array($parentId, $allowedParentSubjects)) {
            return false;
        }

        $templateId = 53;

        if ($action === 'create') {
            $templateId = 63;
        } elseif ($action === 'open') {
            if ($parentId === 10) {
                $templateId = 52;
            } elseif ($parentId === 9) {
                $templateId = 61;
            }
        }
        $site_id = $this->users->get_site_id_by_user_id($userId);
        $template = $this->sms->get_template($templateId, $site_id);
        if (!$template) {
            return false;
        }

        $message = $template->template;
        $text = $message;

        $resp = $this->smssender->send_sms($phone, $text, $site_id);

        $this->sms->add_message([
            'user_id' => $userId ?: null,
            'order_id' => $orderId ?: null,
            'phone' => $phone,
            'message' => $message,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $resp[1],
            'delivery_status' => '',
            'send_id' => $resp[0],
            'type' => $smsType,
        ]);

        return $resp[1] == 'success';
    }

    /**
     * Отправка мобильного Push-уведомления по тикету
     * @param int $userId ID пользователя
     * @param string $type Тип уведомления ('pause' или 'resolved')
     * @param int|null $ticketId ID тикета (нужен для определения родительской темы)
     * @return array Данные с результатами отправки
     */
    public function sendTicketPushNotification(int $userId, string $type = 'pause', ?int $ticketId = null): array
    {
        $baseApiUrl = config('services.push.api_url');
        $apiKey = config('services.push.api_key');

        $pushTitle = self::PUSH_TITLE;
        $pushDescription = self::PUSH_DESCRIPTION;

        if ($type === 'resolved') {
            $pushTitle = self::RESOLVED_PUSH_TITLE;
            $pushDescription = self::RESOLVED_PUSH_DESCRIPTION;

            // Если передан ID тикета, проверяем родительскую тему
            if ($ticketId) {
                $ticket = $this->getTicketById($ticketId);

                if (!empty($ticket->subject_id)) {
                    $this->db->query("SELECT parent_id FROM __mytickets_subjects WHERE id = ?", $ticket->subject_id);
                    $parentId = $this->db->result('parent_id');

                    if ($parentId) {
                        $this->db->query("SELECT name FROM __mytickets_subjects WHERE id = ?", $parentId);
                        $parentName = $this->db->result('name');

                        if (mb_strtolower(trim($parentName)) === 'взыскание') {
                            $pushDescription = self::RESOLVED_PUSH_DESCRIPTION_CLAIMS;
                        }
                    }
                }
            }
        }

        if (!$userId) {
            return [];
        }

        $client = new Client([
            'timeout' => 10,
            'connect_timeout' => 5,
            'headers' => [
                'api-key' => $apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'PHP-MCAPI/2.0',
            ],
            'verify' => false,
        ]);

        $data = [
            'user_id' => [$userId],
            'title' => $pushTitle,
            'description' => $pushDescription,
        ];

        try {
            $response = $client->post($baseApiUrl . '/push/send', [RequestOptions::FORM_PARAMS => $data]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result ?? [];
        } catch (Exception|GuzzleException $e) {
            error_log("Push API Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Добавить пометку в тикет для отправки уведомления в ЛК клиента на сайте
     *
     * @param int $ticketId ID тикета
     * @return array Данные с результатами отправки
     */
    public function markTicketForNotification(int $ticketId): array
    {
        try {
            $this->db->query("UPDATE __mytickets SET notify_user = ? WHERE id = ?", 1, $ticketId);

            return [
                'success' => true,
                'message' => 'Тикет успешно обновлен для отправки уведомления в ЛК клиента на сайте',
            ];
        } catch (Exception $e) {
            error_log("Ticket Update Error: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Ошибка обновления тикета для отправки уведомления в ЛК клиента на сайте: '. $e->getMessage(),
            ];
        }
    }

    /**
     * Обработка данных заказа
     *
     * @param array $dataPost Данные из формы
     * @param array &$userData Данные пользователя для заполнения
     * @param bool &$workloadExclusionSucceeded Результат снятия с нагрузки
     * @param object|null $responsiblePerson Ответственное лицо
     * @return void
     */
    public function processOrderData(array $dataPost, array &$userData, bool &$workloadExclusionSucceeded, $responsiblePerson): void
    {
        $order = $this->orders->get_order($dataPost['order_id']);

        if (!$order) {
            $this->response->json_output([
                'status' => false,
                'message' => 'Займ не найден.'
            ]);
        }

        // Получение истории заказа и расчет просрочки
        $orderHistory = $this->users->getLoanFromHistory($order, $order->order_id);
        if ($orderHistory && !empty($orderHistory->plan_close_date)) {
            $paymentDate = Carbon::parse($orderHistory->plan_close_date);
            $daysOverdue = $paymentDate->isFuture() ? 0 : $paymentDate->diffInDays(Carbon::now());
            $userData['overdue_days'] = $daysOverdue;
        }

        // Проверка темы обращения
        $subject = $this->tickets->getSubjectById($dataPost['subject']);
        if (!$subject) {
            $this->response->json_output([
                'status' => false,
                'message' => 'Тема обращения не найдена.'
            ]);
        }

        // Проверка наличия менеджера
        $manager = $this->managers->get_manager($dataPost['manager_id']);
        if (!$manager) {
            $this->response->json_output([
                'status' => false,
                'message' => 'Менеджер не найден'
            ]);
        }

        // Отправка жалобы
        $this->soap->sendComplaint([
            'ComplaintUID' => $subject->uid,
            'LoanApplicationUID' => $order->order_uid,
            'Comment' => $dataPost['description'],
            'Responsible' => $manager->name_1c,
            'Source' => $dataPost['source'] ?: '',
        ]);

        // Снятие с нагрузки, если требуется
        if ($responsiblePerson && !empty($order->order_uid) && $dataPost['remove_from_load'] === 'on') {
            $contract = $this->contracts->get_contract($order->contract_id);

            if ($contract) {
                $workloadExclusionSucceeded = $this->soap->removeFromTheLoad($contract->uid, $responsiblePerson->uid);
            }
        }
    }

    /**
     * Получение ID ответственного лица по данным пользователя
     *
     * @param int $userId ID пользователя
     * @param int $orderId ID займа
     * @return int|null ID ответственного лица
     */
    public function getResponsiblePersonId(int $userId, int $orderId): ?int
    {
        $contract = $this->contracts->get_contract_by_params(['order_id' => $orderId]);

        if (empty($contract)) {
            return null;
        }

        if ((int)$contract->user_id !== $userId) {
            return null;
        }


        $response = $this->soap->getResponsibleForContracts([$contract->number]);

        if (empty($response) || isset($response['errors']) || !isset($response['response'][0])) {
            return null;
        }

        $contractData = $response['response'][0];


        if (empty($contractData['Ответственный'])) {
            return null;
        }

        $responsibleData = $contractData['Ответственный'];

        $dataToSave = [];

        if (is_array($responsibleData)) {
            $dataToSave['uid'] = $responsibleData['УИД'] ?? null;
            $dataToSave['code'] = $responsibleData['Ответственный'] ?? null;
            $dataToSave['group_name'] = $responsibleData['Группа']['Наименование'] ?? null;
            $dataToSave['group_uid'] = $responsibleData['Группа']['УИД'] ?? null;
        }
        elseif (is_string($responsibleData)) {
            $dataToSave['code'] = $responsibleData;
            $dataToSave['uid'] = null;
        }

        if (empty($dataToSave['code'])) {
            return null;
        }

        $dataToSave['is_sync_available'] = 0;

        return $this->createOrUpdateResponsiblePerson($dataToSave);
    }
    
    /**
     * Обработка отключения дополнительных услуг
     *
     * @param array $dataToSave Данные тикета
     * @return void
     */
    public function handleAdditionalServices(array $dataToSave): void
    {
        $autoDisableAdditionalServices = $this->settings->auto_disable_additional_services;

        if (
            $autoDisableAdditionalServices && !empty($dataToSave['order_id']) &&
            (
                (int)$dataToSave['subject_id'] === self::SUBJECT_COMPLAINT_ADDITIONAL_SERVICES_ID ||
                (int)$dataToSave['subject_id'] === self::SUBJECT_COMPLAINT_RECALCULATION_ID
            )
        ) {
            $this->order_data->disableAdditionalServices(
                $dataToSave['order_id'],
                $dataToSave['client_id'],
                $dataToSave['manager_id']
            );
        }
    }

    /**
     * Проверяет, имеет ли менеджер доступ к указанной теме
     *
     * @param int $managerId ID менеджера
     * @param int $subjectId ID темы
     * @return bool
     */
    public function hasAccessToSubject(int $managerId, int $subjectId): bool
    {
        // Получаем информацию о теме
        $subject = $this->getSubjectById($subjectId);
        if (!$subject) {
            return false;
        }

        $accessMap = [
            10 => $this->settings->authorized_dopy_managers ?? [],
            9  => $this->settings->authorized_collection_managers ?? [],
        ];

        foreach ($accessMap as $rootId => $authorizedManagers) {
            if ($subject->id == $rootId || $subject->parent_id == $rootId) {
                return in_array($managerId, $authorizedManagers);
            }
        }

        return true;
    }


    /**
     * Обработка спорной жалобы
     *
     * @param int $ticketId ID тикета
     * @return array Результат операции
     * @throws Exception
     */
    public function handleDisputedComplaint(int $ticketId): array
    {
        $this->db->query('SELECT * FROM __mytickets WHERE id = ?', $ticketId);
        $ticket = $this->db->result();

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Тикет не найден.'
            ];
        }

        // Проверка канала обращения
        $allowedChannels = [self::CHANNEL_PHONE, self::CHANNEL_EMAIL, self::CHANNEL_CHAT];
        if (!in_array($ticket->chanel_id, $allowedChannels)) {
            return [
                'success' => false,
                'message' => 'Функция "Спорная жалоба" недоступна для данного канала обращения.'
            ];
        }

        $hasJustificationComment = $this->checkJustificationComment($ticketId);
        if (!$hasJustificationComment) {
            return [
                'success' => false,
                'message' => 'Необходимо добавить комментарий с обоснованием решения о признании тикета спорным.'
            ];
        }

        $updateResult = $this->updateTicketStatus(
            $ticketId, 
            self::DISPUTED_COMPLAINT, 
            null, 
            'Тикет отмечен как спорная жалоба'
        );

        if ($updateResult['success']) {
            $this->sendDisputedComplaintNotifications($ticketId);

            $updateResult['message'] = 'Тикет успешно отмечен как спорная жалоба.';
        }

        return $updateResult;
    }

    /**
     * Проверка наличия комментария с обоснованием
     *
     * @param int $ticketId ID тикета
     * @return bool
     */
    private function checkJustificationComment(int $ticketId): bool
    {
        $managerId = $this->getManagerId();

        $this->db->query("
            SELECT COUNT(*) as count
            FROM __mytickets_comments
            WHERE ticket_id = ? 
            AND manager_id = ?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
            AND LENGTH(text) > 20
        ", $ticketId, $managerId);

        $result = $this->db->result();
        return $result->count > 0;
    }

    /**
     * Получение ID старшего смены, который онлайн
     *
     * @return int|null ID менеджера или null
     */
    private function getOnlineShiftSupervisor(): ?int
    {
        $shiftSupervisors = [
            self::MANAGER_SMIRNOVA_TATIANA,
            self::MANAGER_SMIRNOV_DANIIL
        ];

        foreach ($shiftSupervisors as $managerId) {
            if ($this->managers->isManagerOnline($managerId)) {
                return $managerId;
            }
        }

        return self::MANAGER_SMIRNOVA_TATIANA;
    }

    /**
     * Отправка уведомлений при создании спорной жалобы
     *
     * @param int $ticketId ID тикета
     * @return void
     */
    private function sendDisputedComplaintNotifications(int $ticketId): void
    {
        $linkToTicket = "{$this->config->back_url}/tickets/{$ticketId}";
        $currentManager = $this->managers->get_manager($this->getManagerId());

        $this->notificationsManagers->sendNotification([
            'from_user' => $currentManager->id,
            'to_user' => self::MANAGER_OMAROVA_DILARA,
            'subject' => "Спорная жалоба",
            'message' => "Создана спорная жалоба. Тикет: {$linkToTicket}"
        ]);
    }

    /**
     * Возврат спорной жалобы в статус "Новый"
     *
     * @param int $ticketId ID тикета
     * @return array Результат операции
     */
    public function returnDisputedComplaintToNew(int $ticketId): array
    {
        $this->db->query('SELECT * FROM __mytickets WHERE id = ? AND status_id = ?', $ticketId, self::DISPUTED_COMPLAINT);
        $ticket = $this->db->result();

        if (!$ticket) {
            return [
                'success' => false,
                'message' => 'Тикет не найден или не является спорной жалобой.'
            ];
        }

        $updateResult = $this->updateTicketStatus(
            $ticketId, 
            self::NEW, 
            null, 
            'Спорная жалоба признана обоснованной и возвращена в работу'
        );

        if ($updateResult['success']) {
            $updateResult['message'] = 'Тикет успешно возвращен в статус "Новый".';
        }

        return $updateResult;
    }

    /**
     * Получить ответственное лицо по его коду (имени)
     *
     * @param string $code
     * @return object|false
     */
    private function getResponsiblePersonByCode(string $code)
    {
        $this->db->query("SELECT * FROM __responsible_persons WHERE code = ?", $code);
        return $this->db->result();
    }

    /**
     * Проверяет наличие активного тикета от бота для данного договора
     *
     * @param int $order_id ID договора
     * @param int $subject_id
     * @return bool
     */
    public function hasActiveTicketFromBot($order_id, $subject_id): bool
    {
        $order_id = (int)$order_id;
        $subject_id = (int)$subject_id;

        $query = $this->db->placehold("
            SELECT COUNT(*) as count
            FROM __mytickets
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            AND order_id = ?
            AND initiator_id = ?
            AND subject_id = ?
            AND status_id != ?
        ",
            $order_id,
            self::MANAGER_SUVVY_BOT,
            $subject_id,
            self::RESOLVED
        );

        $this->db->query($query);
        return (int)$this->db->result('count') > 0;
    }

    /**
     * Добавляет клиента в белый список при создании тикета по темам взыскания или допов
     * @param array $data Данные тикета
     * @param int $ticketId ID созданного тикета
     * @return void
     */
    private function addClientToWhitelist(array $data, int $ticketId): void
    {
        if (empty($data['client_id']) || empty($data['subject_id'])) {
            return;
        }

        $subject = $this->getSubjectById($data['subject_id']);
        if (!$subject) {
            return;
        }

        $whitelistSubjectIds = [
            self::SUBJECT_COLLECTION_ID,
            self::SUBJECT_DOPY_ID
        ];

        $subjectId = (int)$subject->id;
        $parentId = (int)($subject->parent_id ?? 0);

        if (!in_array($subjectId, $whitelistSubjectIds, true) &&
            !in_array($parentId, $whitelistSubjectIds, true)) {
            return;
        }

        $existing = $this->user_data->read($data['client_id'], 'whitelist_dop');
        $wasAlreadyInWhitelist = !empty($existing);

        $this->user_data->set($data['client_id'], 'whitelist_dop', '1');

        if (!$wasAlreadyInWhitelist) {
            $this->logWhitelistAddition($data, $ticketId);
        }
    }

    /**
     * Логирует добавление клиента в белый список
     * @param array $data Данные тикета
     * @param int $ticketId ID тикета
     * @return void
     */
    private function logWhitelistAddition(array $data, int $ticketId): void
    {
        $managerId = ($data['initiator_id'] == self::MANAGER_SUVVY_BOT)
            ? self::MANAGER_SUVVY_BOT
            : ($this->getManagerId() ?: $data['initiator_id']);

        $changelogData = [
            'manager_id'  => $managerId,
            'type'        => 'whitelist_add',
            'old_values'  => json_encode(['whitelist_dop' => 0]),
            'new_values'  => json_encode([
                'whitelist_dop' => 1,
                'ticket_id'     => $ticketId
            ]),
            'user_id'     => $data['client_id'],
            'order_id'    => $data['order_id'] ?? null,
            'created'     => date('Y-m-d H:i:s'),
        ];

        $this->changelogs->add_changelog($changelogData);
    }

    /**
     * Отобразить доступные организации при создании тикетов
     *
     * @return array Результат операции
     */
    public function getCompaniesForTickets(): array
    {
        $query = $this->db->placehold("
        SELECT * 
        FROM __organizations 
        WHERE use_in_tickets = 1
    ");
        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    public function getStatusesToNotifyInitiator(): array
    {
        return [self::RESOLVED, self::ON_HOLD, self::IN_WORK];
    }

    public function getStatusNotifyMessageTemplate(int $statusId): string
    {
        $messagesMap = [
            self::IN_WORK => "У тикета <a href='/tickets/%s'>№%s</a> новый статус: В работе",
            self::RESOLVED => "У тикета <a href='/tickets/%s'>№%s</a> новый статус: Урегулирован",
            self::ON_HOLD => "У тикета <a href='/tickets/%s'>№%s</a> новый статус: Остановлен. Пожалуйста, перейдите в тикет для уточнения информации",
        ];

        return $messagesMap[$statusId] ?? "У тикета <a href='/tickets/%s'>№%s</a> изменился статус";
    }

    /**
     * Отправить уведомление о смене статуса менеджеру
     * @param int $ticketId
     * @return void
     */
    private function notifyTicketInitiatorAfterChangeStatus(int $ticketId): void
    {
        $ticket = $this->getTicketById($ticketId);

        if (!$ticket || !$ticket->initiator_id || ($ticket->subject_id != 19)) {
            return;
        }

        if ($ticket->initiator_id === $this->getManagerId()) {
            return;
        }

        $ticketStatusId = $ticket->status_id;

        if (!in_array($ticketStatusId, $this->getStatusesToNotifyInitiator())) {
            return;
        }

        $ticketNumber = $ticket->id;

        $notifyMessage = sprintf(
            $this->getStatusNotifyMessageTemplate($ticketStatusId),
            $ticketNumber,
            $ticketNumber
        );

        $this->notificationsManagers->sendNotification([
            'to_user' => $ticket->initiator_id,
            'message' => $notifyMessage,
            'subject' => 'Тикет система',
        ]);
    }

    /**
     * Смена приоритета тикета
     *
     * @param int $ticketId
     * @param int $priorityId
     * @return array
     */
    public function updatePriority(int $ticketId, int $priorityId): array
    {
        $this->db->query('SELECT * FROM __mytickets WHERE id = ?', $ticketId);
        $ticket = $this->db->result();

        if (!$ticket) {
            return ['success' => false, 'message' => 'Тикет не найден.'];
        }

        if ((int)$ticket->priority_id === $priorityId) {
            return [
                'success' => false,
                'message' => 'Этот приоритет уже установлен для тикета.'
            ];
        }

        $oldPriorityId = (int)$ticket->priority_id;

        $this->db->query(
            'SELECT id, name FROM __mytickets_priority WHERE id IN (?, ?)',
            $oldPriorityId, $priorityId
        );

        $prioritiesRaw = $this->db->results();
        $priorities = [];
        foreach ($prioritiesRaw as $row) {
            $priorities[(int)$row->id] = $row;
        }

        $oldPriorityName = $priorities[$oldPriorityId]->name ?? '';
        $newPriorityName = $priorities[$priorityId]->name ?? '';

        $this->db->query('UPDATE __mytickets SET priority_id = ? WHERE id = ?', $priorityId, $ticketId);

        if ($this->db->affected_rows() > 0) {
            $this->logTicketHistory($ticketId, 'priority_id', $oldPriorityId, $priorityId, $this->getManagerId(), 'Приоритет тикета обновлён');

            $commentText = sprintf('Приоритет изменен с "%s" на "%s"', $oldPriorityName, $newPriorityName);
            $this->addCommentToTicket($commentText, $ticketId, $this->getManagerId());

            return [
                'success' => true,
                'message' => 'Приоритет успешно обновлён'
            ];
        }

        return [
            'success' => false,
            'message' => 'Ошибка при смене приоритета.'
        ];
    }

    /**
     * Сохранение договоренности
     *
     * @param int $ticketId ID тикета
     * @param string $date Дата договоренности
     * @param string $note Суть договоренности
     * @return array Результат операции
     */
    public function saveAgreement(int $ticketId, string $date, string $note): array
    {
        try {
            $this->db->query("SELECT id FROM s_mytickets WHERE id = ?", $ticketId);
            if (!$this->db->result()) {
                return [
                    'success' => false,
                    'message' => 'Тикет не найден'
                ];
            }

            $this->db->query("
                SELECT id FROM s_mytickets_agreements 
                WHERE ticket_id = ? AND processed_at IS NULL
            ", $ticketId);
            
            if ($this->db->result()) {
                return [
                    'success' => false,
                    'message' => 'Для этого тикета уже есть активная договоренность'
                ];
            }

            $this->db->query("
                INSERT INTO s_mytickets_agreements
                    (ticket_id, agreement_date, note, created_by, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", $ticketId, $date, $note, $this->getManagerId());

            $this->db->query("
                UPDATE s_mytickets 
                SET data = JSON_SET(COALESCE(data, '{}'), '$.has_agreement', true)
                WHERE id = ?
            ", $ticketId);

            $this->logTicketHistory(
                $ticketId, 
                'agreement', 
                '',
                '',
                $this->getManagerId(), 
                "Назначена дата повторного контакта: {$date}. {$note}"
            );

            return [
                'success' => true,
                'message' => 'Договоренность сохранена'
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Ошибка сохранения: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Создание копии тикета по договоренности
     *
     * @param object $agreement Объект договоренности
     * @return int|false ID созданного тикета или false при ошибке
     */
    public function createAgreementCopy(object $agreement)
    {
        try {
            $this->db->query("SELECT * FROM s_mytickets WHERE id = ?", $agreement->ticket_id);
            $originalTicket = $this->db->result();
            
            if (!$originalTicket) {
                return false;
            }

            $originalData = json_decode($originalTicket->data ?? '{}', true) ?: [];

            $newData = array_merge($originalData, [
                'agreement_copy' => 1,
                'agreement_date' => $agreement->agreement_date,
                'agreement_note' => $agreement->note,
                'source_ticket_id' => $originalTicket->id
            ]);

            $newTicketData = [
                'client_id' => $originalTicket->client_id,
                'chanel_id' => $originalTicket->chanel_id,
                'subject_id' => $originalTicket->subject_id,
                'status_id' => self::AGREEMENT_REACHED,
                'priority_id' => $originalTicket->priority_id,
                'company_id' => $originalTicket->company_id,
                'order_id' => $originalTicket->order_id,
                'initiator_id' => $agreement->created_by,
                'manager_id' => $originalTicket->manager_id,
                'responsible_person_id' => $originalTicket->responsible_person_id,
                'description' => $originalTicket->description,
                'data' => json_encode($newData, JSON_UNESCAPED_UNICODE),
                'created_at' => date('Y-m-d 07:00:00'),
                'accepted_at' => null,
                'closed_at' => null
            ];

            $this->db->query("INSERT INTO s_mytickets SET ?%", $newTicketData);
            $newTicketId = $this->db->insert_id();

            if ($newTicketId) {
                $this->logTicketHistory(
                    $newTicketId, 
                    'agreement_copy', 
                    '',
                    '',
                    $agreement->created_by, 
                    "Копия по договоренностям из тикета #{$originalTicket->id} на {$agreement->agreement_date}"
                );

                $this->logTicketHistory(
                    $originalTicket->id, 
                    'agreement_copy', 
                    '',
                    '',
                    $agreement->created_by, 
                    "Создана копия #{$newTicketId} на {$agreement->agreement_date}"
                );

                return $newTicketId;
            }

            return false;

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Снятие выделения с копии договоренности
     *
     * @param int $ticketId ID тикета
     * @return bool Результат операции
     */
    public function removeAgreementHighlight(int $ticketId): bool
    {
        try {
            $this->db->query("SELECT data FROM s_mytickets WHERE id = ?", $ticketId);
            $ticketData = json_decode($this->db->result('data') ?? '{}', true) ?: [];

            if (!isset($ticketData['agreement_copy']) || $ticketData['agreement_copy'] != 1) {
                return false;
            }

            unset($ticketData['agreement_copy']);
            $updatedData = json_encode($ticketData, JSON_UNESCAPED_UNICODE);

            $this->db->query("UPDATE s_mytickets SET data = ? WHERE id = ?", $updatedData, $ticketId);

            $this->logTicketHistory(
                $ticketId, 
                'agreement_completed',
                '',
                '',
                $this->getManagerId(), 
                'Договоренность выполнена, выделение снято'
            );

            return true;

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Получение необработанных договоренностей на указанную дату
     *
     * @param string $date Дата в формате Y-m-d
     * @return array Массив договоренностей
     */
    public function getUnprocessedAgreements(string $date): array
    {
        $this->db->query("
        SELECT 
            a.id as agreement_id,
            a.ticket_id,
            a.agreement_date,
            a.note,
            a.created_by
        FROM s_mytickets_agreements a
        WHERE a.agreement_date = ? 
        AND a.processed_at IS NULL
        ORDER BY a.created_at ASC
        ", $date);

        return $this->db->results() ?: [];
    }

    /**
     * Отметить договоренность как обработанную
     *
     * @param int $agreementId ID договоренности
     * @return bool Результат операции
     */
    public function markAgreementAsProcessed(int $agreementId): bool
    {
        try {
            $this->db->query("
                UPDATE s_mytickets_agreements 
                SET processed_at = NOW() 
                WHERE id = ?
            ", $agreementId);

            return $this->db->affected_rows() > 0;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Получение копий договоренностей для указанного тикета
     *
     * @param int $sourceTicketId ID исходного тикета
     * @return array Массив копий договоренностей
     */
    public function getAgreementCopies(int $sourceTicketId): array
    {
        $this->db->query("
            SELECT id 
            FROM s_mytickets 
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data, '$.source_ticket_id')) = ?
        ", (string)$sourceTicketId);
        
        return $this->db->results() ?: [];
    }

    /**
     * Получение детальной информации о копиях договоренностей
     *
     * @param int $sourceTicketId ID исходного тикета
     * @return array Массив копий договоренностей с деталями
     */
    public function getAgreementCopiesDetails(int $sourceTicketId): array
    {
        $this->db->query("
            SELECT
                tick.id,
                tick.created_at,
                tick.status_id,
                JSON_UNQUOTE(JSON_EXTRACT(tick.data, '$.agreement_date')) as agreement_date,
                stat.name AS status_name
            FROM s_mytickets AS tick
            LEFT JOIN s_mytickets_statuses AS stat ON stat.id = tick.status_id
            WHERE JSON_UNQUOTE(JSON_EXTRACT(tick.data, '$.source_ticket_id')) = ?
            ORDER BY tick.created_at DESC
        ", (string)$sourceTicketId);
        
        return $this->db->results() ?: [];
    }

    /**
     * Получение комментариев из нескольких тикетов
     *
     * @param array $ticketIds Массив ID тикетов
     * @return array Массив комментариев
     */
    public function getCommentsFromMultipleTickets(array $ticketIds): array
    {
        if (empty($ticketIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ticketIds), '?'));
        $query = $this->db->placehold("
            SELECT
                com.id,
                com.text,
                com.created_at,
                man.name as manager_name,
                com.ticket_id
            FROM __mytickets_comments com
            LEFT JOIN __managers man ON man.id = com.manager_id
            WHERE com.ticket_id IN ($placeholders)
            AND com.is_show = 1
            ORDER BY com.created_at DESC
        ", ...$ticketIds);

        $this->db->query($query);

        return $this->db->results() ?: [];
    }

    /**
     * Перенос договоренности на другую дату
     *
     * @param int $ticketId ID текущей копии ДД
     * @param int $sourceTicketId ID исходного тикета
     * @param string $newDate Новая дата договоренности
     * @param string $reason Причина переноса
     * @return array Результат операции
     */
    public function rescheduleAgreement(int $ticketId, int $sourceTicketId, string $newDate, string $reason): array
    {
        try {
            $this->db->query("SELECT data FROM s_mytickets WHERE id = ?", $ticketId);
            $ticketData = json_decode($this->db->result('data') ?? '{}', true) ?: [];
            
            $oldNote = $ticketData['agreement_note'] ?? '';
            $oldDate = $ticketData['agreement_date'] ?? '';

            $noteText = $oldNote;
            if ($reason) {
                $noteText .= " (Перенесено: {$reason})";
            }

            $this->db->query("
                INSERT INTO s_mytickets_agreements
                    (ticket_id, agreement_date, note, created_by, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ", $sourceTicketId, $newDate, $noteText, $this->getManagerId());

            $this->updateTicketStatus($ticketId, self::UNRESOLVED);

            $this->logTicketHistory(
                $ticketId,
                'agreement_rescheduled',
                $oldDate,
                $newDate,
                $this->getManagerId(),
                "Договоренность перенесена с {$oldDate} на {$newDate}" . ($reason ? ". Причина: {$reason}" : "")
            );

            $this->logTicketHistory(
                $sourceTicketId,
                'agreement',
                '',
                '',
                $this->getManagerId(),
                "Договоренность перенесена на {$newDate}" . ($reason ? ". Причина: {$reason}" : "")
            );
            
            return [
                'success' => true,
                'message' => 'Договоренность перенесена'
            ];
            
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Ошибка переноса: ' . $e->getMessage()
            ];
        }
    }


    private function isInitiatorOpr(int $initiatorId): bool
    {
        $query = $this->db->placehold('SELECT role FROM s_managers WHERE id = ?', $initiatorId);
        $this->db->query($query);
        return $this->db->result('role') === 'opr';
    }

    private function getPriorityIdByName(string $name): int
    {
        $query = $this->db->placehold('SELECT id FROM s_mytickets_priority WHERE name = ?', $name);
        $this->db->query($query);
        return intval($this->db->result('id'));
    }

    private function getValidPrioritiesForTechnicalSupport(): array
    {
        $result = [];

        $query = $this->db->placehold('SELECT id FROM s_mytickets_priority WHERE name IN (?,?)', 'Минимальный', 'Средний');
        $this->db->query($query);

        foreach ($this->db->results() as $priority) {
            $result[] = (int)$priority->id;
        }

        return $result;
    }
}
