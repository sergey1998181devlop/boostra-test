<?php

use App\Containers\DomainSection\Tickets\Tables\TsTicketTable;
use Carbon\Carbon;

require_once 'View.php';
require_once 'Exports/TicketStatsExport.php';
require_once 'Exports/TicketsExport.php';

class TicketsView extends View {

    /** @const string Название темы тикета для ТП */
    private const TS_SUBJECT_NAME = 'Подача тикета';
    private const TS_DIRECTION_CODE = 'technical_support';

    /**
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Writer_Exception
     * @throws Exception
     */
    public function fetch()
    {
        $action = $this->request->method('post')
            ? $this->request->post('action', 'string')
            : $this->request->get('action', 'string');

        if ($this->request->method('post')) {
            switch ($action) {
                case 'save':
                    $this->createTicket();
                    break;

                case 'update_status':
                    $this->updateTicketStatus();
                    break;

                case 'add_comment':
                    $this->addComment();
                    break;

                case 'change_manager':
                    $this->changeManager();
                    break;

                case 'change_subject':
                    $this->changeSubject();
                    break;

                case 'disputed_complaint':
                    $this->handleDisputedComplaint();
                    break;

                case 'return_disputed_to_new':
                    $this->returnDisputedToNew();
                    break;

                case 'change_priority':
                    $this->changePriority();
                    break;

                case 'save_agreement':
                    $this->saveAgreement();
                    break;

                case 'reschedule_agreement':
                    $this->rescheduleAgreement();
                    break;
                    
                case 'highlight_ticket':
                    $this->highlightTicket();
                    break;
            }
        } else {
            switch ($action) {
                case 'create':
                    return $this->createTicketView();

                case 'create_techincal_support':
                    return $this->createTicketView('technical_support');

                case 'show':
                    return $this->showTicketView();

                case 'get_managers':
                    return $this->getManagers();

                case 'get_subjects':
                    return $this->searchSubjects();

                case 'technical_support':
                    return $this->listTicketsView('support');

                case 'download':
                    return $this->exportTicketsToExcel();

                default:
                    return $this->listTicketsView();
            }
        }
    }

    /**
     * Список тикетов
     *
     * @param string $ticketType
     * @return string
     */
    private function listTicketsView(string $ticketType = ''): string
    {
        $itemsPerPage = 100;
        $currentPage = max(1, (int) $this->request->get('page', 'integer'));

        $highlightTicketId = $this->request->get('highlight_ticket_id', 'integer');

        $filter = $this->tickets->prepareFilters($this->request->get('search'));
        $filter['sort'] = $this->tickets->prepareSort($this->request->get('sort'));
        $filter['limit'] = $itemsPerPage;
        $filter['page'] = $currentPage;
        $filter['exclude'] = ['subject_id' => [$this->getSubjectId('Подача тикета')]];

        $tickets = $this->tickets->getAllTickets($filter);
        $ticketsCount = $tickets['total_count'];
        $totalPages = (int) ceil($ticketsCount / $itemsPerPage);

        $subjects = $this->tickets->getMainAndChildSubjects();

        // Получаем всех менеджеров для фильтров
        $managers = $this->managers->get_managers();
        $manager_data = null;
        $initiator_data = null;
        
        // Получаем данные текущего выбранного менеджера, если есть
        if (!empty($filter['search']['manager_id'])) {
            $manager_data = $this->managers->get_manager($filter['search']['manager_id']);
        }
        
        // Получаем данные текущего выбранного инициатора, если есть
        if (!empty($filter['search']['initiator_id'])) {
            $initiator_data = $this->managers->get_manager($filter['search']['initiator_id']);
        }

        $this->design->assign_array([
            'sort' => $this->request->get('sort') ?? 'date',
            'filters' => $filter['search'] ?? [],
            'current_page_num' => $currentPage,
            'total_pages_num' => $totalPages,
            'total_items' => $ticketsCount,
            'items' => $tickets['data'],
            'subjects' => $subjects,
            'companies' => $this->tickets->getCompanies(),
            'channels' => $this->tickets->getChannels(),
            'priorities' => $this->tickets->getPriorities(),
            'statuses' => $this->tickets->getStatuses(),
            'responsible_persons' => $this->tickets->getUniqueResponsiblePersonNames(),
            'responsible_groups' => $this->tickets->getUniqueGroups(),
            'managers' => $managers,
            'manager_data' => $manager_data,
            'initiators' => $managers,
            'initiator_data' => $initiator_data,
            'highlight_ticket_id' => $highlightTicketId,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
        ]);

        return $this->design->fetch('contact_center/tickets.tpl');
    }

