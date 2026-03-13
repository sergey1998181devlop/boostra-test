<?php

namespace Messengers\Core\Dialogs;

use Messengers\Core\Models\Message;
use Messengers\Core\Models\Verification;

final class Dialogs
{
    public static function init(Message $message): void
    {
        $bind = [
            'sender_id' => $message->sender_id,
            'messenger_type' => $message->messenger_type
        ];
        $verification = (new Verification())->find($bind);
        if ($verification && !$verification->verify_status) {
            $method = 'step' . $verification->verify_step;
            (new VerificationDialog($message))->$method();
        } elseif (!$verification) {
            $verification = new Verification();
            $verification->messenger_type = $message->messenger_type;
            $verification->verify_step = 1;
            $verification->sender_id = $message->sender_id;
            $verification->save();
            $method = 'step' . $verification->verify_step;
            (new VerificationDialog($message))->$method();
        } else {
            new BotDialog($message);
        }
    }
}