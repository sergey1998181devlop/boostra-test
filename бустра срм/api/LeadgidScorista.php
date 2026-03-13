<?php

require_once 'Simpla.php';

/**
 * Class LeadgidScorista
 * s_leadgid_scorista
 */
class LeadgidScorista extends Simpla
{
    /**
     * **Тип настройки** - Срабатывание после отказа по скористе.
     *
     * Используется для принудительного одобрения и изменяет одобренную сумму.
     */
    public const TYPE_REJECT = 0;

    /**
     * **Тип настройки** - Срабатывание после положительного решения по скористе.
     *
     * Изменяет одобренную сумму.
     */
    public const TYPE_APPROVE = 1;


    /** @var string Ключ отметки в s_order_data с версией настройки затронувшей заявку */
    public const ORDER_DATA_LEADGID_VERSION = 'leadgid_scorista_version';

    /** @var string Ключ отметки в s_order_data с типом настройки затронувшей заявку */
    public const ORDER_DATA_LEADGID_TYPE = 'leadgid_scorista_type';

    /** @var string Ключ отметки в s_order_data с id применённой настройки */
    public const ORDER_DATA_LEADGID_ID = 'leadgid_scorista_id';

    /** @var string Ключ отметки в s_order_data с баллом скористы в момент применения настройки */
    public const ORDER_DATA_LEADGID_BALL = 'leadgid_scorista_ball';

    /** @var string Ключ отметки в s_order_data с id отказавшей по заявке настройки */
    public const ORDER_DATA_LEADGID_REJECT = 'leadgid_scorista_reject';


    /**
     * Получение всех записей
     * @return array
     */
    public function getAll()
    {
        $this->db->query("SELECT * FROM __leadgid_scorista");
        return $this->db->results() ?: [];
    }

    /**
     * Получение всех соответствующих записей
     *
     * ```
     * $rows = $this->leadgidScorista->getWhere([
     *  'utm_source' => 'crm_auto_approve',
     *  'type' => [0, 1],
     * ]);
     * ```
     *
     * @param $columns
     * @return array
     */
    public function getWhere($columns)
    {
        if (empty($columns))
            return $this->getAll();

        $where = [];
        foreach ($columns as $column => $value) {
            if (is_array($value))
                $where[] = $this->db->placehold("$column IN (?@)", $value);
            else
                $where[] = $this->db->placehold("$column = ?", $value);
        }
        $where = implode(' AND ', $where);

        $this->db->query("SELECT * FROM __leadgid_scorista WHERE $where");
        return $this->db->results() ?: [];
    }