    /***
     * Добавить тикет
     *
     * @return void
     * @throws Exception
     */
    private function createTicket(): void
    {
        // Подготовка исходных данных
        $rawData = $this->request->post();
        parse_str($rawData, $dataPost);

        $userData = [];
        $workloadExclusionSucceeded = false;
        $subjectId = (int)$dataPost['subject'];
        $responsiblePerson = $this->tickets->getResponsiblePersonForSubject($subjectId);

        // Обработка заказа, если он указан
        if (!empty($dataPost['order_id'])) {
            $this->tickets->processOrderData($dataPost, $userData, $workloadExclusionSucceeded, $responsiblePerson);
        }

        // Обработка данных клиента
        if (empty($dataPost['client_id'])) {
            $userData = [
                'fio'   => $dataPost['client_fio']  ?? '',
                'phone' => formatPhoneNumber($dataPost['clientPhone'] ?? '') ?? '',
                'birth' => $dataPost['clientBirth'] ?? '',
                'email' => $dataPost['clientEmail'] ?? '',
            ];
        }

        if (!empty($dataPost['source'])) {
            $userData['source'] = $dataPost['source'];
        }

        $createdAt = Carbon::now()->format('Y-m-d H:i:s');
        $responsiblePersonId = null;

        // Получение данных пользователя и ответственного лица
        $user = null;
        if (!empty($dataPost['client_id']) && !empty($dataPost['order_id'])) {
            $user = $this->users->get_user($dataPost['client_id']);
            
            $responsiblePersonId = $this->tickets->getResponsiblePersonId($user->id, $dataPost['order_id']);
        }

        // Подготовка данных для сохранения
        $dataToSave = [
            'client_id' => $dataPost['client_id'] ?: null,
            'subject_id' => $dataPost['subject'],
            'status_id' => 1,
            'manager_id' => $dataPost['manager_id'],
            'initiator_id' => $_SESSION['manager_id'],
            'description' => $dataPost['description'],
            'company_id' => $dataPost['company'],
            'priority_id' => $dataPost['priority_id'],
            'order_id' => $dataPost['order_id'],
            'client_status' => $dataPost['client_status'],
            'is_repeat' => isset($dataPost['is_repeat']) && $dataPost['is_repeat'] === 'on',
            'data' => $userData ? json_encode($userData) : null,
            'created_at' => $createdAt,
            'responsible_person_id' => $responsiblePersonId,
        ];

        if (intval($dataPost['subject']) === $this->getSubjectId(self::TS_SUBJECT_NAME)) {
            $dataToSave['direction_id'] = $this->tickets->getDirectionIdByCode(self::TS_DIRECTION_CODE);;
            $dataToSave['chanel_id'] = null;
        } else {
            $dataToSave['chanel_id'] = $dataPost['chanel'];
            $dataToSave['direction_id'] = null;
        }

        $itemsToSave = [$dataToSave];

        //Если нужно создать дополнительный тикет
        if (($dataPost['is_add_ticket_subject'] ?? null) &&
            !empty($dataPost['additional_subject_id']) &&
            $dataPost['additional_subject_id'] != $dataPost['subject'])
        {
            $additionalData = $dataToSave;
            $additionalData['subject_id'] = $dataPost['additional_subject_id'];
            $itemsToSave[] = $additionalData;
        }

        $createdTicketsId = [];
        foreach($itemsToSave as $dataToSave){
            // Создание тикета
            $ticketId = $this->tickets->createNewTicket($dataToSave);
            // Инициализация тикета ТП
            TsTicketTable::getInstance()->initTsTicket($ticketId);

            // Отключение дополнительных услуг, если необходимо
            $this->tickets->handleAdditionalServices($dataToSave);

            // Логирование снятия с нагрузки
            if (isset($responsiblePerson) && !empty($order->order_uid ?? null)) {
                $this->tickets->logTicketHistory(
                    $ticketId,
                    'workload_exclusion',
                    '',
                    $responsiblePerson->code,
                    $_SESSION['manager_id'],
                    $workloadExclusionSucceeded
                        ? 'Займ успешно снят с нагрузки и перемещен в папку: ' . $responsiblePerson->code
                        : 'Ошибка при снятии займа с нагрузки'
                );
            }

            // Отправка SMS
            $phone = !empty($user)
                ? $user->phone_mobile
                : ($userData['phone'] ?? null);

            // Не отправлять SMS, если компания с id 10
            if ($phone && (int)$dataPost['company'] !== 10) {
                $this->tickets->sendTicketSmsNotification(
                    $phone,
                    $dataPost['client_id'] ?: null,
                    $dataPost['order_id'] ?: null,
                    $subjectId
                );
            }
            $createdTicketsId[] = $ticketId;
        }

        $this->response->json_output([
            'status' => true,
            'id' => $createdTicketsId,
            'subject' => $dataPost["subject"],
            'created_at' => $createdAt
        ]);
    }

