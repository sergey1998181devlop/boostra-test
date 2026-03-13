<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__) . '/../api/Simpla.php';

class SendNotificationComplaintCalls extends Simpla
{
    private const TELEGRAM_BOT_TOKEN = '7555812531:AAFH-BjYIJIkgwxuDyU2ZFOeqzm43SB22Uc';
    private const TELEGRAM_CHAT_ID = '-1002459695515';
    private const MESSAGE_THREAD_ID = '236';
//    private const MESSAGE_THREAD_ID = '396'; // тестовый чат

    private Telegram $telegram;

    public function __construct()
    {
        parent::__construct();

        $this->telegram = new Telegram(self::TELEGRAM_BOT_TOKEN, self::TELEGRAM_CHAT_ID);
    }

    public function run()
    {
        $startDate = date('Y-m-d H:i:s', strtotime('-1 day'));
        $endDate = date('Y-m-d H:i:s');

        $limit = 30;
        $offset = 0;

        do {
            $comments = $this->getComments($startDate, $endDate, $limit, $offset);
            if (empty($comments)) {
                break;
            }

            foreach ($comments as $comment) {
                $call = json_decode($comment->text, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    continue;
                }

                $this->sendCallNotification($comment, $call);

                $call['is_sent_complaint_notification'] = true;

                $this->updateComment($comment->id, $call);
            }

            $offset += $limit;
        } while (count($comments) == $limit);
    }

    private function getComments(string $startDate, string $endDate, int $limit, int $offset)
    {
        $sql = "SELECT 
                    c.id, c.user_id, c.block, c.created, c.text, u.firstname, u.lastname, u.patronymic, u.phone_mobile 
                FROM s_comments c
                LEFT JOIN s_users u ON c.user_id = u.id
                WHERE c.block = 'incomingCall'
                  AND c.created BETWEEN ? AND ?
                  AND c.text LIKE '%\"is_sent_complaint_notification\":false%'
                  AND c.text LIKE '%\"blacklisted\":\"true\"%'
                ORDER BY c.created
                LIMIT ? OFFSET ?";
        $this->db->query($sql, $startDate, $endDate, $limit, $offset);
        return $this->db->results();
    }

    private function updateComment(int $id, array $call)
    {
        $jsonText = json_encode($call, JSON_UNESCAPED_UNICODE);

        $sql = "UPDATE s_comments SET text = ? WHERE id = ?";
        $this->db->query($sql, $jsonText, $id);
    }

    private function sendCallNotification(object $comment, array $call): void
    {
        $clientLink = $this->config->back_url . "/client/$comment->user_id";
        $operatorName = $call['handled_by'] === 'aviar' ? 'Aviar' : $call['operator_name'];
        $fullName = trim("$comment->lastname $comment->firstname $comment->patronymic");
        $tag = $call['operator_tag'] ?: 'Нет тега';
        $stage = $call['stage'] ?: 'Не указан';
        $assessment = $call['assessment'] ?: 'Нет оценки';

        $message = sprintf(
            "<b>Клиент жалобщик позвонил в контакт-центр.</b>\n\n" .
            "Клиент: <a href='%s'>%s</a>\n" .
            "Телефон: %s\n" .
            "Оператор: %s\n" .
            "Тег: %s\n" .
            "Стадия: %s\n" .
            "Оценка: %s%s",
            $clientLink,
            $fullName,
            '+' . $comment->phone_mobile,
            $operatorName,
            $tag,
            $stage,
            $assessment,
            empty($call['record_url']) ? "\n\nАудио-запись отсутствует" : ''
        );

        $this->telegram->sendMessage($message, ['parse_mode' => 'HTML', 'message_thread_id' => self::MESSAGE_THREAD_ID]);

        if (!empty($call['record_url'])) {
            $this->telegram->sendAudio($call['record_url'], [
                'message_thread_id' => self::MESSAGE_THREAD_ID
            ]);
        }
    }
}

$cron = new SendNotificationComplaintCalls();
$cron->run();
