<?php

declare(strict_types=1);

require_once 'View.php';

/**
 * MissingsView - Класс для отображения и обработки отвалившихся заявок пользователей
 */
class MissingsView extends View
{
    /**
     * Количество элементов на странице по умолчанию
     */
    private const ITEMS_PER_PAGE = 20;

    /**
     * Пороговое значение тайм-аута для отвалившихся пользователей (в секундах)
     */
    private const MISSING_TIMEOUT = 300;

    /**
     * Соответствие полей и номеров этапов
     */
    private const STAGE_MAP = [
        'additional_data_added' => 7,
        'files_added' => 6,
        'card_added' => 5,
        'accept_data_added' => 4,
        'address_data_added' => 3,
        'personal_data_added' => 2
    ];

    /**
     * Метод для получения и подготовки данных
     */
    public function fetch(): string
    {
        if (!in_array('missings', $this->manager->permissions, true)) {
            return $this->design->fetch('403.tpl');
        }

        if ($this->request->method('post') && $this->request->post('action', 'string') === 'set_manager') {
            $this->setManagerAction();
        }

        $filter = $this->prepareFilterParameters();
        $paginationData = $this->handlePagination($filter);
        $filter = array_merge($filter, $paginationData['filter']);

        $clients = $this->getClientsWithData($filter);

        if (!empty($clients)) {
            $userIds = array_column($clients, 'id');
            $callRobotStatuses = $this->getCallRobotStatuses($userIds);

            foreach ($clients as &$client) {
                if (isset($callRobotStatuses[$client->id])) {
                    $client->status = $callRobotStatuses[$client->id];
                }
            }
        }

        $smsTemplates = $this->sms->get_templates(['type' => 'missing']);
//        $statistics = $this->getStatistics();

        $this->design->assign_array([
            'clients' => $clients,
            'sms_templates' => $smsTemplates,
//            'statistic' => $statistics,
            'stages_stats' => [
                'stage1' => $statistics->stage1 ?? 0,
                'stage2' => $statistics->stage2 ?? 0,
                'stage3' => $statistics->stage3 ?? 0,
                'stage4' => $statistics->stage4 ?? 0,
                'stage5' => $statistics->stage5 ?? 0,
                'stage6' => $statistics->stage6 ?? 0,
                'stage7' => $statistics->stage7 ?? 0
            ]
        ]);

        return $this->design->fetch('missings.tpl');
    }

