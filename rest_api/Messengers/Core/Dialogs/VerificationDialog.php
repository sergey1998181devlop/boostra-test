<?php

namespace Messengers\Core\Dialogs;

use JsonException;
use Messengers\Config;
use Messengers\Core\Models\Message;
use Messengers\Core\Models\User;
use Messengers\Core\Models\Verification;
use Messengers\Main;
use Messengers\ProviderInterface;
use function Messengers\Dialogs\random_int;

/**
 *
 */
final class VerificationDialog
{

    /**
     * @var Message
     */
    private $message = null;

    /**
     * @var ProviderInterface|null
     */
    private $provider = null;

    /**
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->setProvider($message);
        if ($this->provider) {
            $this->message = new Message();
            $this->message->messenger_type = $message->messenger_type;
            $this->message->manager_id = $message->manager_id;
            $this->message->body = $message->body;
            $this->message->status = $this->provider->getStatusCode('send');
            $this->message->client_id = $message->client_id;
            $this->message->sender_id = $message->sender_id;
            $this->message->chat_id = $message->chat_id;
        }
    }

    /**
     * @param Message $message
     * @return void
     */
    private function setProvider(Message $message): void
    {
        $this->provider = Main::getProvider($message->messenger_type);
    }

    public function step3(): ?Message
    {
        $verification = $this->getVerification();
        if ($verification && preg_match('~(.+)?(?<code>\d{6})(.+)?~u', $this->message->body, $matches)) {
            $verifyTime = strtotime($verification->date_update) + (60 * 60 * Config::VERIFY_TIME);
            if ((int)$verification->verify_code === (int)$matches['code'] && $verifyTime >= time()) {
                $this->message->body = 'Ваш мессенджер ' . ucfirst($this->message->messenger_type) . ' успешно верифицирован. ';
                $this->message->body .= 'Для получения кода авторизации на сайте ' . Config::MAIN_URL . ' отправьте слово CODE';
                $verification->verify_step = 4;
                $verification->verify_status = 1;
            } elseif ($verifyTime < time()) {
                $this->message->body = 'Прошло более ' . Config::VERIFY_TIME . ' часа(ов) для верификации вашего мессенджера. ';
                $this->message->body .= 'Попробуйте повторить попытку еще раз';
                $verification->verify_step = 2;
            } else {
                $this->message->body = 'Не верно указ код верификации. Попробуйте еще раз';
            }
            $verification->save();
        } else {
            $this->message->body = 'Верификация не пройдена.';
            $this->step1();
        }
        return $this->message->send();
    }

    /**
     * @return Verification|null
     */
    public function getVerification(): ?Verification
    {
        return (new Verification())->find(
            [
                'sender_id' => $this->message->sender_id,
                'messenger_type' => $this->message->messenger_type
            ]
        );
    }

    /**
     * @return Message|null
     * @throws JsonException
     */
    public function step1(): ?Message
    {
        $this->message->body = 'Добро пожаловать в систему чатов сайта ' . Config::MAIN_URL . '. ';
        $this->message->body .= 'В данный момент Ваш мессенджер ' . ucfirst($this->message->messenger_type) . ' не связан ';
        $this->message->body .= 'с Вашим аккаунтом на нашем сайте. Для привязки Вашего мессенджера с аккаунтом отправьте ';
        $this->message->body .= 'в ответном сообщении номер Вашего мобильного телефона в формате +79991112233';
        $verification = $this->getVerification();
        if ($verification) {
            $verification->verify_step = 2;
            $verification->save();
        }
        return $this->message->send();
    }

    public function step4(): ?Message
    {
        $this->message->body .= 'Ваш мессенджер ' . ucfirst($this->message->messenger_type) . ' уже верифицирован.';
        return $this->message->send();
    }

    public function step2(): ?Message
    {
        $verification = $this->getVerification();
        if ($verification && preg_match
            (
                '~(.+)?(?<phone>\+(\d)([\s(]+)?(\d{3})([\s)]+)?(\d{3})([\s-]+)?(\d{2})([\s-]+)?(\d{2}))(.+)?~u',
                $this->message->body,
                $matches
            )
        ) {
            $user = (new User())->find([Config::DB_USER_PHONE_FIELD_NAME => Main::preparePhone($matches['phone'])]);
            if ($user) {
                $code = random_int(100000, 999999);
                $this->message->body = 'На указанный Вами номером телефона ' . $matches['phone'];
                $this->message->body .= ' отправлен код для верификации вашего мессенджера ' . ucfirst($this->message->messenger_type) . '. ';
                $this->message->body .= 'Пришлите его после получения в ответном сообщении в Вашем мессенджере ' . ucfirst($this->message->messenger_type) . '. ';
                $verification->verify_step = 3;
                $verification->client_id = $user->id;
                $verification->verify_code = $code;
                $sms = 'Код для верификации Вашего мессенджера ' . ucfirst($this->message->messenger_type) . ': ' . $code;
                Main::sendSms($sms, $user->{Config::DB_USER_PHONE_FIELD_NAME});
                $verification->save();
            } else {
                $this->message->body = 'С указанным Вами номером телефона ' . $matches['phone'];
                $this->message->body .= ' не найден ни один аккаунт в системе. Отправьте в ответном ';
                $this->message->body .= 'сообщении номер Вашего мобильного телефона в формате +79991112233';
            }
        } else {
            $this->step1();
        }
        return $this->message->send();
    }

}