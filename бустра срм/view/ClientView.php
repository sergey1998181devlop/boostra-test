<?php

use api\handlers\AddUserPaymentHandler;
use Api\Services\UserDataService;
use boostra\services\UsersAddressService;
use App\Core\Application\Application;
use App\Service\FileStorageFactory;
use App\Service\FileStorageService;

require_once __DIR__ . '/../lib/autoloader.php';
require_once 'View.php';
include_once dirname(__DIR__) . '/api/addons/sms_new.php';
require_once __DIR__ . '/../api/services/UserDataService.php';

class ClientView extends View
{
    /** @var UserDataService */
    private UserDataService $userDataService;
    /** @var FileStorageService|null */
    private ?FileStorageService $callRecordsStorage = null;

    public function __construct()
    {
        parent::__construct();

        $this->userDataService = new UserDataService($this->user_data);

        try {
            $app = Application::getInstance();
            /** @var FileStorageFactory $factory */
            $factory = $app->make(FileStorageFactory::class);
            $this->callRecordsStorage = $factory->make('call_records');
        } catch (\InvalidArgumentException $e) {
            // Storage не настроен — функционал записей звонков будет недоступен
        }
    }
    public const FIELD_NAMES = [
        'lastname' => 'Фамилия',
        'firstname' => 'Имя',
        'patronymic' => 'Отчество',
        'phone_mobile' => 'Номер телефона',
        'email' => 'Почта',
        'gender' => 'Пол',
        'birth' => 'Дата рождения',
        'birth_place' => 'Место рождения',
        'passport_serial' => 'Серия и номер паспорта',
        'passport_date' => 'Дата выдачи',
        'subdivision_code' => 'Код подразделения',
        'passport_issued' => 'Кем выдан',
        'work_scope' => 'Сфера деятельности',
        'profession' => 'Должность',
        'work_phone' => 'Рабочий телефон',
        'workplace' => 'Название организации',
        'workdirector_name' => 'ФИО руководителя',
        'income_base' => 'Доход',
        'Regregion' => 'Прописка - Область',
        'Regcity' => 'Прописка - Город',
        'Regstreet' => 'Прописка - Улица',
        'Reghousing' => 'Прописка - Дом',
        'Regbuilding' => 'Прописка - Строение',
        'Regroom' => 'Прописка - Квартира',
        'Faktregion' => 'Проживание - Область',
        'Faktcity' => 'Проживание - Город',
        'Faktstreet' => 'Проживание - Улица',
        'Fakthousing' => 'Проживание - Дом',
        'Faktbuilding' => 'Проживание - Строение',
        'Faktroom' => 'Проживание - Квартира',
        'Workregion' => 'Организация - Область',
        'Workcity' => 'Организация - Город',
        'Workstreet' => 'Организация - Улица',
        'Workhousing' => 'Организация - Дом',
        'Workbuilding' => 'Организация - Строение',
        'Workroom' => 'Организация - Офис',
        'contact_person_name' => 'Контактные лица - ФИО',
        'contact_person_relation' => 'Контактные лица - Кем приходится',
        'contact_person_phone' => 'Контактные лица - Тел.',
        'contact_person_comment' => 'Контактные лица - Комментарий'
    ];
    private const COMPLAINT_THEME = [
        'borrower' => 'c98a7be7-018e-11e8-80eb-000c293676f4',
        'ministry' => '525d4d44-fae6-11e7-80eb-000c293676f4 ',
        'prosecutor' => 'cb557e4b-070d-11e8-80eb-000c293676f4',
        'roskomnadzor' => '8b2dfc71-069a-11e8-80eb-000c293676f4',
        'rospotrebnadzor' => '11e5a14a-067f-11e8-80eb-000c293676f4',
        'sro' => '41cc5f4f-0694-11e8-80eb-000c293676f4',
        'third_person' => 'b72169ba-081e-11e8-80eb-000c293676f4',
        'fssp' => '11e5a11c-067f-11e8-80eb-000c293676f4',
        'central_bank' => '11e5a12c-067f-11e8-80eb-000c293676f4',
        'hotline' => '8e235c1a-a67a-11ef-9127-a4bf0165bff5',
        'email' => '941c8813-a67a-11ef-9127-a4bf0165bff5',
        'third-face' => '38e62d2b-ab2b-11ef-9127-a4bf0165bff5',
        'bomber' => '3fece4da-ab2b-11ef-9127-a4bf0165bff5',
        'threats' => '4ff442d8-ab2b-11ef-9127-a4bf0165bff5',
        'robot' => '56238a27-ab2b-11ef-9127-a4bf0165bff5',
        'sms' => '5d8ba54b-ab2b-11ef-9127-a4bf0165bff5',
        'add_service' => '6db8b106-ab2b-11ef-9127-a4bf0165bff5',
    ];

    public function fetch()
    {
        $user_id = $this->request->post('user_id', 'integer');

        if ($this->request->method('post'))
        {
            $id = $this->request->post('id', 'integer');
            $action = $this->request->post('action', 'string');

            switch($action):

                case 'personal':
                case 'personal_agreement':
                    $this->action_personal();
                    break;

                case 'passport':
                case 'passport_agreement':
                    $this->action_passport();
                    break;

                case 'reg_address':
                    $this->reg_address_action();
                    break;

                case 'fakt_address':
                    $this->fakt_address_action();
                    break;

                case 'contacts':
                    $this->contacts_action();
                    break;

                case 'workdata':
                    $this->workdata_action();
                    break;

                case 'work_address':
                    $this->work_address_action();
                    break;

                case 'socials':
                    $this->socials_action();
                    break;

                case 'images':
                    $this->action_images();
                    break;

                case 'add_comment':
                    $this->action_add_comment();
                    break;

                case 'send_sms':
                    $this->action_send_sms();
                    break;

                case 'block':
                    $this->actionBlock();
                    break;

                case 'blacklist':
                    $this->actionBlacklist();
                    break;

                case 'graylist':
                    $this->actionGraylist();
                    break;

                case 'test_user':
                    $this->actionTestUser();
                    break;

                case 'add_payment':
                    $this->actionAddPayment();
                    break;

                case 'save_select':
                    $this->actionSaveSelect();
                    break;
                    
                case 'toggle_approved_order_call_disabling':
                    $this->actionToggleAutoCall();
                    break;

                case 'unlock_rejected_nk':
                    $this->actionUnlockRejectedNk();
                    break;

                case 'leave_complaint':
                    $this->leaveComplaint();
                    break;

                case 'toggle_user_data_field':
                    $this->toggleUserDataField();
                    break;

                case 'disable_outgoing_calls':
                    $this->actionDisableOutgoingCalls();
                    break;

                case 'client_recurring_payment_so':
                    $this->actionClientRecurringPaymentSo();
                    break;

                case 'vsev_debt_notification_disabled':
                    $this->actionVsevDebtNotificationDisabled();
                    break;

                case 'toggle_show_cession_info':
                case 'toggle_show_agent_info':
                    $this->actionToggleVisibilityInfo();
                    break;

                case 'get_tickets':
                    $this->actionGetTickets();
                    break;

            endswitch;

            $this->design->assign('is_post', 1);
        }
        else
        {

            if (!($id = $this->request->get('id', 'integer')))
                return false;
            if ($id != 594201 && $this->manager->id == 167){
                return $this->design->fetch('403.tpl');
            }
            if (!($client = $this->users->get_user($id))) {
                return false;
            }

            $activeTicket = $this->tickets->getClientMainTicket($client->id);
            $this->design->assign('active_ticket', $activeTicket);

            $this->design->assign('front_url', $this->organizations->getSiteUrl($client->site_id));
            $this->design->assign('site_id', $client->site_id);

            // проверим пользователя, пришел ли он с сервиса
            $is_esia = $this->user_data->read($client->id, $this->user_data::IS_ESIA_NEW_USER);
            $is_tid = $this->user_data->read($client->id, $this->user_data::IS_TID_NEW_USER);
            $this->design->assign('is_esia', $is_esia);
            $this->design->assign('is_tid', $is_tid);

            $payment_exitpools = $this->payment_exitpools->get_exitpools(array('user_id' => $id));
            $this->design->assign('payment_exitpools', $payment_exitpools);

            $contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$client->id));
            $this->design->assign('contactpersons', $contactpersons);

