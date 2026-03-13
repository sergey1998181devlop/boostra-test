<?php

session_start();
chdir('..');

require_once 'api/Simpla.php';

class LoanGameAjax extends Simpla
{
    const ACTIONS = [
        'metric_create',
        'metric_update'
    ];

    private $response = [];
    private $user_id;

    public function run()
    {
        $this->user_id = (int)$_SESSION['user_id'];
        $action = $this->request->get('action', 'string');

        if (!empty($this->user_id) && in_array($action, self::ACTIONS))
            $this->$action();

        $this->output();
    }

    private function output()
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");

        echo json_encode($this->response);
    }

    private function metric_create()
    {
        $is_mobile = $this->request->get('is_mobile');
        $order = $this->orders->get_last_order($this->user_id);

        $query = $this->db->placehold("INSERT INTO __metric_game SET ?%", [
                'user_id' => $this->user_id,
                'order_id' => $order->id,
                'start_time' => $this->get_datetime(),
                'is_mobile' => $is_mobile
            ]);
        $this->db->query($query);

        $this->response['metric_id'] = $this->db->insert_id();
    }

    private function metric_update()
    {
        $metric_id = $this->request->get('metric_id');
        $score = $this->request->get('score');

        $query = $this->db->placehold("UPDATE __metric_game SET ?% WHERE id = ? AND user_id = ?", [
                'score' => $score,
                'end_time' => $this->get_datetime()
            ], $metric_id, $this->user_id);
        $this->db->query($query);
    }

    /**
     * Возвращает текущую дату и время для вставки в БД
     * @return string
     * @throws Exception
     */
    private function get_datetime()
    {
        $timezone = new DateTimeZone('Europe/Moscow');
        $datetime = new DateTime('now', $timezone);
        return $datetime->format('Y-m-d H:i:s');
    }
}

$loan_game = new LoanGameAjax();
$loan_game->run();