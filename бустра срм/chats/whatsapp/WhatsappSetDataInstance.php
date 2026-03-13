<?php

namespace chats\whatsapp;

use chats\whatsapp\WhatsappSettings AS Settings;
use \stdClass;

class WhatsappSetDataInstance {

    public static function setDataInstance(
            string $type, string $messageId = '', string $name = '',
            string $color = '', string $labelId = ''
    ) {
        if ($type === 'repeatHook') {
            return self::setDataRepeatHook($messageId);
        } elseif ($type === 'createLabel') {
            return self::setDataCreateLabel($name);
        } elseif ($type === 'updateLabel') {
            return self::setDataUpdateLabel($labelId, $name, $color);
        } elseif ($type === 'removeLabel') {
            return self::setDataRemoveLabel($labelId);
        }
    }

    private static function setDataRemoveLabel($labelId) {
        $data = new \stdClass();
        $data->labelId = $labelId;
        return $data;
    }

    private static function setDataRepeatHook($messageId) {
        $data = new \stdClass();
        $data->messageId = $messageId;
        return $data;
    }

    private static function setDataCreateLabel(string $name) {
        $data = new \stdClass();
        $data->name = $name;
        return $data;
    }

    private static function setDataUpdateLabel(string $labelId, string $name = '', string $color = '') {
        $data = new \stdClass();
        $data->labelId = $labelId;
        if ($name) {
            $data->name = $name;
        }
        if ($color) {
            $data->color = $color;
        }
        return $data;
    }

}
