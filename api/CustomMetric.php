<?php

require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../api/Simpla.php');

/**
 * API для работы со своей метрикой
 * Class CustomMetric
 */
class CustomMetric extends Simpla
{
    /**
     * Кнопка на главной получить процентный займ
     */
    const GOAL_GET_PAY = 1;

    /**
     * Кнопка на главной получить бесплатный займ
     */
    const GOAL_GET_FREE_PAY = 2;

    /**
     * НК посетил страницу рейтинга
     */
    const GOAL_CR_NK_VISIT_PAGE = 3;

    /**
     * КР НК - нажал Получить рейтинг
     */
    const GOAL_CR_NK_CLICK_BTN = 4;

    /**
     * КР НК - нажал Получить смс код
     */
    const GOAL_CR_NK_CLICK_SMS_CODE = 5;

    /**
     * КР НК - Регистрация смс кода в базе
     * когда пользователь прошел проверку на правильность кода
     */
    const GOAL_CR_NK_REGISTER_SMS_CODE = 6;

    /**
     * КР НК - Переход на оплату
     * сработал редирект на страницу оплаты
     */
    const GOAL_CR_NK_OPEN_PAY_PAGE = 7;

    /**
     * Срабатывает, когда пользователь авторизован в ЛК
     */
    const GOAL_USER_LOGIN_LK = 8;

    private $visitor_id;
    private $user_id;

    public function __construct()
    {
        parent::__construct();
        $this->visitor_id = (int)$_SESSION['vid'];
        $this->user_id = (int)($_SESSION['user_id'] ?? 0);
    }

    /**
     * Добавляет метрику
     * @param int $metric_goal_id
     * @param int $from_backend
     * @return mixed
     */
    public function addMetricAction(int $metric_goal_id, int $from_backend = 0)
    {
        $result = false;

        $metric_goal = $this->getMetricGoalById($metric_goal_id);

        if (!empty($metric_goal->status)) {
            $data = [
                'metric_goal_id' => $metric_goal_id,
                'visitor_id' => $this->visitor_id,
                'referer' =>  $_SERVER['HTTP_REFERER'] ?? '',
                'from_backend' => $from_backend,
            ];

            // флаг уникальности в пределах сессии
            $data['session_unique'] = (int)!$this->custom_metric->hasMetricActionByVisitorId($this->visitor_id, $data['metric_goal_id']);
            if (!empty($this->user_id)) {
                $data['user_id'] = $this->user_id;

                // флаг уникальности для пользователя
                $data['user_unique'] = (int)!$this->custom_metric->hasMetricActionByUserId($this->user_id, $data['metric_goal_id']);

                // проверим НК или ПК
                $user = $this->users->get_user($this->user_id);
                $loan_history = $user->loan_history;
                $credits_history = $loan_history;
                $data['client_type'] = (int)!empty($credits_history);
            }

            $result = $this->custom_metric->addMetricActionToBD($data);

            // добавим пул метрик которые требуют признак НК, ПК, он заполняется после авторизации
            if (!empty($result) && !empty($metric_goal->validate_client_type) && empty($this->user_id)) {
                if (!isset($_SESSION['metric_actions'])) {
                    $_SESSION['metric_actions'] = [];
                }
                $_SESSION['metric_actions'][] = $result;
            }
        }

        return $result;
    }

    /**
     * Добавляет новое событие метрики в БД
     * @param $data
     * @return mixed
     */
    public function addMetricActionToBD($data)
    {
        $query = $this->db->placehold("INSERT INTO s_metric_actions SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновление метрик в БД
     * @param $ids
     * @param $data
     * @return void
     */
    public function updateMetricActionsByIds($ids, $data)
    {
        $sql = "UPDATE s_metric_actions SET ?% WHERE id IN(?@)";
        $query = $this->db->placehold($sql, $data, $ids);
        $this->db->query($query);
    }

    /**
     * Проверяет наличие существующей метрики по id визита
     * @param int $visitor_id
     * @param int $metric_goal_id
     * @return bool
     */
    public function hasMetricActionByVisitorId(int $visitor_id, int $metric_goal_id): bool
    {
        $query = $this->db->placehold("SELECT EXISTS (SELECT * FROM s_metric_actions WHERE visitor_id = ? AND metric_goal_id = ?) as r", $visitor_id, $metric_goal_id);
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }

    /**
     * Проверяет наличие существующей метрики по id пользователя
     * @param int $user_id
     * @param int $metric_goal_id
     * @return bool
     */
    public function hasMetricActionByUserId(int $user_id, int $metric_goal_id): bool
    {
        $query = $this->db->placehold("SELECT EXISTS (SELECT * FROM s_metric_actions WHERE user_id = ? AND metric_goal_id = ?) as r", $user_id, $metric_goal_id);
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }

    /**
     * Получает цель из БД
     * @param int $id
     * @return false|int
     */
    public function getMetricGoalById(int $id)
    {
        $query = $this->db->placehold("SELECT * FROM s_metric_goals WHERE id = ?", $id);
        $this->db->query($query);
        return $this->db->result();
    }
}