    /**
     * Страница создания тикета
     *
     */
    private function createTicketView(string $ticketType = 'call_center'): string
    {
        $clientId = $this->request->get('client_id', 'integer');
        $orderId = $this->request->get('order_id', 'integer');

        if ($clientId) {
            $client = $this->users->get_user($clientId);
            $clientTickets = $this->tickets->getClientTickets($clientId);

            if ($orderId) {
                $this->design->assign('client_order', $this->orders->get_order($orderId));
            }

            $this->design->assign('client_info', $client);
            $this->design->assign('client_tickets', $clientTickets);
        }

        switch ($ticketType) {
            case 'call_center':
                $channels = $this->tickets->getChannels();
                $priorities = $this->tickets->getPriorities();
                break;
            case 'technical_support':
                $channels = $this->tickets->getDirections();
                $priorities = $this->tickets->getTsPriorities();
                break;
            default:
                $channels = [];
                $priorities = [];
        }

        $this->design->assign_array([
            'subjects' => $this->tickets->getSubjectsGroupedByParent(),
            'companies' => $this->tickets->getCompaniesForTickets(),
            'priorities' => $priorities,
            'channels' => $channels,
            'manager_data' => $this->managers->get_manager($_SESSION['manager_id']),
        ]);

        switch ($ticketType) {
            case 'call_center':
                return $this->design->fetch('contact_center/create_ticket.tpl');
            case 'technical_support':
                return $this->design->fetch('technical_support/tickets/create.tpl');
            default:
                throw new \InvalidArgumentException('ticket type "' . $ticketType . '" not supported');
        }
    }

