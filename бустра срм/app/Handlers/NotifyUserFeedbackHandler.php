<?php

namespace App\Handlers;

use App\Contracts\NotifyUserFeedbackHandlerContract;
use App\Enums\UsedeskTicketTypes;
use App\Models\User;
use App\Service\TelegramService;
use App\Service\UsedeskService;
use UserPhones;

require_once __DIR__ . '/../../api/UserPhones.php';

class NotifyUserFeedbackHandler implements NotifyUserFeedbackHandlerContract
{
    private UsedeskService $usedeskService;
    private string $usedeskApiToken;
    private TelegramService $telegramService;
    private string $telegramChatId;
    private int $telegramMessageThreadId;
    private string $orderUrl;
    private string $userUrl;

    public function __construct()
    {
        $this->usedeskService = new UsedeskService();
        $this->usedeskApiToken = config('services.usedesk.negative_feedback_secret_key');
        $this->telegramService = new TelegramService();
        $this->telegramChatId = config('services.telegram.notifications_chat_id');
        $this->telegramMessageThreadId = config('services.telegram.negative_feedback_thread_id');
        $this->orderUrl = config('services.app.back_url') . '/order/';
        $this->userUrl = config('services.app.back_url') . '/client/';
    }

    public function handle(array $feedback): void
    {
        $user = $this->getUser($feedback['user_id']);

        $message = $this->prepareMessage($feedback, $user);

        $this->sendTelegramNotification($message);

        $this->createUsedeskTicket($message, $user);

        $this->syncPhone($feedback);
    }

    private function getUser(int $userId)
    {
        return (new User())->get(
            ['firstname', 'lastname', 'patronymic', 'email'],
            ['id' => $userId]
        )->getData();
    }

    private function prepareMessage(array $feedback, array $user): string
    {
        $userFullName = trim("{$user['lastname']} {$user['firstname']} {$user['patronymic']}");
        $userUrl = $this->userUrl . $feedback['user_id'];
        $orderUrl = $this->orderUrl . $feedback['order_id'];

        $feedbackData = json_decode($feedback['data'], true);

        return sprintf(
            "<b>Клиент оставил негативную оценку</b>\n\n" .
            "Клиент: <a href='%s'>%s</a>\n" .
            "Заявка: <a href='%s'>%s</a>\n" .
            "Оценка: %s\n" .
            "Причина: %s",
            $userUrl,
            $userFullName,
            $orderUrl,
            $feedback['order_id'],
            $feedbackData['rate'],
            $feedbackData['reason'],
        );
    }

    private function sendTelegramNotification(string $message): void
    {
        $this->telegramService->sendMessage(
            $this->telegramChatId,
            $message,
            [
                'parse_mode' => 'HTML',
                'message_thread_id' => $this->telegramMessageThreadId
            ]
        );
    }

    private function createUsedeskTicket(string $message, array $user): void
    {
        $userFullName = trim("{$user['lastname']} {$user['firstname']} {$user['patronymic']}");

        $data = [
            'subject' => 'Клиент ' . $userFullName . ' оставил негативную оценку',
            'message' => $message,
            'client_name' => $userFullName,
            'client_email' => $user['email'],
            'type' => UsedeskTicketTypes::PROBLEM
        ];

        $this->usedeskService->createTicket($this->usedeskApiToken, $data);
    }

    private function syncPhone(array $feedback): void
    {
        $feedbackData = json_decode($feedback['data'], true);

        if (!empty($feedbackData['phone'])) {
            try {
                $userPhones = new UserPhones();

                // Get user's UID first to avoid potential errors
                $user = $userPhones->users->get_user($feedback['user_id']);
                $userUid = $user ? ($user->UID ?? '') : '';

                // Pass the UID explicitly to avoid possible errors in the sync_phone method
                $userPhones->sync_phone(
                    $feedback['user_id'],
                    $feedbackData['phone'],
                    UserPhones::SOURCE_NBKI_PHONE,
                    $userUid
                );
            } catch (\Exception $e) {
                error_log('Error in syncPhone(): ' . $e->getMessage());
            }
        }
    }
}