    private function getCallRobotStatuses(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $query = $this->db->placehold("
            SELECT user_id, status
            FROM s_vox_robot_calls
            WHERE user_id IN (?@)
        ", $userIds);

        $this->db->query($query);
        $results = $this->db->results();

        $statuses = [];
        if ($results) {
            foreach ($results as $result) {
                $statuses[$result->user_id] = $result->status;
            }
        }

        return $statuses;
    }

    /**
     * Подготовка параметров фильтра
     *
     * @return array Параметры фильтра
     */
    private function prepareFilterParameters(): array
    {
        $filter = [
            'missing' => self::MISSING_TIMEOUT,
            'exclude_duplicates' => false,
            'first_missings' => true,
            'include_org_changed' => true,
        ];

        $status = $this->request->get('status', 'string');
        $sort = $this->request->get('sort', 'string') ?: 'date_desc';
        $search = (array)($this->request->get('search') ?? []);

        if (!empty($search['stages'])) {
            $search['stages'] = array_values(array_unique(
                array_filter((array)$search['stages'])
            ));
        }

        $search = array_filter($search, static function ($value) {
            return is_array($value) ? !empty($value) : $value !== '' && $value !== null;
        });

        if ($search) {
            $filter['search'] = $search;
        }

        if ($status === 'unhandled') {
            $filter['missing_manager_id'] = null;
        }

        $filter['sort'] = $sort;

        $this->design->assign_array([
            'status' => $status,
            'sort' => $sort,
            'search' => $search ?: null,
        ]);

        return $filter;
    }

    /**
     * Обработка пагинации для списка клиентов
     *
     * @param array $filter Параметры фильтра
     * @return array Данные пагинации, включая обновленный фильтр
     */
    private function handlePagination(array $filter): array
    {
        $currentPage = max(1, (int)$this->request->get('page', 'integer'));
        $clientsCount = $this->users->count_users($filter);
        $pagesCount = ceil($clientsCount / self::ITEMS_PER_PAGE);

        $this->design->assign_array([
            'current_page_num' => $currentPage,
            'total_pages_num' => $pagesCount,
            'total_orders_count' => $clientsCount
        ]);

        return [
            'filter' => [
                'page' => $currentPage,
                'limit' => self::ITEMS_PER_PAGE,
            ],
            'count' => $clientsCount,
            'pages' => $pagesCount,
        ];
    }

    /**
     * Получение клиентов со всеми связанными данными (звонки, этапы)
     *
     * @param array $filter Параметры фильтра
     * @return array Массив объектов клиентов с дополнительной информацией
     */
    private function getClientsWithData(array $filter): array
    {
        $clients = $this->users->get_users(
            $filter,
            ['with_last_comment' => true, 'last_comment_type' => 'missing', 'with_status' => true]
        );

        if (empty($clients)) {
            return [];
        }

        $userIds = array_column($clients, 'id');
        $lastCalls = $this->getLastCalls($userIds);
        $this->design->assign('last_calls', $lastCalls);

        $orgSwitchData = $this->users->getOrgSwitchData($userIds);
        $this->design->assign('org_switch_data', $orgSwitchData);

        foreach ($clients as &$client) {
            // Добавляем информацию о последнем звонке если есть
            if (isset($lastCalls[$client->id])) {
                $client->last_call = $lastCalls[$client->id];
            }

            // Определяем стадию клиента
            $client = $this->defineClientStage($client);
        }

        return $clients;
    }

    /**
     * Получение последнего звонка для каждого пользователя
     *
     * @param array $userIds Массив ID пользователей
     * @return array Ассоциативный массив user_id => данные_звонка
     */
    private function getLastCalls(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $sql = "
            SELECT 
                user_id, 
                created, 
                'vox' AS source 
            FROM s_vox_calls 
            WHERE user_id IN (?@)
            ORDER BY created DESC
        ";

        $this->db->query($sql, $userIds);
        $calls = $this->db->results();

        $lastCalls = [];
        foreach ($calls as $call) {
            $userId = $call->user_id;
            $callDate = new DateTime($call->created);

            if (!isset($lastCalls[$userId]) ||
                new DateTime($lastCalls[$userId]->created) < $callDate) {
                $lastCalls[$userId] = $call;
            }
        }

        return $lastCalls;
    }

    /**
     * Определение этапа клиента на основе завершенных шагов
     *
     * @param object $client Объект клиента
     * @return object Обновленный объект клиента с информацией об этапе
     */
    private function defineClientStage(object $client): object
    {
        foreach (self::STAGE_MAP as $field => $stage) {
            if (!empty($client->{$field})) {
                $client->stages = $stage;
                $client->last_stage_date = $client->{$field . '_date'};
                return $client;
            }
        }

        $client->stages = 1;
        $client->last_stage_date = $client->created;

        return $client;
    }

    /**
     * Назначение менеджера для отвалившейся заявки
     */
    public function setManagerAction(): void
    {
        $userId = (int)$this->request->post('user_id', 'integer');

        if (!$userId) {
            $this->json_output(['error' => 'EMPTY_USER_ID']);
        }

        $user = $this->users->get_user($userId);
        if (!$user) {
            $this->json_output(['error' => 'UNDEFINED_USER']);
        }

        $affected = $this->users->assign_manager($userId, $this->manager->id);

        if ($affected === 0) {
            $this->json_output(['error' => 'Заявка уже принята']);
        }

        $this->json_output(['success' => 1, 'manager_name' => $this->manager->name]);
    }

    /**
     * Получение статистики
     *
     * @return object Объект статистики с количеством и метриками
     */
    private function getStatistics(): object
    {
        $sql = $this->prepareStatisticsQuery();
        $this->db->query($sql, date('Y-m-d'));
        $result = $this->db->result();

        if (!$result) {
            $result = $this->createEmptyStatisticsObject();
        }

        // Расчет коэффициента конверсии с использованием тернарного оператора
        $result->conversion = $result->in_progress > 0
            ? round(($result->completed / $result->in_progress) * 100)
            : 0;

        return $result;
    }

    /**
     * Подготовка SQL-запроса для статистики
     *
     * @return string SQL-запрос
     */
    private function prepareStatisticsQuery(): string
    {
        $firstMissingDateExpr = $this->getFirstMissingDateExpr();
        $lastStageExpr = $this->getLastStageExpr();

        return "
            SELECT
                -- Общее количество заявок на сегодня
                COUNT(DISTINCT u.id) AS totals,
                
                -- Взято в работу - клиенты с назначенным менеджером
                SUM(IF(u.missing_manager_id > 0, 1, 0)) AS in_progress,
                
                -- Заполнено полностью - полностью заполненные заявки
                SUM(IF(u.missing_manager_id > 0 AND u.additional_data_added = 1, 1, 0)) AS completed,
                
                -- Не обработано - нет назначенного менеджера
                SUM(
                  IF((
                    (COALESCE(u.missing_manager_id, 0) <= 0)
                    AND (u.additional_data_added <> 1 
                         AND (u.additional_data_added_date IS NULL 
                              OR DATE(u.additional_data_added_date) <> CURDATE())
                        AND (
                          SELECT COUNT(*)
                          FROM s_orders o
                          WHERE o.user_id = u.id
                            AND DATE(o.date) = DATE(u.accept_data_added_date)
                            AND o.status = 1
                        )
                    )
                  ), 1, 0)
                ) AS unhandled,
                
                -- Продолжат заполнение - continue_order = 1
                SUM(IF(u.continue_order = 1, 1, 0)) AS continue_order,
                
                -- Статистика по этапам
                SUM(IF($lastStageExpr = 1, 1, 0)) AS stage1,
                SUM(IF($lastStageExpr = 2, 1, 0)) AS stage2,
                SUM(IF($lastStageExpr = 3, 1, 0)) AS stage3,
                SUM(IF($lastStageExpr = 4, 1, 0)) AS stage4,
                SUM(IF($lastStageExpr = 5, 1, 0)) AS stage5,
                SUM(IF($lastStageExpr = 6, 1, 0)) AS stage6,
                SUM(IF($lastStageExpr = 7, 1, 0)) AS stage7
            FROM s_users u
            LEFT JOIN s_user_data ud ON ud.user_id = u.id AND ud.key = 'is_rejected_nk'
            WHERE 
                (ud.value IS NULL OR ud.value = 0) AND
                $firstMissingDateExpr IS NOT NULL AND
                DATE($firstMissingDateExpr) = ?
        ";
    }

    /**
     * Создание пустого объекта статистики, когда данные отсутствуют
     *
     * @return object Пустой объект статистики
     */
    private function createEmptyStatisticsObject(): object
    {
        $result = new stdClass();
        $properties = [
            'totals', 'in_progress', 'completed', 'unhandled', 'continue_order',
            'stage1', 'stage2', 'stage3', 'stage4', 'stage5', 'stage6', 'stage7'
        ];

        foreach ($properties as $property) {
            $result->{$property} = 0;
        }

        return $result;
    }

    /**
     * Получение SQL-выражения для определения даты первого отвала
     *
     * @return string SQL CASE-выражение
     */
    private function getFirstMissingDateExpr(): string
    {
        return "CASE 
                WHEN u.personal_data_added_date IS NULL THEN u.created
                WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date 
                      OR u.address_data_added_date IS NULL) THEN u.personal_data_added_date
                WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date 
                      OR u.accept_data_added_date IS NULL) THEN u.address_data_added_date
                WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date 
                      OR u.card_added_date IS NULL) THEN u.accept_data_added_date
                WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date 
                      OR u.files_added_date IS NULL) THEN u.card_added_date
                WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date 
                      OR u.additional_data_added_date IS NULL) THEN u.files_added_date
                ELSE NULL
            END";
    }

    /**
     * Получение SQL-выражения для определения последнего этапа
     *
     * @return string SQL CASE-выражение
     */
    private function getLastStageExpr(): string
    {
        return "CASE
                WHEN u.personal_data_added_date IS NULL THEN 1
                WHEN (DATE_ADD(u.personal_data_added_date, INTERVAL 5 MINUTE) <= u.address_data_added_date 
                      OR u.address_data_added_date IS NULL) THEN 2
                WHEN (DATE_ADD(u.address_data_added_date, INTERVAL 5 MINUTE) <= u.accept_data_added_date 
                      OR u.accept_data_added_date IS NULL) THEN 3
                WHEN (DATE_ADD(u.accept_data_added_date, INTERVAL 5 MINUTE) <= u.card_added_date 
                      OR u.card_added_date IS NULL) THEN 4
                WHEN (DATE_ADD(u.card_added_date, INTERVAL 5 MINUTE) <= u.files_added_date 
                      OR u.files_added_date IS NULL) THEN 5
                WHEN (DATE_ADD(u.files_added_date, INTERVAL 5 MINUTE) <= u.additional_data_added_date 
                      OR u.additional_data_added_date IS NULL) THEN 6
                ELSE 7
            END";
    }
}