            $files = $this->users->get_files(array('user_id'=>$id));
            $blockcalls = $this->tasks->getCallsBlacklistUsers($client->id);
            $this->design->assign('files', $files);

            $this->design->assign('client', $client);
            $this->design->assign('education_name', $this->users->getEducationName($client->education));
            $this->design->assign('client_data', $this->user_data->readAll($client->id));
            $this->design->assign('blockcalls', $blockcalls);
            
            // Проверяем, отключены ли звонки для клиента
            $calls_disabled = $this->getActiveCallsDisable($client->id);
            $this->design->assign('calls_disabled', $calls_disabled);
            
            $orders = $this->orders->get_orders(array(
                'user_id'=> $client->id,
                'sort' => 'date_desc'
            ));
            $repeat = array_filter($orders, function($item) {return $item->have_close_credits;});
            $this->design->assign('orders', $orders);

            if (!empty($orders)) {
                $pdnCalculations = $this->getPdnCalculations($orders, $client);

                if (!empty($pdnCalculations)) {
                    $this->design->assign('pdn_results', $pdnCalculations);
                }
            }

            $this->design->assign('has_approved_orders', $this->users->hasApprovedOrders($id));

            $passport_error = array();

            if(empty($repeat)) {
                $passport_user_id = $this->users->get_passport_user($client->passport_serial, $client->site_id, (int)$client->id);
                if (!empty($passport_user_id)) {
                    $passport_error[(int)$client->id] = $passport_user_id;
                }
            }

            $this->design->assign('passport_error', $passport_error);

            $total_flood_sms = $this->sms->check_total_limit($client->phone_mobile);
            $total_hour_sms = $this->sms->count_messages(
                [
                    'phone' => $client->phone_mobile,
                    'validated' => 1,
                    'created_after' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                ]
            );

            $stop_list = $total_flood_sms || $total_hour_sms > 6;

            if($stop_list) {
                $this->design->assign('stop_list', $stop_list);
            }

            $sms_messages = $this->sms->get_messages(['phone' => $client->phone_mobile]);
            // получим актуальные статусы сообщений
            if (!empty($sms_messages)) {
                foreach ($sms_messages as $sms_message) {
                    $sms_api_status = get_status($this, $sms_message->send_id, $client->phone_mobile, 0, $client->site_id);
                    $sms_message->api_status = $sms_api_status;
                }
            }

            $this->design->assign('sms_messages', $sms_messages);

            $calls = $this->mango->get_calls(array('phone'=>$client->phone_mobile));
            foreach ($calls as $call)
            {
                $call->date = date('Y-m-d H:i:s', $call->end_time);
                $call->duration = $call->talk_time > 0 ? $call->end_time - $call->talk_time : 0;
            }
            usort($calls, function($a, $b){
                return strtotime($b->date) - strtotime($a->date);
            });
            $this->design->assign('calls', $calls);

            $questions = array();
            foreach ($this->exitpools->get_exitpools(array('user_id'=>$client->id)) as $q)
                $questions[$q->question_id] = $q;
            $this->design->assign('questions', $questions);
            $this->design->assign('chat', $client->id);


            // получаем комменты из 1С
            #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($comments_1c);echo '</pre><hr />';

            $comments = $this->comments->get_comments(array('user_id'=>$client->id));

            foreach ($comments as $key => $comment) {
                if (in_array($comment->block, ChangelogsView::LOGS_TYPE_TO_HIDE_LOGS) && in_array($comment->manager_id, ChangelogsView::MANAGERS_TO_HIDE_LOGS)) {
                    unset($comments[$key]);
                    continue;
                }

                if ($comment->block === 'fromtechIncomingCall' && !empty($comment->text)) {
                    $data = json_decode($comment->text, true);
                    if (is_array($data) && !empty($data['call_record']) && $this->callRecordsStorage !== null) {
                        $val = (string)$data['call_record'];
                        if (stripos($val, 'http://') !== 0 && stripos($val, 'https://') !== 0) {
                            $data['call_record'] = $this->callRecordsStorage->getPublicUrl($val);
                            $comment->text = json_encode($data, JSON_UNESCAPED_UNICODE);
                        }
                    }
                }
            }

            $this->design->assign('comments', $comments);

            // получаем комменты из 1С
            if ($comments_1c_response = $this->soap->get_comments($client->UID, $client->site_id))
            {
                $comments_1c = array();
                if (!empty($comments_1c_response->Комментарии))
                {
                    foreach ($comments_1c_response->Комментарии as $comm)
                    {
                        $comment_1c_item = new StdClass();

                        $comment_1c_item->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
                        $comment_1c_item->text = $comm->Комментарий;
                        $comment_1c_item->block = $comm->Блок;
                        $comment_1c_item->color = $comm->color;

                        $comments_1c[] = $comment_1c_item;
                    }
                }

                usort($comments_1c, function($a, $b){
                    return strtotime($b->created) - strtotime($a->created);
                });

                $comments_1c = array_slice($comments_1c, 0, 300);

                $this->design->assign('comments_1c', $comments_1c);
                $this->design->assign('blacklist', $this->blacklist->getOne(['user_id' => $client->id]));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($comments_1c_response);echo '</pre><hr />';
            }

            $comment_blocks = $this->comments->get_blocks();
            $this->design->assign('comment_blocks', $comment_blocks);

            $scorings = $this->scorings->get_scorings(array('user_id' => $client->id, 'sort'=>'date_desc'));
            foreach ($scorings as $scoring) {
                if (in_array($scoring->type, [$this->scorings::TYPE_JUICESCORE, $this->scorings::TYPE_FSSP])) {
                    $scoring->body = $this->scorings->get_scoring_body($scoring->id);
                    $scoring->body = unserialize($scoring->body);
                }

                if (in_array($scoring->type, [$this->scorings::TYPE_AXILINK, $this->scorings::TYPE_SCORISTA])) {
                    $scoring->body = $this->scorings->get_scoring_body($scoring->id);
                    $scoring->body = json_decode($scoring->body);
//                    if (!empty($scoring->body->equifaxCH))
//                        $scoring->body->equifaxCH = iconv('cp1251', 'utf8', base64_decode($scoring->body->equifaxCH));
                }

                $scoring->type = $this->scorings->get_type($scoring->type);
            }

            if (in_array('looker_link', $this->manager->permissions)) {
                $this->design->assign('looker_link', $this->users->get_looker_link($id));
            }

