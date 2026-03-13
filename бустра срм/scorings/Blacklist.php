<?php

class Blacklist extends Simpla
{
    private const SYSTEM_NAME = 'boostra.ru';


    /** Максимальное количество попыток ЧС при ошибке сервиса */
    public const MAX_BLACKLIST_ERROR_ATTEMPTS = 3;

    /**
     * List of need fields record
     * @var array $fieldList
     */
    protected $fieldList = [
        'u.firstname',
        'u.lastname',
        'u.patronymic',
        'u.phone_mobile',
        'b.user_id',
        'b.manager_id',
        'b.comment',
        'b.created_date',
        'm.name_1c'
    ];

    public const REASONS = [
        "Убран из нагрузки",
        "Клиент жалуется в надзорные инстанции (ЦБ, ФССП..)",
        "Клиент ведет себя неадекватно",
        "Клиент банкротится",
        "По просьбе близких родственников",
        "По просьбе самого клиента",
        "Подтверждена смерть клиента",
        "Подозрение на мошенничество",
        "Диспут на займ",
        "С В О",
        "Жалуется на допы.",
        "Невозможно взыскать",
        "Возврат доп. услуг",
    ];

    /**
     * Current Sql Query
     * @var string $sqlQuery
     */
    private $sqlQuery = '';

    /**
     * Count of finded records
     * @var int $count
     */
    public $count = 0;

    /**
     * One record
     * @var object $row
     */
    public $row;

    /**
     * Multiple records
     * @var array $rows
     */
    public $rows;

    /**
     * Filters for query
     * @var array $filters
     */
    protected $filters = [];

