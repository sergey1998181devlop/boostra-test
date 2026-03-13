<?php

require_once 'Simpla.php';

class Scorings extends Simpla
{
    // region Типы скорингов (Id)
    public const TYPE_SCORISTA = 1;
    public const TYPE_FMS = 2;
    public const TYPE_FSSP = 3;
    public const TYPE_FNS = 4;
    public const TYPE_LOCAL_TIME = 5;
    public const TYPE_LOCATION = 6;
    public const TYPE_JUICESCORE = 7;
    public const TYPE_BLACKLIST = 8;
    public const TYPE_EFRSB = 9;
    public const TYPE_AGE = 11;
    public const TYPE_SVO = 12;
    public const TYPE_AXILINK = 13;
    public const TYPE_WEBMASTER = 14;
    public const TYPE_DBRAIN_PASSPORT = 15;
    public const TYPE_DBRAIN_CARD = 16;
    public const TYPE_AXILINK_2 = 17;
    public const TYPE_PDN = 18;
    public const TYPE_FINKARTA = 19;
    public const TYPE_UPRID = 20;
    public const TYPE_PYTON_NBKI = 21;
    public const TYPE_PYTON_SMP = 22;
    public const TYPE_MEGAFON = 23;
    public const TYPE_MTS = 24;
    public const TYPE_EGRUL = 25;
    public const TYPE_WORK = 26;
    public const TYPE_REPORT = 27;
    public const TYPE_CYBERITY = 28; // скоринг выполняется на сайте: /ajax/cyberity_callback.php
    public const TYPE_LOCATION_IP = 29;

    /**
     * У hyper_c в s_scoring_types.params могут быть ключи ['utm_sources' => [], 'autoconfirm_enabled' => 1, 'test_mode' => 0]
     * test_mode = 1 - тестовый режим, когда его решения не учитываются, а просто собираются данные для обучения модели
     */
    public const TYPE_HYPER_C = 30; // см таблицу s_hyper_c (заполняется сервисом hyper-c)
    /** @var int Скоринг для тестирования BP платформы по автоматической проверке фрода и качества присылаемых фотографий. */
    public const TYPE_BP_PHOTO = 31;

    public const TYPE_TERRORIST_CHECK = 32;
    // endregion

    // region Статусы скорингов (Id)

    /** @var int Новый скоринг, ждёт своей очереди */
    public const STATUS_NEW = 1;

    /** @var int Скоринг обрабатывается */
    public const STATUS_PROCESS = 2;

    /** @var int Скоринг остановлен досрочно */
    public const STATUS_STOPPED = 3;

    /** @var int Завершённый скоринг */
    public const STATUS_COMPLETED = 4;

    /** @var int Скоринг не прошёл из-за ошибки */
    public const STATUS_ERROR = 5;

    public const STATUS_IMPORT = 6;

    /** @var int Скоринг обрабатывается */
    public const STATUS_WAIT = 7;

    /** @var array Соответствие id статуса и его названия, для обратной совместимости */
    public const STATUSES = [
        self::STATUS_NEW => 'new',
        self::STATUS_PROCESS => 'process',
        self::STATUS_STOPPED => 'stopped',
        self::STATUS_COMPLETED => 'completed',
        self::STATUS_ERROR => 'error',
        self::STATUS_IMPORT => 'import',
        self::STATUS_WAIT => 'wait'
    ];

    // endregion

    /**
     * Статус скористы при результате отказано
     */
    public const SCORISTA_STATUS_RESULT_ERROR = 'Отказ';

    /**
     * Статус скористы при результате успешно
     */
    public const SCORISTA_STATUS_RESULT_SUCCESS = 'Одобрено';

    /**
     * Статус пройденного скоринга, при одобрении на заём
     * P.S. у каждого скоринга формируется по своим условиям
     */
    public const SCORING_STATUS_SUCCESS = 1;

    /** @var array Список скорингов для НК, статус которых проверяется при проверке возможности добавления скористы и акси */
    private const REQUIRED_FOR_SCORISTA_NK = [
        // При изменении списка его нужно обновить и на сайте
        // Проверяем скористу и акси, чтобы убедиться что их ещё нет
        self::TYPE_BLACKLIST,
        self::TYPE_SCORISTA,
        self::TYPE_AXILINK_2
    ];

    /** @var array Список скорингов для ПК, статус которых проверяется при проверке возможности добавления скористы и акси */
    private const REQUIRED_FOR_SCORISTA_PK = [
        // При изменении списка его нужно обновить и на сайте
        self::TYPE_BLACKLIST,
        // Проверяем скористу и акси, чтобы убедиться что их ещё нет
        self::TYPE_SCORISTA,
        self::TYPE_AXILINK_2
    ];

    private const LOG_FILE = 'scorings.txt';

