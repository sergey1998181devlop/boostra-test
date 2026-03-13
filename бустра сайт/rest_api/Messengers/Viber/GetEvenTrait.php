<?php

namespace Messengers\Viber;

use JsonException;
use Messengers\Core\Dialogs\VerificationDialog;
use Messengers\Core\Models\Message;
use Messengers\Core\Models\Verification;
use stdClass;

trait GetEvenTrait
{

    /**
     * Отчет о доставке
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function eventDelivered(stdClass $hook): ?Message
    {
        $message = (new Message())->find($hook->message_token, 'message_id');
        if ($message) {
            $message->status = $this->getStatusCode('delivered');
            return $message->save();
        }
        return null;
    }

    /**
     * Отчет о прочтении
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function eventSeen(stdClass $hook): ?Message
    {
        $message = (new Message())->find($hook->message_token, 'message_id');
        if ($message) {
            $message->status = $this->getStatusCode($hook->event);
            return $message->save();
        }
        return null;
    }

    /**
     * Отчет об ошибке доставки
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    private function eventFailed(stdClass $hook): ?Message
    {
        $message = (new Message())->find($hook->message_token, 'message_id');
        if ($message) {
            $message->status = $this->getStatusCode($hook->event);
            return $message->save();
        }
        return null;
    }

    /**
     * События при  новом подписчике
     * @param stdClass $hook
     * @return Message|null
     * @throws JsonException
     */
    final public function eventSubscribed(stdClass $hook): ?Message
    {
        $message = new Message();
        $message->status = $this->getStatusCode($hook->event);
        $message->messenger_type = 'viber';
        $message->message_id = $hook->message_token;
        $message->sender_id = $hook->user->id;
        $hook->message = (object)['text' => 'Новый подписчик ' . $hook->user->name];
        $message->body = $this->text($hook);
        $verification = new Verification();
        $verification->sender_id = $message->sender_id;
        $verification->messenger_type = $message->messenger_type;
        $verification->verify_step = 1;
        $verify = $verification->save();
        $verifyMethodName = false;
        if ($verify) {
            $verifyMethodName = 'step' . $verify->verify_step;
        }
        if ($verify && method_exists(VerificationDialog::class, $verifyMethodName)) {
            (new VerificationDialog($message))->$verifyMethodName();
        }
        return $message->save();
    }

}