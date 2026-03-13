<?php

use SSilence\ImapClient\ImapClient as Imap;

class Imap_agent {

    private static $imap;
    private static $action = false;
    private static $dirAttachments = Simpla . 'files/email/';

    public function connect($imapServer, $userName, $password) {
        self::$imap = new Imap($imapServer, $userName, $password, Imap::ENCRYPT_SSL);
    }

    public function getFolders() {
        return self::$imap->getFolders();
    }

    public function saveAttachments($message, $from) {
        if (!is_dir(self::$dirAttachments . $from . '/attachments/')) {
            mkdir(self::$dirAttachments . $from . '/attachments/', 0777, true);
        }
        return self::$imap->saveAttachments(['dir' => self::$dirAttachments . $from . '/attachments/', 'incomingMessage' => $message]);
    }

    public function addFolder($folderName) {
        $folders = $this->getFolders();
        foreach ($folders as $folder) {
            if ($folder == $folderName) {
                self::$action = true;
            }
        }
        if (!self::$action) {
            return self::$imap->addFolder($folderName);
        }
        return true;
    }

    public function deleteMessage($message) {
        return self::$imap->deleteMessage($message->header->uid);
    }

    public function moveMessage($message, $folder) {
        return self::$imap->moveMessage($message->header->uid, $folder);
    }

    public function selectFolder($folderName) {
        return self::$imap->selectFolder($folderName);
    }

    public function getMessages() {
        return self::$imap->getMessages();
    }

    public function getMessage($message) {
        return self::$imap->getMessage($message->header->message_id);
    }

}
