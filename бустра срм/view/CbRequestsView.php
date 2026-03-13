<?php

require_once 'View.php';

class CbRequestsView extends View
{
    public function fetch()
    {
        if (!in_array('cb_requests', $this->manager->permissions ?? [], true)) {
            if ($this->request->method('post')) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            }

            return $this->design->fetch('403.tpl');
        }

        $action = $this->request->method('post')
            ? $this->request->post('action', 'string')
            : $this->request->get('action', 'string');

        if ($this->request->method('post')) {
            switch ($action) {
                case 'update':
                    $this->updateRequest();
                    break;

                case 'update_status':
                    $this->updateStatus();
                    break;

                case 'add_comment':
                    $this->addComment();
                    break;

                case 'change_subject':
                    $this->changeSubject();
                    break;

                case 'save_subject':
                    $this->saveSubject();
                    break;

                case 'search_client':
                    $this->searchClient();
                    break;

                case 'search_order':
                    $this->searchOrder();
                    break;

                case 'update_field':
                    $this->updateField();
                    break;

                case 'get_client_phones':
                    $this->getClientPhones();
                    break;

                case 'get_history':
                    $this->getHistory();
                    break;
            }
        } else {
            switch ($action) {
                case 'show':
                    return $this->showRequestView();

                case 'subjects':
                    return $this->subjectsView();

                default:
                    return $this->listRequestsView();
            }
        }
    }

    /**
     * Листинг запросов ЦБ
     *
     * @return string
     */
    private function listRequestsView(): string
    {
        $itemsPerPage = 100;
        $currentPage = max(1, (int) $this->request->get('page', 'integer'));

        $filter = $this->cbRequests->prepareFilters($this->request->get('search'));
        $filter['sort'] = $this->cbRequests->prepareSort($this->request->get('sort'));
        $filter['limit'] = $itemsPerPage;
        $filter['page'] = $currentPage;

        $requests = $this->cbRequests->getAllRequests($filter);
        $requestsCount = $requests['total_count'];
        $totalPages = (int) ceil($requestsCount / max(1, $itemsPerPage));

        $subjects = $this->cbRequests->getSubjects();
        $legalEntities = $this->cbRequests->getLegalEntities();

        // Подписываем S3-ссылки файлов и конвертируем в массивы для Smarty
        $cbS3 = null;
        $s3Available = true;
        $items = [];
        foreach ($requests['data'] as $item) {
            $row = (array) $item;
            $files = $this->resolveFileLinks($item->file_links, $cbS3, $s3Available);
            $row['files'] = $files;
            $items[] = $row;
        }

        $this->design->assign_array([
            'sort' => $this->request->get('sort') ?? 'received_at',
            'filters' => $filter['search'] ?? [],
            'current_page_num' => $currentPage,
            'total_pages_num' => $totalPages,
            'total_items' => $requestsCount,
            'items' => $items,
            'subjects' => $subjects,
            'legal_entities' => $legalEntities,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
        ]);

        return $this->design->fetch('cb_requests/list.tpl');
    }

    /**
     * Карточка запроса ЦБ
     *
     * @return string
     */
    private function showRequestView(): string
    {
        $requestId = $this->request->get('id', 'integer');

        $requestData = $this->cbRequests->getRequestById($requestId);

        if (!$requestData) {
            return $this->design->fetch('404.tpl');
        }

        // Комментарии по секциям
        $commentsDescription = $this->cbRequests->getComments($requestId, 'description');
        $commentsOpr = $this->cbRequests->getComments($requestId, 'opr');
        $commentsOkk = $this->cbRequests->getComments($requestId, 'okk');
        $commentsMeasures = $this->cbRequests->getComments($requestId, 'measures');
        $commentsLawyers = $this->cbRequests->getComments($requestId, 'lawyers');

        $history = $this->cbRequests->getHistory($requestId);
        $subjects = $this->cbRequests->getSubjects();

        // Декодируем JSON file_links в массив
        $cbS3 = null;
        $s3Available = true;
        $requestFiles = $this->resolveFileLinks($requestData->file_links, $cbS3, $s3Available);

        // Дополнительные телефоны клиента
        $clientPhones = [];
        if (!empty($requestData->client_id)) {
            $clientPhones = $this->phones->get_phones($requestData->client_id) ?: [];
        }

        $this->design->assign_array([
            'request' => $requestData,
            'request_files' => $requestFiles,
            'client_phones' => $clientPhones,
            'comments_description' => $commentsDescription,
            'comments_opr' => $commentsOpr,
            'comments_okk' => $commentsOkk,
            'comments_measures' => $commentsMeasures,
            'comments_lawyers' => $commentsLawyers,
            'request_history' => $history,
            'subjects' => $subjects,
        ]);

        return $this->design->fetch('cb_requests/show.tpl');
    }

    /**
     * Управление темами запросов
     *
     * @return string
     */
    private function subjectsView(): string
    {
        $subjects = $this->cbRequests->getSubjects(false);

        $this->design->assign('subjects', $subjects);

        return $this->design->fetch('cb_requests/subjects.tpl');
    }

    /**
     * POST: Обновление запроса (AJAX)
     */
    private function updateRequest(): void
    {
        $id = $this->request->post('id', 'integer');

        $possibleFields = [
            'client_id', 'client_fio', 'client_birth_date', 'client_email', 'client_phone',
            'order_id', 'order_number',
            'subject_id', 'response_deadline', 'request_after_opr', 'opr_contacted_client',
        ];

        $data = [];
        foreach ($possibleFields as $field) {
            $val = $this->request->post($field, 'string');
            if ($val !== null && $val !== '') {
                $data[$field] = $val;
            }
        }

        if (!empty($data)) {
            $this->cbRequests->updateRequest($id, $data);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * POST: Обновление статуса (AJAX)
     */
    private function updateStatus(): void
    {
        $requestId = $this->request->post('request_id', 'integer');
        $statusField = $this->request->post('status_field', 'string');
        $value = $this->request->post('value', 'integer');
        $managerId = isset($_SESSION['manager_id']) ? (int) $_SESSION['manager_id'] : null;

        $result = $this->cbRequests->updateStatus($requestId, $statusField, $value, $managerId);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * POST: Добавление комментария (AJAX)
     */
    private function addComment(): void
    {
        $requestId = $this->request->post('request_id', 'integer');
        $section = $this->request->post('section', 'string');
        $text = $this->request->post('text', 'string');
        $managerId = isset($_SESSION['manager_id']) ? (int) $_SESSION['manager_id'] : 0;

        $result = $this->cbRequests->addComment($requestId, $managerId, $section, $text);

        // Получаем имя менеджера для динамической вставки
        $managerName = '';
        if ($managerId) {
            $manager = $this->managers->get_manager($managerId);
            if ($manager) {
                $managerName = $manager->name;
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => $result,
            'comment' => [
                'manager_name' => $managerName,
                'text' => $text,
                'created_at' => date('d.m.Y H:i'),
            ],
        ]);
        exit;
    }

    /**
     * POST: Смена темы (AJAX)
     */
    private function changeSubject(): void
    {
        $requestId = $this->request->post('request_id', 'integer');
        $subjectId = $this->request->post('subject_id', 'integer');
        $managerId = isset($_SESSION['manager_id']) ? (int) $_SESSION['manager_id'] : null;

        $currentRequest = $this->cbRequests->getRequestById($requestId);
        $oldSubjectId = $currentRequest ? (int) $currentRequest->subject_id : 0;
        $oldSubjectName = $currentRequest && !empty($currentRequest->subject_name)
            ? $currentRequest->subject_name
            : 'Не указана';

        $result = $this->cbRequests->updateRequest($requestId, ['subject_id' => $subjectId]);

        if ($result && $oldSubjectId !== (int) $subjectId) {
            $subjects = $this->cbRequests->getSubjects(false);
            $newSubjectName = 'Не указана';
            foreach ($subjects as $subject) {
                if ((int) $subject->id === (int) $subjectId) {
                    $newSubjectName = $subject->name;
                    break;
                }
            }

            $this->cbRequests->logHistory(
                $requestId,
                $managerId,
                'subject_change',
                'Тема изменена: ' . $oldSubjectName . ' → ' . $newSubjectName
            );
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
        exit;
    }

    /**
     * POST: Сохранение/обновление темы
     */
    private function saveSubject(): void
    {
        $id = $this->request->post('id', 'integer');
        $name = $this->request->post('name', 'string');

        if ($id) {
            $data = [
                'name' => $name,
                'is_active' => $this->request->post('is_active', 'integer'),
            ];
            $this->cbRequests->updateSubject($id, $data);
        } else {
            $this->cbRequests->createSubject($name);
        }

        header('Location: /cb-requests/subjects');
        exit;
    }

    /**
     * POST: Поиск клиента по ФИО + дата рождения (AJAX)
     */
    private function searchClient(): void
    {
        $fio = $this->request->post('fio', 'string');
        $birthDate = $this->request->post('birth_date', 'string');
        $email = trim((string) $this->request->post('email'));

        $results = $this->cbRequests->searchClient($fio, $birthDate ?: null, $email ?: null);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => !empty($results),
            'users' => $results,
        ]);
        exit;
    }

    /**
     * POST: Поиск договора по номеру контракта (AJAX)
     */
    private function searchOrder(): void
    {
        $requestId = $this->request->post('request_id', 'integer');
        $contractNumber = $this->request->post('contract_number', 'string');
        $clientId = null;

        if ($requestId) {
            $requestData = $this->cbRequests->getRequestById($requestId);
            if ($requestData && !empty($requestData->client_id)) {
                $clientId = (int) $requestData->client_id;
            }
        }

        $contractNumber = trim((string) $contractNumber);
        if ($contractNumber === '' && $clientId) {
            $orders = $this->cbRequests->getOrdersByClientId($clientId);

            header('Content-Type: application/json');
            echo json_encode([
                'success' => !empty($orders),
                'orders' => $orders,
            ]);
            exit;
        }

        $order = $this->cbRequests->searchOrderByContractNumber($contractNumber, $clientId);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => !empty($order),
            'order' => $order,
        ]);
        exit;
    }

    /**
     * POST: Обновление одного поля запроса (AJAX)
     */
    private function updateField(): void
    {
        $id = $this->request->post('id', 'integer');
        $field = $this->request->post('field', 'string');
        $value = $this->request->post('value', 'string');

        $allowedFields = [
            'client_fio', 'client_birth_date', 'client_email', 'client_phone',
            'order_number', 'order_id', 'client_id',
            'response_deadline', 'opr_contacted_client',
        ];

        if (!in_array($field, $allowedFields, true)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Field not allowed']);
            exit;
        }

        $this->cbRequests->updateRequest($id, [$field => $value]);

        $managerId = isset($_SESSION['manager_id']) ? (int) $_SESSION['manager_id'] : null;

        if ($field === 'client_id' && $value) {
            $user = $this->users->get_user((int) $value);
            $clientName = $user
                ? trim($user->lastname . ' ' . $user->firstname . ' ' . $user->patronymic)
                : 'ID ' . $value;
            $this->cbRequests->logHistory($id, $managerId, 'field_update', 'Привязан клиент: ' . $clientName);
        }

        if ($field === 'order_id' && $value) {
            $details = 'Привязан займ ID ' . $value;
            $orderNumber = $this->request->post('order_number', 'string');
            if ($orderNumber) {
                $details = 'Привязан займ: ' . $orderNumber;
            }
            $this->cbRequests->logHistory($id, $managerId, 'field_update', $details);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * POST: Получение телефонов клиента (основной + дополнительные)
     */
    private function getClientPhones(): void
    {
        $userId = $this->request->post('user_id', 'integer');

        $mainPhone = '';
        $user = $this->users->get_user($userId);
        if ($user) {
            $mainPhone = $user->phone_mobile ?: '';
        }

        $additionalPhones = $this->phones->get_phones($userId) ?: [];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'main_phone' => $mainPhone,
            'phones' => $additionalPhones,
        ]);
        exit;
    }

    /**
     * POST: Получение истории запроса (AJAX)
     */
    private function getHistory(): void
    {
        $requestId = $this->request->post('request_id', 'integer');
        $history = $this->cbRequests->getHistory($requestId);
        $items = [];

        foreach ($history as $event) {
            $items[] = [
                'action' => (string) ($event->action ?? ''),
                'details' => (string) ($event->details ?? ''),
                'manager_name' => (string) ($event->manager_name ?? ''),
                'created_at' => (string) ($event->created_at ?? ''),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'history' => $items,
        ]);
        exit;
    }

    /**
     * Декодирует file_links и подписывает S3-ссылки.
     * Формат: [{"key": "s3-key", "name": "filename.pdf"}, ...]
     *
     * @param string|null $fileLinksJson
     * @param S3ApiClient|null &$cbS3
     * @param bool &$s3Available
     * @return array [['url' => '...', 'name' => '...'], ...]
     */
    private function resolveFileLinks($fileLinksJson, &$cbS3, &$s3Available): array
    {
        if (empty($fileLinksJson)) {
            return [];
        }

        $decoded = json_decode($fileLinksJson, true);
        if (is_string($decoded)) {
            $decodedNested = json_decode($decoded, true);
            if (is_array($decodedNested)) {
                $decoded = $decodedNested;
            }
        }

        if (!is_array($decoded) || count($decoded) === 0) {
            if (is_string($fileLinksJson) && filter_var($fileLinksJson, FILTER_VALIDATE_URL)) {
                $path = parse_url($fileLinksJson, PHP_URL_PATH) ?: '';
                return [[
                    'url' => $fileLinksJson,
                    'name' => urldecode(basename($path) ?: 'file'),
                ]];
            }
            return [];
        }

        if ($cbS3 === null && $s3Available) {
            try {
                $cbS3 = new S3ApiClient('', 'cb_request_s3');
            } catch (\Exception $e) {
                $s3Available = false;
            }
        }

        $files = [];
        foreach ($decoded as $entry) {
            if (is_array($entry)) {
                $s3Key = $entry['key'] ?? '';
                $name = $entry['name'] ?? basename($s3Key);
            } else {
                $s3Key = (string) $entry;
                $name = basename($s3Key);
            }

            if (empty($s3Key)) {
                continue;
            }

            $url = '';
            if ($cbS3) {
                try {
                    $url = $cbS3->getPublicUrl($s3Key, '+3 hour', $name);
                } catch (\Exception $e) {}
            }

            $files[] = [
                'url' => $url,
                'name' => urldecode($name),
            ];
        }

        return $files;
    }
}
