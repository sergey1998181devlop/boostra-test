<?php

declare(strict_types=1);

error_reporting(0);
ini_set('display_errors', 'Off');

chdir('..');

require_once dirname(__FILE__).'/../api/Telegram.php';
require_once dirname(__FILE__).'/../api/Simpla.php';

class CbrToTelegramForwarder extends Simpla
{
    private const TELEGRAM_BOT_TOKEN = '7555812531:AAFH-BjYIJIkgwxuDyU2ZFOeqzm43SB22Uc';
    private const TELEGRAM_CHAT_ID = '-1002459695515';
    private const MESSAGE_THREAD_ID = '238';
    
    private $imapConnection;

    private Telegram $telegram;

    public function __construct()
    {
        parent::__construct();

        $this->imapConnection = imap_open(
            '{imap.yandex.ru:993/imap/ssl}INBOX',
            'Cb.alert@boostra.ru',
            'frbbzfdjurgkrmat'
        );

        if (!$this->imapConnection) {
            error_log('Failed to connect to IMAP: ' . imap_last_error());
            return;
        }

        $this->telegram = new Telegram(self::TELEGRAM_BOT_TOKEN, self::TELEGRAM_CHAT_ID);
    }

    public function run(): void
    {
        $emails = imap_search($this->imapConnection, 'UNSEEN');

        if (empty($emails)) {
            imap_close($this->imapConnection);
            return;
        }

        foreach ($emails as $emailId) {
            $this->processEmail($emailId);
        }

        imap_close($this->imapConnection);
    }
    
    /**
     * Обработка одного письма по ID
     *
     * @param int $emailId ID письма
     */
    private function processEmail(int $emailId): void
    {
        $header = imap_headerinfo($this->imapConnection, $emailId);
        $body   = imap_body($this->imapConnection, $emailId);

        if (!$header || !$body) {
            return;
        }

        $cleanedBody = $this->cleanEmailBody($body);
        $parsedText = $this->parseEmailBody($cleanedBody);
        $formatedDate = (new DateTime())->setTimestamp($header->udate)->format('Y-m-d H:i:s');

        $messageContent = !empty($parsedText) ? $parsedText : $cleanedBody;

        $message = sprintf(
            "<b>Пришло письмо от ЦБ</b>\n\nДата: %s\n<blockquote expandable>%s</blockquote>",
            $formatedDate,
            $messageContent
        );

        $this->telegram->sendMessage($message, ['parse_mode' => 'HTML', 'message_thread_id' => self::MESSAGE_THREAD_ID]);

        // Помечаем письмо как прочитанное
        imap_setflag_full($this->imapConnection, (string)$emailId, "\\Seen");
    }

    /**
     * Очистка тела письма от лишних тегов и пробелов
     *
     * @param string $body
     * @return string
     */
    private function cleanEmailBody(string $body): string
    {
        $body = base64_decode($body);
        $body = strip_tags($body);

        // Заменяем множественные пробелы на один пробел и обрезаем
        return trim(preg_replace('/\s+/', ' ', $body) ?? '');
    }

    /**
     * Парсинг тела письма для извлечения необходимой информации
     *
     * @param string $text Очищенное тело письма
     * @return string
     */
    private function parseEmailBody(string $text): string
    {
        $position = strpos($text, 'Руководителю ');
        if ($position === false) {
            return '';
        }

        return trim(substr($text, $position));
    }
}

$cron = new CbrToTelegramForwarder();
$cron->run();
