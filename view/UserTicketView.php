<?php


use api\enums\UsedeskTicketSubjects;
use api\services\FileStorageService;
use api\services\UsedeskService;
use api\traits\JWTAuthTrait;

require_once('View.php');

class UserTicketView extends View
{
    use JWTAuthTrait;

    private const TELEGRAM_BOT_TOKEN = '7555812531:AAFH-BjYIJIkgwxuDyU2ZFOeqzm43SB22Uc';

    private const TELEGRAM_CHAT_ID = '-1002459695515';

    private const MESSAGE_THREAD_ID = '6262';
//    private const MESSAGE_THREAD_ID = '396'; # TEST

    private const PAGE_CAPACITY = 10;
    private const USEDESK_CHANNEL_ID = 63955;
    private UsedeskService $usedeskService;
    private string $usedeskApiToken;
    private FileStorageService $fileStorageService;

    public function __construct()
    {
        parent::__construct();

        try {
            $this->usedeskService = new UsedeskService();
            $this->usedeskApiToken = $this->config->USEDESK['TICKET_SECRET_KEY'];

            $this->fileStorageService = new FileStorageService(
                $this->config->USER_TICKET_STORAGE['endpoint'],
                $this->config->USER_TICKET_STORAGE['region'],
                $this->config->USER_TICKET_STORAGE['key'],
                $this->config->USER_TICKET_STORAGE['secret'],
                $this->config->USER_TICKET_STORAGE['bucket'],
            );
        } catch (Throwable $e) {}

        $this->handleAction();
    }

    public function fetch()
    {
        $this->jwtAuthValidate();

        $filters = $this->getFilters();
        $filtersWhere = $this->getFiltersWhere($filters);
        $orderBy = $this->getOrderBy();

        $currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $totalItems = $this->getTotals($filtersWhere);
        $pagesNum = (int)ceil($totalItems / self::PAGE_CAPACITY);

        $contracts = $this->contracts->get_contracts(['user_id' => $this->user->id]);
        $tickets = $this->getTickets($currentPage, $filtersWhere, $orderBy);

        $this->design->assignBulk(array_merge([
            'contracts' => $contracts,
            'tickets' => $tickets,
            'per_page' => self::PAGE_CAPACITY,
            'current_page' => $currentPage,
            'total_pages' => $pagesNum,
            'total_items' => $totalItems,
            'viewUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'sort' => $this->request->get('sort') ?? null,
            'user' => $this->user
        ], $filters));

        return $this->design->fetch('user_tickets/index.tpl');
    }

    private function handleAction(): void
    {
        $action = $this->request->get('action');
        if ($action && method_exists($this, $action)) {
            $this->jwtAuthValidate();

            $this->$action();
        }
    }

    private function getFilters(): array
    {
        return [
            'filter_usedesk_id' => $this->request->get('filter_usedesk_id') ?? null,
            'filter_subject' => $this->request->get('filter_subject') ?? null,
            'filter_status' => $this->request->get('filter_status') ?? null,
        ];
    }

    private function getFiltersWhere(array $filters): string
    {
        $where = '';
        foreach ($filters as $key => $value) {
            if ($value) {
                switch ($key) {
                    case 'filter_usedesk_id':
                        $where .= $this->filterUsedeskIdWhere($value);
                        break;
                    case 'filter_subject':
                        $where .= $this->filterSubjectWhere($value);
                        break;
                    case 'filter_status':
                        $where .= $this->filterStatusWhere($value);
                        break;
                    default:
                        break;
                }
            }
        }
        return $where;
    }

    private function filterUsedeskIdWhere(string $value): string
    {
        if (!empty($value)) {
            $idSearch = '%' . $this->db->escape($value) . '%';
            return " AND ut.usedesk_id LIKE '$idSearch'";
        }
        return '';
    }

    private function filterSubjectWhere(string $value): string
    {
        if (!empty($value)) {
            return " AND ut.subject = '$value'";
        }
        return '';
    }

