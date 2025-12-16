<?php

namespace Messengers\WhatsApp;

use Messengers\Config as MainConfig;
use Messengers\Core\Models\Message;
use Messengers\Main;
use Messengers\MessageHtmlBody;
use stdClass;

trait SaveMessagesTrait
{

    private function text(Message $message, stdClass $hookMessage): ?Message
    {
        $message->body = MessageHtmlBody::text($hookMessage->body);
        return $message->save();
    }

    private function picture(Message $message, stdClass $hookMessage): ?Message
    {
        
        $caption = null;
        if(isset($hookMessage->caption)){
            $caption = $hookMessage->caption;
        }
        $file = $this->download($hookMessage);
        if($file){
            $message->body = MessageHtmlBody::picture($file, $caption);
        }
        return $message->save();
    }

    private function video(Message $message, stdClass $hookMessage): ?Message
    {
        
        $caption = null;
        if(isset($hookMessage->caption)){
            $caption = $hookMessage->caption;
        }
        $file = $this->download($hookMessage);
        if($file){
            $message->body = MessageHtmlBody::video($file, $caption);
        }
        return $message->save();
    }


    private function document(Message $message, stdClass $hookMessage): ?Message
    {
        $caption = null;
        if(isset($hookMessage->caption)){
            $caption = $hookMessage->caption;
        }
        $file = $this->download($hookMessage);
        if($file){
            $message->body = MessageHtmlBody::file($file, $caption);
        }
        return $message->save();
    }

    private function audio(Message $message, stdClass $hookMessage): ?Message
    {
        
        $caption = null;
        if(isset($hookMessage->caption)){
            $caption = $hookMessage->caption;
        }
        $file = $this->download($hookMessage);
        if($file){
            $message->body = MessageHtmlBody::audio($file, $caption);
        }
        return $message->save();
    }

    private function file(Message $message, stdClass $hookMessage): ?Message
    {
        
        $caption = null;
        if(isset($hookMessage->caption)){
            $caption = $hookMessage->caption;
        }
        $file = $this->download($hookMessage);
        if($file){
            $message->body = MessageHtmlBody::file($file, $caption);
        }
        return $message->save();
    }

    private function download(stdClass $hookMessage):?string
    {
        $fileName = md5($hookMessage->body);
        $file = self::FILES_DIR.'/'.$hookMessage->type.'/'.$fileName.'.'.$this->getExtFile($hookMessage->mimetype);
        if(!is_file($file)) {
            if (Main::createFilesDir(dirname($file))) {
                file_put_contents($file, base64_decode($hookMessage->body));
            }
            if (is_file($file)) {
                return str_replace(ROOT_DIR, MainConfig::MAIN_URL, $file);
            }
            return null;
        }
        return str_replace(ROOT_DIR, MainConfig::MAIN_URL, $file);
    }

    private function getExtFile(string $mimetype):?string
    {
        $types = require dirname(__FILE__, 2).'/mime_types.php';
        foreach ($types as $ext => $mime){
            if(mb_strtolower($mimetype) === mb_strtolower($mime)){
                return $ext;
            }
        }
        return null;
    }
}