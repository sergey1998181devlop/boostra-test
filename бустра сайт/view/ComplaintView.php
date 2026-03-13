<?php

require_once 'View.php';
require_once dirname(__DIR__) . '/api/traits/ComplaintMessageTrait.php';
require_once dirname(__DIR__) . '/api/services/UsedeskService.php';
require_once dirname(__DIR__) . '/api/TelegramApi.php';
require_once dirname(__DIR__) . '/api/YaSmartCaptcha.php';

use api\services\UsedeskService;

class ComplaintView extends View
{
    use ComplaintMessageTrait;

    private const PAUSE_MINUTES = 10;
    private const MAX_FILES_COUNT = 5;
    private const MAX_FILE_SIZE = 20000000;

    private const USEDESK_CHANNEL_ID = 42068;
    private const TELEGRAM_BOT_TOKEN = '7555812531:AAFH-BjYIJIkgwxuDyU2ZFOeqzm43SB22Uc';
    private const TELEGRAM_CHAT_ID = '-1002459695515';
    private const MESSAGE_THREAD_ID = '924';

    private UsedeskService $usedeskService;
    private string $usedeskApiToken;

    public function __construct()
    {
        parent::__construct();

        $this->usedeskService = new UsedeskService();
        $this->usedeskApiToken = $this->config->USEDESK['TICKET_SECRET_KEY'] ?? '';
    }

    private static $available_image_exts = [
        "image/png",
        "image/jpeg",
    ];

    private static $available_doc_exts = [
        "application/pdf",
        "application/msword",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    ];

    public function fetch()
    {
        if ($this->request->method('post')) {
            $this->createComplaint();
        }

        $organizationId = $this->organizations::FINTEHMARKET_ID;

        $complaintTopics = $this->complaint->get_topics($organizationId);

        $complaintTopicsArray = array_map(function ($topic) {
            return [
                'id' => $topic->id,
                'yandex_goal_id' => $topic->yandex_goal_id,
                'name' => $topic->name
            ];
        }, $complaintTopics);

        $this->design->assign('user', $this->user);
        $this->design->assign('complaint_topics', $complaintTopicsArray);
        $this->design->assign('eighteen_years_birthdate', date('Y-m-d', strtotime('-18 years')));
        $this->design->assign('complaint_csrf', $this->getOrCreateCsrfToken());

        return $this->design->fetch('complaint/index.tpl');
    }

    /**
     * Возвращает CSRF-токен для формы жалобы.
     *
     * @return string
     */
    private function getOrCreateCsrfToken(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        if (!isset($_SESSION['complaint_csrf']) || !is_string($_SESSION['complaint_csrf']) || strlen($_SESSION['complaint_csrf']) < 16) {
            try {
                $_SESSION['complaint_csrf'] = bin2hex(random_bytes(32));
            } catch (\Throwable $e) {
                $_SESSION['complaint_csrf'] = md5(microtime(true) . mt_rand());
            }
        }

        return $_SESSION['complaint_csrf'];
    }

    /**
     * Обрабатывает отправку формы жалобы.
     *
     * @return void
     * @throws Exception
     */
    private function createComplaint(): void
    {
        $organizationId = $this->organizations::FINTEHMARKET_ID;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        $csrf = (string)$this->request->post('complaint_csrf');
        $csrfSession = (string)($_SESSION['complaint_csrf'] ?? '');
        if ($csrf === '' || $csrfSession === '' || !hash_equals($csrfSession, $csrf)) {
            $this->jsonError('csrf');
        }

        $hp = trim((string)$this->request->post('complaint_hp'));
        if ($hp !== '') {
            $this->jsonError('spam');
        }

        if (empty($this->is_developer)) {
            $smartToken = (string)$this->request->post('smart-token');
            if ($smartToken === '' || !\api\YaSmartCaptcha::check_captcha($smartToken)) {
                $this->jsonError('captcha');
            }
        }

        $agree = (string)$this->request->post('agree');
        if ($agree === '') {
            $this->jsonError('agree_required');
        }

        $name = trim((string)$this->request->post('complaint_name'));
        $phone = trim((string)$this->request->post('complaint_phone'));
        $email = trim((string)$this->request->post('complaint_email'));
        $birth = trim((string)$this->request->post('complaint_birth'));
        $topic_id = (int)$this->request->post('complaint_topic');
        $text = trim((string)$this->request->post('complaint_text'));
        $files = $this->request->files('complaint_file');

        if ($name === '' || $phone === '' || $email === '' || $birth === '' || empty($topic_id) || $text === '') {
            $this->jsonError('empty_required_fields');
        }

        if (!preg_match('/^[А-ЯЁа-яё]+(\s[А-ЯЁа-яё]+)+$/u', $name)) {
            $this->jsonError('invalid_name');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->jsonError('invalid_email');
        }

        $digits = preg_replace('/\D+/', '', $phone);
        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }
        if (strlen($digits) !== 11 || $digits[0] !== '7') {
            $this->jsonError('invalid_phone');
        }
        $phone = '+'.$digits;

