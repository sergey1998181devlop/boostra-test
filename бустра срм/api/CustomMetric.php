<?php

require_once 'Simpla.php';

/**
 * API для работы со своей метрикой
 * Class CustomMetric
 */
class CustomMetric extends Simpla
{
    /**
     * Кнопка на главной получить процентный займ
     */
    public const GOAL_GET_PAY = 1;

    /**
     * Кнопка на главной получить бесплатный займ
     */
    public const GOAL_GET_FREE_PAY = 2;

    /**
     * НК посетил страницу рейтинга
     */
    public const GOAL_CR_NK_VISIT_PAGE = 3;

    /**
     * КР НК - нажал Получить рейтинг
     */
    public const GOAL_CR_NK_CLICK_BTN = 4;

    /**
     * КР НК - нажал Получить смс код
     */
    public const GOAL_CR_NK_CLICK_SMS_CODE = 5;

    /**
     * КР НК - Регистрация смс кода в базе
     * когда пользователь прошел проверку на правильность кода
     */
    public const GOAL_CR_NK_REGISTER_SMS_CODE = 6;

    /**
     * КР НК - Переход на оплату
     * сработал редирект на страницу оплаты
     */
    public const GOAL_CR_NK_OPEN_PAY_PAGE = 7;

    /**
     * Срабатывает, когда пользователь авторизован в ЛК
     */
    const GOAL_USER_LOGIN_LK = 8;

    /**
     * Получает количество достигнутых целей из БД
     * @param array $filter_data
     * @return array|false
     */
    public function getTotalsMetricActions(array $filter_data = [])
    {
        $where = [];
        $group_by = '';
        $left_join = [];

        $sql = "SELECT 
                    COUNT(*) as total
                    -- {{select}}
                FROM s_metric_actions m
                -- {{left_join}}
                WHERE 1=1
                -- {{where}}
                -- {{group_by}}
                ";

        if (!empty($filter_data['filter_group_by'])) {
            $group_by = "GROUP BY filter_date ASC";

            if ($filter_data['filter_group_by'] === 'day') {
                $select[] = ", DATE_FORMAT(m.date_added, '%Y.%m.%d') as filter_date";
            } elseif ($filter_data['filter_group_by'] === 'month') {
                $select[] = ", DATE_FORMAT(m.date_added, '%Y.%m') as filter_date";
            }
        }

        if (!empty($filter_data['filter_utm_source']) || !empty($filter_data['filter_webmaster_id'])) {
            $left_join[] = "LEFT JOIN s_users u ON u.id = m.user_id";
            if (!empty($filter_data['filter_utm_source'])) {
                $where[] = $this->db->placehold("u.utm_source IN (?@)", $filter_data['filter_utm_source']);
            }

            if (!empty($filter_data['filter_webmaster_id'])) {
                $where[] = $this->db->placehold("u.webmaster_id IN (?@)", $filter_data['filter_webmaster_id']);
            }
        }

        if (!empty($filter_data['filter_goal_id'])) {
            $where[] = $this->db->placehold("m.metric_goal_id = ?", (int)$filter_data['filter_goal_id']);
        }

        if (!empty($filter_data['filter_date_added'])) {
            $where[] = $this->db->placehold("m.date_added BETWEEN ? AND ?", $filter_data['filter_date_added']['filter_date_start'] . ' 00:00:00', $filter_data['filter_date_added']['filter_date_end'] . ' 23:59:59');
        }

        if (isset($filter_data['filter_client_type'])) {
            $where[] = $this->db->placehold("m.client_type = ?", $filter_data['filter_client_type']);
        }

        if (isset($filter_data['filter_user_unique'])) {
            $where[] = $this->db->placehold("m.user_unique = ?", $filter_data['filter_user_unique']);
        }

        $query = strtr($sql, [
            '-- {{select}}' => !empty($select) ? implode("\n", $select) : '',
            '-- {{left_join}}' => !empty($left_join) ? implode("\n", $left_join) : '',
            '-- {{where}}' => !empty($where) ? "AND " . implode(" AND ", $where) : '',
            '-- {{group_by}}' => $group_by,
        ]);

        $this->db->query($query);
        return $this->db->results();
    }
}
