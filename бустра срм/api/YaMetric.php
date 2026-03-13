<?php

namespace api\YaMetric;

use Simpla;

/**
 * Работа со статистикой Yandex
 * Class YaMetric
 */
class YaMetric extends Simpla
{
    public const TRANSACTION_STATUS_CONFIRMED = 'CONFIRMED';
    public const TRANSACTION_STATUS_AUTHORIZED = 'AUTHORIZED';

    /**
     * id цели в Яндекс метрики
     * ЛК, Кнопка: "Погасить заем - Погасить"
     */
    public const GOAL_ID_CLICK_PAY_LOAN = 235993421;

    private \AXP\YaMetrika\YaMetrika $YaMetric;

    public function __construct()
    {
        parent::__construct();
        $this->YaMetric = new \AXP\YaMetrika\YaMetrika($this->config->metric_token, $this->config->counter_id);
    }

    /**
     * Цель для НК, страница регистрации ФИО + СМС
     * https://www.boostra.ru/neworder?amount=9000&period=7
     */
    public const GOAL_ID_REGISTER_PAGE_STEP_1 = 258855672;

    /**
     * Переводим наши id метрик в массив для удобства
     * ym:s:visits - общее кол-во посещений
     * ym:s:users - уникальные визиты
     * Для Кредитного рейтинга
     */
    public const GOALS_CREDIT_RATING = [
        'view' => "ym:s:{{type_visit}}",
        'click' => '230568927', // кнопка "Показать кредитный рейтинг"
        'request_code' => '232439249', // кнопка "Получить код"
        'sign' => '232439342', // кнопка "Подписать" после смс
        'pay_after_sign' => '233333114', // кнопка "Оплатить"
        'pay_fact' => '' // todo подумать над реализацией фактической оплаты, надо отправлять запрос с бека
    ];

    /**
     * Цели для аналитика
     */
    public const GOALS_ANALYTICS = [
        'registration_click' => 253230900,
        'fio' => 225208454,
        'telephone' => 225208557,
        'passport' => 225208591,
        'address' => 225208642,
        'predreshenie' => 225208650,
        'reg_cards' => 225208672,
        'page_photo' => 256371094,
        'foto' => 104138056,
        'work' => 104137999,
        'view' => "ym:s:{{type_visit}}",
    ];

    /**
     * Генерируем цели для запроса
     * @param array $filter_data
     * @param array $goals_search
     * @return string
     */
    public static function generateGoals(array $goals_search, array $filter_data = []): string
    {
        $goals = [];
        foreach (array_filter($goals_search) as $key => $goal) {
            $visits_type = empty($filter_data['filter_client']) ? 'visits' : 'users';

            if ($key === 'view' && !is_numeric($goal)) {
                // https://yandex.ru/dev/metrika/doc/api2/api_v1/metrics/visits/basic.html
                // https://yandex.ru/adv/edu/metrika/metrika-start/bazovye-ponyatiya-prosmotry-vizity-posetiteli
                $goals[] = str_replace('{{type_visit}}', $visits_type, $goal);
            } else {
                $goals[] = 'ym:s:goal' . $goal . $visits_type;
            }
        }

        $goals[] = 'ym:s:users'; // добавляем метрику для возможности фильтрации по параметрам пользователя

        return implode(',', $goals);
    }

    /**
     * Генератор фильтра для userParams
     * @param array $filter_data
     * @param string $operator
     * @return string
     */
    public static function generateFilterLevel1(array $filter_data, string $operator = ''): string
    {
        $filters = '';
        foreach ($filter_data as $key => $value)
        {
            if (is_array($value)) {
                $filters .= ' AND (';
                foreach ($value as $key_inner => $item) {
                    $filters .= self::generateFilterLevel1([$key => $item], $key_inner > 0 ? ' OR ' : '');
                }
                $filters .= ')';
            } else {
                if (!empty($filters)) {
                    $filters .= ' AND ';
                }
                $filters .= "EXISTS(ym:up:paramsLevel1=='" . $key . "' AND ym:up:paramsLevel2=='" . $value . "')";
            }
        }
        return $operator . $filters;
    }