    private function filterStatusWhere(string $value): string
    {
        if (!empty($value)) {
            return " AND ut.status = '$value'";
        }
        return '';
    }

    private function getOrderBy(): string
    {
        $orderBy = 'ut.created_at DESC';

        $sort = $this->request->get('sort') ?? null;
        if ($sort) {
            $pos = strrpos($sort, '_');
            if ($pos !== false) {
                $field = substr($sort, 0, $pos);
                $direction = substr($sort, $pos + 1);
                $direction = (strtoupper($direction) === 'ASC') ? 'ASC' : 'DESC';

                switch ($field) {
                    case 'usedesk_id':
                        $orderBy = "ut.usedesk_id $direction";
                        break;
                    case 'created_at':
                        $orderBy = "ut.created_at $direction";
                        break;
                    case 'updated_at':
                        $orderBy = "ut.updated_at $direction";
                        break;
                    case 'subject':
                        $orderBy = "ut.subject $direction";
                        break;
                    case 'status':
                        $orderBy = "ut.status $direction";
                        break;
                    default:
                        break;
                }
            }
        }

        return $orderBy;
    }

    private function getTickets(int $currentPage = 0, string $andWhere = '', string $orderBy = '')
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $this->db->query("
            SELECT 
                ut.id, 
                ut.created_at,
                ut.updated_at,
                ut.usedesk_id,
                ut.user_id,
                ut.subject,
                ut.status,
                (
                    SELECT COUNT(utc.id)
                    FROM s_user_ticket_comments utc
                    WHERE utc.ticket_id = ut.id AND utc.sender_type = 'operator' AND utc.is_read = false
                ) as unread_operator_count
            FROM s_user_tickets ut
            WHERE ut.user_id = ? " . $andWhere . "
            ORDER BY " . $orderBy . "
            LIMIT ? OFFSET ?",
            $this->user->id, self::PAGE_CAPACITY, $offset
        );

