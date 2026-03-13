<?php

namespace Messengers\Core\Dialogs;

use Messengers\Config;
use Messengers\Core\Models\AuthCode;
use Messengers\Core\Models\Message;
use Messengers\Core\Models\User;
use function Messengers\Dialogs\random_int;

class BotDialog
{

    private static $dialogs = [
        '(.+)?(CODE)(.+)?' => 'sendCode'
    ];
    /**
     * @var Message
     */
    private $message;


    public function __construct(Message $message)
    {
        $this->message = new Message();
        foreach ($message as $field => $value) {
            $this->message->$field = $value;
        }
        foreach (self::$dialogs as $pattern => $action) {
            if (preg_match('~' . $pattern . '~uim', $message->body)) {
                $this->$action();
            }
        }
    }

    private function sendCode(): ?Message
    {
        $code = rand(1000, 9999);
        $this->message->body = 'Ваш код для входа на сайте ' . Config::MAIN_URL . ': ' . $code;
        $userId = (new VerificationDialog($this->message))->getVerification()->client_id;
        $user = (new User())->find($userId);
        if ($user) {
            AuthCode::addAuthCode($code, $user->{Config::DB_USER_PHONE_FIELD_NAME});
            return $this->message->send();
        }
        return null;
    }
}