    /**
     * @deprecated
     * Тестовый запрос для проверки
     * @return mixed
     */
    public function sendRequest()
    {
        $this->params = array(
            'ids'     => $this->ids,
            'metrics' => 'ym:s:visits,ym:s:users',
            'date1'   => $this->date1,
            'date2'   => $this->date2,
            'preset'   => 'tags_u_t_m',
            'dimensions'   => 'ym:s:lastSignUTMSource',
        );

        $ch = curl_init('https://api-metrika.yandex.net/management/v1/counter/' . $this->config->counter_id . '/goals');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $this->config->metric_token));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        $res = json_decode($res, true);

        return $res;
    }

    /**
     * Выполняет запрос к метрике
     * @param array $filter_data
     * @param array $goals
     * @return array
     */
    public function getStatistic(array $goals, array $filter_data = []): array
    {
        // генерируем метрики по определенному формату
        $request_metrics = self::generateGoals($goals, $filter_data);

        $filters = [];

        if (!empty($filter_data['filter_url_exists'])) {
            $filters[] = "EXISTS(ym:pv:URL=*'" . $filter_data['filter_url_exists'] . "')"; // фильтр для определенной страницы или так ym:pv:URL=='https://boostra.ru/user/'
        }

        // применяем параметры от пользователя в userParams
        if (isset($filter_data['filter_offer']) && is_numeric($filter_data['filter_offer'])) {
            $filters[] = "EXISTS(ym:up:paramsLevel1=='user_approved' AND ym:up:paramsLevel2==" . (int)$filter_data['filter_offer'] . ")";
        }

        if (!empty($filter_data['paramsLevel1'])) {
            $filters[] = $filter_data['paramsLevel1'];
        }

        // новый посетитель
        if (!empty($filter_data['isNewUser'])) {
            $filters[] = "ym:s:isNewUser=='Yes'";
        }

        // без роботов
        if (empty($filter_data['isRobot'])) {
            $filters[] = "ym:s:isRobot=='No'";
        }

        $data = [
            'date1' => $filter_data['filter_date_start']->format('Y-m-d'),
            'date2' => $filter_data['filter_date_end']->format('Y-m-d'),
            'metrics' => $request_metrics,
            'filters' => implode(' AND ', $filters),
            'dimensions' => 'ym:s:date', // отображает доп информацию о полях, пока нам нужна только дата
            'sort' => '-ym:s:date', // сортировка
            'group' => $filter_data['filter_group_by'], // группировка
        ];

        return $this->YaMetric->customQuery($data)->data;
    }

    /**
     * Получает информацию об успешных транзакциях по кредитному рейтингу
     * @param array $filter_data
     * @return array
     */
    public function getOffersFromDB(array $filter_data = []): array
    {
        $where = [];
        $join = [];
        $select = [];

        $query = $this->db->placehold(
            "SELECT
                COUNT(t.id) as total_transactions,
                SUM(ROUND(t.amount / 100)) as total_fact_pay,
                -- {{select}}
            FROM
                __transactions t
                -- {{join}}
            WHERE
                t.payment_type = 'credit_rating'
                -- {{where}}
                GROUP BY `date` DESC
        ");

        $filter_date_start = $filter_data['filter_date_start']->format('Y.m.d');
        $filter_date_end = $filter_data['filter_date_end']->format('Y.m.d');

        $where[] = " DATE(t.created) BETWEEN '" . $this->db->escape($filter_date_start) . "' AND '" . $this->db->escape($filter_date_end) . "'";

        if (!empty($filter_data['filter_offer'])) {
            $between = $filter_data['filter_offer'] === 'approve' ? 'NOT BETWEEN' : 'BETWEEN';
            $join[] = " INNER JOIN __users u ON (u.id = t.user_id AND (u.maratorium_date " . $between . " '" . $this->db->escape($filter_date_start) . "' AND '" . $this->db->escape($filter_date_end) . "'))";
        }

        if ($filter_data['filter_group_by'] === 'day') {
            $select[] = "DATE_FORMAT(t.created, '%Y.%m.%d') as date";
        }

        if ($filter_data['filter_group_by'] === 'month') {
            $select[] = " DATE_FORMAT(t.created, '%Y.%m') as date";
        }

        if (!empty($filter_data['referer'])) {
            $where[] = " t.referer REGEXP '^(" . $filter_data['referer']  . ")([^\/]*)$'";
        }

        if (!empty($filter_data['is_completed'])) {
            $where[] = " status IN ('" . self::TRANSACTION_STATUS_CONFIRMED . "', '" . self::TRANSACTION_STATUS_AUTHORIZED . "')";
        }

        $query = strtr($query, [
            '-- {{select}}' => !empty($select) ? implode("\n", $select) : '',
            '-- {{join}}' => !empty($join) ? implode("\n", $join) : '',
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
        ]);

        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Приводим массив к единому виду
     * @param array|false $metric_db
     * @param array $metric_yandex
     * @param array $filter_data
     * @return array
     */
    public function mergeData($metric_db, array $metric_yandex, array $filter_data): array
    {
        $result = [];

        $generate_date = function ($date) use ($filter_data) {
            $is_month = $filter_data['filter_group_by'] === 'month';
            $date_string = $is_month ? $date . '.01' : $date;
            $date_time = \DateTime::createFromFormat('Y.m.d', $date_string);
            $formatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);

            if ($is_month) {
                $formatter->setPattern('y г. MMM');
            } else {
                $formatter->setPattern('y.MM.dd');
            }

            return $formatter->format($date_time);
        };

        foreach ($metric_db as $item) {
            $key_date = $generate_date($item->date);
            $result[$key_date] = [
                'total_fact_pay' => (int)$item->total_fact_pay,
                'total_transactions' => (int)$item->total_transactions,
                'date_sort' => $item->date,
            ];
        }

        foreach ($metric_yandex['data'] as $item) {
            $date = str_replace('-', '.', $item['dimensions'][0]['name']);

            if ($filter_data['filter_group_by'] === 'month') {
                $date = substr($date, 0, -3);
            }

            $key_date = $generate_date($date);

            if (isset($result[$key_date]['total_view'])) {
                $result[$key_date]['total_view'] += (int)$item['metrics'][0];
                $result[$key_date]['total_click'] += (int)$item['metrics'][1];
                $result[$key_date]['total_request_code'] += (int)$item['metrics'][2];
                $result[$key_date]['total_sign'] += (int)$item['metrics'][3];
                $result[$key_date]['total_after_sign'] += (int)$item['metrics'][4];
            } else {
                $result[$key_date] = array_merge(
                    [
                        'total_view' => (int)$item['metrics'][0],
                        'total_click' => (int)$item['metrics'][1],
                        'total_request_code' => (int)$item['metrics'][2],
                        'total_sign' => (int)$item['metrics'][3],
                        'total_after_sign' => (int)$item['metrics'][4],
                        'total_after_pay' => 0, // todo метрика после оплаты пока пропущена
                    ],
                    $result[$key_date] ?? ['date_sort' => $date]
                );
            }

            if (!isset($result[$key_date]['total_transactions'])) {
                $result[$key_date]['total_transactions'] = 0;
                $result[$key_date]['total_fact_pay'] = 0;
            }
        }

        if ($filter_data['filter_group_by'] !== 'month') {
            krsort($result);
        } else {
            uasort($result, function ($a, $b) {
                if ($a['date_sort'] == $b['date_sort']) {
                    return 0;
                }
                return ($a['date_sort'] > $b['date_sort']) ? -1 : 1;
            });
        }

        return $result;
    }

    /**
     * @deprecated
     * Получает utm метки, отсеевая только те по которым были одорения
     * @return array|false
     */
    public function getUtmSources()
    {
        $query = $this->db->placehold("SELECT utm_source FROM s_orders WHERE utm_source != '' AND approve_date IS NOT NULL GROUP BY utm_source");
        $this->db->query($query);
        return $this->db->results('utm_source');
    }
}