    /**
     * Method for run scorings
     * @param $scoring_id
     * @return array|void
     */
    public function run_scoring($scoring_id)
    {
        $scoringType = $this->scorings->get_type($this->scorings::TYPE_BLACKLIST);
        if (empty($scoringType->active)) {
            $update = [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => true,
                'body' => '',
                'string_result' => 'Проверка на стороне СРМ отключена',
                'end_date' => date('Y-m-d H:i:s')
            ];
            $this->scorings->update_scoring($scoring_id, $update);
            return $update;
        }
        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            if ($client = $this->users->get_user((int)$scoring->user_id)) {
                $is_local_blacklist = $this->in($scoring->user_id);
                $result = null;
                $is_remote_blacklisted = false;

                if (!$is_local_blacklist) {
                    // Try up to 5 times to get a response from 1C
                    for ($attempt = 1; $attempt <= 5; $attempt++) {
                        $result = $this->checkIsUserIn1cBlacklistSafe($client->UID);
                        if ($result['ok']) {
                            // Successful response from 1C, store the blacklist status
                            $is_remote_blacklisted = $result['in_blacklist'];
                            break;
                        }
                        if ($attempt < 5) {
                            sleep($attempt * 10);  // wait before retrying
                        }
                    }
                }

                if (!$is_local_blacklist && (!$result || !$result['ok'])) {
                    // All attempts to contact 1C failed -> mark as scoring error
                    $update = [
                        'status'  => $this->scorings::STATUS_ERROR,
                        'string_result'    => 'Сервис 1С недоступен',  // 1C service not working
                        'success' => false,
                        'body'    => ''
                    ];
                } else {
                    // Determine final blacklist status based on local or 1C results
                    $is_blacklist = $is_local_blacklist || $is_remote_blacklisted;
                    $update = [
                        'status'  => $this->scorings::STATUS_COMPLETED,
                        'success' => !$is_blacklist,
                        'body'    => ''
                    ];
                    if ($is_blacklist) {
                        $update['body'] = serialize([
                            'created' => $this->row->created_date,
                            'text'    => $this->row->comment,
                            'block'   => $this->row->name_1c
                        ]);
                        $update['string_result'] = 'Клиент найден в ЧС';
                        if (!empty($scoring->order_id) && ($order = $this->orders->get_order((int)$scoring->order_id))) {
                            $this->orders->rejectOrder($order, $this->reasons::REASON_BLACK_LIST);
                            // Stop other scorings for this order
                            $update['status'] = $this->scorings::STATUS_COMPLETED;
                            $scoring_type = $this->scorings->get_type($this->scorings::TYPE_BLACKLIST);
                            $this->scorings->stopOrderScorings($order->order_id, [
                                'string_result' => 'Причина: скоринг ' . $scoring_type->title
                            ]);
                        }
                    } else {
                        $update['string_result'] = 'Клиент в ЧС не найден';
                    }
                }
            } else {
                $update = [
                    'status' => $this->scorings::STATUS_ERROR,
                    'body' => 'Клиент не найден',
                    'success' => 0
                ];
            }

            if (!empty($update)) {
                $update['end_date'] = date('Y-m-d H:i:s');
                $this->scorings->update_scoring($scoring_id, $update);
            }
            return $update;
        }
    }

    /**
     * Get filter by FIO
     * @param string $searchText
     * @return string[]
     */
    public function getFilterFio(string $searchText): array
    {
        $searchText = $this->db->escape($searchText);
        return ["(CONCAT(lastname, ' ', firstname, ' ', patronymic) LIKE '%{$searchText}%' OR 
                  CONCAT(lastname, ' ', patronymic, ' ', firstname) LIKE '%{$searchText}%' OR        
                  CONCAT(firstname, ' ', patronymic, ' ', lastname) LIKE '%{$searchText}%' OR        
                  CONCAT(firstname, ' ', lastname, ' ', patronymic) LIKE '%{$searchText}%' OR        
                  CONCAT(patronymic, ' ', lastname, ' ', firstname) LIKE '%{$searchText}%' OR        
                  CONCAT(patronymic, ' ', firstname, ' ', lastname) LIKE '%{$searchText}%' )" => '$'];
    }

    /**
     * Get filter by comment
     * @param string $searchText
     * @return string[]
     */
    public function getFilterComment(string $searchText): array
    {
        $searchText = $this->db->escape($searchText);
        return ["comment LIKE '%{$searchText}%'" => '$'];
    }

    /**
     * Get Filter by phone
     * @param string $searchText
     * @return string[]
     */
    public function getFilterPhone(string $searchText): array
    {
        $searchText = $this->db->escape($searchText);
        return ["phone_mobile LIKE '%{$searchText}%'" => '$'];
    }

    /**
     * Search in blacklist with filters
     * @return bool
     */
    protected function search(): bool
    {
        $limit = $order = $filters = '';
        if ($this->filters) {
            if (!empty($this->filters['fields'])) {
                $this->setFieldList($this->filters['fields']);
                unset($this->filters['fields']);
            }
            if (!empty($this->filters['sort'])) {
                $this->filters['order'] = !empty($this->filters['order']) ? $this->filters['order'] : 'ASC';
                $limit = 'ORDER BY ' . $this->filters['sort'] . ' '. $this->filters['order'];
                unset($this->filters['sort'], $this->filters['order']);
            }
            if (!empty($this->filters['limit'])) {
                $this->filters['offset'] = !empty($this->filters['offset']) ? $this->filters['offset'] : 0;
                $limit = 'LIMIT ' . $this->filters['offset'] . ', '. $this->filters['limit'];
                unset($this->filters['limit'], $this->filters['offset']);
            }
            if ($this->filters) {
                $filters = 'WHERE ?$';
            }
        }
        $fields = implode(', ', $this->fieldList);
        $sql = "SELECT  {$fields} 
            FROM __blacklist b
            LEFT JOIN __managers m ON m.id = b.manager_id
            LEFT JOIN __users u ON u.id = b.user_id 
            {$filters}
            {$order}
            {$limit}";
        $this->db->query($this->sqlQuery = $this->db->placehold($sql, $this->filters));
        if ($this->count = $this->db->num_rows()) {
            if ($this->count > 1) {
                $this->rows = $this->db->results();
            } else {
                $this->row = $this->db->result();
            }
            return true;
        }
        return false;
    }

    /**
     * Set need fields list for record
     * @param array|string $fields
     * @return void
     */
    public function setFieldList($fields): void
    {
        $this->fieldList = is_array($fields) ? $fields : [$fields];
    }

    /**
     * Get current sql query
     * @return string
     */
    public function toSql(): string
    {
        return $this->sqlQuery;
    }

    /**
     * Check has user in blacklist
     * @param int $userId
     * @return bool
     */
    public function in(int $userId): bool
    {
        $cacheKey = "blacklist:in:{$userId}";
        return (bool)$this->caches->wrap($cacheKey, 600, function () use ($userId) {
            $this->filters = [
                'user_id' => $userId,
                'limit' => 1
            ];
            return $this->search();
        });
    }

    /**
     * Get count of records
     * @param array $filters
     * @param string $byField
     * @return int
     */
    public function count(array $filters = [], string $byField = ''): int
    {
        $byField = $byField ?: '*';
        $this->filters = $filters;
        if (empty($this->filters['fields'])) {
            $filters['fields'] = $this->filters['fields'] = "COUNT({$byField})";
        }
        unset($this->filters['limit'], $this->filters['offset'], $this->filters['sort'], $this->filters['order']);
        $this->search();
        return !empty($this->row->{$filters['fields']}) ? $this->row->{$filters['fields']} : 0;
    }

    /**
     * Remove user from Blacklist
     * @param int $userId
     * @return void
     */
    public function delete(int $userId): void
    {
        $this->db->query($this->db->placehold("DELETE FROM __blacklist WHERE user_id = ?", $userId));
        $this->caches->delete("blacklist:in:{$userId}");
    }

    /**
     * Add to blacklist
     * @param array $data
     * @return int
     */
    public function add(array $data): int
    {
        if (!$data) {
            return 0;
        }
        $this->db->query($this->db->placehold("INSERT INTO __blacklist SET ?%", $data));
        $id = $this->db->insert_id();

        if (isset($data['user_id'])) {
            $this->caches->delete("blacklist:in:{$data['user_id']}");
        }

        return $id;
    }

    /**
     * Get one in blacklist
     * @param array $filters
     * @return ?object
     */
    public function getOne(array $filters = [])
    {
        $this->filters = $filters;
        if (empty($this->filters['limit'])) {
            $this->filters['limit'] = 1;
        }

        // Если пользватель найден в ЧС, то возвращаем его, если нет, то возвращаем null
        if ($this->search() === true) {
            return $this->row;
        }

        return null;
    }

    /**
     * Get all records
     * @param array $filters
     * @return ?array
     */
    public function getAll(array $filters = [])
    {
        $this->filters = $filters;
        $this->search();
        return $this->rows;
    }

    public function sendAddUserToBlacklist1c(string $uid, string $reason, string $comment, string $manager1cName)
    {
        if ($this->checkIsUserIn1cBlacklist($uid)) {
            return;
        }

        $reasons1c = $this->getReasons1c();
        if (!empty($reasons1c['response'])) {
            if ($reasonUid = $this->get1cReasonUid($reasons1c['response'], $reason)) {
                $data = $this->soap->generateObject([
                    'Date' => date('YmdHis'),
                    'ContragentUID' => $uid,
                    'ReasonUID' => $reasonUid,
                    'SystemName' => static::SYSTEM_NAME,
                    'Comment' => $comment,
                    'Username' => $manager1cName
                ]);

                $this->soap->requestSoap($data, 'WebSignal', 'AddToBlacklist');
            }
        }
    }

    public function sendDeleteUserFromBlacklist1c($uid, string $reason, string $comment, string $manager1cName)
    {
        if (!$this->checkIsUserIn1cBlacklist($uid)) {
            return;
        }

        $data = $this->soap->generateObject([
            'Date' => date('YmdHis'),
            'ContragentUID' => $uid,
            'Reason' => $reason,
            'SystemName' => static::SYSTEM_NAME,
            'Comment' => $comment,
            'Username' => $manager1cName
        ]);

        $this->soap->requestSoap($data, 'WebSignal', 'DelFromBlacklist');
    }

    private function getReasons1c(): array
    {
        for ($i = 0; $i < 3; $i++) {
            $result = $this->soap->requestSoap([],'WebSignal', 'ReasonsForBlacklisting');
            if (!isset($result['errors'])) {
                break;
            }
        }

        return $result;
    }

    public function checkIsUserIn1cBlacklist(string $uid): bool
    {
        $res = $this->checkIsUserIn1cBlacklistSafe($uid);
        return $res['ok'] && $res['in_blacklist'];
    }

    /**
     * Безопасная проверка пользователя в ЧС 1С.
     *
     * @param string $uid
     * @return array{ok:bool,in_blacklist:?bool,error:?string}
     */
    public function checkIsUserIn1cBlacklistSafe(string $uid): array
    {
        $payload = $this->soap->generateObject(['ContragentUID' => $uid]);
        $result  = $this->soap->requestSoap($payload, 'WebSignal', 'isContragentInBlacklist');

        // 1. Ошибка соединения или SoapFault
        if (!empty($result['errors'])) {
            return $this->makeResponse(false, null, (string)$result['errors']);
        }

        // 2. Проверка структуры ответа
        if (!array_key_exists('response', $result)) {
            return $this->makeResponse(false, null, 'Некорректный формат ответа 1С');
        }

        $value = $result['response'];

        // 3. Проверка типа данных
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($bool === null) {
            return $this->makeResponse(false, null, 'Некорректное значение в ответе 1С');
        }

        // 4. Успешный результат
        return $this->makeResponse(true, $bool);
    }


    /**
     * Формирует единый ответ.
     *
     * @param bool        $ok           — флаг успешности запроса
     * @param bool|null   $inBlacklist  — результат проверки в ЧС (null, если ошибка)
     * @param string|null $error        — текст ошибки
     * @return array{ok:bool,in_blacklist:?bool,error:?string}
     */
    private function makeResponse(bool $ok, ?bool $inBlacklist, ?string $error = null): array
    {
        return [
            'ok'           => $ok,
            'in_blacklist' => $inBlacklist,
            'error'        => $error,
        ];
    }

    private function get1cReasonUid(array $reasons1c, string $crmReason): ?string
    {
        foreach ($reasons1c as $reason1c) {
            if ($reason1c['Наименование'] === $crmReason) {
                return $reason1c['УИД'];
            }
        }

        return null;
    }

    public function addUserToBlackList($user_id,$reason,$manager,$comment=''): array
    {
        $user = $this->users->get_user($user_id);

        $id = $this->add([
            'user_id' => $user->id,
            'manager_id' => $manager->id,
            'comment' => $comment,
            'reason' => $reason,
        ]);

        if ($id) {
            $this->blacklist->sendAddUserToBlacklist1c($user->UID, $reason, $comment, $manager->name_1c);
            return [
                'success' => true,
                'message' => 'Клиент добавлен в ЧС!',
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Невозможно добавить клиента в ЧС!',
        ];
    }
}
