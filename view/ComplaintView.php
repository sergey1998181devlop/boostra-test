<?php

require_once 'View.php';
require_once dirname(__DIR__) . '/api/traits/ComplaintMessageTrait.php';
require_once dirname(__DIR__) . '/api/services/UsedeskService.php';
require_once dirname(__DIR__) . '/api/TelegramApi.php';

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

        return $this->design->fetch('complaint/index.tpl');
    }

    private function createComplaint(): void
    {
        $organizationId = $this->organizations::FINTEHMARKET_ID;

        $name = $this->request->post('complaint_name');
        $phone = $this->request->post('complaint_phone');
        $email = $this->request->post('complaint_email');
        $birth = $this->request->post('complaint_birth');
        $topic_id = $this->request->post('complaint_topic');
        $text = $this->request->post('complaint_text');
        $files = $this->request->files('complaint_file');

        if (empty($name) || empty($phone) || empty($email) || empty($birth) || empty($topic_id) || empty($text)) {
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode(['error' => 'empty_required_fields']);
            exit;
        }

        $topicData = $this->complaint->get_topic((int)$topic_id);
        $topicName = $topicData ? $topicData->name : 'Тема не указана';

        $exist_complaint = $this->complaint->get_limit($name, $phone, $email, $birth);
        if ($exist_complaint && !empty($exist_complaint->created)) {
            $current_date = new DateTime(date('Y-m-d H:i:s'));
            $created = new DateTime($exist_complaint->created);
            $diff = $current_date->diff($created);

            if ($diff->h == 0 && $diff->i <= static::PAUSE_MINUTES) {
                header("Content-type: application/json; charset=UTF-8");
                echo json_encode(['error' => 'time_limit']);
                exit;
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

            header("Content-type: application/json; charset=UTF-8");
            echo json_encode(['message' => 'Обращение отправлено.']);
            exit;
        } catch (\Exception $e) {
            $this->logging('ERROR', 'Error send complaint', '', ['error' => $e->getMessage()], 'complaint.txt');

            header("Content-type: application/json; charset=UTF-8");
            echo json_encode(['message' => 'Сейчас невозможно отправить обращение.']);
            exit;
        }
    }

    /**
     * Обработка загруженных файлов
     */
    private function processUploadedFiles(?array $files): array
    {
        $complaint_files = [];

        if (empty($files)) {
            return $complaint_files;
        }

        if (count($files['name']) > static::MAX_FILES_COUNT) {
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode(['error' => 'max_files']);
            exit;
        }

        foreach ($files['name'] as $num => $file) {
            if ($files['size'][$num] >= static::MAX_FILE_SIZE) {
                header("Content-type: application/json; charset=UTF-8");
                echo json_encode(['error' => 'max_file_size']);
                exit;
            }

            if (!in_array($files['type'][$num], static::$available_image_exts) && !in_array($files['type'][$num], static::$available_doc_exts)) {
                header("Content-type: application/json; charset=UTF-8");
                echo json_encode(['error' => 'error_file_type']);
                exit;
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

}
