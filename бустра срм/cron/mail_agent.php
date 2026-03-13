<?php

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('max_execution_time', '60000');

require_once dirname(__FILE__) . '/../api/Simpla.php';

/**
 * Description of mail_agent
 *
 * @author alexey
 */
class mail_agent extends Simpla {

    private static $accounts = [
        ['login' => 'sv@boostra.ru', 'password' => 'SVB163(hj9', 'server' => 'imap.yandex.ru'],
        ['login' => 'insur@boostra.ru', 'password' => 'MAS2306@', 'server' => 'imap.yandex.ru'],
        ['login' => 'lk@boostra.ru', 'password' => 'SSB0707@', 'server' => 'imap.yandex.ru'],
        ['login' => 'doc@boostra.ru', 'password' => 'Jyt78hUhu8', 'server' => 'imap.yandex.ru'],
    ];
    private $data = [];

    public function run() {
        foreach (self::$accounts as $account) {
            $this->imap_agent->connect($account['server'], $account['login'], $account['password']);
            $this->imap_agent->selectFolder('INBOX');
            $emails = $this->imap_agent->getMessages();
            foreach ($emails as $email) {
                $messages = $this->imap_agent->getMessages();
                foreach ($messages as $message) {
                    $this->emailParse($message);
                }
            }
        }
    }

    private function emailParse($message) {
        $headers = $message->header;
        $userInfo = $this->users->getUserByEmail($headers->from);
        $this->data = [
            'AppealDate' => date('Y-m-d H:i:s', strtotime($headers->date)),
            'Text' => $this->setText($message->message),
            'Them' => $headers->subject,
            'Email' => $headers->from,
            'ToEmail' => $headers->to,
        ];
        if ($userInfo) {
            $this->data['Phone'] = '+' . $userInfo->phone_mobile;
        }
        $appealInfo = $this->appeals->chekAppeal($this->data);
        $this->imap_agent->addFolder('archive');
        $this->imap_agent->moveMessage($message, 'archive');
        $this->imap_agent->deleteMessage($message);
        if (!$appealInfo) {
            $this->appeals->addAppeal($this->data);
            $this->addMessage($this->data);
        } else {
            $this->addMessage($this->data);
        }
    }

    public function addMessage($data) {
        $userInfo = $this->users->getUserByEmail($data['Email']);
        $obj = [
            'chat_type' => 'email',
            'user_id_in_chat' => $data['Email'],
            'chat_id' => $data['Email'],
            'update_id' => 0,
            'status' => 1,
            'message_status' => 0,
            'message_id' => random_int(1000000, 99999999999),
            'text' => $data['Text'],
            'user_id' => $userInfo->id,
            'date' => $data['AppealDate'],
            'phone' => $userInfo->phone_mobile
        ];
        return $this->chats->addMessage($obj);
    }

    private function setText($message) {
        if ($message->types[0] === 'plain') {
            return $message->text;
        } elseif ($message->types[0] === 'html') {
            return $message->info[0]->body;
        }
    }

    private function messageAttachments($message) {
        if (!empty($message->attachments)) {
            return $this->imap_agent->saveAttachments($message);
        }
        return false;
    }

}

$mailAgent = new mail_agent();
$mailAgent->run();
