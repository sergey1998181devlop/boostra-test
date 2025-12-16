<?php

error_reporting(0);
ini_set('display_errors', 'Off');
chdir('..');

require_once('api/Simpla.php');
require_once('api/TelegramApi.php');

class ClientActionHandler extends Simpla
{
    private const TELEGRAM_BOT_TOKEN = '7555812531:AAFH-BjYIJIkgwxuDyU2ZFOeqzm43SB22Uc';
    private const TELEGRAM_CHAT_ID = '-1002459695515';
    private const MESSAGE_THREAD_ID = '234';
    
    public function run(): void
    {
        $action = $this->request->get('action');
        $allowedActions = ['clickCbrLink'];

        if ($action && in_array($action, $allowedActions, true)) {
            if (!method_exists($this, $action) || !is_callable([$this, $action])) {
                $this->request->json_output([
                    'message' => "Action $action is not callable"
                ]);
            }
            $this->$action();
        }
    }

    /**
     * Обработка клика на ссылку ЦБ
     *
     * @return void
     */
    private function clickCbrLink(): void
    {
        $telegram = new TelegramApi([
            'token' => self::TELEGRAM_BOT_TOKEN,
            'chat_id' => self::TELEGRAM_CHAT_ID,
            'message_thread_id' => self::MESSAGE_THREAD_ID
        ]);

        $userIdFromCookie = $_COOKIE['user_id'] ?? null;
        $userIp = $this->getUserIp();


        if (!$userIdFromCookie && !$userIp) {
            return;
        }

        $currentUser = $userIdFromCookie
            ? $this->users->get_user_by_id($userIdFromCookie)
            : $this->users->get_user_by_ip($userIp);
            
        if (!$currentUser) {
            return;
        }

        $message = $this->formatTelegramMessage($currentUser, $userIp, $userIdFromCookie);

        $telegram->sendMessage($message);
    }

    /**
     * Форматирует сообщение для Telegram
     */
    private function formatTelegramMessage($user, $userIp, ?int $userIdFromCookie): string
    {
        $foundBy = $userIdFromCookie
            ? "ID клиента из Cookie: $userIdFromCookie"
            : "⚠️ Клиент найден по IP";
        $userUrl = $this->buildUserUrl($user->id);
        $userFullName = $this->formatUserFullName($user);

        return sprintf(
            "Клиент нажал на ссылку cbr.ru\n\n" .
            "<b>Клиент</b>: <a href='%s'>%s</a>\n" .
            "<b>Дата рождения</b>: %s\n" .
            "<b>Телефон</b>: +%s\n" .
            "<b>IP-адрес</b>: %s\n\n" .
            "<b>%s</b>",
            $userUrl,
            $userFullName,
            $user->birth,
            $user->phone_mobile,
            $userIp,
            $foundBy
        );
    }

    /**
     * Формирует полное имя пользователя
     */
    private function formatUserFullName($user): string
    {
        return trim(sprintf('%s %s %s',
            $user->lastname ?? '',
            $user->firstname ?? '',
            $user->patronymic ?? ''
        ));
    }

    /**
     * Формирует URL профиля пользователя
     */
    private function buildUserUrl(int $userId): string
    {
        $backUrl = rtrim($this->config->back_url, '/');
        return sprintf('%s/client/%d', $backUrl, $userId);
    }

    /**
     * Извлекает IP-адрес пользователя
     *
     * @return string|null
     */
    private function getUserIp(): ?string
    {
        $ipSources = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

        foreach ($ipSources as $source) {
            if (!empty($_SERVER[$source]) && filter_var($_SERVER[$source], FILTER_VALIDATE_IP)) {
                return $_SERVER[$source];
            }
        }

        return null;
    }

}

$action = new ClientActionHandler();
$action->run();