    /**
     * Получение конкретной записи по её Id
     * @param $id
     * @return false|ArrayObject
     */
    public function get($id)
    {
        $query = $this->db->placehold('SELECT * FROM __leadgid_scorista WHERE id = ?', $id);
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
        $query = $this->db->placehold('INSERT INTO __leadgid_scorista SET ?%', (array)$row);
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
        $query = $this->db->placehold("UPDATE __leadgid_scorista SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Удаление записи
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $query = $this->db->placehold("DELETE FROM __leadgid_scorista WHERE id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Возвращает запись из s_leadgid_scorista под которую подходит заявка
     * @param $order
     * @param int $type Тип искомой настройки
     * @return false|object
     * @see LeadgidScorista::TYPE_REJECT
     * @see LeadgidScorista::TYPE_APPROVE
     */
    public function getByOrder($order, $type = self::TYPE_REJECT)
    {
        $leadgid = $this->getLeadgid($order->utm_source, $order->utm_medium, $order->have_close_credits, false, $type);
        if (empty($leadgid))
            return false;

        if ($leadgid->min_ball > 0)
            return ($order->scorista_ball >= $leadgid->min_ball) ? $leadgid : false;
        else
            return ($order->scorista_ball <= abs($leadgid->min_ball)) ? $leadgid : false;
    }

    /**
     * Находит запись из s_leadgid_scorista подходящую под параметры
     * @param $utm_source
     * @param $utm_medium
     * @param $have_close_credits
     * @param bool $strictMatch Если true - ищем строгое совпадение по всем параметрам
     * @param int $type Тип искомой настройки
     * @return false|object
     * @see LeadgidScorista::TYPE_REJECT
     * @see LeadgidScorista::TYPE_APPROVE
     */
    public function getLeadgid($utm_source, $utm_medium, $have_close_credits, $strictMatch = false, $type = self::TYPE_REJECT)
    {
        $leadgids = $this->getWhere([
            'type' => $type,
        ]);

        // Ищем строгое совпадение по всем параметрам
        if ($strictMatch) {
            foreach ($leadgids as $leadgid) {
                if ($leadgid->have_close_credits == $have_close_credits &&
                    $leadgid->utm_source == $utm_source &&
                    $leadgid->utm_medium == $utm_medium)
                    return $leadgid;
            }
            return false;
        }

        // Стандартный поиск самой подходящей настройки
        $foundSource = $foundAny = false;
        foreach ($leadgids as $leadgid) {
            if ($leadgid->have_close_credits != $have_close_credits)
                continue;

            if ($leadgid->utm_source == $utm_source) {
                if ($leadgid->utm_medium == $utm_medium)
                    return $leadgid;
                elseif ($leadgid->utm_medium == '*')
                    $foundSource = $leadgid;
            }
            elseif ($leadgid->utm_source == '*')
                $foundAny = $leadgid;
        }
        return $foundSource ?: $foundAny;
    }


    /**
     * Получение всех стоп-факторов
     * @param bool $asList Получение списка стоп-факторов в виде одномерного массива строк в нижнем регистре
     * @return array
     */
    public function getStopFactors($asList = false)
    {
        $this->db->query("SELECT * FROM __leadgid_scorista_factors");

        if (!$asList)
            return $this->db->results() ?: [];

        $factors = $this->db->results('factor') ?: [];
        foreach ($factors as &$factor) {
            $factor = mb_strtolower($factor);
        }
        return $factors;
    }

    /**
     * Проверка наличия стоп-факторов в теле скоринга **скористы**
     * @param $body
     * @return bool
     */
    public function hasStopFactor($body)
    {
        if (is_string($body))
            $body = json_decode($body);

        $scoring_factors = $body->stopFactors;
        if (empty($scoring_factors))
            return false;

        $stop_factors = $this->getStopFactors(true);
        foreach ($scoring_factors as $factor_name => $factor_body) {
            if (empty($factor_body) || $factor_body->result != 1)
                continue;

            $factor_name = mb_strtolower($factor_name);
            if (in_array($factor_name, $stop_factors))
                return true;
        }

        return false;
    }

    /**
     * Получение конкретного стоп-фактора
     * @param string $factor
     * @return false|ArrayObject
     */
    public function getStopFactor($factor)
    {
        $query = $this->db->placehold('SELECT * FROM __leadgid_scorista_factors WHERE factor = ?', $factor);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавление нового стоп-фактора
     * @param string $factor
     * @param null|string $comment
     * @return int
     */
    public function addStopFactor($factor, $comment = '')
    {
        $query = $this->db->placehold('INSERT INTO __leadgid_scorista_factors SET ?%', [
            'factor' => $factor,
            'comment' => $comment
        ]);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление стоп-фактора
     * @param string $factor
     * @param array|ArrayObject $update
     * @return mixed
     */
    public function updateStopFactor($factor, $update)
    {
        $query = $this->db->placehold("UPDATE __leadgid_scorista_factors SET ?% WHERE factor = ?", (array)$update, $factor);
        return $this->db->query($query);
    }

    /**
     * Удаление стоп-фактора
     * @param string $factor
     * @return mixed
     */
    public function deleteStopFactor($factor)
    {
        $query = $this->db->placehold("DELETE FROM __leadgid_scorista_factors WHERE factor = ?", $factor);
        return $this->db->query($query);
    }


    /**
     * Логирует изменение настроек лидгида
     * @param int $manager_id
     * @param string $type
     * @param array|object $old_values
     * @param array|object $new_values
     * @return int
     */
    public function addChangelog($manager_id, $type, $old_values, $new_values = [])
    {
        $old_values = (array)$old_values;
        $new_values = (array)$new_values;
        $log = [
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => $type,
            'old_values' => serialize($old_values),
            'new_values' => serialize($new_values),
            'leadgid_id' => !empty($old_values) ? $old_values['id'] : $new_values['id'],
            'utm_source' => !empty($old_values['utm_source']) ? $old_values['utm_source']: $new_values['utm_source'],
            'utm_medium' => !empty($old_values['utm_medium']) ? $old_values['utm_medium']: $new_values['utm_medium'],
        ];

        $query = $this->db->placehold('INSERT INTO __leadgid_scorista_logs SET ?%', $log);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Получение списка всех залогированных изменений
     * @param array $filter
     * @return array
     */
    public function getChangelogs($filter = [])
    {
        $keys = [];
        $values = [];
        $createdFilter = $where = '';
        foreach ($filter as $key => $value) {
            $value = trim($value);
            if (empty($value))
                continue;

            if ($key == 'created') {
                // Проверяем, есть ли полное время в строке
                if ($dateTime = DateTime::createFromFormat('d.m.Y H:i:s', $value)) {
                    $value = $dateTime->format('Y-m-d H:i:s');
                }
                else {
                    $createdFilter = $this->getCreatedFilter($value);
                    continue;
                }
            }

            $keys[] = "`$key` = ?";
            $values[] = $value;
        }

        if (!empty($keys)) {
            $keys = implode(' AND ', $keys);
            $where = $this->db->placehold($keys, ...$values);
        }
        if (!empty($createdFilter)) {
            if (!empty($where))
                $where .= ' AND ';
            $where .= $createdFilter;
        }

        if (!empty($where))
            $this->db->query("SELECT * FROM __leadgid_scorista_logs WHERE $where ORDER BY id DESC LIMIT 100");
        else
            $this->db->query("SELECT * FROM __leadgid_scorista_logs ORDER BY id DESC LIMIT 20");

        $changelogs = $this->db->results() ?: [];
        foreach ($changelogs as $changelog) {
            $changelog->old_values = @unserialize($changelog->old_values) ?: [];
            $changelog->new_values = @unserialize($changelog->new_values) ?: [];
        }
        return $changelogs;
    }

    /**
     * @param string $created
     * @return string
     */
    function getCreatedFilter($created)
    {
        if ($dateTime = DateTime::createFromFormat('d.m.Y H:i', $created)) {
            // Берём записи за минуту
            $from = $dateTime->format('Y-m-d H:i:00');
            $to = $dateTime->format('Y-m-d H:i:59');
        }
        elseif ($dateTime = DateTime::createFromFormat('d.m.Y H', $created)) {
            // Берём записи за час
            $from = $dateTime->format('Y-m-d H:00:00');
            $to = $dateTime->format('Y-m-d H:59:59');
        }
        elseif ($dateTime = DateTime::createFromFormat('d.m.Y', $created)) {
            // Берём записи за сутки
            $from = $dateTime->format('Y-m-d 00:00:00');
            $to = $dateTime->format('Y-m-d 23:59:59');
        }
        else {
            return '';
        }
        return "created >= '$from' AND created <= '$to'";
    }

    public function getChangelogTypes()
    {
        return [
            'add' => 'Добавление',
            'update' => 'Обновление',
            'delete' => 'Удаление',
        ];
    }

    /**
     * Включена ли таблица с настройками лидгенов скористы
     * @return bool
     */
    public function isEnabled()
    {
        $isEnabled = $this->settings->leadgid_scorista_enabled;
        return !empty($isEnabled);
    }

    /**
     * Текущая версия таблицы с настройками лидгенов скористы
     * @return int
     */
    public function getVersion()
    {
        $this->db->query('SELECT MAX(id) AS version FROM __leadgid_scorista_logs');
        return $this->db->result('version') ?: 1;
    }

    /**
     * Отмечаем заявку как обработанную таблицей с настройками и указываем в ней версию настроек
     * @param int $order_id
     * @param int $type Тип искомой настройки
     * @param int $leadgid_id Id настройки которая повлияла на заявку
     * @param int $scorista_ball Балл скористы в момент применения настройки
     * @return void
     * @see LeadgidScorista::TYPE_REJECT
     * @see LeadgidScorista::TYPE_APPROVE
     */
    public function markOrder($order_id, $type = self::TYPE_REJECT, $leadgid_id = 0, $scorista_ball = 0)
    {
        $this->order_data->set($order_id, self::ORDER_DATA_LEADGID_VERSION, $this->getVersion());
        $this->order_data->set($order_id, self::ORDER_DATA_LEADGID_TYPE, $type);
        if (!empty($leadgid_id))
            $this->order_data->set($order_id, self::ORDER_DATA_LEADGID_ID, $leadgid_id);
        if (!empty($scorista_ball))
            $this->order_data->set($order_id, self::ORDER_DATA_LEADGID_BALL, $scorista_ball);
    }

    /**
     * Отмечаем заявку как отказанную таблицей с настройками и указываем в ней id выдавшей отказ настройки
     * @param int $order_id
     * @param int $leadgid_id
     * @return void
     * @see LeadgidScorista::ORDER_DATA_LEADGID_REJECT
     */
    public function markOrderAsRejected($order_id, $leadgid_id)
    {
        $this->order_data->set($order_id, self::ORDER_DATA_LEADGID_REJECT, $leadgid_id);
    }
}