            $this->design->assign('scorings', $scorings);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($scorings);echo '</pre><hr />';
        }

        $history_deleted_user_cabinet = [];
        if (!empty($client)) {
            $changelogs_remove_user_cabinet = $this->changelogs->get_changelogs(
                [
                    'user_id' => (int)$client->id,
                    'search' => [
                        'type' => Changelogs::TYPE_EVENT_USER_BLOCKED,
                    ],
                ]
            );

            foreach ($changelogs_remove_user_cabinet as $changelog_item) {
                $manager_event = $this->managers->get_manager((int)$changelog_item->manager_id);

                $history_deleted_user_cabinet[] = [
                    'created' => $changelog_item->created,
                    'type_event' => $changelog_item->new_values ? 'Блокировка/удаление' : 'Снятие блокировки/удаления',
                    'manager' => $manager_event->name . " (" . $manager_event->id . ")",
                ];
            }

            $additional_phones = $this->phones->get_phones($client->id);
            $this->design->assign('additional_phones', $additional_phones);

            $additionalEmails = $this->emails->getUserEmails($client->id);
            $this->design->assign('additionalEmails', $additionalEmails);

            $userDuplicates = $this->users->findDuplicates($client);
            $this->design->assign('userDuplicates', $userDuplicates);

            $has_pay_credit_rating = $this->scorings->hasPayCreditRating((int) $client->id);
            $this->design->assign('has_pay_credit_rating', $has_pay_credit_rating);
            $this->design->assign('client_log', $this->changelogs->get_client_log($user_id ?: $client->id));

            // Данные о скоринге из ЕФРСБ (банкротство)
            $scorings_efrsb = ( new \boostra\repositories\Repository( \boostra\domains\ScoringEFRSB::class ) )
                ->read(
                    [ 'user_id' => $client->id ],
                    'end_date',
                    'desc'
                );
            $this->design->assign( 'scorings_efrsb', $scorings_efrsb );
            $this->design->assign('blocked_adv_sms', $this->blocked_adv_sms->getItemByUserId((int)$client->id));

            if ($this->short_flow->isShortFlowUser($client->id)) {
                $this->design->assign('is_short_flow', true);
                $this->design->assign('is_short_flow_data_confirm', $this->short_flow->isPersonalDataConfirm($client->id));
            }

            $vkUser = $this->vk_api->get((int)$client->id);
            $this->design->assign('vk_user_id', $vkUser ? (int)$vkUser->vk_user_id : 0);

            $userData = $this->userDataService->getAll((int)$client->id);
            $innNotFound = $this->userDataService->checkInn((int)$client->id, $client->inn);

            $this->design->assign('inn_not_found', $innNotFound);

            $this->design->assign('user_data', $userData);

            $last_order = $this->orders->get_user_last_order($client->id);
            $ip_samara_office = ['85.113.49.9', '141.0.180.209'];
            if (in_array($client->reg_ip, $ip_samara_office)
                || (!empty($last_order) && in_array($last_order->ip, $ip_samara_office))) {
                $this->design->assign('is_samara_office', true);
            }
        }

        $this->design->assign('history_deleted_user_cabinet', $history_deleted_user_cabinet);

        $sms_templates_quality = $this->sms->get_templates(array('type' => 'quality_control'));
        $sms_templates_missing = $this->sms->get_templates(array('type' => 'missing'));

        $sms_templates = array_merge($sms_templates_quality, $sms_templates_missing);
        $this->design->assign('sms_templates', $sms_templates);

        $this->design->assign('blacklistReasons', $this->blacklist::REASONS);

        return $this->design->fetch('client.tpl');
    }

    private function actionAddPayment()
    {
        $userId = $this->request->get('id', 'integer');
        $oderId = $this->request->post('orderId');
        $paymentDate = $this->request->post('payment_date');

        (new AddUserPaymentHandler)->handle($oderId, $userId, $paymentDate, $this->manager);

        header('Location: ' . $this->request->url());
    }

    /**
     * Remove client from Blacklist
     * @return void
     */
    private function actionBlacklist(): void
    {
        $userId = $this->request->get('id', 'integer');
        $user = $this->users->get_user($userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }

        $comment = htmlspecialchars(trim($this->request->post('comment')));
        $reason = htmlspecialchars(trim($this->request->post('reason')));
        $remove = $this->request->post('remove', 'integer');
        $type = 'success';

        if ($remove) {
            $this->blacklist->delete($userId);
            $this->blacklist->sendDeleteUserFromBlacklist1c($user->UID, $reason, $comment, $this->manager->name_1c);
            $response = 'Клиент удален из ЧС';
        } else {
            if ($reason) {
                $id = $this->blacklist->add([
                    'user_id' => $userId,
                    'manager_id' => $this->manager->id,
                    'comment' => $comment,
                    'reason' => $reason,
                ]);

                $response = 'Клиент добавлен успешно в ЧС';
                if (!$id) {
                    $response = 'Невозможно добавить! Повторите позднее';
                    $type = 'error';
                } else {
                    $this->blacklist->sendAddUserToBlacklist1c($user->UID, $reason, $comment, $this->manager->name_1c);
                    $this->rejectApprovedOrders($userId);
                }
            } else {
                $response = 'Необходимо указать причину';
                $type = 'error';
            }
        }

        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'blacklist',
            'old_values' => $remove,
            'new_values' => (int) !$remove,
            'user_id' => $userId
        ]);

        if (!$this->request->isAjax()) {
            header('Location: ' . $this->request->url());
        } else {
            $this->json_output([$type => $response]);
        }
    }


    /**
     * Добавление/удаление подозрения в мошенничестве
     *
     * @return void
     */
    private function actionGraylist(): void
    {
        $userId = $this->request->get('id', 'integer');
        $user = $this->users->get_user($userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }

        $oldValue = (int)$this->user_data->read($userId, 'gray_list');
        $newValue = $oldValue ? '0' : '1';

        $this->user_data->set($userId, 'gray_list', $newValue);

        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'graylist',
            'old_values' => $oldValue,
            'new_values' => (int)$newValue,
            'user_id' => $userId
        ]);

        header('Location: ' . $this->request->url());
    }

    /**
     * Добавление/удаление флага тестового аккаунта
     *
     * @return void
     */
    private function actionTestUser()
    {
        $userId = $this->request->get('id', 'integer');
        $user = $this->users->get_user($userId);

        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }

        $oldValue = (int)$this->user_data->read($userId, 'test_user');
        $newValue = $oldValue ? '0' : '1';

        if((int)$newValue){
            try {
                $response = $this->soap->SetTestClient($user->UID);
            } catch (\SoapFault $e) {
                $this->json_output(['error' => 'Ошибка SOAP: ' . $e->getMessage()]);
                return;
            } catch (\Throwable $e) {
                $this->json_output(['error' => 'Ошибка: ' . $e->getMessage()]);
                return;
            }

            if(empty($response->return) || $response->return !== 'ОК'){
                $this->json_output(['error' => 'Не пришел ОК от 1С']);
                return;
            }

            $this->changelogs->add_changelog([
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => '1c_test_user_set',
                'old_values' => '',
                'new_values' => 'Статус тестового пользователя успешно установлен в 1С',
                'user_id' => $userId
            ]);
        }

        $this->user_data->set($userId, 'test_user', $newValue);
        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'test_user',
            'old_values' => $oldValue,
            'new_values' => (int)$newValue,
            'user_id' => $userId
        ]);

        header('Location: ' . $this->request->url());
    }

    /**
     * Переводим заявки пользователя в статусе "Одобрено" в отказ
     *
     * @param int $userId
     * @return void
     */
    private function rejectApprovedOrders(int $userId): void
    {
        $orders = $this->orders->get_orders([
            'user_id' => $userId,
            'status' => $this->orders::ORDER_STATUS_CRM_APPROVED
        ]);

        if (empty($orders)) {
            return;
        }

        foreach ($orders as $order) {
            $this->orders->rejectOrder($order, $this->reasons::REASON_BLACK_LIST);
        }
    }

    /**
     * Blocked User
     * @return void
     */
    private function actionBlock(): void
    {
        $userId = $this->request->get('id', 'integer');
        $state = $this->request->post('state', 'integer');
        $user = $this->users->get_user((int) $userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Пользователь не найден!']);
        }
        $this->users->update_user($userId, [Changelogs::TYPE_EVENT_USER_BLOCKED => $state]);
        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => Changelogs::TYPE_EVENT_USER_BLOCKED,
            'old_values' => $user->blocked,
            'new_values' => $state,
            'user_id' => $userId
        ]);
        $action = $state == 0 ? 'unblock' : 'block';
        $this->comments->add_action_log($userId, $this->manager->id, $action);

        if ($state === 0) {
            $this->users->editPassword(['incorrect_total' => 0], $userId);

            if ($user_orders = $this->orders->get_orders(['user_id' => $userId]))
                foreach ($user_orders as $order)
                    $this->orders->update_order($order->order_id, ['accept_try' => 0]);
        }

        $this->json_output(['success' => true]);
    }

    /**
     * Save custom select field
     * @return void
     */
    private function actionSaveSelect()
    {
        $userId = $this->request->get('id', 'integer');
        $field = $this->request->post('field');
        $fieldValue = $this->request->post('value');

        $user = $this->users->get_user((int) $userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Пользователь не найден!']);
        }
        $this->users->update_user($userId, [$field => $fieldValue]);
        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'save_select',
            'old_values' => '',
            'new_values' => $fieldValue,
            'user_id' => $userId
        ]);

        $this->json_output(['success' => true]);
    }

    private function action_send_sms()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $template_id = $this->request->post('template_id', 'integer');

        $user = $this->users->get_user((int)$user_id);

        $template = $this->sms->get_template($template_id, $user->site_id);

        if (!$type = $this->request->post('type'))
            $type = 'sms';