    /**
     * Страница тикета
     *
     * @return string
     * @throws Exception
     */
    private function showTicketView(): string
    {
        $ticketID = $this->request->get('id', 'integer');
        $callHistory = [];

        $ticketData = $this->tickets->getTicketById($ticketID);

        if (!$ticketData) {
            return $this->design->fetch('404.tpl');
        }

        // Если статус тикета "Новая", принимаем тикет и обновляем данные
        if ($ticketData->status_id == $this->tickets::NEW) {
            $this->tickets->accept($ticketID, $_SESSION['manager_id']);
            $ticketData = $this->tickets->getTicketById($ticketID);
        }

        $userInfo = $this->tickets->getUserInfo($ticketData);
        $attachedOrder = $this->tickets->getAttachedOrder($ticketData, $userInfo);

        if ($ticketData->client_id && $ticketData->chanel_id == 1) {
            $callHistory = $this->comments->get_comments([
                'user_id' => $ticketData->client_id,
                'block' => 'incomingCall'
            ]);
        }

        $commentsFromOrder = [];
        if ($ticketData->order_id) {
            $commentsFromOrder = $this->comments->get_comments([
                'order_id' => $ticketData->order_id,
                'exclude_block' => ['incomingCall']
            ]);
        }

        $ticketComments = [];

        if ($ticketData->is_duplicate) {
            $mainTicket = $this->tickets->getTicketById($ticketData->main_ticket_id);
            $this->design->assign('main_ticket', $mainTicket);

            $ticketComments = $this->tickets->getCommentsTicket($ticketID);
        } elseif (!empty($ticketData->data['agreement_copy']) && !empty($ticketData->data['source_ticket_id'])) {
            $ticketComments = $this->tickets->getCommentsTicket($ticketID);
        } else {
            $agreementCopies = $this->tickets->getAgreementCopies($ticketID);
            
            if ($ticketData->duplicates_count > 0) {
                $duplicateTickets = $this->tickets->getDuplicateTickets($ticketID);
                $this->design->assign('duplicate_tickets', $duplicateTickets);

                $ticketComments = $this->tickets->getRelatedTicketsComments($ticketID);
            } elseif (!empty($agreementCopies)) {
                $agreementCopiesDetails = $this->tickets->getAgreementCopiesDetails($ticketID);
                $this->design->assign('agreement_copies', $agreementCopiesDetails);
                $this->design->assign('agreement_copies_count', count($agreementCopiesDetails));
                
                $ticketIds = array_merge([$ticketID], array_column($agreementCopies, 'id'));
                $ticketComments = $this->tickets->getCommentsFromMultipleTickets($ticketIds);
            } else {
                $ticketComments = $this->tickets->getCommentsTicket($ticketID);
            }
        }

        $managersList = $this->managers->get_managers();
        $managers = [];
        foreach ($managersList as $manager) {
            $managers[$manager->id] = $manager->name;
        }

        $statusesList = $this->tickets->getStatuses();
        $statuses = [];
        foreach ($statusesList as $status) {
            $statuses[$status->id] = $status->name;
        }

        $subjectsList = $this->tickets->getSubjects();
        $subjects = [];
        foreach ($subjectsList as $subject) {
            $subjects[$subject->id] = $subject->name;
        }

        $prioritiesList = $this->tickets->getPriorities();
        $prioritiesMap = [];
        foreach ($prioritiesList as $priority) {
            $prioritiesMap[$priority->id] = $priority->name;
        }

        $this->design->assign_array([
            'comments' => $ticketComments,
            'comments_from_order' => $commentsFromOrder,
            'companies' => $this->tickets->getCompanies(),
            'managers' => $managers,
            'statuses' => $statuses,
            'subjects' => $subjects,
            'ticket' => $ticketData,
            'ticket_history' => $this->tickets->getTicketHistory($ticketID),
            'working_time' => $this->tickets->calculateWorkingTime($ticketID),
            'order' => $attachedOrder,
            'manager_data' => $this->managers->get_manager($ticketData->manager_id),
            'initiator' => $this->managers->get_manager($ticketData->initiator_id),
            'priorities' => $prioritiesList,
            'priorities_map' => $prioritiesMap,
            'client_info' => $userInfo,
            'call_history' => $callHistory
        ]);

        return $this->design->fetch('contact_center/ticket.tpl');
    }

    /**
     * Обновить статус тикета
     *
     * @return void
     * @throws Exception
     */
    private function updateTicketStatus()
    {
        $ticketId = $this->request->post('ticket_id', 'integer');
        $statusId = $this->request->post('status_id', 'integer');
        $feedbackReceived = $this->request->post('feedback_received', 'integer');
        $notifyUser = $this->request->post('notify_user', 'integer');

        if ($ticketId && $statusId) {
            // Отправляем пуш-уведомления клиенту, если не смогли с ним связаться и его тикет переводится в статус Ожидание
            if ($statusId === $this->tickets::ON_HOLD && $notifyUser) {
                $this->sendTicketNotification($ticketId);
            }

            // Отправляем пуш-уведомление при урегулировании тикета
            if ($statusId === $this->tickets::RESOLVED && $notifyUser) {
                $this->sendTicketNotification($ticketId, 'resolved');
            }

            $response = $this->tickets->updateTicketStatus($ticketId, $statusId, $feedbackReceived);

            $this->response->json_output($response);
        }

        $this->response->json_output(['success' => false, 'message' => 'Не удалось обновить статус тикета']);
    }