        $birthDt = \DateTime::createFromFormat('Y-m-d', $birth);
        $birthErrors = \DateTime::getLastErrors() ?: ['warning_count' => 0, 'error_count' => 0];
        if (!$birthDt || !empty($birthErrors['warning_count']) || !empty($birthErrors['error_count'])) {
            $this->jsonError('invalid_birth');
        }

        $year = (int)$birthDt->format('Y');
        if ($year < 1920) {
            $this->jsonError('invalid_birth');
        }

        $today = new \DateTime('today');
        $eighteenYearsAgo = (clone $today)->modify('-18 years');
        if ($birthDt > $today || $birthDt > $eighteenYearsAgo) {
            $this->jsonError('invalid_birth');
        }

        $allowedTopics = $this->complaint->get_topics($organizationId);
        $topicMap = [];
        foreach ($allowedTopics as $t) {
            $topicMap[(int)$t->id] = $t;
        }
        if (!isset($topicMap[$topic_id])) {
            $this->jsonError('invalid_topic');
        }
        $topicName = (string)$topicMap[$topic_id]->name;

        $len = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($len < 50 || $len > 300) {
            $this->jsonError('invalid_text_length');
        }

        $exist_complaint = $this->complaint->get_limit($name, $phone, $email, $birth);
        if ($exist_complaint && !empty($exist_complaint->created)) {
            $current_date = new DateTime(date('Y-m-d H:i:s'));
            $created = new DateTime($exist_complaint->created);
            $diff = $current_date->diff($created);

            if ($diff->h == 0 && $diff->i <= static::PAUSE_MINUTES) {
                $this->jsonError('time_limit');
            }
        }

        if ($this->user) {
            $this->userEmails->syncEmail($this->user, $email, UserEmails::SOURCE_COMPLAINT_EMAIL);
        }

