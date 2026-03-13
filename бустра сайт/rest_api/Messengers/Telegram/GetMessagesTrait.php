<?php

namespace Messengers\Telegram;

use Messengers\Config as MainConfig;
use Messengers\Core\Models\Message;
use Messengers\Core\Models\Verification;
use Messengers\Main;
use Messengers\MessageHtmlBody;
use stdClass;

trait GetMessagesTrait
{
    private function saveText(Message $message, stdClass $hook): ?Message
    {
        $message->body = MessageHtmlBody::text($hook->message->text);
        $message->client_id = (new Verification())->find(['sender_id' => $message->sender_id])->client_id ?? null;
        return $message->save();
    }


    private function saveAudio(Message $message, stdClass $hook): ?Message
    {
        $caption = $hook->message->caption ?? null;
        $fileInfo = $this->getFileInfo($hook->message->audio);
        if ($fileInfo && $fileInfo->ok) {
            $file = $this->downloadFile($fileInfo->result->file_path);
            if ($file) {
                $message->body = MessageHtmlBody::audio($file, $hook->message->audio->file_name, $caption);
                $message->client_id = (new Verification())->find(['sender_id' => $message->sender_id])->client_id ?? null;
                return $message->save();
            }
        }
        return null;
    }

    private function saveVideo(Message $message, stdClass $hook): ?Message
    {
        $caption = $hook->message->caption ?? null;
        $fileInfo = $this->getFileInfo($hook->message->video);
        if ($fileInfo && $fileInfo->ok) {
            $file = $this->downloadFile($fileInfo->result->file_path);
            $thumb = $this->getThumb($hook->message->video);
            if ($file) {
                $message->body = MessageHtmlBody::video($file, $thumb, $caption);
                $message->client_id = (new Verification())->find(['sender_id' => $message->sender_id])->client_id ?? null;
                return $message->save();
            }
        }
        return null;
    }

    private function saveAnimation(Message $message, stdClass $hook): ?Message
    {
        $caption = $hook->message->caption ?? null;
        $fileInfo = $this->getFileInfo($hook->message->animation);
        if ($fileInfo && $fileInfo->ok) {
            $file = $this->downloadFile($fileInfo->result->file_path);
            $thumb = $this->getThumb($hook->message->animation);
            if ($file) {
                $message->body = MessageHtmlBody::video($file, $thumb, $caption);
                $message->client_id = (new Verification())->find(['sender_id' => $message->sender_id])->client_id ?? null;
                return $message->save();
            }
        }
        return null;
    }

    private function getThumb(stdClass $video): ?string
    {
        $fileInfo = $this->getFileInfo($video->thumb);
        if ($fileInfo && $fileInfo->ok) {
            return $this->downloadFile($fileInfo->result->file_path);
        }
        return null;
    }

    private function savePhoto(Message $message, stdClass $hook): ?Message
    {
        $caption = $hook->message->caption ?? null;
        $file = $this->getPhoto((array)$hook->message->photo);
        if ($file) {
            $message->body = MessageHtmlBody::picture($file, $caption);
            $message->client_id = (new Verification())->find(['sender_id' => $message->sender_id])->client_id ?? null;
            return $message->save();
        }
        return null;
    }

    private function saveSticker(Message $message, stdClass $hook): ?Message
    {
        $fileInfo = $this->getFileInfo($hook->message->sticker);
        if ($fileInfo && $fileInfo->ok && $file = $this->downloadFile($fileInfo->result->file_path)) {
            $message->body = MessageHtmlBody::picture($file);
            $message->client_id = (new Verification())->find(['sender_id' => $message->sender_id])->client_id ?? null;
            return $message->save();
        }
        return null;
    }

    private function saveDocument(Message $message, stdClass $hook): ?Message
    {
        $caption = $hook->message->caption ?? null;
        $fileInfo = $this->getFileInfo($hook->message->document);
        if ($fileInfo && $fileInfo->ok && $file = $this->downloadFile($fileInfo->result->file_path)) {
            $message->body = MessageHtmlBody::file($file, $hook->message->document->file_name, $caption);
            $message->client_id = (new Verification())->find(['sender_id' => $message->sender_id])->client_id ?? null;
            return $message->save();
        }
        return null;
    }


    private function getPhoto(array $files): ?string
    {
        $getFile = (object)((array)$files[count($files) - 1]);
        $fileInfo = $this->getFileInfo($getFile);
        if ($fileInfo && $fileInfo->ok) {
            return $this->downloadFile($fileInfo->result->file_path);
        }
        return null;
    }

    private function downloadFile(string $file_path): ?string
    {
        $file = self::FILES_DIR . '/' . $file_path;
        if(!is_file($file)) {
            $src = file_get_contents(Config::GET_FILE_URL . '/' . $file_path);
            if ($src) {
                Main::createFilesDir(dirname($file));
                file_put_contents($file, $src);
                if (is_file($file)) {
                    return str_replace(ROOT_DIR, MainConfig::MAIN_URL, $file);
                }
            }
        }elseif (is_file($file)){
            return $file;
        }
        return null;
    }

    private function getFileInfo(stdClass $getFile): ?stdClass
    {
        $data = (object)[
            'file_id' => $getFile->file_id
        ];
        $url = Config::MAIN_URL . '/getFile';
        return $this->request($url, $data);
    }
}