    public function get_overtime_scorings($datetime, $type = NULL)
    {
        $type_filter = empty($type) ? '' : $this->db->placehold("AND type = ?", $type);

        $query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE `status` = ?
            AND start_date < ?
            $type_filter
            ORDER BY id ASC
        ", self::STATUS_PROCESS, $datetime);
        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function get_scorista_scoring_id($scorista_id)
    {
        $query = $this->db->placehold("
            SELECT id
            FROM __scorings
            WHERE scorista_id = ?
        ", (string)$scorista_id);
        $this->db->query($query);
        $result = $this->db->result('id');

        return $result;
    }

    public function get_scorista_organization($scorista_id)
    {
        $query = $this->db->placehold("
            SELECT 
                s.id AS scoring_id,
                o.id AS order_id,
                o.organization_id
            FROM __scorings AS s
            LEFT JOIN s_orders AS o
            ON o.id = s.order_id
            WHERE s.scorista_id = ?
        ", (string)$scorista_id);
        $this->db->query($query);
        return  $this->db->result();
    }

    public function get_new_scoring($type = NULL, bool $retry = false, bool $check_lock = false)
    {
        $type_filter = empty($type) ? '' : $this->db->placehold("AND type IN (?@)", (array)$type);
        $check_lock_condition = $check_lock ? " AND GET_LOCK(CONCAT('SCORING_CHECK_', id), 0)" : '';

        $sql = "            
            SELECT * 
            FROM __scorings
            WHERE status = ?
            $type_filter
            $check_lock_condition
            ORDER BY id ASC
            LIMIT 1";

        $query = $this->db->placehold($sql, self::STATUS_NEW);
        $this->db->query($query);
        $result = $this->db->result();

        if (empty($result) && $retry) {
            $sql = "            
                SELECT * 
                FROM __scorings
                WHERE status = ?
                $type_filter
                $check_lock_condition
                ORDER BY id ASC
                LIMIT 1";
            $query = $this->db->placehold($sql, self::STATUS_WAIT);
            $this->db->query($query);
            $result = $this->db->result();
        }
        return $result;
    }

    public function get_scoring_mt(array $types, array $suspended, bool $reverse = false, int $status = self::STATUS_NEW)
    {
        $type_filter = $this->db->placehold("AND `type` IN (?@)", array_keys($types));
        $suspended   = $this->db->placehold("AND id NOT IN (?@)", $suspended);
        $locks_chunk = implode(' ', array_map(fn($item) => "WHEN `type` = $item THEN '{$types[$item]}'", array_keys($types)));
        $ordering    = $reverse ? 'DESC' : 'ASC';

        $sql = "SELECT *, CONCAT(CASE {$locks_chunk} END, '_', id) locker_id
                FROM __scorings
                WHERE
                    `status` = ?
                    {$type_filter}
                    {$suspended}
                    AND GET_LOCK(CONCAT(CASE {$locks_chunk} END, '_', id), 0)
                ORDER BY id {$ordering}
                LIMIT 1";

        $query = $this->db->placehold($sql, $status);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @param int|array $type
     * @param array $processed
     * @return false|array
     */
    public function getWaitScoring($type = '', $processed = [])
    {
        if ($type) {
            $type = $this->db->placehold("AND type IN (?@)", (array)$type);
        }
        $processed = !$processed ? '' : $this->db->placehold("AND id NOT IN (?@)", $processed);
        $sql = "SELECT id, string_result, order_id, user_id, scorista_id FROM __scorings
            WHERE status = ?
            {$type} {$processed}
            ORDER BY id DESC
            LIMIT 1";
        $query = $this->db->placehold($sql, self::STATUS_WAIT);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получение списка ждущих скорингов в статусе WAIT (status = 7)
     * @param array{int}|int $type Id типа или список из id типов
     * @param int $limit
     * @param string $sort ASC или DESC
     * @param string|null $max_age Максимальный возраст скоринга из start_date, чтобы не брать слишком старые.
     *
     * Пример значений:
     *
     * `null` - Нет ограничения по возрасту (По-умолчанию).
     *
     * `1 HOUR` - Не старше 1 часа.
     *
     * `30 MINUTE` - Не старше 30 минут.
     * @return array Список скорингов. Может быть пустым
     */
    public function getWaitScorings($type, int $limit = 100, string $sort = 'ASC', string $max_age = null)
    {
        if ($sort != 'ASC')
            $sort = 'DESC';

        $where_max_age = empty($max_age) ? '' : ('AND start_date >= NOW() - INTERVAL ' . $max_age);

        $query = $this->db->placehold("SELECT * FROM __scorings WHERE `status` = ? AND type IN (?@) $where_max_age ORDER BY id $sort LIMIT $limit",
            self::STATUS_WAIT, (array)$type);
        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    /**
     * Получение списка ждущих скорингов в статусе WAIT (status = 7)
     * @param array{int}|int $type Id типа или список из id типов
     * @param int $limit
     * @param string $sort ASC или DESC
     * @param string|null $max_age Максимальный возраст скоринга из start_date, чтобы не брать слишком старые.
     *
     * Пример значений:
     *
     * `null` - Нет ограничения по возрасту (По-умолчанию).
     *
     * `1 HOUR` - Не старше 1 часа.
     *
     * `30 MINUTE` - Не старше 30 минут.
     * @return object Список скорингов. Может быть пустым
     */
    public function getWaitScoring_mt(array $type, array $suspended, bool $reverse = false)
    {
        $suspended = $this->db->placehold("AND id NOT IN (?@)", $suspended);
        $ordering  = $reverse ? 'DESC' : 'ASC';

        $sql = "SELECT *, CONCAT(?, '_', id) locker_id
                FROM __scorings
                WHERE
                    `status` = ?
                    AND `type` = ?
                    {$suspended}
                    AND GET_LOCK(CONCAT(?, '_', id), 0)
                ORDER BY id {$ordering}
                LIMIT 1";

        $query = $this->db->placehold($sql, $type['lock_name'], self::STATUS_WAIT, $type['id'], $type['lock_name']);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получение списка новых скорингов в статусе (status = 1)
     * @param array{int}|int $type Id типа или список из id типов
     * @param int $limit
     * @param string $sort ASC или DESC
     * @param string|null $max_age Максимальный возраст скоринга из start_date, чтобы не брать слишком старые.
     *
     * Пример значений:
     *
     * `null` - Нет ограничения по возрасту (По-умолчанию).
     *
     * `1 HOUR` - Не старше 1 часа.
     *
     * `30 MINUTE` - Не старше 30 минут.
     * @return array Список скорингов. Может быть пустым
     */
    public function getNewScorings($type, int $limit = 100, string $sort = 'ASC', string $max_age = null)
    {
        if ($sort != 'ASC')
            $sort = 'DESC';

        $where_max_age = empty($max_age) ? '' : ('AND created >= NOW() - INTERVAL ' . $max_age);

        $query = $this->db->placehold("SELECT * FROM __scorings WHERE `status` = ? AND type IN (?@) $where_max_age ORDER BY id $sort LIMIT $limit",
            self::STATUS_NEW, (array)$type);
        $this->db->query($query);
        return $this->db->results() ?: [];
    }

    public function get_import_scoring($type = self::TYPE_SCORISTA, bool $validate_fms = false)
    {
        $sql = "
            SELECT * 
            FROM __scorings
            WHERE status = ?
            AND type = ?
            ORDER BY id ASC
            LIMIT 1
        ";

        $query = $this->db->placehold($sql, self::STATUS_IMPORT, $type);

        $this->db->query($query);
        $result = $this->db->result();
        /*
                if($validate_fms && !empty($result) && $result->type != 'fms') {
                    $fms_last_scoring = $this->get_last_type_scoring('fms', $result->user_id);
                    if($fms_last_scoring->status != 'completed') {
                        $result = false;
                    }
                }
        */
        return $result;
    }

    public function get_import_scoring_mt(array $type, array $suspended, bool $reverse = false)
    {
        $suspended = $this->db->placehold("AND id NOT IN (?@)", $suspended);
        $ordering  = $reverse ? 'DESC' : 'ASC';

        $sql = "SELECT *, CONCAT(?, '_', id) locker_id
                FROM __scorings
                WHERE
                    `status` = ?
                    AND `type` = ?
                    {$suspended}
                    AND GET_LOCK(CONCAT(?, '_', id), 0)
                ORDER BY id {$ordering}
                LIMIT 1";

        $query = $this->db->placehold($sql, $type['lock_name'], self::STATUS_IMPORT, $type['id'], $type['lock_name']);
        $this->db->query($query);
        return $this->db->result();
    }

    public function checkSingleScoring_mt($type, $status, $order_id, $scoring_id, $lock_name)
    {
        $sql = "SELECT id
                FROM __scorings
                WHERE
                    order_id = ?
                    AND `type` = ?
                    AND `status` = ?
                    AND id <> ?
                    AND IS_USED_LOCK(CONCAT(?, '_', id)) IS NULL
                LIMIT 1";

        $query = $this->db->placehold($sql, $order_id, $type, $status, $scoring_id, $lock_name);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Устанавливает скорингам статус STATUS_PROCESS
     *
     * @param array $scorings
     * @return void
     */
    public function setInProcess(array $scorings): void
    {
        $scoringsId = [];
        foreach ($scorings as $scoring) {
            $scoringsId[] = $scoring->id;
        }

        if (empty($scoringsId)) {
            return;
        }

        $this->db->query('UPDATE __scorings SET status = ? WHERE id IN (?@)', $this->scorings::STATUS_PROCESS, $scoringsId);
    }

    public function get_last_type_scoring($type, $user_id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __scorings
            WHERE type = ?
            AND user_id = ?
            ORDER BY id DESC
            LIMIT 1
        ", (int)$type, (int)$user_id);
        $this->db->query($query);
        $result = $this->db->result();

        return $result;
    }

    public function get_body_by_type($scoring)
    {
        switch ($scoring->type) {
            case self::TYPE_FSSP:
            case self::TYPE_JUICESCORE:
            case self::TYPE_EGRUL:
                return $this->get_unserialized_body($scoring->id);
            case self::TYPE_AXILINK:
            case self::TYPE_SCORISTA:
            case self::TYPE_DBRAIN_PASSPORT:
            case self::TYPE_DBRAIN_CARD:
            case self::TYPE_AXILINK_2:
            case self::TYPE_FINKARTA:
            case self::TYPE_BP_PHOTO:
                return $this->get_json_decoded_body($scoring->id);
            case self::TYPE_EFRSB:
                return $this->get_scoring_body($scoring->id);
            default:
                return null;
        }
    }

    protected function get_unserialized_body(int $scoringId)
    {
        $body = $this->get_scoring_body($scoringId);
        if (empty($body))
            return $body;

        return unserialize($body);
    }

    protected function get_json_decoded_body(int $scoringId)
    {
        $body = $this->get_scoring_body($scoringId);
        $body = json_decode($body);
        if (!empty($body->equifaxCH)) {
            $body->equifaxCH = iconv('cp1251', 'utf8', base64_decode($body->equifaxCH));
        }

        return $body;
    }

    /**
     * @param $id
     * @return false|string
     */
    public function get_scoring_body($id)
    {
        $query = $this->db->placehold("
            SELECT body 
            FROM __scoring_body
            WHERE scoring_id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result('body');

        return $result;
    }

    public function add_scoring_body($id, string $body)
    {
        $this->db->query('INSERT INTO __scoring_body SET ?%', [
            'scoring_id' => $id,
            'body' => $body
        ]);
    }

    public function update_scoring_body($id, string $new_body)
    {
        $this->db->query("UPDATE __scoring_body SET `body` = ? WHERE scoring_id = ?", $new_body, $id);
    }

    public function get_scoring($id, string $table_name = 's_scorings')
    {
        $query = $this->db->placehold("
            SELECT sc.*,
                   sct.name AS type_name
            FROM $table_name sc
            LEFT JOIN __scoring_types sct ON sc.type = sct.id
            WHERE sc.id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();

        if (!empty($result)) {
            $result->body = $this->get_scoring_body($id);
            $result->status_name = self::STATUSES[$result->status] ?? '';
        }

        return $result;
    }

    public function get_scorings($filter = array())
    {

        $id_filter = '';
        $audit_id_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $status_filter = '';
        $type_filter = '';
        $success_filter = '';
        $keyword_filter = '';
        $start_date_from_filter = '';
        $start_date_to_filter = '';
        $limit = 1000;
        $page = 1;
        $sort = 'sc.id ASC';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND sc.id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND sc.status IN (?@)", (array)$filter['status']);

        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND sc.type IN (?@)", (array)$filter['type']);

        if (!empty($filter['audit_id']))
            $audit_id_filter = $this->db->placehold("AND sc.audit_id IN (?@)", array_map('intval', (array)$filter['audit_id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND sc.user_id IN (?@)", array_map('intval', (array)$filter['user_id']));

        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND sc.order_id IN (?@)", array_map('intval', (array)$filter['order_id']));

        if (!empty($filter['success']))
            $order_id_filter = $this->db->placehold("AND sc.success IN (?@)", array_map('intval', (array)$filter['success']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (sc.name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        if (!empty($filter['start_date_from']))
            $start_date_from_filter = $this->db->placehold("AND sc.start_date >= ?", $filter['start_date_from']);

        if (!empty($filter['start_date_to']))
            $start_date_to_filter = $this->db->placehold("AND sc.start_date <= ?", $filter['start_date_to']);

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        if (!empty($filter['sort'])) {
            switch ($filter['sort']):

                case 'date_desc':
                    $sort = 'sc.created DESC';
                    break;

                // Есть дубликаты, созданные в одну и ту же секунды, поэтому надежнее сортировать также по id по убыванию
                case 'id_date_desc':
                    $sort = 'sc.id DESC, sc.created DESC';
                    break;

            endswitch;
        }

        $query = $this->db->placehold("
            SELECT 
                sc.id, 
                sc.user_id,
                sc.order_id,
                sc.type,
                sct.name AS type_name,
                sc.status,
                sc.success,
                sc.created,
                sc.scorista_id,
                sc.scorista_status,
                sc.scorista_ball,
                sc.string_result,
                sc.start_date,
                sc.end_date
            FROM __scorings sc
            LEFT JOIN __scoring_types sct ON sc.type = sct.id
            WHERE 1
                $id_filter
                $status_filter
                $type_filter
                $audit_id_filter
                $user_id_filter
                $order_id_filter
                $success_filter
                $keyword_filter
                $start_date_from_filter
                $start_date_to_filter
            ORDER BY $sort
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();

        if (!empty($results)) {
            foreach ($results as &$result) {
                $result->status_name = self::STATUSES[$result->status] ?? '';
            }
        }

        return $results;
    }

    public function get_scorings_by_scorista($user_id)
    {
        $date = date("Y-m-d H:i:s", time() - 86400);
        $query = $this->db->placehold("
            SELECT 
                id, 
                user_id,
                order_id,
                type,
                status,
                success,
                created,
                scorista_id,
                scorista_ball,
                string_result 
            FROM __scorings
            WHERE
                user_id = ?
                and created >= ?
                and type = ?
            ORDER BY id DESC
        ", $user_id, $date, self::TYPE_SCORISTA);

        $this->db->query($query);
        $results = $this->db->results();

        return $results;
    }

    public function count_scorings($filter = array())
    {
        $id_filter = '';
        $status_filter = '';
        $type_filter = '';
        $audit_id_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status IN (?@)", (array)$filter['status']);

        if (!empty($filter['type']))
            $type_filter = $this->db->placehold("AND type IN (?@)", (array)$filter['type']);

        if (!empty($filter['audit_id']))
            $audit_id_filter = $this->db->placehold("AND audit_id IN (?@)", array_map('intval', (array)$filter['audit_id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));

        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __scorings
            WHERE 1
                $id_filter
                $status_filter
                $type_filter
                $audit_id_filter
                $user_id_filter
                $order_id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    private function check_scoring_duplicate(array $scoring)
    {
        $old_scoring = $this->get_last_type_scoring($scoring['type'], $scoring['user_id']);
        if (!empty($old_scoring) && $scoring['order_id'] == $old_scoring->order_id) {
            // У этой заявки уже был такой скоринг, смотрим как давно
            $current_dt = new DateTime();
            $other_dt = new DateTime($old_scoring->created);
            $interval = $current_dt->diff($other_dt);
            // Получение разницы в секундах
            $diff_in_seconds = abs(
                ($interval->days * 24 * 60 * 60) +
                ($interval->h * 60 * 60) +
                ($interval->i * 60) +
                $interval->s
            );
            if ($diff_in_seconds <= 10) {
                // Пред.скоринг был < 10 секунд назад, возвращаем его id
                $e = new \Exception;
                $this->logging(__METHOD__, $e->getTraceAsString(), $scoring, $old_scoring->id, 'scoring_duplicates.txt');
                return $old_scoring->id;
            }
        }
        return null;
    }

    public function add_scoring($scoring)
    {
        $scoring = (array)$scoring;

        // Временная проверка пока решаем баг:
        // 03.07.24 - 04.07.24 накидывается очень много скорист на одну и ту же заявку
        if ($duplicate_id = $this->check_scoring_duplicate($scoring)) {
            return $duplicate_id;
        }

        if (!empty($scoring['body'])) {
            $body = $scoring['body'];
        }
        unset($scoring['body']);

        if (empty($scoring['status'])) {
            $scoring['status'] = self::STATUS_NEW;
        }

        if (empty($scoring['created'])) {
            $scoring['created'] = date('Y-m-d H:i:s');
        }

        $query = $this->db->placehold("INSERT INTO __scorings SET ?%", $scoring);
        $this->db->query($query);

        $id = $this->db->insert_id();
        if (!empty($body))
            $this->add_scoring_body($id, $body);
        return $id;
    }

    public function log_add_scoring($from, $data)
    {
        $this->logging(__METHOD__, 'Добавляем ' . $from, $data, '', 'scorista_duplicate.txt');
    }

    public function update_scoring($id, $scoring)
    {
        $scoring = (array)$scoring;
        if (!empty($scoring['body'])) {
            if (!empty($this->get_scoring_body($id)))
                $this->update_scoring_body($id, $scoring['body']);
            else
                $this->add_scoring_body($id, $scoring['body']);
        }
        unset($scoring['body']);

        $query = $this->db->placehold("
            UPDATE __scorings SET ?% WHERE id = ?
        ", $scoring, (int)$id);
        $this->db->query($query);

        return $id;
    }

    /**
     * Останавливает все скоринги со статусом STATUS_NEW по id заявки
     *
     * @param int $orderId
     * @param array $updateData
     * @return void
     */
    public function stopOrderScorings(int $orderId, array $updateData): void
    {
        $updateData = [
            'status' => $this->scorings::STATUS_STOPPED,
            'string_result' => $updateData['string_result'] ?? '',
        ];

        $query = $this->db->placehold("
            UPDATE __scorings SET ?% WHERE order_id = ? AND status = ?
        ", $updateData, $orderId, self::STATUS_NEW);
        $this->db->query($query);

        // Останавливаем в заявке также скоринг TYPE_REPORT со статусом STATUS_WAIT
        $query = $this->db->placehold("
            UPDATE __scorings SET ?% WHERE order_id = ? AND type IN (?@) AND status = ?
        ", $updateData, $orderId, [self::TYPE_REPORT], self::STATUS_WAIT);
        $this->db->query($query);
    }

    /**
     * Останавливает скоринги указанного типа со статусом STATUS_NEW по id заявки
     *
     * @param int $orderId
     * @param array $updateData
     * @param int $scoringTypeId
     * @return void
     */
    public function stopOrderScoringsByType(int $orderId, array $updateData, int $scoringTypeId, int $scoring_id = 0): void
    {
        $updateData = [
            'status' => $this->scorings::STATUS_STOPPED,
            'string_result' => $updateData['string_result'] ?? '',
        ];

        $query = $this->db->placehold("UPDATE __scorings SET ?% WHERE order_id = ? AND type = ? AND status = ? AND id <> ?",
                                        $updateData, $orderId, $scoringTypeId, self::STATUS_NEW, $scoring_id);
        $this->db->query($query);
    }

    public function delete_scoring($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __scorings WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    /**
     * @param string|int $idOrName Id скоринга или его имя
     * @return false|int|stdClass
     */
    public function get_type($idOrName)
    {
        $id = intval($idOrName);
        if (!empty($id))
            $idOrName = $id;

        $where = is_int($idOrName) ? $this->db->placehold("WHERE id = ?", (int)$idOrName) : $this->db->placehold("WHERE name = ?", (string)$idOrName);

        $query = $this->db->placehold("
            SELECT * 
            FROM __scoring_types
            $where
        ");
        $this->db->query($query);
        if ($result = $this->db->result())
            $result->params = unserialize($result->params);

        return $result;
    }

    public function get_types($filter = array())
    {
        $id_filter = '';
        $active_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['active']))
            $active_filter = $this->db->placehold("AND active = ?", (int)$filter['active']);

        $query = $this->db->placehold("
            SELECT * 
            FROM __scoring_types
            WHERE 1
                $id_filter
                $active_filter
            ORDER BY position ASC 
        ");
        $this->db->query($query);
        $results = array();
        foreach ($this->db->results() as $result) {
            $result->params = unserialize($result->params);

            $results[$result->name] = $result;
        }

        return $results;
    }

    public function count_types($filter = array())
    {
        $id_filter = '';
        $active_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (isset($filter['active']))
            $active_filter = $this->db->placehold("AND active = ?", (int)$filter['active']);

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __scoring_types
            WHERE 1
                $id_filter
                $active_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_type($type)
    {
        $type = (array)$type;

        if (isset($type['params']))
            $type['params'] = serialize($type['params']);

        $query = $this->db->placehold("
            INSERT INTO __scoring_types SET ?%
        ", $type);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_type($id, $type)
    {
        $type = (array)$type;

        if (isset($type['params']))
            $type['params'] = serialize($type['params']);

        $query = $this->db->placehold("
            UPDATE __scoring_types SET ?% WHERE id = ?
        ", $type, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_type($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __scoring_types WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }


    /** Audit **/
    public function get_audit($id)
    {
        $query = $this->db->placehold("
            SELECT * 
            FROM __audits
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        if ($result = $this->db->result())
            $result->types = unserialize($result->types);

        return $result;
    }

    public function get_audits($filter = array())
    {
        $id_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $status_filter = '';
        $keyword_filter = '';
        $limit = 1000;
        $page = 1;

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));

        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status = ?", (string)$filter['status']);

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        if (isset($filter['limit']))
            $limit = max(1, intval($filter['limit']));

        if (isset($filter['page']))
            $page = max(1, intval($filter['page']));

        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page - 1) * $limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __audits
            WHERE 1
                $id_filter
                $user_id_filter
                $order_id_filter
                $status_filter
                $keyword_filter
            ORDER BY id ASC 
            $sql_limit
        ");
        $this->db->query($query);
        if ($results = $this->db->results()) {
            foreach ($results as $result)
                $result->types = unserialize($result->types);
        }
        return $results;
    }

    public function count_audits($filter = array())
    {
        $id_filter = '';
        $user_id_filter = '';
        $order_id_filter = '';
        $status_filter = '';
        $keyword_filter = '';

        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));

        if (!empty($filter['user_id']))
            $user_id_filter = $this->db->placehold("AND user_id IN (?@)", array_map('intval', (array)$filter['user_id']));

        if (!empty($filter['order_id']))
            $order_id_filter = $this->db->placehold("AND order_id IN (?@)", array_map('intval', (array)$filter['order_id']));

        if (!empty($filter['status']))
            $status_filter = $this->db->placehold("AND status = ?", (string)$filter['status']);

        if (isset($filter['keyword'])) {
            $keywords = explode(' ', $filter['keyword']);
            foreach ($keywords as $keyword)
                $keyword_filter .= $this->db->placehold('AND (name LIKE "%' . $this->db->escape(trim($keyword)) . '%" )');
        }

        $query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __audits
            WHERE 1
                $id_filter
                $user_id_filter
                $order_id_filter
                $status_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count;
    }

    public function add_audit($audit)
    {
        $audit = (array)$audit;

        if (isset($audit['types']))
            $audit['types'] = serialize($audit['types']);

        $query = $this->db->placehold("
            INSERT INTO __audits SET ?%
        ", $audit);
        $this->db->query($query);
        $id = $this->db->insert_id();

        return $id;
    }

    public function update_audit($id, $audit)
    {
        $audit = (array)$audit;

        if (isset($audit['types']))
            $audit['types'] = serialize($audit['types']);

        $query = $this->db->placehold("
            UPDATE __audits SET ?% WHERE id = ?
        ", $audit, (int)$id);
        $this->db->query($query);

        return $id;
    }

    public function delete_audit($id)
    {
        $query = $this->db->placehold("
            DELETE FROM __audits WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }

    /**
     * Проверяет, был ли куплен кредитный рейтинг
     * @param int $user_id
     * @return bool
     */
    public function hasPayCreditRating(int $user_id): bool
    {
        $sql = "SELECT EXISTS (SELECT 
                    *
                FROM 
                    __transactions
                WHERE 
                    user_id = ? 
                    AND payment_type IN (?@)
                    AND `status` IN('CONFIRMED', 'AUTHORIZED')) as result";

        $query = $this->db->placehold($sql, $user_id, $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING_ALL);
        $this->db->query($query);
        $tinkoff = $this->db->result('result');

        $sql = "SELECT EXISTS (SELECT 
                    *
                FROM 
                    b2p_payments
                WHERE 
                    user_id = ? 
                    AND payment_type IN (?@)
                    AND reason_code = 1) as result";

        $query = $this->db->placehold($sql, $user_id, $this->best2pay::PAYMENT_TYPE_CREDIT_RATING_MAPPING_ALL);
        $this->db->query($query);

        $best2pay = $this->db->result('result');

        return $tinkoff || $best2pay;
    }


    /**
     * Get last scoring of user
     * @param int $userId
     * @return false|int|object
     */
    public function getLastScoringOfUser(int $userId)
    {
        $this->db->query($this->db->placehold(
            "SELECT * FROM __scorings WHERE `type` IN(?@) AND user_id = ? AND status = ? ORDER BY id DESC",
            [self::TYPE_SCORISTA, self::TYPE_AXILINK],
            $userId,
            self::STATUS_COMPLETED
        ));
        $result = $this->db->result();
        if (!empty($result))
            $result->body = $this->get_scoring_body($result->id);
        return $result;
    }

    /**
     * Проверка последней записи скористы
     * @param $user_id
     * @param $only_completed
     * @return false|int
     */
    public function get_last_scorista_for_user($user_id, $only_completed = false)
    {
        $where_completed = '';
        if ($only_completed) {
            $where_completed = "AND status = " . self::STATUS_COMPLETED;
        }

        $query = $this->db->placehold(
            "SELECT * FROM __scorings WHERE type IN(?@) AND user_id = ? $where_completed ORDER BY id DESC",
            [self::TYPE_SCORISTA, self::TYPE_AXILINK],
            $user_id
        );

        $this->db->query($query);

        $result = $this->db->result();
        if (!empty($result))
            $result->body = $this->get_scoring_body($result->id);
        return $result;
    }

    public function get_last_scorista_for_order($order_id, $only_completed = false)
    {
        $where_completed = '';
        if ($only_completed) {
            $where_completed = "AND status = " . self::STATUS_COMPLETED;
        }

        $query = $this->db->placehold(
            "SELECT * FROM __scorings WHERE type = ? AND order_id = ? $where_completed ORDER BY id DESC  LIMIT 1",
            self::TYPE_SCORISTA,
            $order_id
        );

        $this->db->query($query);

        $result = $this->db->result();
        if (!empty($result))
            $result->body = $this->get_scoring_body($result->id);
        return $result;
    }

    /**
     * Получает скоринг по заявке
     * @param int $order_id
     * @param array $filter_data
     * @return stdClass|false
     */
    public function get_scoring_by_order_id(int $order_id, array $filter_data = [])
    {
        $where = [];

        $sql = "
            SELECT * 
            FROM __scorings
            WHERE 1 = 1
            AND order_id = ?
            -- {{where}}
            ORDER BY id DESC
            LIMIT 1
        ";

        if (!empty($filter_data['type'])) {
            $where[] = $this->db->placehold("type = ?", $filter_data['type']);
        }

        $query = strtr($sql, [
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $query = $this->db->placehold($query, $order_id);

        $this->db->query($query);
        $result = $this->db->result();
        if (!empty($result))
            $result->body = $this->get_scoring_body($result->id);
        return $result;
    }

    /**
     * Получение последнего скоринга подходящего под заданные критерии.
     *
     * Пример:
     * ```
     * $scoring = $this->simpla->getLastScoring([
     *  'order_id' => 2489,
     *  'success' => 0,
     * ]);
     * ```
     * @param array $where
     * @return false|object
     */
    public function getLastScoring(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM __scorings WHERE $conditions ORDER BY id DESC LIMIT 1");
        $result = $this->db->result();
        if (!empty($result))
            $result->body = $this->get_scoring_body($result->id);
        return $result;
    }

    public function get_scorings_by_order($order, $scoring_types)
    {

        $return = new stdClass();
        $return->scor_amount = null;
        $return->scor_period = null;
        $return->scor_message = null;
        $return->scorings = [];
        $return->user_scorings = null;
        $return->inactive_run_scorings = 0;
        $return->need_update_scorings = 0;
        $return->installment_scor_amount = null;
        $return->installment_scor_message = null;

        $filtered_scorings = [];
        $user_scorings = $this->scorings->get_scorings(['user_id' => $order->user_id, 'sort' => 'id_date_desc']);
        if ($user_scorings) {
            $order_scorings = $this->scorings->get_scorings(['user_id' => $order->user_id, 'order_id' => $order->order_id, 'sort' => 'id_date_desc']);

            $merged_scorings = array_merge($order_scorings, $user_scorings);

            // В первую очередь показываем те скоринги, которые есть в заявке
            foreach ($merged_scorings as $scoring) {
                if (empty($filtered_scorings[$scoring->type_name])) {
                    $scoring->body = $this->get_body_by_type($scoring);

                    // флаги need_update_scorings и inactive_run_scorings для template
                    if (in_array(
                        $scoring->status,
                        [
                            self::STATUS_NEW,
                            self::STATUS_PROCESS,
                            $this->scorings::STATUS_IMPORT
                        ]
                    )) {
                        $return->need_update_scorings = 1;
                        if ($scoring_types[$scoring->type]->type == 'first') {

                            $return->inactive_run_scorings = 1;
                        }
                    }
                    // значения $scor_amount $scor_period $scor_message для template
                    if (!empty($scoring->body->additional->decisionSum)) {
                        $return->scor_amount = $scoring->body->additional->decisionSum;
                    } elseif (!empty($scoring->body->sum)) {
                        $return->scor_amount = $scoring->body->sum;
                    }
                    if (!empty($scoring->body->additional->decisionPeriod)) {
                        $return->scor_period = $scoring->body->additional->decisionPeriod;
                    } elseif (!empty($scoring->body->limit_period)) {
                        $return->scor_period = $scoring->body->limit_period;
                    }
                    if (!empty($scoring->body->additional->decisionMessage)) {
                        $return->scor_message = $scoring->body->additional->decisionMessage;
                    } elseif (!empty($scoring->body->message)) {
                        $return->scor_message = $scoring->body->message;
                    }

                    // значения $scor_amount $scor_period $scor_message для инстолментов
                    if (isset($scoring->body->additional->result2) && $scoring->body->additional->result2->additional->decisionSum > 30000) {
                        if (!empty($scoring->body->additional->result2->additional->decisionSum)) {
                            $return->installment_scor_amount = $scoring->body->additional->result2->additional->decisionSum;
                        }
                        if (!empty($scoring->body->additional->result2->additional->decisionMessage)) {
                            $return->installment_scor_message = $scoring->body->additional->result2->additional->decisionMessage;
                        }
                    }

                    if ($scoring->type == $this->scorings::TYPE_FINKARTA) {
                        $scoring->body = $this->finkarta_api->getFormattedBody($scoring->body ?? []);
                    }

                    $filtered_scorings[$scoring->type_name] = $scoring;
                }
            }

            foreach ($user_scorings as $user_scoring) {
                $user_scoring->body = $this->scorings->get_body_by_type($user_scoring);

                $user_scoring->type = $scoring_types[$user_scoring->type];
                $user_scoring->table_name = 's_scorings';

                if ($user_scoring->type->id == $this->scorings::TYPE_FINKARTA) {
                    $user_scoring->body = $this->finkarta_api->getFormattedBody($user_scoring->body ?? []);
                }
            }

            $return->scorings = $filtered_scorings;
            $return->user_scorings = $user_scorings;
        }


        return $return;
    }

    /**
     * Проверяет возможность добавления HYPER_C скоринга и, если всё ок - добавляет его
     * @param int $orderId
     * @return bool true если скоринг был добавлен
     */
    public function tryAddHyperC(int $orderId): bool
    {
        $order = $this->orders->get_order($orderId);

        // 1. Для заявки должен быть скоринг hyper_c
        if (!$this->isHyperEnabledForOrder($order)) {
            return false;
        }

        // 2. Заявка не отказана
        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_REJECT) {
            return false;
        }

        $hyperCScoring = $this->scorings->get_scorings([
            'type' => self::TYPE_HYPER_C,
            'order_id' => $order->order_id,
        ]);

        // 3. Скоринг уже был добавлен
        if (!empty($hyperCScoring)) {
            return false;
        }

        // Сохраняем отчеты
        $result = $this->report->checkReportsDate($order, new DateTimeImmutable());

        $user = $this->users->get_user((int)$order->user_id);

        // 5. Если еще не заполнена работа, то не добавляем скоринг hyper_c (он добавится на сайте, когда заполнят работу)
        if (empty($user->additional_data_added)) {
            return false;
        }

        // 5. Добавляем хайпер только 10% заявкам
        $rand = mt_rand(1, 100);
        if ($rand > 10) {
            $this->logging(__METHOD__, '', 'Не добавим   скоринг hyper_c', ['order_id' => $order->order_id, 'rand' => $rand], self::LOG_FILE);
            return false;
        } else {
            $this->logging(__METHOD__, '', 'Добавим скоринг hyper_c', ['order_id' => $order->order_id, 'rand' => $rand], self::LOG_FILE);
        }

        $this->add_scoring([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'status' => self::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
            'type' => self::TYPE_HYPER_C,
        ]);

        $this->logging(__METHOD__, '', 'Добавлен скоринг hyper_c', ['order_id' => $order->order_id, 'result' => $result], self::LOG_FILE);

        return true;
    }

    /**
     * Проверяет возможность добавления Pdn скоринга и, если всё ок - добавляет его
     * @param int $orderId
     * @return bool true если скоринг был добавлен
     */
    public function tryAddPdn(int $orderId): bool
    {
        $order = $this->orders->get_order($orderId);

        // 1. Для заявки должен быть скоринг pdn
        if (!$this->isPdnEnabledForOrder($order)) {
            return false;
        }

        // 2. Заявка новая
        if ((int)$order->status === $this->orders::ORDER_STATUS_CRM_REJECT) {
            return false;
        }

        $pdnScoring = $this->scorings->get_scorings([
            'type' => self::TYPE_PDN,
            'order_id' => $order->order_id,
        ]);

        // 3. Скоринг уже был добавлен
        if (!empty($pdnScoring) && $pdnScoring[0]->status != self::STATUS_ERROR) {
            return false;
        }

        // 4. Сохраняем отчеты (только если не установлен флаг AXI_WITHOUT_CREDIT_REPORTS)
        $result = null;
        $axiWithoutCreditReports = !empty($this->order_data->read($order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS));
        if (!$axiWithoutCreditReports) {
            $result = $this->orders->isCrossOrder($order)
                ? $this->report->checkCrossOrderReportsDate($order, new DateTimeImmutable())
                : $this->report->checkReportsDate($order, new DateTimeImmutable());
        }

        $user = $this->users->get_user((int)$order->user_id);

        // 5. Если еще не заполнена работа, то не добавляем скоринг pdn (он добавится на сайте, когда заполнят работу)
        if (empty($user->additional_data_added)) {
            return false;
        }

        $this->add_scoring([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'status' => self::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
            'type' => self::TYPE_PDN,
        ]);

        $this->logging(__METHOD__, '', 'Добавлен скоринг pdn', ['order_id' => $order->order_id, 'result' => $result], self::LOG_FILE);

        return true;
    }

    private function tryAddScorista($order)
    {
        // Для запуска скористы нужен завершённый акси
        $axi = $this->scorings->getlastScoring([
            'order_id' => $order->order_id,
            'type' => self::TYPE_AXILINK_2
        ]);
        if (empty($axi) || $axi->status != self::STATUS_COMPLETED) {
            return false;
        }

        // Если включен режим без ПДН (Однорукий бандит) - не запускаем Скористу
        $site_id = $this->organizations->get_site_organization($order->organization_id);
        $this->settings->setSiteId($site_id);
        $disablePdnCheck = (bool)$this->settings->disable_pdn_check;
        if ($disablePdnCheck) {
            return false;
        }

        $scoristaSource = $this->order_data->read($order->order_id, $this->order_data::SCORISTA_SOURCE);
        if (isset($scoristaSource) && $scoristaSource == 'aksi') {
            // Скориста проведена на стороне акси
            return false;
        }

        $this->scorings->add_scoring([
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'status' => self::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
            'type' => self::TYPE_SCORISTA,
        ]);

        return true;
    }

    /**
     * Пытаемся добавить скористу и акси на заявку, если все нужные скоринги завершились удачно.
     * @param number|string $order_id
     * @return boolean
     */
    public function tryAddScoristaAndAxi($order_id): bool
    {
        $order = $this->orders->get_order($order_id);
        if (empty($order)) {
            return false;
        }

        $is_new_order = $order->status == $this->orders::ORDER_STATUS_CRM_NEW;
        $is_scorista_allowed = $this->scorings->isScoristaAllowed($order);
        if (!$is_new_order && $is_scorista_allowed) {
            return false;
        }

        if (in_array($order->utm_source, ['crm_auto_approve', 'cross_order'])) {
            return false;
        }

        // Для тестовых заявок идёт ручной запуск скористы и акси
        if ($this->user_data->read($order->user_id, $this->user_data::TEST_USER)) {
            return false;
        }

        $REQUIRED_FOR_SCORISTA = self::REQUIRED_FOR_SCORISTA_NK;
        if ($order->have_close_credits == 1) {
            $REQUIRED_FOR_SCORISTA = self::REQUIRED_FOR_SCORISTA_PK;
        }

        $scorings = $this->get_scorings([
            'order_id' => $order_id,
            'type' => $REQUIRED_FOR_SCORISTA
        ]) ?: [];

        // Формируем массив из самых актуальных скорингов
        $last_scorings = [];
        foreach ($scorings as $scoring) {
            if (empty($last_scorings[$scoring->type])) {
                $last_scorings[$scoring->type] = $scoring;
                continue;
            }

            if ($last_scorings[$scoring->type]->id < $scoring->id) {
                // Этот скоринг новее, сохраним его, а старый уберём
                $last_scorings[$scoring->type] = $scoring;
            }
        }

        $required_scorings = array_fill_keys($REQUIRED_FOR_SCORISTA, false);

        $has_scorista = $has_axi = false;
        foreach ($last_scorings as $type => $scoring) {
            $required_scorings[$type] = true;

            if ($type == self::TYPE_SCORISTA) {
                $has_scorista = true;
                if ($has_axi) // И скориста и акси уже добавлены, нет смысла проверять дальше
                    return false;
                continue;
            }

            if ($type == self::TYPE_AXILINK_2) {
                $has_axi = true;
                if ($has_scorista) // И акси и скориста уже добавлены, нет смысла проверять дальше
                    return false;
                continue;
            }

            // Если один из важных скорингов не готов или завершился с ошибкой - не запускаем скористу и акси
            if ($scoring->status != self::STATUS_COMPLETED) {
                return false;
            }

            // Если один из скорингов не прошёл - не запускаем скористу и акси
            // Не смотрим на результат выполнения Регион IP
            if ($scoring->success == 0 && $type != $this->scorings::TYPE_LOCATION_IP) {
                return false;
            }
        }

        // Все ли нужные скоринги отмечены как найденные
        if ($order->have_close_credits == 0) {
            // Проверяем только для НК, как было раньше
            unset($required_scorings[self::TYPE_SCORISTA], $required_scorings[self::TYPE_AXILINK_2]);
            if (in_array(false, $required_scorings, true)) {
                // Не все, ждём их добавления
                return false;
            }
        }

        if (!$has_axi) {
            $new_axi = [
                'user_id' => $order->user_id,
                'order_id' => $order_id,
                'status' => self::STATUS_NEW,
                'created' => date('Y-m-d H:i:s'),
                'type' => self::TYPE_AXILINK_2,
            ];
            $this->scorings->add_scoring($new_axi);
        }

        if ($has_axi && !$has_scorista) {
            return $this->tryAddScorista($order);
        }

        return true;
    }

    /**
     * Пытаемся добавить скористу и акси на заявку, если все нужные скоринги завершились удачно.
     * @param number|string $order_id
     * @return boolean
     */
    public function tryAddScoristaAndAxi_mt($order_id,  $stream = ''): bool
    {
        $this->db->query("SELECT GET_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}', 0) lock_is_valid");
        if(!$this->db->result('lock_is_valid')) {
            return false;
        }
        
        $order = $this->orders->get_order($order_id);
        if (empty($order)) {
            $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
            return false;
        }

        $is_new_order = $order->status == $this->orders::ORDER_STATUS_CRM_NEW;
        $is_scorista_allowed = $this->scorings->isScoristaAllowed($order);
        if (!$is_new_order && $is_scorista_allowed) {
            $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
            return false;
        }

        if (in_array($order->utm_source, ['crm_auto_approve', 'cross_order'])) {
            $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
            return false;
        }

        // Для тестовых заявок идёт ручной запуск скористы и акси
        if ($this->user_data->read($order->user_id, $this->user_data::TEST_USER)) {
            $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
            return false;
        }

        $REQUIRED_FOR_SCORISTA = self::REQUIRED_FOR_SCORISTA_NK;
        if ($order->have_close_credits == 1) {
            $REQUIRED_FOR_SCORISTA = self::REQUIRED_FOR_SCORISTA_PK;
        }

        $scorings = $this->get_scorings([
            'order_id' => $order_id,
            'type' => $REQUIRED_FOR_SCORISTA
        ]) ?: [];

        // Формируем массив из самых актуальных скорингов
        $last_scorings = [];
        foreach ($scorings as $scoring) {
            if (empty($last_scorings[$scoring->type])) {
                $last_scorings[$scoring->type] = $scoring;
                continue;
            }

            if ($last_scorings[$scoring->type]->id < $scoring->id) {
                // Этот скоринг новее, сохраним его, а старый уберём
                $last_scorings[$scoring->type] = $scoring;
            }
        }

        $required_scorings = array_fill_keys($REQUIRED_FOR_SCORISTA, false);

        $has_scorista = $has_axi = false;
        foreach ($last_scorings as $type => $scoring) {
            $required_scorings[$type] = true;

            if ($type == self::TYPE_SCORISTA) {
                $has_scorista = true;
                if ($has_axi) {
                    // И скориста и акси уже добавлены, нет смысла проверять дальше
                    $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
                    return false;
                }
                continue;
            }

            if ($type == self::TYPE_AXILINK_2) {
                $has_axi = true;
                if ($has_scorista) {
                    // И акси и скориста уже добавлены, нет смысла проверять дальше
                    $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
                    return false;
                }
                continue;
            }

            // Если один из важных скорингов не готов или завершился с ошибкой - не запускаем скористу и акси
            if ($scoring->status != self::STATUS_COMPLETED) {
                $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
                return false;
            }

            // Если один из скорингов не прошёл - не запускаем скористу и акси
            // Не смотрим на результат выполнения Регион IP
            if ($scoring->success == 0 && $type != $this->scorings::TYPE_LOCATION_IP) {
                $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
                return false;
            }
        }

        // Все ли нужные скоринги отмечены как найденные
        if ($order->have_close_credits == 0) {
            // Проверяем только для НК, как было раньше
            unset($required_scorings[self::TYPE_SCORISTA], $required_scorings[self::TYPE_AXILINK_2]);
            if (in_array(false, $required_scorings, true)) {
                // Не все, ждём их добавления
                $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
                return false;
            }
        }

        if (!$has_axi) {
            $new_axi = [
                'user_id' => $order->user_id,
                'order_id' => $order_id,
                'status' => self::STATUS_NEW,
                'created' => date('Y-m-d H:i:s'),
                'type' => self::TYPE_AXILINK_2,
            ];
            $this->scorings->add_scoring($new_axi);
        }

        if ($has_axi && !$has_scorista) {
            $has_scorista = $this->tryAddScorista($order);
        }

        $this->db->query("DO RELEASE_LOCK('tryAddScoristaAndAxi{$stream}_{$order_id}')");
        return !$has_axi || $has_scorista;
    }

    /**
     * Может ли скориста сделать автоотказ или изменить сумму в заявке.
     * @param stdClass $order
     * @return bool
     */
    public function isScoristaAllowed($order)
    {
        $fake_scorista_amount = $this->order_data->read(
            $order->order_id ?? $order->id,
            $this->order_data::FAKE_SCORISTA_AMOUNT
        );
        if (!empty($fake_scorista_amount)) {
            return false;
        }

        return true;
    }

    /**
     * Получает выполненные успешно скоринги
     * @param int $user_id
     * @param array $types
     * @return false|null|stdClass
     */
    public function getCompleteScoringByTypes(int $user_id, array $types)
    {
        $query = $this->db->placehold(
            "SELECT * FROM __scorings WHERE type IN(?@) AND user_id = ? AND status = ? AND success = 1 ORDER BY id DESC LIMIT 1",
            $types,
            $user_id,
            self::STATUS_COMPLETED
        );
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает одобренную сумму по скорингам
     * @param int $user_id
     * @param int $order_id
     * @return int
     */
    public function getApproveAmountScoring(int $user_id, int $order_id): int
    {
        $order = $this->orders->get_order($order_id);

        // Если ВКЛ, то возвращаем сумму из $this->order_data::RCL_AMOUNT
        $isRcl = (bool)$this->order_data->read($order_id, $this->order_data::RCL_LOAN);
        if ($isRcl) {
            return (int)$this->order_data->read($order_id, $this->order_data::RCL_AMOUNT);
        }

        $increase_amount = $this->order_data->read($order_id, $this->order_data::INCREASE_AMOUNT_FOR_NNU);
        if (!empty($increase_amount)) {
            return $increase_amount;
        }

        $hyperCApproveAmount = $this->getHyperCApproveAmount($user_id, $order);
        if (!empty($hyperCApproveAmount)) {
            $this->logging(__METHOD__, '', 'Получена рекомендуемая сумма', ['order_id' => $order_id, 'scoring_type' => self::TYPE_HYPER_C, 'amount' => $hyperCApproveAmount], self::LOG_FILE);
            return $hyperCApproveAmount;
        }

        $scoring_type = self::TYPE_SCORISTA;
        if (!empty($order) && !$this->isScoristaAllowed($order)) {
            $scoring_type = self::TYPE_AXILINK_2;
        }

        // Сумму всегда берём из скористы, в том числе для заявок идущих по Акси СПР
        $scoring = $this->getCompleteScoringByTypes($user_id, [$scoring_type]);

        if (!empty($scoring)) {
            $body = $this->get_body_by_type($scoring);
            $amount = 0;
            switch ($scoring->type) {
                case self::TYPE_AXILINK:
                    $amount = (int)$body->sum;
                    break;
                case self::TYPE_SCORISTA:
                    $amount = (int)$body->additional->decisionSum;
                    break;
                case self::TYPE_AXILINK_2:
                    $amount = (int)($body->final_limit ?: $this->order_data->read($order_id, $this->order_data::FAKE_SCORISTA_AMOUNT));
                    break;
            }

            $this->logging(__METHOD__, '', 'Получена рекомендуемая сумма', ['order_id' => $order_id, 'scoring_type' => $scoring->type, 'amount' => $amount], self::LOG_FILE);
            return $amount;
        }

        return 0;
    }

    private function getHyperCApproveAmount(int $userId, $order): int
    {
        $scoring = $this->getCompleteScoringByTypes($userId, [self::TYPE_HYPER_C]);

        // Если нет выполненного скоринга hyper_c
        if (empty($scoring)) {
            return 0;
        }

        // Если hyper_c отключен для заявки
        if (!$this->isHyperEnabledForOrder($order)) {
            return 0;
        }

        $hyperCScoringType = $this->scorings->get_type(self::TYPE_HYPER_C);

        // Если hyper_c в тестовом режиме (когда его решения не учитываются, а просто собираются данные для обучения модели)
        if ($this->isHyperCInTestMode($hyperCScoringType)) {
            return 0;
        }

        // Выключена автовыдача по hyper_c
        if (!$this->isHyperCAutoconfirmEnabled($hyperCScoringType)) {
            return 0;
        }

        return (int)$this->order_data->read((int)$order->order_id, $this->order_data::HYPER_C_APPROVE_AMOUNT);
    }

    /**
     * Должна ли заявка прохдить по скорингу Hyper C?
     * @param array|stdClass $order
     * @return bool
     */
    public function isHyperEnabledForOrder($order): bool
    {
        if (empty($order)) {
            return false;
        }

        $order = (array)$order;

        // 1. Включен ли скоринг
        $hyperCScoringType = $this->scorings->get_type(self::TYPE_HYPER_C);
        if (empty($hyperCScoringType) || empty($hyperCScoringType->active)) {
            return false;
        }

        // 2. Только первичные заявки НК при регистрации
//        if ($order['first_loan'] != 1) {
//            return false;
//        }

        // 3. Должен быть PDL
//        if ($order['loan_type'] != $this->orders::LOAN_TYPE_PDL) {
//            return false;
//        }

        // 4. Нужный ли utm_source
//        $utmSource = $order['utm_source'] ?: 'Boostra';
//        if (empty($hyperCScoringType->params['utm_sources']) || !in_array($utmSource, $hyperCScoringType->params['utm_sources'])) {
//            return false;
//        }

        $orderId = (int)$order['order_id'] ?: (int)$order['id'];
        $axiWithoutCreditReports = $this->order_data->read($orderId, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS);

        // 5. Если по заявке нельзя запрашивать отчеты
        if (!empty($axiWithoutCreditReports)) {
            return false;
        }

        // 6. Если ручеек не пройден
        $orderOrgSwitchParentOrderId = $this->order_data->read($orderId, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);
        $orderOrgSwitchResult = $this->order_data->read($orderId, $this->order_data::ORDER_ORG_SWITCH_RESULT);
        if (empty($orderOrgSwitchParentOrderId) && empty($orderOrgSwitchResult)) {
            return false;
        }

        return true;
    }

    public function isHyperCInTestMode($hyperCScoringType = null): bool
    {
        if (empty($hyperCScoringType)) {
            $hyperCScoringType = $this->scorings->get_type(self::TYPE_HYPER_C);
        }

        return !empty($hyperCScoringType->params['test_mode']);
    }

    public function isHyperCAutoconfirmEnabled($hyperCScoringType = null): bool
    {
        if (empty($hyperCScoringType)) {
            $hyperCScoringType = $this->scorings->get_type(self::TYPE_HYPER_C);
        }

        return !empty($hyperCScoringType->params['autoconfirm_enabled']);
    }

    public function getRecentCompletedScorista($user_id, int $days = 3)
    {
        $query = $this->db->placehold("
        SELECT *
        FROM __scorings
        WHERE type = ?
          AND status = ?
          AND user_id = ?
          AND end_date IS NOT NULL
          AND end_date >= NOW() - INTERVAL ? DAY
          AND is_resend != 1
        ORDER BY end_date ASC
        LIMIT 1
    ", self::TYPE_SCORISTA, self::STATUS_COMPLETED, $user_id, $days);

        $this->db->query($query);
        $result = $this->db->result();
        if (!empty($result)) {
            $result->body = $this->get_scoring_body($result->id);
            $result->status_name = self::STATUSES[$result->status] ?? '';
        }
        
        return $result;
    }

    /**
     * Получаем сумму одобренную скорингом для ИЛ займа, по пользователю
     * @param int $user_id
     * @return int|null
     */
    public function getApproveILAmountScoring(int $user_id): ?int
    {
        $scoring = $this->getCompleteScoringByTypes($user_id, [self::TYPE_SCORISTA]);
        if (!empty($scoring)) {
            $body = $this->get_body_by_type($scoring);
            return (int)$body->additional->result2->additional->decisionSum;
        }

        return null;
    }

    /**
     * @param array|stdClass $order
     * @return bool
     */
    public function isPdnEnabledForOrder($order): bool
    {
        if (empty($order)) {
            return false;
        }

        $order = (array)$order;

        if ($order['loan_type'] != $this->orders::LOAN_TYPE_PDL) {
            return false;
        }

        $pdnScoringType = $this->scorings->get_type(self::TYPE_PDN);
        if (empty($pdnScoringType) || empty($pdnScoringType->active)) {
            return false;
        }

        $organizationIds = array_map('intval', $pdnScoringType->params['organization_ids'] ?? []);
        if (!empty($organizationIds) && !in_array((int)$order['organization_id'], $organizationIds, true)) {
            return false;
        }

        return true;
    }
}