        try {
            $complaint_files = $this->processUploadedFiles($files);

            $usedeskTicketId = $this->createUsedeskComplaintTicket($name, $phone, $email, $birth, $topicName, $text, $complaint_files);

            $this->complaint->add_complaint([
                'fio' => $name,
                'phone' => $phone,
                'email' => $email,
                'birth' => $birth,
                'topic_id' => $topic_id,
                'topic' => $topicName,
                'organization_id' => $organizationId,
                'message' => $text,
                'files' => json_encode($complaint_files),
                'status' => $usedeskTicketId ? 'processed' : 'failed',
                'usedesk_ticket_id' => $usedeskTicketId
            ]);

            if ($usedeskTicketId) {
                $this->sendTelegramComplaintNotification($name, $phone, $email, $birth, $topicName, $text, $usedeskTicketId, $complaint_files);
            }

            $this->jsonMessage('Обращение отправлено.');
        } catch (\Exception $e) {
            $this->logging('ERROR', 'Error send complaint', '', ['error' => $e->getMessage()], 'complaint.txt');

            $this->jsonMessage('Сейчас невозможно отправить обращение.');
        }
    }

    /**
     * Обрабатывает загруженные файлы из формы жалобы.
     *
     * @param array<string,mixed>|null $files
     * @return array<int,array{path:string,mime_type:string,name:string}>
     */
    private function processUploadedFiles(?array $files): array
    {
        $complaint_files = [];

        if (empty($files)) {
            return $complaint_files;
        }

        if (count($files['name']) > static::MAX_FILES_COUNT) {
            $this->jsonError('max_files');
        }

        foreach ($files['name'] as $num => $file) {
            if ($files['size'][$num] >= static::MAX_FILE_SIZE) {
                $this->jsonError('max_file_size');
            }

            if (!in_array($files['type'][$num], static::$available_image_exts) && !in_array($files['type'][$num], static::$available_doc_exts)) {
                $this->jsonError('error_file_type');
            }

            if (!is_dir($this->config->root_dir . 'files/complaints/')) {
                mkdir($this->config->root_dir . 'files/complaints/');
            }

            $new_filename = $this->config->root_dir . 'files/complaints/' . md5(microtime() . mt_rand()) . $file;
            $file_uploaded = move_uploaded_file($files['tmp_name'][$num], $new_filename);
            if ($file_uploaded) {
                $complaint_files[] = [
                    'path' => $new_filename,
                    'mime_type' => $files['type'][$num],
                    'name' => $file
                ];
            }
        }

        return $complaint_files;
    }

    /**
     * Создание тикета в Usedesk
     */
    private function createUsedeskComplaintTicket(
        string $name,
        string $phone,
        string $email,
        string $birth,
        string $topic,
        string $message,
        array  $complaint_files
    ): ?int
    {
        try {
            $profileUrl = $this->user ? trim($this->config->back_url, '/') . "/client/" . $this->user->id : null;
            $fullMessage = $this->setMessage($name, $phone, $email, $birth, $topic, $message, null, true, $profileUrl);

            $uploadedFilesForUsedesk = array_map(function ($f) {
                return ['tmp_name' => $f['path'], 'type' => $f['mime_type'], 'name' => $f['name']];
            }, $complaint_files);

            $usedeskClientId = null;
            if ($this->user) {
                $usedeskClientId = $this->userUsedesk->getUsedeskUserId($this->user);
            }

            $data = [
                'subject' => 'Boostra. Клиент отправил форму жалобы',
                'message' => $fullMessage,
                'channel_id' => self::USEDESK_CHANNEL_ID,
                'priority' => 'extreme',
                'type' => 'question',
            ];

            if ($usedeskClientId) {
                $data['client_id'] = $usedeskClientId;
            }

            $response = $this->usedeskService->createTicket($this->usedeskApiToken, $data, $uploadedFilesForUsedesk);
            $ticketId = $response['ticket_id'] ?? null;

            if ($ticketId) {
                $this->logging('INFO', 'Usedesk ticket created', '', [
                    'ticket_id' => $ticketId,
                    'email' => $email,
                    'client_id' => $usedeskClientId
                ], 'complaint.txt');
            } else {
                $this->logging('ERROR', 'Usedesk ticket not created', '', ['response' => $response], 'complaint.txt');
            }

            return $ticketId;
        } catch (\Exception $e) {
            $this->logging('ERROR', 'Usedesk create ticket failed', '', [
                'error' => $e->getMessage(),
                'email' => $email
            ], 'complaint.txt');
            return null;
        }
    }

    /**
     * Отправка уведомления в Telegram
     */
    private function sendTelegramComplaintNotification(
        string $name,
        string $phone,
        string $email,
        string $birth,
        string $topic,
        string $message,
        int    $usedeskTicketId,
        array  $complaint_files
    ): void
    {
        try {
            $telegram = new TelegramApi([
                'token' => self::TELEGRAM_BOT_TOKEN,
                'chat_id' => self::TELEGRAM_CHAT_ID,
                'message_thread_id' => self::MESSAGE_THREAD_ID
            ]);

            $profileUrl = $this->user ? trim($this->config->back_url, '/') . "/client/" . $this->user->id : null;
            $tgMessage = $this->setMessage($name, $phone, $email, $birth, $topic, $message, $usedeskTicketId, false, $profileUrl);
            $telegram->sendMessage($tgMessage);

            if (!empty($complaint_files)) {
                $media = [];
                $media_files = [];

                foreach ($complaint_files as $file) {
                    $file_name = basename($file['path']);
                    $media[] = [
                        'type' => 'document',
                        'media' => 'attach://' . $file_name
                    ];
                    $media_files[$file_name] = curl_file_create($file['path'], $file['mime_type'], $file['name']);
                }

                if (!empty($media)) {
                    $telegram->sendMediaGroup($media, $media_files);
                }
            }

            $this->logging('INFO', 'Telegram notification sent', '', [
                'usedesk_ticket_id' => $usedeskTicketId
            ], 'complaint.txt');
        } catch (\Exception $e) {
            $this->logging('ERROR', 'Telegram send failed', '', [
                'error' => $e->getMessage(),
                'usedesk_ticket_id' => $usedeskTicketId
            ], 'complaint.txt');
        }
    }

    /**
     * Отправляет JSON-ответ с кодом ошибки.
     *
     * @param string $code
     * @param array<string,mixed> $extra
     * @return void
     */
    private function jsonError(string $code, array $extra = []): void
    {
        $this->request->json_output(array_merge(['error' => $code], $extra));
    }

    /**
     * Отправляет JSON-ответ с сообщением.
     *
     * @param string $message
     * @return void
     */
    private function jsonMessage(string $message): void
    {
        $this->request->json_output(['message' => $message]);
    }
}
