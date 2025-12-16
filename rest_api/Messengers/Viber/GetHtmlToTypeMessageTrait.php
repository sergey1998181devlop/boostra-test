<?php

namespace Messengers\Viber;

use Messengers\Config as MainConf;
use Messengers\MessageHtmlBody;
use stdClass;

trait GetHtmlToTypeMessageTrait
{
    final private function location(): string
    {

        return '';
    }

    final private function sticker(stdClass $hook, string $file): string
    {
        $str = '<div class="' . MainConf::MESSAGE_WRAPPER_CLASS . '">';
        $str .= '<img class="' . MainConf::MESSAGE_IMAGE_CLASS . '" src="' . $file . '"/>';
        $str .= '</div>';
        return $str;
    }

    final private function contact(): string
    {

        return '';
    }

    private function video(stdClass $hook, string $file): string
    {
        if (isset($hook->message->text) && $hook->message->text) {
            return MessageHtmlBody::video($file, $hook->message->thumbnail, $hook->message->text);
        }
        return MessageHtmlBody::video($file, $hook->message->thumbnail);
    }

    /**
     * @param stdClass $hook
     * @param string $file
     * @return string
     */
    private function file(stdClass $hook, string $file): string
    {
        if (isset($hook->message->text) && $hook->message->text) {
            return MessageHtmlBody::file($file, $hook->message->file_name, $hook->message->text);
        }
        return MessageHtmlBody::file($file, $hook->message->file_name);
    }

    private function carousel(): string
    {

        return '';
    }

    private function content(): string
    {

        return '';
    }

    /**
     * @param stdClass $hook
     * @return string
     */
    private function url(stdClass $hook): string
    {
        return MessageHtmlBody::url($hook->message->media);
    }

    private function text(stdClass $hook): string
    {
        return MessageHtmlBody::text($hook->message->text);
    }

    private function picture(stdClass $hook, string $file): string
    {
        if ($hook->message->text) {
            return MessageHtmlBody::picture($file, $hook->message->text);
        }
        return MessageHtmlBody::picture($file);
    }


}