        return $this->db->results();
    }

    private function getTicket()
    {
        $ticketId = $this->request->get('ticketId', 'integer');

        $this->db->query("
            SELECT 
                ut.id, 
                ut.created_at,
                ut.updated_at,
                ut.usedesk_id,
                ut.user_id,
                ut.subject,
                ut.status
            FROM s_user_tickets ut
            WHERE ut.id = ? AND ut.user_id = ?",
            $ticketId, $this->user->id
        );

        $ticket = $this->db->result();

        $this->db->query("
            SELECT 
                utc.id,
                utc.message,
                utc.sender_type,
                utc.created_at,
                utc.attachments,
                u.firstname as user_firstname,
                u.lastname as user_lastname
            FROM s_user_ticket_comments utc
            LEFT JOIN s_users u ON utc.user_id = u.id
            WHERE utc.ticket_id = ?",
            $ticketId
        );

        $comments = (array)$this->db->results();
        foreach ($comments as $comment) {
            $comment->attachments = $this->prepareAttachments($comment->attachments);
        }

        $ticket->comments = $comments;

        $this->db->query(
            "UPDATE s_user_ticket_comments SET is_read = true WHERE ticket_id = ? AND sender_type = 'operator'",
            $ticketId
        );

        return $ticket;
    }

    private function getTotals(string $andWhere = ''): int
    {
        $this->db->query("
            SELECT 
                COUNT(ut.id) AS total
            FROM s_user_tickets ut
            WHERE ut.user_id = ? " . $andWhere,
            $this->user->id
        );

        return (int)$this->db->result('total') ?? 0;
    }

    private function createTicket(): void
    {
        $subject = $this->request->post('subject');
        $message = $this->request->post('message');

        if ($subject === '' || $message === '') {
            $this->design->assign('error', 'Тема и сообщение обязательны.');
            return;
        }

        $fullname = $this->request->post('fullname');
        $email = $this->request->post('email');
        $phone_mobile = $this->request->post('phone_mobile');

        $contractIds = $this->request->post('contracts');
        if (!is_array($contractIds)) {
            $contractIds = [$contractIds];
        }

        $userId = $this->user->id;

        $uploadedFiles = $this->extractUploadedFiles();

        if (isset($uploadedFiles['error'])) {
            echo json_encode(['error' => $uploadedFiles['error']]);
            exit;
        }

        $contractNumbers = [];
        foreach ($contractIds as $cid) {
            $contract = $this->contracts->get_contract($cid);
            if ($contract && !empty($contract->number)) {
                $contractNumbers[] = $contract->number;
            }
        }

        $usedeskTicketId = $this->createUsedeskTicket($subject, $message, $contractNumbers, $email, $fullname, $uploadedFiles);

        $this->db->query(
            "INSERT INTO s_user_tickets (user_id, subject, usedesk_id) VALUES (?, ?, ?)",
            $userId, $subject, $usedeskTicketId
        );
        $ticketId = (int)$this->db->insert_id();

        foreach ($contractIds as $cid) {
            $this->db->query(
                "INSERT INTO s_user_ticket_contracts (ticket_id, contract_id) VALUES (?, ?)",
                $ticketId, (int)$cid
            );
        }

        $attachments = $this->uploadAttachments($uploadedFiles, $ticketId);

        $this->db->query(
            "INSERT INTO s_user_ticket_comments (ticket_id, user_id, message, sender_type, attachments) VALUES (?, ?, ?, ?, ?)",
            $ticketId, $userId, $message, 'user', json_encode($attachments)
        );

        $this->sendTelegramMessage($usedeskTicketId, $subject, $message, $fullname);

        if ($this->user) {
            try {
                $this->userEmails->syncEmail($this->user, $email, UserEmails::SOURCE_USER_TICKET_EMAIL);
                $this->userPhones->syncPhone($this->user->id, $phone_mobile, UserPhones::SOURCE_TICKET_PHONE);
            } catch (Exception $e) {
                error_log("Sync email or phone failed: " . $e->getMessage());
            }
        }

        if ($this->isAjax()) {
            $this->db->query("SELECT * FROM s_user_tickets WHERE id = ?", $ticketId);
            $ticket = $this->db->result();
            $this->design->assign('ticket', $ticket);
            echo $this->design->fetch('user_tickets/row.tpl');
            exit;
        }

        header("Location: " . strtok($_SERVER['REQUEST_URI'], '?'));
        exit;
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    private function createComment(): void
    {
        $ticketId = $this->request->post('ticket_id', 'integer');
        $message = $this->request->post('reply_message');
        $userId = $this->user->id;

        if ($ticketId === 0 || $message === '') {
            exit;
        }

        $uploadedFiles = $this->extractUploadedFiles();

        if (isset($uploadedFiles['error'])) {
            echo json_encode(['error' => $uploadedFiles['error']]);
            exit;
        }

        $attachments = $this->uploadAttachments($uploadedFiles, $ticketId);

        $this->db->query("SELECT usedesk_id, status FROM s_user_tickets WHERE id = ? AND user_id = ?", $ticketId, $userId);
        $ticket = $this->db->result();
        $usedeskTicketId = (int)$ticket->usedesk_id;

        $usedeskCommentId = $this->createUsedeskTicketComment($usedeskTicketId, $message, $uploadedFiles);

        $this->db->query(
            "INSERT INTO 
                s_user_ticket_comments (ticket_id, usedesk_id, user_id, message, sender_type, attachments) 
            VALUES (?, ?, ?, ?, ?, ?)",
            $ticketId, $usedeskCommentId, $userId, $message, 'user', json_encode($attachments)
        );
        $newCommentId = (int)$this->db->insert_id();

        $this->db->query(
            "SELECT 
                utc.id, 
                utc.message, 
                utc.sender_type,
                utc.created_at,
                utc.attachments,
                u.firstname AS user_firstname,
                u.lastname AS user_lastname
             FROM s_user_ticket_comments utc
             LEFT JOIN s_users u ON utc.user_id = u.id
             WHERE utc.id = ?",
            $newCommentId
        );
        $comment = $this->db->result();

        $comment->attachments = $this->prepareAttachments($comment->attachments);

        $this->design->assign('comment', $comment);
        echo $this->design->fetch('user_tickets/comment.tpl');
        exit;
    }

    private function detail(): void
    {
        $ticketData = $this->getTicket();
        $this->design->assign('ticket', $ticketData);
        echo $this->design->fetch('user_tickets/detail.tpl');
        exit;
    }

    private function hasUnreadOperatorComments()
    {
        $this->db->query(
            "SELECT COUNT(*) as count FROM s_user_ticket_comments utc
             JOIN s_user_tickets ut ON ut.id = utc.ticket_id
             WHERE ut.user_id = ? AND utc.sender_type = 'operator' AND utc.is_read = false",
            $this->user->id
        );

        $count = $this->db->result('count');

        header('Content-Type: application/json');
        echo json_encode(['has_unread' => $count > 0]);
        exit;
    }

    public function getUnreadOperatorCommentsCount()
    {
        $ticketId = (int)$this->request->get('ticketId');
        $this->db->query("
            SELECT (
                SELECT COUNT(utc.id)
                FROM s_user_ticket_comments utc
                WHERE utc.ticket_id = ut.id AND utc.sender_type = 'operator' AND utc.is_read = false
            ) as unread_operator_count
            FROM s_user_tickets ut
            WHERE ut.id = ? AND ut.user_id = ?
            LIMIT 1",
            $ticketId, $this->user->id
        );

        $count = $this->db->result('unread_operator_count');
        header('Content-Type: application/json');
        echo json_encode(['unread_operator_count' => (int)$count]);
        exit;
    }

    private function createUsedeskTicket(
        string $subject,
        string $message,
        array  $contractNumbers,
        string $email,
        string $fullname,
        array  $uploadedFiles = []
    )
    {
        $usedeskUserId = $this->userUsedesk->getUsedeskUserId($this->user);
        $profileUrl = trim($this->config->back_url, '/') . "/client/" . $this->user->id . "\n";
        $contractNumbersStr = implode(', ', $contractNumbers);
        $usedeskSubjectId = UsedeskTicketSubjects::getUsedeskIdBySubject($subject);

        $fullMessage = sprintf(
            "Клиент: <a href='%s'>%s</a><br>" .
            "Email: %s<br>" .
            "Телефон: %s<br>" .
            "Дата рождения: %s<br>" .
            "Номер договора: %s<br><br>" .
            "Описание:<br>%s",
            $profileUrl,
            $fullname,
            $email,
            $this->user->phone_mobile,
            $this->user->birth,
            $contractNumbersStr,
            $message,
        );

        try {
            $data = [
                'subject' => $subject,
                'message' => $fullMessage,
                'client_id' => $usedeskUserId,
                'priority' => 'medium',
                'channel_id' => self::USEDESK_CHANNEL_ID,
                'type' => 'question',
                'from' => 'client',
                'field_id' => '26142;22066',
                'field_value' => $contractNumbersStr . ';' . $usedeskSubjectId,
            ];
            $response = $this->usedeskService->createTicket($this->usedeskApiToken, $data, $uploadedFiles);
        } catch (Exception $e) {
            error_log('Ошибка Usedesk: ' . $e->getMessage());
            return false;
        }

        if (!isset($response['ticket_id'])) {
            $this->design->assign('error', 'Не удалось создать тикет');
            return false;
        }

        return (int)$response['ticket_id'] ?? false;
    }

    private function createUsedeskTicketComment(int $usedeskTicketId, string $message, array $uploadedFiles = [])
    {
        try {
            $response = $this->usedeskService->createComment($this->usedeskApiToken, $usedeskTicketId, $message, [], $uploadedFiles);
        } catch (Exception $e) {
            error_log('Usedesk create comment error: ' . $e->getMessage());
        }

        return (int)$response['comment_id'] ?? false;
    }

    private function sendTelegramMessage(
        int    $usedeskTicketId,
        string $subject,
        string $message,
        string $fullname
    ): void
    {
        $telegramService = new TelegramApi([
            'token' => self::TELEGRAM_BOT_TOKEN,
            'chat_id' => self::TELEGRAM_CHAT_ID,
            'message_thread_id' => self::MESSAGE_THREAD_ID
        ]);
        $telegramMessage = $this->prepareTelegramMessage($usedeskTicketId, $subject, $message, $fullname);
        $telegramService->sendMessage($telegramMessage);
    }

    private function prepareTelegramMessage(
        int    $usedeskTicketId,
        string $subject,
        string $message,
        string $fullname
    ): string
    {
        $profileUrl = trim($this->config->back_url, '/') . "/client/" . $this->user->id . "\n";
        $ticketUrl = "https://secure.usedesk.ru/tickets/" . $usedeskTicketId;

        return sprintf(
            "Создан новый тикет:\n\n" .
            "Клиент: <a href='%s'>%s</a>\n" .
            "Номер: <a href='%s'>%s</a>\n" .
            "Тема: %s \n" .
            "Описание: %s",
            $profileUrl,
            $fullname,
            $ticketUrl,
            $usedeskTicketId,
            $subject,
            $message,
        );
    }

    private function extractUploadedFiles(string $inputName = 'attachments'): array
    {
        $result = [];

        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'zip', 'rar', 'txt'];
        $maxFileSize = 100 * 1024 * 1024; // 100 МБ

        if (!empty($_FILES[$inputName]['tmp_name'][0])) {
            foreach ($_FILES[$inputName]['tmp_name'] as $i => $tmpName) {
                if ($_FILES[$inputName]['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = $_FILES[$inputName]['name'][$i];
                    $fileSize = $_FILES[$inputName]['size'][$i];

                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    if (!in_array($fileExtension, $allowedExtensions)) {
                        $errorMsg = "Недопустимый формат файла: {$fileName}. Разрешены только: " . implode(', ', $allowedExtensions);
                        $this->design->assign('error', $errorMsg);
                        return ['error' => $errorMsg];
                    }

                    if ($fileSize > $maxFileSize) {
                        $errorMsg = "Файл слишком большой (>100 МБ): {$fileName}";
                        $this->design->assign('error', $errorMsg);
                        return ['error' => $errorMsg];
                    }

                    $result[] = [
                        'name' => $fileName,
                        'type' => $_FILES[$inputName]['type'][$i],
                        'tmp_name' => $tmpName,
                        'error' => $_FILES[$inputName]['error'][$i],
                        'size' => $fileSize,
                    ];
                }
            }
        }

        return $result;
    }

    private function uploadAttachments(array $files, int $ticketId): array
    {
        $attachments = [];

        foreach ($files as $file) {
            if (!isset($file['tmp_name'])) {
                continue;
            }

            $key = "tickets/$ticketId/" . uniqid() . '_' . basename($file['name']);
            $result = $this->fileStorageService->putFile($file['tmp_name'], $key);

            if ($result->hasKey('ObjectURL') || $result->hasKey('ETag')) {
                $attachments[] = $key;
            }
        }

        return $attachments;
    }

    private function prepareAttachments($attachments): array
    {
        $result = [];
        if (!empty($attachments) && is_string($attachments)) {
            $keys = json_decode($attachments, true);
            if (is_array($keys)) {
                foreach ($keys as $key) {
                    $result[] = [
                        'url' => $this->fileStorageService->getPublicUrl($key),
                        'name' => basename($key)
                    ];
                }
            }
        }
        return $result;
    }
}
