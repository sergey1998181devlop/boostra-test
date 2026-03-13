<?php

require_once 'Simpla.php';

/**
 * s_approve_amount_settings
 * @see https://tracker.yandex.ru/BOOSTRARU-3303
 */
class ApproveAmountSettings extends Simpla
{
    /** @var string Ключ отметки в s_order_data с версией настройки затронувшей заявку */
    public const ORDER_DATA_APPROVE_AMOUNT_VERSION = 'approve_amount_version';

    /** @var string Ключ отметки в s_order_data с суммой на которую мы увеличили одобренную сумму */
    public const ORDER_DATA_APPROVE_AMOUNT_INCREASED = 'approve_amount_increased';

    /** @var string Ключ отметки в s_order_data с суммой на которую мы должны были увеличить одобренную сумму */
    public const ORDER_DATA_APPROVE_AMOUNT_EXPECTED = 'approve_amount_increased_expected';

    /**
     * Получение всех записей
     * @return array|false
     */
    public function getAll()
    {
        $this->db->query("SELECT * FROM __approve_amount_settings");
        return $this->db->results();
    }

    /**
     * Получение конкретной записи по её Id
     * @param $id
     * @return false|ArrayObject
     */
    public function get($id)
    {
        $query = $this->db->placehold('SELECT * FROM __approve_amount_settings WHERE id = ?', $id);
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
        $query = $this->db->placehold('INSERT INTO __approve_amount_settings SET ?%', (array)$row);
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
        $query = $this->db->placehold("UPDATE __approve_amount_settings SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Удаление записи
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        $query = $this->db->placehold("DELETE FROM __approve_amount_settings WHERE id = ?", $id);
        return $this->db->query($query);
    }

    /**
     * Возвращает запись из таблицы настроек под которую подходит заявка
     * @param $order
     * @return false|object
     */
    public function getByOrder($order)
    {
        $suitable = $this->getSuitable($order->utm_source, $order->utm_medium, $order->have_close_credits);
        if (empty($suitable))
            return false;

        return ($order->scorista_ball >= $suitable->min_ball) ? $suitable : false;
    }

    /**
     * Находит запись из таблицы настроек подходящую под параметры
     * @param $utm_source
     * @param $utm_medium
     * @param $have_close_credits
     * @param bool $strictMatch Если true - ищем строгое совпадение по всем параметрам
     * @return false|object
     */
    public function getSuitable($utm_source, $utm_medium, $have_close_credits, $strictMatch = false)
    {
        $amounts = $this->getAll();

        // Ищем строгое совпадение по всем параметрам
        if ($strictMatch) {
            foreach ($amounts as $amount) {
                if ($amount->have_close_credits == $have_close_credits &&
                    $amount->utm_source == $utm_source &&
                    $amount->utm_medium == $utm_medium)
                    return $amount;
            }
            return false;
        }

        // Стандартный поиск самой подходящей настройки
        $foundSource = $foundAny = false;
        foreach ($amounts as $amount) {
            if ($amount->have_close_credits != $have_close_credits)
                continue;

            if ($amount->utm_source == $utm_source) {
                if ($amount->utm_medium == $utm_medium)
                    return $amount;
                elseif ($amount->utm_medium == '*')
                    $foundSource = $amount;
            }
            elseif ($amount->utm_source == '*')
                $foundAny = $amount;
        }
        return $foundSource ?: $foundAny;
    }

    /**
     * Логирует изменение настроек
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
            'setting_id' => !empty($old_values) ? $old_values['id'] : $new_values['id'],
            'utm_source' => !empty($old_values['utm_source']) ? $old_values['utm_source']: $new_values['utm_source'],
            'utm_medium' => !empty($old_values['utm_medium']) ? $old_values['utm_medium']: $new_values['utm_medium'],
        ];

        $query = $this->db->placehold('INSERT INTO __approve_amount_settings_logs SET ?%', $log);
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
            $this->db->query("SELECT * FROM __approve_amount_settings_logs WHERE $where ORDER BY id DESC LIMIT 100");
        else
            $this->db->query("SELECT * FROM __approve_amount_settings_logs ORDER BY id DESC LIMIT 20");

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
     * Включена ли таблица с настройками
     * @return bool
     */
    public function isEnabled()
    {
        $isEnabled = $this->settings->approve_amount_settings_enabled;
        return !empty($isEnabled);
    }

    /**
     * Текущая версия таблицы с настройками
     * @return int
     */
    public function getVersion()
    {
        $this->db->query('SELECT MAX(id) AS version FROM __approve_amount_settings_logs');
        return $this->db->result('version') ?: 1;
    }

    /**
     * Отмечаем заявку как обработанную таблицей с настройками и указываем в ней версию настроек
     * @param int $order_id
     * @param int $increased_amount Сумма, на которую увеличили одобренную сумму
     * @param int $expected_amount Сумма, на которую ожидалось увеличение одобренной суммы
     * (*т.к. есть ограничения по суммам и можем увеличить меньше чем хотели*)
     * @return void
     */
    public function markOrder($order_id, $increased_amount, $expected_amount)
    {
        $this->order_data->set($order_id, self::ORDER_DATA_APPROVE_AMOUNT_VERSION, $this->getVersion());
        $this->order_data->set($order_id, self::ORDER_DATA_APPROVE_AMOUNT_INCREASED, $increased_amount);
        $this->order_data->set($order_id, self::ORDER_DATA_APPROVE_AMOUNT_EXPECTED, $expected_amount);
    }
}