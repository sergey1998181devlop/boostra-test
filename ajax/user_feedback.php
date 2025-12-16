<?php

error_reporting(-1);
ini_set('display_errors', 'Off');

date_default_timezone_set('Europe/Moscow');

session_start();
require_once('../api/Simpla.php');
require_once('../vendor/autoload.php');
require_once('../api/UserEmails.php');

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class UserFeedbackAjax extends Simpla
{
    private const API_TOKEN_NAME = 'Boostra';
    private const EMAIL = 'info@boostra.ru';
    private array $response = [];

    public function __construct()
    {
        parent::__construct();

        $this->run();

        $this->output();
    }

    private function setResponse(bool $success, string $message): void
    {
        $this->response['success'] = $success;
        $this->response['message'] = $message;
    }

    private function run(): void
    {
        $action = $this->request->get('action', 'string');

        switch ($action) {
            case 'check':
                $this->check();
                break;
            case 'create':
                $this->create();
                break;
        }
    }

    private function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setResponse(false, 'Неверный метод запроса');
            return;
        }

        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');

        if (is_null($user_id) || is_null($order_id)) {
            $this->setResponse(false, 'Не заданы обязательные параметры: user_id, order_id');
            return;
        }

        $sql = "SELECT id FROM s_user_feedbacks WHERE user_id = ? AND order_id = ?";

        try {
            $result = $this->db->query($sql, $user_id, $order_id);
        } catch (Exception $e) {
            $this->setResponse(false, $e->getMessage());
            return;
        }

        if ($result->num_rows > 0) {
            $this->setResponse(true, "Отзыв уже оставлен");
            $this->response['show_modal'] = false;
        } else {
            $this->setResponse(true, "Отзыв не оставлен");
            $this->response['show_modal'] = true;
        }
    }

    private function create(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->setResponse(false, 'Неверный метод запроса');
            return;
        }

        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $rate = $this->request->post('rate', 'integer');
        $reason = $this->request->post('reason', 'string');
        $needsFeedback = $this->request->post('needs_feedback', 'string');
        $contactMethod = $this->request->post('contact_method', 'string');
        $phone = $this->request->post('phone');
        $email = $this->request->post('email');
        $emailComment = $this->request->post('email_comment');

        if (is_null($user_id) || is_null($order_id) || is_null($rate)) {
            $this->setResponse(false, 'Не заданы обязательные параметры: user_id, order_id, rate');
            return;
        }

        // There is no reason input for a feedback via email, then assign an email comment input as a reason
        $reason = empty($reason) && !empty($emailComment) ? $emailComment : $reason;

        $feedbackData = [
            'rate' => $rate,
            'reason' => $reason,
            'needs_feedback' => $needsFeedback,
            'contact_method' => $contactMethod,
            'phone' => $phone,
            'email' => $email,
            'email_comment' => $emailComment
        ];

        $jsonData = json_encode($feedbackData, JSON_UNESCAPED_UNICODE);

        $sql = "INSERT INTO s_user_feedbacks (user_id, order_id, data) VALUES (?, ?, ?)";

        try {
            $this->db->query($sql, $user_id, $order_id, $jsonData);
        } catch (Exception $e) {
            $this->setResponse(false, $e->getMessage());
            return;
        }

        $user = $this->users->get_user($user_id);
        (new UserEmails())->syncEmail($user, $email, UserEmails::SOURCE_FEEDBACK_EMAIL);

        if ($rate < 3) {
            if ($contactMethod === 'email') {
                $this->sendEmail($feedbackData, $user, $order_id);
            }

            if ($contactMethod === 'phone') {
                $this->createTicket($feedbackData, $user, $order_id);
            }

            $id = $this->getId($user_id, $order_id);
            $this->sendNotificationFeedback($id);
        }

        $this->setResponse(true, "Данные успешно сохранены");
    }

    private function getId(int $user_id, int $order_id)
    {
        $query = "SELECT id FROM s_user_feedbacks WHERE user_id = ? AND order_id = ?";

        $this->db->query($query, $user_id, $order_id);

        return $this->db->result('id');
    }

    private function sendNotificationFeedback(int $id): void
    {
        $apiToken = $this->getApiToken();
        if (empty($apiToken)) {
            return;
        }

        $baseUrl = $this->config->back_url . '/app/clients/feedback/:id/notify';

        $url = str_replace(':id', $id, $baseUrl);
        $headers = [
            'Authorization: Bearer ' . $apiToken
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            error_log("cURL error: " . $error_msg);
        }
        curl_close($ch);
    }

    private function getApiToken()
    {
        $query = "SELECT token FROM application_tokens WHERE name = ?";

        $this->db->query($query, self::API_TOKEN_NAME);

        return $this->db->result('token');
    }

    private function output(): void
    {
        header("Content-type: application/json; charset=UTF-8");
        header("Cache-Control: must-revalidate");
        header("Pragma: no-cache");
        header("Expires: -1");

        echo json_encode($this->response);
    }

    private function sendEmail(array $feedbackData, object $user, int $order_id): void
    {
        $userFullName = trim("{$user->lastname} {$user->firstname} {$user->patronymic}");

        $userUrl = $this->config->back_url . '/client/' . $user->id;
        $orderUrl = $this->config->back_url . '/order/' . $order_id;

        $message = sprintf(
            "<b>Клиент оставил негативную оценку</b><br><br>" .
            "Клиент: <a href='%s'>%s</a><br>" .
            "Заявка: <a href='%s'>%s</a><br>" .
            "Оценка: %s<br>" .
            "Email: %s<br>" .
            "Сообщение:<br>   %s",
            $userUrl,
            $userFullName,
            $orderUrl,
            $order_id,
            $feedbackData['rate'],
            $feedbackData['email'],
            $feedbackData['email_comment']
        );

        try {
            $phpMailer = new PHPMailer();
            $phpMailer->SMTPDebug = 0;

            $phpMailer->isSMTP();
            $phpMailer->Host = 'smtp.mail.ru';
            $phpMailer->SMTPAuth = true;
            $phpMailer->Username = $this->config->mail_mp_user_name;
            $phpMailer->Password = $this->config->mail_mp_password;

            $phpMailer->SMTPSecure = 'ssl';
            $phpMailer->Port = 465;

            $phpMailer->addAddress(static::EMAIL);
            $phpMailer->CharSet = 'UTF-8';
            $phpMailer->setFrom($this->config->mail_mp_user_name, 'Boostra Mail Agent');
            $phpMailer->Subject = "Boostra. Оценка с сайта";
            $phpMailer->isHTML(true);
            $phpMailer->Body = $message;

            $phpMailer->send();
        } catch (Exception $e) {
            error_log("Sending email error: " . $e->getMessage());
            return;
        }
    }

    private function createTicket(array $feedbackData, object $user, int $order_id)
    {
        if ($feedbackData['contact_method'] !== 'phone') {
            return;
        }

        if ($feedbackData['reason'] === 'Работа службы взыскания') {
            $subjectId = 12;
        } else {
            $subjectId = 4;
        }

        $description = 'Получена оценка с сайта, телефон для обратной связи: ' . $feedbackData['phone'] . ', причина недовольства: "' . $feedbackData['reason'] . '"';
        $jsonData = json_encode(['phone' => $feedbackData['phone']]);
        $createdAt = date('Y-m-d H:i:s');

        $query = "
            SELECT c.id FROM s_ticket_companies c
                JOIN s_organizations o ON c.name = o.short_name
            WHERE o.id = (
                SELECT organization_id FROM s_orders WHERE id = $order_id
            )";

        try {
            $this->db->query($query);
            $companyId = (int) $this->db->result('id');
        } catch (Exception $e) {
            error_log("Query for company id failed: " . $e->getMessage());
            $companyId = null;
        }

        $sql = "
            INSERT INTO s_mytickets (
                client_id, 
                chanel_id, 
                subject_id, 
                status_id, 
                description, 
                data, 
                created_at, 
                order_id, 
                company_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        try {
            $this->db->query($sql, $user->id, 6, $subjectId, 1, $description, $jsonData, $createdAt, $order_id, $companyId);
            return $this->db->insert_id();
        } catch (Exception $e) {
            $this->setResponse(false, $e->getMessage());
            return;
        }
    }
}

new UserFeedbackAjax();