//        if ($this->is_developer)
//            $this->json_output(array('success'=>'developer_mode'));
//        else
        switch ($type):

            case 'sms':

                // проверяем лимит
                $balance = $this->users->get_user_balance($user_id);
                if (empty($template->check_limit))
                    $empty_balance = 1;
                elseif (!empty($balance->zaim_number) && $balance->ostatok_od > 0)
                    $limit = $this->soap->limit_sms($balance->zaim_number);
                else
                    $empty_balance = 1;
                if (empty($empty_balance) && $limit != 1)
                {
                    $this->json_output(array('error'=>'Превышен лимит звонков'));
                }
                else
                {
                    $message = strtr($template->template, [
                        '{{organization_site}}' => $this->config->SMSC_PROVIDER[$user->site_id]
                    ]);

                    // TODO: [временно, далее будет ссылка на переход в лк]
                    if(($template_id == $this->sms::LK_TYPE) && ($user->site_id != $this->organizations::SITE_BOOSTRA)){
                        $aspCode = mt_rand(1000, 9999);
                        $link = 'https://' . $this->sites->getDomainBySiteId($user->site_id) . '/login';

                        $message = strtr($template->template, [
                            '{{code}}' => $aspCode,
                            '{{url}}' => $link,
                        ]);
                    }

                    $resp = $this->smssender->send_sms(
                    $user->phone_mobile, $message, $user->site_id, 1);

                    $this->sms->add_message(array(
                        'user_id' => $user->id,
                        'order_id' => 0,
                        'phone' => $user->phone_mobile,
                        'message' => $message,
                        'created' => date('Y-m-d H:i:s'),
                        'send_status' => $resp[1],
                        'delivery_status' => '',
                        'send_id' => $resp[0],
                    ));

                    $this->changelogs->add_changelog(array(
                        'manager_id' => $this->manager->id,
                        'created' => date('Y-m-d H:i:s'),
                        'type' => 'send_sms',
                        'old_values' => '',
                        'new_values' => $message,
                        'user_id' => $user->id,
                    ));
                    if ((!empty($balance->zaim_number) && $balance->ostatok_od > 0))
                        $this->soap->send_number_of_sms($balance->zaim_number, $user->phone_mobile, $template->template);

                    if ($resp[1] > 0)
                        $this->json_output(array('success'=>true));
                    else
                        $this->json_output(array('error'=>$resp[1], 'resp'=>$resp));
                }

                break;

            case 'viber':
                $link = $this->config->root_url.'/chats.php?chat=viber&class=messages&method=sendText&text='.$template->template.'&phone='.$user->phone_mobile.'&id='.$user->id;
                $resp = file_get_contents($link);

                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'send_viber',
                    'old_values' => '',
                    'new_values' => $template->template,
                    'user_id' => $user->id,
                ));

                if ($resp == 'false')
                    $this->json_output(array('error'=>'Ошибка при отправке'));
                else
                    $this->json_output(array('success'=>true));

                break;

            case 'whatsapp':
                $link = $this->config->root_url.'/chats.php?chat=whatsapp&class=messages&method=sendText&text='.$template->template.'&phone='.$user->phone_mobile;
                $resp = file_get_contents($link);
                $response = json_decode($resp);

                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'send_whatsapp',
                    'old_values' => '',
                    'new_values' => $template->template,
                    'user_id' => $user->id,
                ));

                if (empty($response->Data->sent))
                    $this->json_output(array('error'=>$response->Data->error));
                else
                    $this->json_output(array('success'=>true));
                break;

        endswitch;

    }

    private function action_add_comment()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $block = $this->request->post('block', 'string');
        $text = $this->request->post('text');

        if (empty($text))
        {
            $this->json_output(array('error'=>'Напишите комментарий!'));
        }
        else
        {


            //roma
            $comment = array(
                'manager_id' => $this->manager->id,
                'user_id' => $user_id,
                'block' => $block,
                'text' => $text,
                'created' => date('Y-m-d H:i:s'),
            );

            if ($comment_id = $this->comments->add_comment($comment))
            {

                $this->json_output(array(
                    'success' => 1,
                    'created' => date('d.m.Y H:i:s'),
                    'text' => $text,
                    'manager_name' => $this->manager->name,
                ));
            }
            else
            {
                $this->json_output(array('error'=>'Не удалось добавить!'));
            }
        }
    }



    private function action_personal()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->lastname = trim($this->request->post('lastname'));
        $client->firstname = trim($this->request->post('firstname'));
        $client->patronymic = trim($this->request->post('patronymic'));
        $client->gender = trim($this->request->post('gender'));
        $client->birth = trim($this->request->post('birth'));
        $client->birth_place = trim($this->request->post('birth_place'));
        $client->email = trim($this->request->post('email'));
        $client->choose_insure = intval($this->request->post('choose_insure'));
        $client->UID = strval($this->request->post('uid'));

        if (in_array($this->manager->role, ['admin', 'developer', 'contact_center_plus', 'boss_cc', 'contact_center', 'yurist', 'opr', 'ts_operator'])) {
            $client->phone_mobile = $this->users->clear_phone($this->request->post('phone_mobile'));
        }

        $personal_error = array();

        if (empty($client->lastname))
            $personal_error[] = 'empty_lastname';
        if (empty($client->firstname))
            $personal_error[] = 'empty_firstname';
        if (empty($client->patronymic))
            $personal_error[] = 'empty_patronymic';
        if (empty($client->gender))
            $personal_error[] = 'empty_gender';
        if (empty($client->birth))
            $personal_error[] = 'empty_birth';
        if (empty($client->birth_place))
            $personal_error[] = 'empty_birth_place';

        if (!empty($client->lastname) && !validateCyrillicPlus($client->lastname)) {
            $personal_error[] = 'symbols_lastname';
        }
        if (!empty($client->firstname) && !validateCyrillicPlus($client->firstname)) {
            $personal_error[] = 'symbols_firstname';
        }
        if (!empty($client->patronymic) && !validateCyrillicPlus($client->patronymic)) {
            $personal_error[] = 'symbols_patronymic';
        }
        if (!empty($client->birth_place) && !validateCyrillicPlus($client->birth_place)) {
            $personal_error[] = 'symbols_birth_place';
        }

        if (in_array($this->manager->role, ['admin', 'developer', 'contact_center_plus', 'boss_cc', 'contact_center', 'yurist', 'opr', 'ts_operator'])) {
            if (empty($client->phone_mobile)) {
                $personal_error[] = 'phone_mobile';
            }
        }

        $additional_phones = array_map('trim', (array)$this->request->post('additional_phones')) ?? [];
        if ($current_phones = $this->phones->get_phones($user_id)) {
            foreach ($current_phones as $cur_phone) {
                if (!in_array($cur_phone->phone, $additional_phones)) {
                    $this->phones->update($cur_phone->id, [
                        'is_active' => 0
                    ]);
                }
            }
        }
        $this->design->assign('additional_phones', $this->phones->get_phones($user_id));

        $additionalEmails = array_map('trim', (array)$this->request->post('additionalEmails')) ?? [];
        if ($current_emails = $this->emails->getUserEmails($user_id)) {
            foreach ($current_emails as $cur_email) {
                if (!in_array($cur_email->email, $additionalEmails)) {
                    $this->emails->update($cur_email->id, [
                        'is_active' => 0
                    ]);
                }
            }
        }
        $this->design->assign('additionalEmails', $this->emails->getUserEmails($user_id));

        if (empty($personal_error))
        {
            $update = array(
                'lastname' => $client->lastname,
                'firstname' => $client->firstname,
                'patronymic' => $client->patronymic,
                'gender' => $client->gender,
                'birth' => $client->birth,
                'birth_place' => $client->birth_place,
                'email' => $client->email,
                'choose_insure' => $client->choose_insure,
                'UID' => $client->UID,
            );

            if (in_array($this->manager->role, ['admin', 'developer', 'contact_center_plus', 'boss_cc', 'contact_center', 'yurist', 'opr', 'ts_operator'])) {
                $update['phone_mobile'] = $client->phone_mobile;
            }

            $old_user = $this->users->get_user($user_id);

            $changeLogs = Helpers::getChangeLogs($update, $old_user);

            $action = $this->request->post('action');
            if ($action == 'personal') {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'personal',
                    'old_values' => serialize($changeLogs['old']),
                    'new_values' => serialize($changeLogs['new']),
                    'user_id' => $user_id,
                ));

                foreach ($changeLogs['old'] as $key => $old_val) {
                    $new_val = $changeLogs['new'][$key];
                    $field_name = self::FIELD_NAMES[$key];
                    $this->comments->add_field_log($user_id, $this->manager->id,  $field_name, $old_val, $new_val);
                }

                $this->users->update_user($user_id, $update);
            }
            else if ($action == 'personal_agreement') {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'personal_agreement',
                    'old_values' => serialize($changeLogs['old']),
                    'new_values' => serialize($changeLogs['new']),
                    'user_id' => $user_id,
                ));

                if ($this->users->select_user_agreement($user_id))
                    $this->users->update_user_agreement($user_id, $update);
                else
                    $this->users->add_user_agreement($user_id, $update);
            }
        }

        $this->design->assign('personal_error', $personal_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }

    private function action_passport()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->passport_serial = trim($this->request->post('passport_serial'));
        $client->passport_date = trim($this->request->post('passport_date'));
        $client->subdivision_code = trim($this->request->post('subdivision_code'));
        $client->passport_issued = trim($this->request->post('passport_issued'));

        $passport_error = array();

        $passport_user_id = $this->users->get_passport_user($client->passport_serial, $client->site_id);
        if (!empty($passport_user_id) && $passport_user_id != $user_id) {
            $passport_error[$user_id] = $passport_user_id;
        }
        if (empty($client->passport_serial))
            $passport_error[] = 'empty_passport_serial';
        if (empty($client->passport_date))
            $passport_error[] = 'empty_passport_date';
        if (empty($client->subdivision_code))
            $passport_error[] = 'empty_subdivision_code';
        if (empty($client->passport_issued))
            $passport_error[] = 'empty_passport_issued';

        if (!empty($client->passport_issued) && !validateCyrillicPlus($client->passport_issued)) {
            $passport_error[] = 'symbols_passport_issued';
        }

        if (empty($passport_error))
        {
            $update = array(
                'passport_serial' => $client->passport_serial,
                'passport_date' => $client->passport_date,
                'subdivision_code' => $client->subdivision_code,
                'passport_issued' => $client->passport_issued
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $action = $this->request->post('action');
            if ($action == 'passport') {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'passport',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($log_update),
                    'user_id' => $user_id,
                ));

                foreach ($log_update as $key => $new_val) {
                    $old_val = $old_values[$key];
                    $field_name = self::FIELD_NAMES[$key];
                    $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'passport');
                }

                $this->users->update_user($user_id, $update);
            }
            else if ($action == 'passport_agreement') {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'passport_agreement',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($log_update),
                    'user_id' => $user_id,
                ));

                if ($this->users->select_user_agreement($user_id))
                    $this->users->update_user_agreement($user_id, $update);
                else
                    $this->users->add_user_agreement($user_id, $update);
            }
        }

        $this->design->assign('passport_error', $passport_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }

    private function reg_address_action()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->Regregion = trim($this->request->post('Regregion'));
        $client->Regcity = trim($this->request->post('Regcity'));
        $client->Regstreet = trim($this->request->post('Regstreet'));
        $client->Reghousing = trim($this->request->post('Reghousing'));
        $client->Regbuilding = trim($this->request->post('Regbuilding'));
        $client->Regroom = trim($this->request->post('Regroom'));

        $regaddress_error = array();

        if (empty($client->Regregion))
            $regaddress_error[] = 'empty_regregion';
        if (empty($client->Regcity))
            $regaddress_error[] = 'empty_regcity';
        if (empty($client->Reghousing))
            $regaddress_error[] = 'empty_reghousing';

        if (!empty($client->Regregion) && !validateCyrillicPlus($client->Regregion)) {
            $regaddress_error[] = 'symbols_regregion';
        }
        if (!empty($client->Regcity) && !validateCyrillicPlus($client->Regcity)) {
            $regaddress_error[] = 'symbols_regcity';
        }

        if (empty($regaddress_error))
        {
            $timezone_id = $this->users->getTimezoneId($client->Regregion);

            $update = array(
                'Regregion' => $client->Regregion,
                'Regcity' => $client->Regcity,
                'Regstreet' => $client->Regstreet,
                'Reghousing' => $client->Reghousing,
                'Regbuilding' => $client->Regbuilding,
                'Regroom' => $client->Regroom,
            );

            if (!empty($timezone_id)) {
                $update['timezone_id'] = $timezone_id;
            }

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'regaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = self::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'regaddress');
            }

            if (!empty($old_user->registration_address_id) && !empty($this->request->safe_post('Regregion'))) {
                (new UsersAddressService())->updateRegistrationAddress($old_user->registration_address_id, $this->request);
            }

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('regaddress_error', $regaddress_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }

    private function fakt_address_action()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->Faktregion = trim($this->request->post('Faktregion'));
        $client->Faktcity = trim($this->request->post('Faktcity'));
        $client->Faktstreet = trim($this->request->post('Faktstreet'));
        $client->Fakthousing = trim($this->request->post('Fakthousing'));
        $client->Faktbuilding = trim($this->request->post('Faktbuilding'));
        $client->Faktroom = trim($this->request->post('Faktroom'));

        $faktaddress_error = array();

        if (empty($client->Faktregion))
            $faktaddress_error[] = 'empty_faktregion';
        if (empty($client->Faktcity))
            $faktaddress_error[] = 'empty_faktcity';
        if (empty($client->Fakthousing))
            $faktaddress_error[] = 'empty_fakthousing';

        if (!empty($client->Faktregion) && !validateCyrillicPlus($client->Faktregion)) {
            $faktaddress_error[] = 'symbols_faktregion';
        }
        if (!empty($client->Faktcity) && !validateCyrillicPlus($client->Faktcity)) {
            $faktaddress_error[] = 'empty_faktcity';
        }

        if (empty($faktaddress_error))
        {
            $update = array(
                'Faktregion' => $client->Faktregion,
                'Faktcity' => $client->Faktcity,
                'Faktstreet' => $client->Faktstreet,
                'Fakthousing' => $client->Fakthousing,
                'Faktbuilding' => $client->Faktbuilding,
                'Faktroom' => $client->Faktroom,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'faktaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = self::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'faktaddress');
            }

            if (!empty($old_user->factual_address_id) && !empty($this->request->safe_post('Faktregion'))) {
                (new UsersAddressService())->updateFactualAddress($old_user->factual_address_id, $this->request);
            }

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('faktaddress_error', $faktaddress_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }

    private function contacts_action()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $contact_person_ids = array_map('trim', (array)$this->request->post('contact_person_id'));
        $contact_person_names = array_map('trim', (array)$this->request->post('contact_person_name'));
        $contact_person_phones = array_map('trim', (array)$this->request->post('contact_person_phone'));
        $contact_person_relations = array_map('trim', (array)$this->request->post('contact_person_relation'));
        $contact_person_comments = array_map('trim', (array)$this->request->post('contact_person_comment'));

//        $this->contactpersons->delete_user_contactpersons($user_id);
        foreach ($contact_person_ids as $i => $contact_person_id)
        {
            $contactperson = array(
                'user_id' => $user_id,
                'name' => $contact_person_names[$i],
                'phone' => $contact_person_phones[$i],
                'relation' => $contact_person_relations[$i],
                'comment' => $contact_person_comments[$i],
            );
            if (empty($contact_person_id))
            {
                $this->contactpersons->add_contactperson($contactperson);

                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'contacts',
                    'old_values' => serialize(array()),
                    'new_values' => serialize($contactperson),
                    'user_id' => $user_id,
                ));
            }
            else
            {
                $old_contact_person = $this->contactpersons->get_contactperson($contact_person_id);

                $update = $contactperson;
                unset($update['user_id']);

                $old_values = array();
                foreach ($update as $key => $val)
                    if ($old_contact_person->$key != $update[$key])
                        $old_values[$key] = $old_contact_person->$key;

                $log_update = array();
                foreach ($update as $k => $u)
                    if (isset($old_values[$k]))
                        $log_update[$k] = $u;

                foreach ($log_update as $key => $new_val) {

                    $old_val = $old_values[$key];
                    $field_name = self::FIELD_NAMES['contact_person_'.$key];
                    $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'contactpersons');
                }
                $this->contactpersons->update_contactperson($contact_person_id, $contactperson);
            }

        }

        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$user_id));
        $this->design->assign('contactpersons', $contactpersons);

        /*
        $contacts_error = array();

        if (empty($contacts_error))
        {
            $update = array(
                'contact_person_name' => $order->contact_person_name,
                'contact_person_phone' => $order->contact_person_phone,
                'contact_person_relation' => $order->contact_person_relation,
                'contact_person2_name' => $order->contact_person2_name,
                'contact_person2_phone' => $order->contact_person2_phone,
                'contact_person2_relation' => $order->contact_person2_relation,
                'contact_person3_name' => $order->contact_person3_name,
                'contact_person3_phone' => $order->contact_person3_phone,
                'contact_person3_relation' => $order->contact_person3_relation,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'contacts',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('contacts_error', $contacts_error);
        */
        $client = new StdClass();
        $client->id = $user_id;
        $this->design->assign('client', $client);
    }


    private function contacts_action_old()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->contact_person_name = trim($this->request->post('contact_person_name'));
        $client->contact_person_phone = trim($this->request->post('contact_person_phone'));
        $client->contact_person_relation = trim($this->request->post('contact_person_relation'));
        $client->contact_person2_name = trim($this->request->post('contact_person2_name'));
        $client->contact_person2_phone = trim($this->request->post('contact_person2_phone'));
        $client->contact_person2_relation = trim($this->request->post('contact_person2_relation'));
        $client->contact_person3_name = trim($this->request->post('contact_person3_name'));
        $client->contact_person3_phone = trim($this->request->post('contact_person3_phone'));
        $client->contact_person3_relation = trim($this->request->post('contact_person3_relation'));

        $contacts_error = array();

        if (empty($client->contact_person_name))
            $contacts_error[] = 'empty_contact_person_name';
        if (empty($client->contact_person_phone))
            $contacts_error[] = 'empty_contact_person_phone';
        if (empty($client->contact_person_relation))
            $contacts_error[] = 'empty_contact_person_relation';
        if (empty($client->contact_person2_name))
            $contacts_error[] = 'empty_contact_person2_name';
        if (empty($client->contact_person2_phone))
            $contacts_error[] = 'empty_contact_person2_phone';
        if (empty($client->contact_person2_relation))
            $contacts_error[] = 'empty_contact_person2_relation';
        if (empty($client->contact_person3_name))
            $contacts_error[] = 'empty_contact_person3_name';
        if (empty($client->contact_person3_phone))
            $contacts_error[] = 'empty_contact_person3_phone';
        if (empty($client->contact_person3_relation))
            $contacts_error[] = 'empty_contact_person3_relation';

        if (empty($contacts_error))
        {
            $update = array(
                'contact_person_name' => $client->contact_person_name,
                'contact_person_phone' => $client->contact_person_phone,
                'contact_person_relation' => $client->contact_person_relation,
                'contact_person2_name' => $client->contact_person2_name,
                'contact_person2_phone' => $client->contact_person2_phone,
                'contact_person2_relation' => $client->contact_person2_relation,
                'contact_person3_name' => $client->contact_person3_name,
                'contact_person3_phone' => $client->contact_person3_phone,
                'contact_person3_relation' => $client->contact_person3_relation,

            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'contacts',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('contacts_error', $contacts_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }

    private function workdata_action()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->work_scope = trim($this->request->post('work_scope'));
        $client->profession = trim($this->request->post('profession'));
        $client->work_phone = trim($this->request->post('work_phone'));
        $client->workplace = trim($this->request->post('workplace'));
        $client->workdirector_name = trim($this->request->post('workdirector_name'));
        $client->income_base = trim($this->request->post('income_base'));

        $workdata_error = array();

        if (empty($client->work_scope))
            $workaddress_error[] = 'empty_work_scope';
        if (empty($client->income_base))
            $workaddress_error[] = 'empty_income_base';

        if (empty($workdata_error))
        {
            $update = array(
                'work_scope' => $client->work_scope,
                'profession' => $client->profession,
                'work_phone' => $client->work_phone,
                'workplace' => $client->workplace,
                'workdirector_name' => $client->workdirector_name,
                'income_base' => $client->income_base,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'workdata',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = self::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'work');
            }

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('workdata_error', $workdata_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }


    private function work_address_action()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->Workregion = trim($this->request->post('Workregion'));
        $client->Workcity = trim($this->request->post('Workcity'));
        $client->Workstreet = trim($this->request->post('Workstreet'));
        $client->Workhousing = trim($this->request->post('Workhousing'));
        $client->Workbuilding = trim($this->request->post('Workbuilding'));
        $client->Workroom = trim($this->request->post('Workroom'));

        $workaddress_error = array();

        if (empty($client->Workregion))
            $workaddress_error[] = 'empty_workregion';
        if (empty($client->Workcity))
            $workaddress_error[] = 'empty_workcity';
        if (empty($client->Workhousing))
            $workaddress_error[] = 'empty_workhousing';

        if (empty($workaddress_error))
        {
            $update = array(
                'Workregion' => $client->Workregion,
                'Workcity' => $client->Workcity,
                'Workstreet' => $client->Workstreet,
                'Workhousing' => $client->Workhousing,
                'Workbuilding' => $client->Workbuilding,
                'Workroom' => $client->Workroom,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'workaddress',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = self::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'workaddress');
            }
            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('workaddress_error', $workaddress_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }

    private function socials_action()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $client = new StdClass();
        $client->social_fb = trim($this->request->post('social_fb'));
        $client->social_inst = trim($this->request->post('social_inst'));
        $client->social_vk = trim($this->request->post('social_vk'));
        $client->social_ok = trim($this->request->post('social_ok'));

        $socials_error = array();

        if (empty($socials_error))
        {
            $update = array(
                'social_fb' => $client->social_fb,
                'social_inst' => $client->social_inst,
                'social_vk' => $client->social_vk,
                'social_ok' => $client->social_ok,
            );

            $old_user = $this->users->get_user($user_id);
            $old_values = array();
            foreach ($update as $key => $val)
                if ($old_user->$key != $update[$key])
                    $old_values[$key] = $old_user->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'socials',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('socials_error', $socials_error);

        $client->id = $user_id;
        $this->design->assign('client', $client);
    }


    private function action_images()
    {
        $user_id = $this->request->post('user_id', 'integer');

        $statuses = $this->request->post('status');
        foreach ($statuses as $file_id => $status)
        {
            $update = array(
                'status' => $status,
                'id' => $file_id
            );
            $old_files = $this->users->get_file($file_id);
            $old_values = array();
            foreach ($update as $key => $val)
                $old_values[$key] = $old_files->$key;
            if ($old_values['status'] != $update['status'])
            {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'images',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($update),
                    'user_id' => $user_id,
                    'file_id' => $file_id,
                ));
            }

            $this->users->update_file($file_id, array('status' => $status));
        }

        $client = new StdClass();
        $client->id = $user_id;
        $this->design->assign('client', $client);

        $files = $this->users->get_files(array('user_id'=>$user_id));
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump(count($files), $user_id);echo '</pre><hr />';
        $this->design->assign('files', $files);
    }

    private function actionToggleAutoCall()
    {
        $user_id = $this->request->post('user_id', 'integer');
        
        $currentState = (boolean) $this->user_data->read($user_id, 'disable_approved_order_calls');
        $newState = !$currentState;

        $this->user_data->set($user_id, 'disable_approved_order_calls', (int) $newState);
        
        $this->json_output(array(
            'success' => 1,
            'disable' => $newState
        ));
    }

    private function actionUnlockRejectedNk()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $this->user_data->set($user_id, 'is_rejected_nk', 0);
        $this->json_output(['success' => 1]);
    }

    private function leaveComplaint(): void
    {
        $user_id = $this->request->post('user_id', 'integer');
        $subject = $this->request->post('subject', 'string');
        $number = $this->request->post('number', 'integer');
        $comment = $this->request->post('comment', 'string');

        if (empty($user_id) || empty($subject) || empty($number) || empty($comment)) {
            $this->json_output([
                'error' => 1,
                'message' => 'Not all fields are filled in.'
            ]);
            return;
        }
        $order = $this->orders->get_orders(['user_id' => $user_id, 'id' => $number]);
        if (empty($order)) {
            $this->json_output([
                'error' => 1,
                'message' => 'The loan does not belong to the client.'
            ]);
            return;
        }

        $this->soap->sendComplaint(
            [
                'ComplaintUID' => self::COMPLAINT_THEME[$subject],
                'LoanApplicationUID' => $order[0]->order_uid,
                'Comment' => $comment,
            ]
        );
        $this->comments->add_comment([
            'manager_id' => $this->manager->id,
            'user_id' => $user_id,
            'order_id' => $number,
            'block' => 'complaint',
            'text' => "Оставили жалобу " . $comment,
            'created' => date('Y-m-d H:i:s'),
        ]);

        $this->json_output([
            'success' => 1,
            'message' => 'Complaint successfully sent to 1C.'
        ]);
    }

    /**
     * Toggle user data boolean field and log the change
     *
     * @return void
     */
    private function toggleUserDataField(): void
    {
        $userId = $this->request->get('id', 'integer');
        $fieldName = $this->request->post('field', 'string');
        $changelogType = lcfirst(str_replace('_', '', ucwords($fieldName, '_')));
        $user = $this->users->get_user($userId);
        
        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }
        if (empty($fieldName)) {
            $this->json_output(['error' => 'Поле не найдено!']);
        }

        $oldValue = (int)$this->user_data->read($userId, $fieldName);
        $newValue = $oldValue ? '0' : '1';

        $this->user_data->set($userId, $fieldName, $newValue);

        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => $changelogType,
            'old_values' => $oldValue,
            'new_values' => (int)$newValue,
            'user_id' => $userId
        ]);

        header('Location: ' . $this->request->url());
        exit;
    }

    /**
     * Переключатель видимости информации в ЛК (цессия, агенты)
     *
     * @return void
     */
    private function actionToggleVisibilityInfo()
    {
        $userId = $this->request->post('user_id', 'integer');
        $action = $this->request->post('action', 'string');

        $fieldName = str_replace('toggle_', '', $action);

        if (!in_array($fieldName, [$this->user_data::SHOW_CESSION_INFO, $this->user_data::SHOW_AGENT_INFO])) {
            $this->json_output(['error' => 'Неизвестное действие!']);
        }

        $value = $this->request->post($fieldName, 'integer');

        $user = $this->users->get_user($userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }

        $oldValue = $this->user_data->read($userId, $fieldName);
        if ($oldValue === false) {
            $oldValue = 1;
        } else {
            $oldValue = (int)$oldValue;
        }

        $this->user_data->set($userId, $fieldName, $value);

        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => $fieldName,
            'old_values' => $oldValue,
            'new_values' => $value,
            'user_id' => $userId
        ]);

        $this->json_output([
            'success' => true,
            'value' => $value
        ]);
    }

    /**
     * Включение/Отключение звонков клиенту через добавление в DNC лист Voximplant
     * @return void
     */
    private function actionDisableOutgoingCalls(): void
    {
        $userId = $this->request->get('id', 'integer');
        $days = $this->request->post('days', 'integer');
        $enable = $this->request->post('enable', 'integer');

        $query = $this->db->placehold(
            "SELECT * FROM __user_dnc WHERE user_id = ? AND date_end > NOW() ORDER BY id DESC LIMIT 1",
            $userId
        );
        $this->db->query($query);
        $existingDnc = $this->db->result();

        if ($enable) {
            if (empty($existingDnc)) {
                $this->json_output(['error' => 'Исходящие звонки не отключены!']);
                return;
            }
            
            try {
                $dncContactIds = json_decode($existingDnc->dnc_contact_ids, true);
                foreach($dncContactIds as $dncContactId) {
                    $result = $this->voximplant->deleteDncContact((int)$dncContactId);

                    if (!isset($result['success']) && !$result['success']) {
                        $this->json_output(['error' => 'Ошибка при включении исходящих звонков: ' . ($result['error'] ?? 'Неизвестная ошибка')]);
                        return;
                    }
                }

                $this->db->query(
                    "DELETE FROM __user_dnc WHERE id = ?",
                    $existingDnc->id
                );

                $this->changelogs->add_changelog([
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'enable_outgoing_calls',
                    'old_values' => '',
                    'new_values' => "Включение иходящих звонков",
                    'user_id' => $userId
                ]);
                    
                $this->json_output(['success' => "Исходящие звонки включены"]);
                return;
            } catch (\Exception $e) {
                $this->json_output(['error' => 'Ошибка: ' . $e->getMessage()]);
                return;
            }
        }

        if (!empty($existingDnc)) {
            $this->json_output(['error' => 'Исходящие звонки уже отключены до ' . date('d.m.Y H:i', strtotime($existingDnc->date_end))]);
            return;
        }
        
        if (empty($days) || $days < 1 || $days > 5) {
            $this->json_output(['error' => 'Некорректное количество дней!']);
            return;
        }
        
        $user = $this->users->get_user($userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
            return;
        }

        $mainPhone = $user->phone_mobile;

        $additionalPhones = [];
        $query = $this->db->placehold(
            "SELECT phone FROM __user_phones WHERE user_id = ? AND is_active = 1",
            $userId
        );
        $this->db->query($query);
        $results = $this->db->results();
        
        foreach ($results as $row) {
            $additionalPhones[] = $row->phone;
        }

        $allPhones = [];
        if (!empty($mainPhone)) {
            $allPhones[] = $mainPhone;
        }
        $allPhones = array_merge($allPhones, $additionalPhones);
        
        if (empty($allPhones)) {
            $this->json_output(['error' => 'У клиента не указаны телефоны!']);
            return;
        }

        $contacts = formatPhonesForDnc($allPhones);
        
        if (empty($contacts)) {
            $this->json_output(['error' => 'У клиента нет валидных телефонов для добавления в DNC!']);
            return;
        }
        
        try {
            $comment = "Отключение исходящих звонков по запросу менеджера {$this->manager->name} на $days дней";

            $result = $this->voximplant->addDncContacts($contacts, Voximplant::OUTGOING_CALLS_DNC_LIST_ID, $comment);

            $dncContactIds = [];
            foreach ($contacts as $contact) {
                $response = $this->voximplant->searchDncContacts($contact, Voximplant::OUTGOING_CALLS_DNC_LIST_ID);

                if (isset($response['success']) && $response['success']) {
                    foreach ($response['result'] as $item) {
                        $dncContactIds[] = $item['id'];
                    }
                }
            }

            if (isset($result['success']) && $result['success']) {
                $this->db->query(
                    "INSERT INTO __user_dnc (user_id, phones, days, date_start, date_end, manager_id, dnc_contact_ids) 
                    VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY), ?, ?)",
                    $userId,
                    json_encode($contacts),
                    $days,
                    $days,
                    $this->manager->id,
                    json_encode($dncContactIds)
                );

                $this->changelogs->add_changelog([
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'disable_outgoing_calls',
                    'old_values' => '',
                    'new_values' => "Отключение исходящих звонков на $days дней для " . count($contacts) . " телефонов",
                    'user_id' => $userId
                ]);
                
                $this->json_output(['success' => "Исходящие звонки отключены на $days дней для " . count($contacts) . " телефонов"]);
            } else {
                $this->json_output(['error' => 'Ошибка при отключении исходящих звонков: ' . ($result['error'] ?? 'Неизвестная ошибка')]);
            }
        } catch (\Exception $e) {
            $this->json_output(['error' => 'Ошибка: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Получение информации об активном отключении исходящих звонков
     * @param int $userId ID пользователя
     * @return object|null Информация об отключении звонков или null
     */
    private function getActiveCallsDisable(int $userId)
    {
        $query = $this->db->placehold(
            "SELECT * FROM __user_dnc WHERE user_id = ? AND date_end > NOW() ORDER BY id DESC LIMIT 1",
            $userId
        );
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получить данные из расчета ПДН (нужно для адреса проживания и дохода клиента)
     *
     * @param array $userOrders
     * @param stdClass $user
     * @return array|null
     */
    private function getPdnCalculations(array $userOrders, stdClass $user): ?array
    {
        $akvariusOrders = array_filter($userOrders, function($order) {
            return (int)$order->organization_id !== $this->organizations::FINLAB_ID;
        });

        if (empty($akvariusOrders)) {
            return null;
        }

        $ordersId = array_column($akvariusOrders, 'order_id');
        $pdnCalculations = $this->pdnCalculation->getPdnCalculationsByOrderId($ordersId);

        if (empty($pdnCalculations)) {
            return null;
        }

        $userAddressDto = (new UsersAddressService())->createFactualAddressDtoFromUser($user);

        foreach ($pdnCalculations as $pdnCalculation) {
            if (!empty($pdnCalculation->fakt_address)) {
                $pdnCalculation->fakt_address = json_decode($pdnCalculation->fakt_address);
            } else {
                $pdnCalculation->fakt_address = $userAddressDto;
            }

            if (!empty($pdnCalculation->income_base)) {
                $pdnCalculation->income_base = $pdnCalculation->income_base;
            } else {
                $pdnCalculation->income_base = $user->income_base;
            }
        }

        return $pdnCalculations;
    }

    /**
     * Включение/отключение списания ЗО на оплате через рекуррентный платеж у клиента
     */
    private function actionClientRecurringPaymentSo()
    {
        $userId = $this->request->post('user_id', 'integer');
        $clientRecurrentPaymentSo = $this->request->post('client_recurring_payment_so', 'integer');

        $user = $this->users->get_user($userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }

        $this->user_data->set($userId, 'client_recurring_payment_so', $clientRecurrentPaymentSo);

        $this->json_output(['success' => 1]);
    }

    private function actionVsevDebtNotificationDisabled()
    {
        $userId = $this->request->post('user_id', 'integer');
        $vsevDebtNotificationDisabled = $this->request->post('vsev_debt_notification_disabled', 'integer');

        $user = $this->users->get_user($userId);
        if (empty($user)) {
            $this->json_output(['error' => 'Клиент не найден!']);
        }

        $this->user_data->set($userId, 'vsev_debt_notification_disabled', $vsevDebtNotificationDisabled);

        $this->json_output(['success' => 1]);
    }

    /**
     * Возвращает список тикетов по клиенту
     * @return void
     */
    private function actionGetTickets(): void
    {
        $clientId = $this->request->post('user_id', 'integer');

        if (empty($clientId)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'empty_client_id']);
            exit;
        }

        $client = $this->users->get_user($clientId);
        if (empty($client)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => 'client_not_found']);
            exit;
        }

        $itemsPerPage = 20;
        $currentPage = max(1, (int)$this->request->post('page', 'integer'));

        $filter = $this->tickets->prepareFilters($this->request->post('search'));

        $tickets = $this->tickets->getClientTicketsForClientCard($clientId, $currentPage, $itemsPerPage, $filter);
        $items = $tickets['data'] ?? [];
        $totalCount = (int)($tickets['total_count'] ?? count($items));
        $totalPages = $totalCount > 0 ? (int)ceil($totalCount / $itemsPerPage) : 1;

        $subjects = $this->tickets->getMainAndChildSubjects();
        $managers = $this->managers->get_managers();

        $this->design->assign_array([
            'items' => $items,
            'total_items' => $totalCount,
            'current_page_num' => $currentPage,
            'total_pages_num' => $totalPages,
            'filters' => $this->request->post('search') ?: [],
            'subjects' => $subjects,
            'companies' => $this->tickets->getCompanies(),
            'channels' => $this->tickets->getChannels(),
            'priorities' => $this->tickets->getPriorities(),
            'statuses' => $this->tickets->getStatuses(),
            'responsible_persons' => $this->tickets->getUniqueResponsiblePersonNames(),
            'responsible_groups' => $this->tickets->getUniqueGroups(),
            'managers' => $managers,
        ]);

        $html = $this->design->fetch('contact_center/blocks/client_tickets.tpl');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'tickets_html' => $html,
            'total_items' => $totalCount,
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
        ]);
        exit;
    }
}