    /**
     * Отправить уведомление в ЛК клиента на сайт и пуш в мобильное приложение
     *
     * @param int $ticketId ID тикета
     * @param string $type Тип уведомления ('pause' или 'resolved')
     * @return void
     */
    private function sendTicketNotification(int $ticketId, string $type = 'pause'): void
    {
        // Сначала отправляем уведомление в ЛК клиента на сайте
        $this->tickets->markTicketForNotification($ticketId);

        // Далее отправляем пуш в мобильное приложение
        $ticket = $this->tickets->getTicketById($ticketId);
        $currentManagerId = $this->getManagerId();

        // Проверяем, есть ли client_id у тикета
        if (!$ticket->client_id) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Уведомление не может быть отправлено: клиент не зарегистрирован в системе'
            ]);
        }

        $pushResponse = $this->tickets->sendTicketPushNotification($ticket->client_id, $type, $ticketId);

        if (!$pushResponse) {
            $this->response->json_output(['success' => false, 'message' => 'Ошибка отправки мобильного пуш-уведомления']);
        }

        // Выбираем константы в зависимости от типа
        if ($type === 'resolved') {
            $successComment = $this->tickets::RESOLVED_PUSH_SUCCESS_COMMENT;
            $failComment = $this->tickets::RESOLVED_PUSH_FAIL_COMMENT;
        } else {
            $successComment = $this->tickets::PUSH_SUCCESS_COMMENT;
            $failComment = $this->tickets::PUSH_FAIL_COMMENT;
        }

        $comment = in_array($ticket->client_id, $pushResponse['result']['without_active_token_users'])
            ? $failComment
            : $successComment . $pushResponse['result']['push_id'];

        $this->tickets->addCommentToTicket($comment, $ticketId, $currentManagerId);
    }

    /**
     * Добавить комментарий
     * 
     * @return void
     */
    private function addComment()
    {
        $dataPost = $this->request->post();
        parse_str($dataPost, $dataPost);

        $comment = $this->tickets->addCommentToTicket(
            $dataPost['text'],
            $dataPost['ticket_id'],
            $_SESSION['manager_id']
        );

        $this->json_output([
            'success' => true,
            'comment' => $comment
        ]);
    }


    /**
     * Поиск менеджера
     * 
     * @return void
     */
    private function getManagers()
    {
        $term = $this->request->get('term', 'string');
        
        $managers = $this->managers->get_managers([
            'keyword' => $term 
        ]);

        $this->json_output($managers);
    }

    /**
     * Смена менеджера
     * 
     * @return void
     */
    private function changeManager()
    {
        $ticketId = $this->request->post('ticket_id');
        $managerId = $this->request->post('manager_id');

        if ($ticketId && $managerId) {
            $response = $this->tickets->updateManager($ticketId, $managerId);

            $this->response->json_output($response);
        }

        $this->response->json_output(['success' => false, 'message' => 'Произошла ошибка при смене исполнителя.']);
    }
    
    /**
     * Смена темы тикета
     * 
     * @return void
     */
    private function changeSubject()
    {
        $ticketId = $this->request->post('ticket_id');
        $subjectId = $this->request->post('subject_id');

        if ($ticketId && $subjectId) {
            $response = $this->tickets->updateSubject($ticketId, $subjectId);
            $this->response->json_output($response);
        }

        $this->response->json_output(['success' => false, 'message' => 'Произошла ошибка при смене темы.']);
    }

    /**
     * Смена приоритета тикета
     *
     * @return void
     */
    private function changePriority()
    {
        $ticketId = $this->request->post('ticket_id');
        $priorityId = $this->request->post('priority_id');

        if ($ticketId && $priorityId) {
            $response = $this->tickets->updatePriority((int)$ticketId, (int)$priorityId);
            $this->response->json_output($response);
        }

        $this->response->json_output(['success' => false, 'message' => 'Произошла ошибка при смене приоритета.']);
    }

    /**
     * Поиск тем обращений по названию
     * 
     * @return void
     */
    private function searchSubjects()
    {
        $term = $this->request->get('term');
        $formattedResults = $this->tickets->searchSubjects($term);

        $this->json_output($formattedResults);
    }

    /**
     * Обработка спорной жалобы
     *
     * @return void
     * @throws Exception
     */
    private function handleDisputedComplaint(): void
    {
        $ticketId = $this->request->post('ticket_id', 'integer');

        if (!$ticketId) {
            $this->response->json_output([
                'success' => false,
                'message' => 'ID тикета не указан.'
            ]);
        }

        $result = $this->tickets->handleDisputedComplaint($ticketId);
        $this->response->json_output($result);
    }

    /**
     * Возврат спорной жалобы в статус "Новый"
     *
     * @return void
     */
    private function returnDisputedToNew(): void
    {
        $ticketId = $this->request->post('ticket_id', 'integer');

        if (!$ticketId) {
            $this->response->json_output([
                'success' => false,
                'message' => 'ID тикета не указан.'
            ]);
        }

        $result = $this->tickets->returnDisputedComplaintToNew($ticketId);
        $this->response->json_output($result);
    }

    /**
     * Получение ID темы обращения по её названию
     * @param string $name
     * @return int
     */
    public function getSubjectId(string $name): int
    {
        $query = $this->db->placehold('SELECT id FROM s_mytickets_subjects WHERE name = ?', $name);
        $this->db->query($query);
        return $this->db->result('id') ?? 0;
    }

    /**
     * Экспорт тикетов в Excel
     *
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     */
    private function exportTicketsToExcel(): void
    {
        $filter = $this->tickets->prepareFilters($this->request->get('search'));
        $filter['sort'] = $this->tickets->prepareSort($this->request->get('sort'));

        $exporter = new TicketsExport($this->tickets, $filter);
        $exporter->download();
    }

    /**
     * Сохранение договоренности
     *
     * @return void
     */
    private function saveAgreement(): void
    {
        $ticketId = $this->request->post('ticket_id', 'integer');
        $date = $this->request->post('date', 'string');
        $note = trim((string)$this->request->post('note', 'string'));

        if (!$ticketId || !$date) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Некорректные данные'
            ]);
            return;
        }

        $result = $this->tickets->saveAgreement($ticketId, $date, $note);
        $this->response->json_output($result);
    }

    /**
     * Перенос договоренности на другую дату
     *
     * @return void
     */
    private function rescheduleAgreement(): void
    {
        $ticketId = $this->request->post('ticket_id', 'integer');
        $sourceTicketId = $this->request->post('source_ticket_id', 'integer');
        $newDate = $this->request->post('new_date', 'string');
        $reason = trim((string)$this->request->post('reason', 'string'));

        if (!$ticketId || !$sourceTicketId || !$newDate) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Некорректные данные'
            ]);
            return;
        }

        $result = $this->tickets->rescheduleAgreement($ticketId, $sourceTicketId, $newDate, $reason);
        $this->response->json_output($result);
    }

    /**
     * Подсветка тикета для всех сотрудников
     *
     * @return void
     */
    private function highlightTicket(): void
    {
        $ticketId = $this->request->post('ticket_id', 'integer');

        if (!$ticketId) {
            $this->response->json_output([
                'success' => false,
                'message' => 'ID тикета не указан'
            ]);
            return;
        }

        $managerId = $this->getManagerId();
        $manager = $this->managers->get_manager($managerId);
        $managerName = $manager ? $manager->name : 'Неизвестный сотрудник';

        $ticket = $this->tickets->getTicketById($ticketId);
        if (!$ticket) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Тикет не найден'
            ]);
            return;
        }

        if ($ticket->is_highlighted) {
             $this->response->json_output([
                'success' => true,
                'message' => 'Тикет уже подсвечен'
            ]);
            return;
        }

        $success = $this->tickets->setHighlightStatus($ticketId, 1);

        if ($success) {
            $clientName = $ticket->client_full_name ?? 'Неизвестный клиент';
            $backUrl = config('services.app.back_url');
            $ticketUrl = rtrim($backUrl, '/') . "/tickets/{$ticketId}";

            $clientLink = rtrim($backUrl, '/') . "/client/{$ticket->client_id}";

            $telegramMessage = sprintf(
                "<b>Подсвечен тикет №%s</b>\n" .
                "<a href='%s'>%s</a>\n\n" .
                "Клиент: <a href='%s'>%s</a>\n" .
                "Телефон: %s\n" .
                "Сотрудник: %s",
                $ticketId,
                $ticketUrl,
                $ticketUrl,
                $clientLink,
                htmlspecialchars($clientName),
                '+' . ltrim($ticket->client_phone, '+'),
                htmlspecialchars($managerName)
            );
            $options = [];
            $threadId = config('services.telegram.highlighted_tickets_thread_id');
            if ($threadId) {
                $options['message_thread_id'] = $threadId;
            }

            $this->telegram->sendMessage($telegramMessage, $options);
        }

        $this->response->json_output([
            'success' => $success,
            'message' => $success ? 'Тикет успешно подсвечен' : 'Ошибка подсветки тикета'
        ]);
    }
}
