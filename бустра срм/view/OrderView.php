<?php

use Api\Services\UserDataService;
use App\Core\Application\Application;
use App\Modules\Card\Services\CardService;
use App\Repositories\UserDncRepository;
use App\Repositories\UserRepository;
use App\Modules\SbpAccount\Services\SbpAccountService;
use boostra\services\UsersAddressService;

require_once 'View.php';
require_once __DIR__ . '/../api/services/UserDataService.php';
require_once __DIR__ . '/../app/Modules/Card/Services/CardService.php';
require_once __DIR__ . '/../app/Modules/SbpAccount/Services/SbpAccountService.php';

class OrderView extends View
{
    /** @var UserDataService */
    private UserDataService $userDataService;
    private CardService $cardService;
    private SbpAccountService $sbpAccountService;

    private const LOG_FILE = 'order_view.txt';

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->userDataService = $app->make(UserDataService::class, $this->user_data);
        $this->cardService = $app->make(CardService::class);
        $this->sbpAccountService = $app->make(SbpAccountService::class);
    }

    /**
     * Список причин скрытых от верификаторов
     */
    const SKIP_REASONS_FOR_VERIFICATORS = [
        1, // Долг по ФССП - 1
        21, //Антифрод
        24, // ФССП
        25, //Локальное время
        28, // Возраст 65+
        38 // Отказ по совпадению паспортных данных
    ];


    /**
     * @throws ErrorException
     * @throws Exception
     */
    public function fetch()
    {
        if ($this->manager->id == 167)
            return $this->design->fetch('403.tpl');

        if ($this->request->method('post')) {
            $order_id = $this->request->post('order_id', 'integer');
            $action = $this->request->post('action', 'string');

            switch ($action):

                case 'change_manager':
                    $this->change_manager_action();
                    break;

                case 'return_insure':
                    $this->return_insure_action();
                    break;

                case 'return_insurance':
                    $this->return_insurance_action();
                    break;

                case 'amount':
                    $this->action_amount();
                    break;

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

                case 'get_scorings':
                    $this->action_get_scorings();
                    break;

                case 'get_credit_history':
                    $this->action_get_credit_history();
                    break;

                case 'get_logs':
                    $this->action_get_logs();
                    break;

                case 'get_documents':
                    $this->action_get_documents();
                    break;

                case 'get_comments':
                    $this->action_get_comments();
                    break;

                case 'get_insures':
                    $this->action_get_insures();
                    break;

                case 'get_overpayments':
                    $this->action_get_overpayments();
                    break;

                case 'socials':
                    $this->socials_action();
                    break;

                case 'images':
                    $this->action_images();
                    break;

                case 'services':
                    $this->action_services();
                    break;

                case 'accept':
                    $this->action_accept();
                    break;

                case 'approve':
                    $this->action_approve();
                    break;

                case 'reject':
                    $this->action_reject();
                    break;

                case 'waiting':
                    $this->action_waiting();
                    break;

                case 'add_maratorium':
                    $this->action_add_maratorium();
                    break;

                case 'add_comment':
                    $this->action_add_comment();
                    break;

                case 'add_identified_phone':
                    $this->action_add_identified_phone();
                    break;

                case 'send_sms':
                    $this->action_send_sms();
                    break;

                case 'next_stage':
                    $this->action_next_stage();
                    break;

                case 'set_call_variants':
                    $this->action_call_variants();
                    break;


                case 'get_balance':
                    $this->get_balance();
                    break;

                case 'resend_approve':
                    $this->resend_approve();
                    break;

                case 'set_tehotkaz':
                    $this->set_tehotkaz();
                    break;

                case 'divide_order':
                    $this->divide_order();
                    break;

                case 'get_user_files':
                    $this->get_user_files();
                    break;

                case 'save_select':
                    $this->actionSaveSelect();
                    break;

                case 'change_autodebit_param':
                    $this->changeAutodebitParam();
                    break;

                case 'payment_deferment':
                    $this->paymentDeferment();
                    break;

            endswitch;

        } else {

            $organizations = [];
            foreach ($this->organizations->getList() as $org) {
                $organizations[$org->id] = $org;
            }
            $this->design->assign('organizations', $organizations);

            $order_id = $this->request->get('id', 'integer');

            if (!empty($order_id)) {
                if ($order = $this->orders->get_order($order_id)) {
                    if (in_array($this->manager->role, ['verificator']) && empty($order->manager_id)) {
                        return $this->design->fetch('403.tpl');
                    }

                    if (in_array('looker_link', $this->manager->permissions))
                        $this->design->assign('looker_link', $this->users->get_looker_link($order->user_id));

                    $this->design->assign('front_url', $this->organizations->getSiteUrl($order->site_id));
                    $this->design->assign('site_id', $order->site_id);

                    if (!empty($order->pay_result)) {
                        $pay_result = @unserialize($order->pay_result);
                        if (isset($pay_result['return']))
                            $order->pay_result = $pay_result['return'];
                    }

                    // автовыдача
                    if ($this->order_data->get($order_id, $this->order_data::AUTOCONFIRM_ASP)) {
                        $this->design->assign('has_autoconfirm_sms', 1);
                    }

                    if (($order->utm_medium == 'autoconfirm') || $this->order_data->get($order_id, $this->order_data::IS_AUTOCONFIRM_CROSS)){
                        $this->design->assign('is_autoconfirm', 1);
                    }

                    // спец предложение из возврата лояльных
                    if ($user_offer = $this->users->get_actual_offer($order->user_id)) {
                        $this->design->assign('user_offer', $user_offer);
                    }

                    $approve_show = 0;
                    if (!empty($order->call_variants)) {
                        $have_success = 0;
                        $all_none = 1;
                        $order->call_variants = unserialize($order->call_variants);
                        foreach ($order->call_variants as $cv) {
                            if ($cv == 1)
                                $have_success = 1;
                            if ($cv != 2)
                                $all_none = 0;
                        }
                        $approve_show = $have_success || $all_none ? 1 : 0;
                    }
                    $this->design->assign('approve_show', $approve_show);

                    if (!empty($order->reason_id)) {
                        $order->reason = $this->reasons->get_reason($order->reason_id);
                    }
                    if (!empty($order->maratorium_date)) {
                        $order->maratorium_valid = strtotime($order->maratorium_date) > time();
                    }


                    $d = date_diff(date_create(date('Y-m-d', strtotime($order->birth))), date_create(date('Y-m-d')));
                    $order->age = $d->format('%y');

                    $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $order->user_id));
                    $this->design->assign('contactpersons', $contactpersons);

                    $this->design->assign('has_approved_orders', $this->users->hasApprovedOrders($order->user_id));

                    $get_files_filters = [
                        'user_id' => $order->user_id,
                        'status' => [1, 2, 3],
                    ];

                    if ($order->first_loan) {
                        $get_files_filters['without_types'] = ['face1'];
                    }

                    $files = $this->users->get_files($get_files_filters);
                    $this->design->assign('files', $files);

                    if (!empty($order->stage5)) {
                        $order->stage5_time = time() - strtotime($order->stage1_date);
                    } elseif (!empty($order->stage4)) {
                        $order->stage4_time = time() - strtotime($order->stage1_date);
                    } elseif (!empty($order->stage3)) {
                        $order->stage3_time = time() - strtotime($order->stage1_date);
                    } elseif (!empty($order->stage2)) {
                        $order->stage2_time = time() - strtotime($order->stage1_date);
                    } elseif (!empty($order->stage1)) {
                        $order->stage1_time = time() - strtotime($order->stage1_date);
                    }

                    $this->design->assign('axi_amount', $this->order_data->read($order_id, 'amount_after_axi'));

                    $this->design->assign('nbki_score', $this->getNbkiScore($order_id));
                    $this->design->assign('min_nbki_score', $this->dbrainAxi::MIN_NBKI_SCORE);
                    $this->design->assign($this->order_data::LINK_ORDER_SCORISTA, $this->order_data->read($order_id, $this->order_data::LINK_ORDER_SCORISTA));


                    $user = $this->users->get_user((int)$order->user_id);
                    $order->user = $user;
                    $autodebit_cards = $this->best2pay->get_autodebit_cards($user->id);

                    // Добавление привязанных счетов СБП
                    $sbpAccounts = $this->sbpAccount->getSbpAccountsByUserId((int)$user->id);
                    if (!empty($sbpAccounts)) {
                        $b2pBanksId = array_column($sbpAccounts, 'member_id');
                        $b2pBanks = $this->b2p_bank_list->get([
                            'id' => $b2pBanksId
                        ]);

                        $b2pBanks = array_column($b2pBanks, null, 'id');

                        foreach ($sbpAccounts as &$sbpAccount) {
                            $sbpAccount->title = $b2pBanks[$sbpAccount->member_id]->title ?? '';
                        }
                        unset($sbpAccount);
                    }

                    if (empty($order->b2p)) {
                        $card_list = $this->soap->get_card_list($user->UID);
//                        $card_list = $this->tinkoff->get_cardlist($user->UID);
                    } else {
                        $card_list = $this->best2pay->get_cards(array('user_id' => $user->id));
                    }
                    if ($this->manager->role == 'verificator_minus') {
                        $card_list = array_filter($card_list, function ($item) {
                            return empty($item->deleted) && empty($item->deleted_by_client) && $item->organization_id == $this->organizations::AKVARIUS_ID;
                        });
                        $autodebit_cards = array_filter($autodebit_cards, function ($item) {
                            return empty($item->deleted) && empty($item->deleted_by_client) && $item->organization_id == $this->organizations::AKVARIUS_ID;
                        });
                    }
                    $this->design->assign('card_list', $card_list);
                    $this->design->assign('autodebit_cards', $autodebit_cards);
                    $this->design->assign('sbp_accounts', $sbpAccounts);

                    $this->design->assign('user', $user);

                    $passport_error = array();

                    // Проверяем наличие закрытых кредитов для определения "повторности" клиента
                    $user_orders = $this->orders->get_orders(array('user_id' => $user->id));
                    $repeat = array_filter($user_orders, function ($item) {
                        return $item->have_close_credits;
                    });

                    if (empty($repeat)) {
                        $passport_user_id = $this->users->get_passport_user($user->passport_serial, $user->site_id, (int)$order->user_id);
                        if (!empty($passport_user_id)) {
                            $passport_error[(int)$order->user_id] = $passport_user_id;
                        }
                    }

                    $this->design->assign('passport_error', $passport_error);



                    $order->eventlogs = $this->eventlogs->get_logs(array('order_id' => $order_id));

                    $order_data = $this->order_data->readAll($order_id);

                    $blockCalls = $this->tasks->getCallsBlacklistUsers($order->user_id);
                    $this->design->assign('order', $order);
                    $this->design->assign('education_name', $this->users->getEducationName($order->education));
                    $this->design->assign('order_data', $order_data);
                    $this->design->assign('self_employee_document', $this->prepareSelfEmployeeDocument($order_data));
                    $this->design->assign('blockCalls', $blockCalls);

                    $eventlogs = $this->eventlogs->get_events(array('order_id' => $order_id));
                    $this->design->assign('eventlogs', $eventlogs);
                    $order_divide = $this->orders->hasOrderDivide((int)$order_id);
                    $this->design->assign('order_divide', $order_divide);

                    if ($order->amount > 0) {
                        $is_new_client = empty($user->loan_history);
                        if ($is_new_client) {
                            try {
                                $credits_history = $this->soap->get_user_credits($user->UID);
                                $is_new_client = empty($credits_history);
                            } catch (\Exception $e) {
                                // Игнорируем ошибки SOAP
                            }
                        }

                        $credit_doctor = $this->credit_doctor->getAmountCreditDoctor($order->amount, $is_new_client);
                        if ($credit_doctor) {
                            $this->design->assign('credit_doctor_price', $credit_doctor->price);
                        }
                    }

                    if ($starOracle = $this->star_oracle->getStarOracle($order->order_id, $order->user_id, $this->star_oracle::ACTION_TYPE_ISSUANCE)) {
                        $this->design->assign('star_oracle_price', $starOracle->amount);
                    }

                    $tvMedical = $this->tv_medical->getTVMedical(
                        $order->order_id,
                        $order->user_id,
                        $this->tv_medical::TV_MEDICAL_PAYMENT_STATUS_SUCCESS,
                        null,
                        null,
                        $this->star_oracle::ACTION_TYPE_ISSUANCE
                    );
                    if ($tvMedical) {
                        $this->design->assign('tv_medical_price', $tvMedical->amount);
                    }

                    if ($safeDeal = $this->safe_deal->get($order->order_id, $order->user_id, 'success')) {
                        $this->design->assign('safe_deal_price', $safeDeal->amount);
                    }
                } else {
                    $this->design->assign('error', 'undefined_order');
                }

                if ($this->request->get('action') == 'load') {

                    $filestorage = new Filestorage();
                    $uid = str_replace('.pdf', '', $this->request->get('uid'));
                    $type = $this->request->get('type');

                    if ($uid == '19e7e23e-4ea3-426f-8f36-86deff750c38')
                        return false;

                    if ($file = $filestorage->load_document($uid, $type)) {
                        header('Location: ' . $file);
                        exit;
                    } else {
                        return false;
                    }
                }

                $fssp_reasons = $this->fssp_api->getReasons();
                $this->design->assign('fssp_reasons', $fssp_reasons);

                $fssp_basis = $this->fssp_api->getBasis();
                $this->design->assign('fssp_basis', $fssp_basis);

                $fssp_items = $this->fssp_api->getFsspByOrderId((int)$order_id);
                $this->design->assign('fssp_items', $fssp_items);
            }

            $this->design->assign('open_scorings', $this->request->get('open_scorings', 'integer'));

            $this->design->assign('regaddress_error', array());
            $this->design->assign('faktaddress_error', array());
            $this->design->assign('is_post', false);
        }

        if ($this->manager->role == 'verificator_minus' && $order->organization_id != $this->organizations::AKVARIUS_ID) {
            return false;
        }

        $maratoriums = array();
        foreach ($this->maratoriums->get_maratoriums() as $m)
            $maratoriums[$m->id] = $m;
        $this->design->assign('maratoriums', $maratoriums);

        $reject_reasons = array();
        $waiting_reasons = array();
        foreach ($this->reasons->get_reasons() as $reason) {
            if (in_array($this->manager->role, ['verificator', 'edit_verificator']) && (in_array($reason->id, self::SKIP_REASONS_FOR_VERIFICATORS))) {
                continue;
            }
            if ($reason->type == 'reject')
                $reject_reasons[] = $reason;
            if ($reason->type == 'waiting')
                $waiting_reasons[] = $reason;
        }

        $this->reasons->sortForVerifier($reject_reasons);
        $this->reasons->sortForVerifier($waiting_reasons);

        $this->design->assign('reject_reasons', $reject_reasons);
        $this->design->assign('waiting_reasons', $waiting_reasons);

        $sms_templates = $this->sms->get_templates(array('type' => 'order'));
        $this->design->assign('sms_templates', $sms_templates);

        if (!empty($order)) {
            $sms_messages = $this->sms->get_messages(array('order_id' => $order->order_id));
            $this->design->assign('sms_messages', $sms_messages);

            $additional_phones = $this->phones->get_phones($order->user_id);
            $this->design->assign('additional_phones', $additional_phones);

            $additionalEmails = $this->emails->getUserEmails($order->user_id);
            $this->design->assign('additionalEmails', $additionalEmails);

            $userDuplicates = $this->users->findDuplicates($order->user_id);
            $this->design->assign('userDuplicates', $userDuplicates);

            //$scorista_step_additional_data = $this->user_data->read($order->user_id, $this->scorista::FLAG_STEP_ADDITIONAL_DATA);
            $this->design->assign('scorista_step_additional_data', false);

            $scorista_step_files = $this->user_data->read($order->user_id, $this->scorista::FLAG_STEP_FILES);
            $this->design->assign('scorista_step_files', $scorista_step_files);
        }

        $has_pay_credit_rating = $this->scorings->hasPayCreditRating((int)$order->user_id);
        if ($last_scorista_scoring = $this->scorings->get_last_scorista_for_user((int)$order->user_id, true)) {
            $last_scorista_scoring->body = json_decode($last_scorista_scoring->body);
            $this->design->assign('last_scorista_scoring', $last_scorista_scoring);
        }

        $this->design->assign('has_pay_credit_rating', $has_pay_credit_rating);
        $this->design->assign('has_last_scorista_scoring', !empty($last_scorista_scoring->scorista_id));

        // проверяем прошел ли пользователь страницу с покупкой КР при регистрации
        $this->design->assign('skip_credit_rating', !empty($user->skip_credit_rating));
        $this->design->assign('accept_reject_orders', 1);

        /**
         * Проверка на возможность нажатия "Принять" в заявке для НК,
         * accept_reject_orders - разница между посещением КР (+ 5 минут) и текущего времени
         */
        $this->design->assign('is_approve_order', 1);

        $this->db->query("SELECT * FROM s_dbrain_statistics WHERE order_id = ?", $order->order_id);
        $dbrain_statistic = $this->db->result();
        $this->design->assign('dbrain_statistic', $dbrain_statistic);

        if (empty($order)) {
            $order_id = $this->request->post('order_id', 'integer');

            if (!empty($order_id)) {
                $order = $this->orders->get_order($order_id);
                $this->design->assign('order', $order);
            }
        }

        if (empty($order_data)) {
            $order_data = $this->order_data->readAll($order->order_id);
            $this->design->assign('order_data', $order_data);
        }
        $this->design->assign('self_employee_document', $this->prepareSelfEmployeeDocument($order_data ?: []));

        $vkUser = $this->vk_api->get((int)$order->user_id);
        $this->design->assign('vk_user_id', $vkUser ? (int)$vkUser->vk_user_id : 0);

        $userData = $this->userDataService->getAll((int)$order->user_id);

        $innNotFound = $this->userDataService->checkInn((int)$order->user_id, $order->inn);

        $this->design->assign('inn_not_found', $innNotFound);

        $isOrderFromAkvarius = !empty($order->payment_details) && $order->payment_details == 'from_akvarius';
        $this->design->assign('is_order_from_akvarius', $isOrderFromAkvarius);

        $this->design->assign('user_data', $userData);

        $locationIpScorings = $this->scorings->get_scorings([
            'order_id' => $order->order_id,
            'type' => $this->scorings::TYPE_LOCATION_IP,
            'sort' => 'id_date_desc',
            'limit' => 1
        ]);

        $region_ip_mismatch = false;

        if (
            !empty($locationIpScorings[0]->status) &&
            (int)$locationIpScorings[0]->status === $this->scorings::STATUS_COMPLETED &&
            empty($locationIpScorings[0]->success)
        ) {
            $region_ip_mismatch = true;
        }

        $this->design->assign('region_ip_mismatch', $region_ip_mismatch);

        if ($this->short_flow->isShortFlowUser($order->user_id)) {
            $this->design->assign('is_short_flow', true);
            $this->design->assign('is_short_flow_data_confirm', $this->short_flow->isPersonalDataConfirm($order->user_id));
        }

        $ip_samara_office = ['85.113.49.9', '141.0.180.209'];
        if (in_array($order->ip, $ip_samara_office) || in_array($user->reg_ip, $ip_samara_office)) {
            $this->design->assign('is_samara_office', true);
        }

        // Если для заявки есть адрес проживания необходимый для расчета ПДН, то отображаем его
        $pdnCalculations = $this->pdnCalculation->getPdnCalculationsByOrderId([$order->order_id], (int)$order->organization_id);
        if (!empty($pdnCalculations[0]->fakt_address)) {
            $faktAddressFromPdn = json_decode($pdnCalculations[0]->fakt_address);

            if (!empty($faktAddressFromPdn)) {
                (new UsersAddressService())->addFactualAddressToUser($order, $faktAddressFromPdn);
            }
        }

        // Заменяем доход из анкеты доходом, который пришел из сервиса ПДН
        if (!empty($pdnCalculations[0]->income_base)) {
            $order->income_base = $pdnCalculations[0]->income_base;
        }

        $this->addSelectedBank($order);
        $this->assignRobotCallsState($order);

        $body = $this->design->fetch('order.tpl');

        if ($this->request->get('ajax', 'integer')) {
            echo $body;
            exit;
        }

        return $body;
    }

    /**
     * Вкл/выкл звонки робота
     * @param stdClass $order
     * @return void
     * @throws Exception
     */
    private function assignRobotCallsState(stdClass $order): void
    {
        $userRepository = Application::getInstance()->make(UserRepository::class);
        $userDncRepository = Application::getInstance()->make(UserDncRepository::class);
        $siteId = $userRepository->getSiteIdByUserId((int) $order->user_id);
        $activeDnc = null;
        if ($siteId !== null && $siteId !== '') {
            $activeDnc = $userDncRepository->findActiveByUserIdAndSiteId((int) $order->user_id, $siteId);
        }
        $this->design->assign('robot_calls_disabled', $activeDnc !== null);
        $this->design->assign('robot_calls_disabled_until', $activeDnc !== null ? date('d.m.Y H:i', strtotime($activeDnc->date_end)) : '');
    }

    /**
     * Подготовка данных документа «Подтверждение целевого займа» из order_data для шаблона.
     *
     * @param array $orderData результат order_data->readAll()
     * @return array|null ['name' => string, 'path' => string] или null
     */
    private function prepareSelfEmployeeDocument(array $orderData): ?array
    {
        $raw = $orderData['self_employee_document'] ?? '';
        if ($raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return null;
        }
        $path = $decoded['path'] ?? $decoded['storage_uid'] ?? '';
        if ($path === '') {
            return null;
        }
        return [
            'name' => $decoded['name'] ?? 'Скачать документ',
            'path' => $path,
        ];
    }

    private function addSelectedBank(stdClass $order)
    {
        $bankId = $this->order_data->read((int)$order->order_id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);

        if (!empty($bankId)) {
            $selectedBank = $this->b2p_bank_list->getOne([
                'id' => $bankId,
                'has_sbp' => 1
            ]);

            if (!empty($selectedBank)) {
                $this->design->assign('selected_bank', $selectedBank);
            }
        }

        $bankId = $this->user_data->read((int)$order->user_id, $this->user_data::DEFAULT_BANK_ID_FOR_SBP_ISSUANCE);

        if (!empty($bankId)) {
            $selectedBank = $this->b2p_bank_list->getOne([
                'id' => $bankId,
                'has_sbp' => 1
            ]);

            if (!empty($selectedBank)) {
                $this->design->assign('default_selected_bank', $selectedBank);
            }
        }
    }

    private function resend_approve()
    {
        if ($order_id = $this->request->post('order_id', 'integer')) {
            if ($order = $this->orders->get_order($order_id)) {
                $manager = $this->managers->get_manager($order->manager_id);

                $res = $this->soap->update_status_1c($order->id_1c, 'Одобрено', $manager->name_1c, $order->amount, $order->percent, '', 0, $order->period);

                $this->json_output($res);
            }
        }

    }

    private function set_tehotkaz()
    {
        if ($order_id = $this->request->post('order_id', 'integer')) {
            if ($order = $this->orders->get_order($order_id)) {
                $manager = $this->managers->get_manager($order->manager_id);

                $res = $this->soap->update_status_1c($order->id_1c, 'Одобрено', $manager->name_1c, $order->amount, $order->percent, '', 0, $order->period);

                $this->orders->update_order($order->order_id, array('status' => 3, 'reason_id' => 10, '1c_status' => '7.Технический отказ'));

                $this->virtualCard->forUser($order->user_id)->delete();

	            $res = $this->soap->set_tehokaz($order->id_1c);

                $this->users->update_user($order->user_id, array('use_b2p' => 1));

                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'tehotkaz',
                    'old_values' => serialize(array('status' => $order->status, 'manager_id' => $order->manager_id)),
                    'new_values' => serialize(array('status' => 3, 'manager_id' => $order->manager_id)),
                    'order_id' => $order_id,
                    'user_id' => $order->user_id,
                ));

                if (!empty($order->is_user_credit_doctor))
                    $this->soap1c->send_credit_doctor($order->id_1c);

                $this->json_output($res);
            }
        }

    }

    private function action_call_variants()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $call_variants = (array)$this->request->post('call_variants');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        $update = array(
            'call_variants' => serialize($call_variants)
        );
        $this->orders->update_order($order_id, $update);

        $output = array('success' => 1, 'status' => 1, 'manager' => $this->manager->name);

        $this->json_output($output);
    }

    private function change_manager_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $manager_id = $this->request->post('manager_id', 'integer');

        if (!($order = $this->orders->get_order((int)$order_id)))
            return array('error' => 'Неизвестный ордер');

        if (!in_array($this->manager->role, array('admin', 'developer', 'chief_verificator', 'opr', 'ts_operator')))
            return array('error' => 'Не хватает прав для выполнения операции', 'manager_id' => $order->manager_id);

        $update = array(
            'manager_change_date' => date('Y-m-d H:i:s'),
            'manager_id' => $manager_id,
            'uid' => exec($this->config->root_dir . 'generic/uidgen')
        );
        $this->orders->update_order($order_id, $update);

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status_order',
            'old_values' => serialize(array('status' => $order->status, 'manager_id' => $order->manager_id)),
            'new_values' => serialize($update),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ));

        $output = array('success' => 1, 'status' => 1, 'manager' => $this->manager->name);

        $this->json_output($output);
    }

    private function action_add_identified_phone()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');

        $identified_phone = $this->request->post('identified_phone');

        $this->users->update_user($user_id, ['identified_phone' => $identified_phone]);

        header('Location: http://manager.boostra.ru/order/' . $order_id);
        exit();
    }

    private function action_next_stage()
    {
        $stage = $this->request->post('stage', 'integer');
        $order_id = $this->request->post('order_id', 'integer');

        $this->orders->update_order($order_id, array(
            'stage' . $stage => 1,
            'stage' . $stage . '_date' => date('Y-m-d H:i:s'),
        ));

    }

    private function action_add_comment()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $order_id = $this->request->post('order_id', 'integer');
        $block = $this->request->post('block', 'string');
        $text = $this->request->post('text');

        if (empty($text)) {
            $this->json_output(array('error' => 'Напишите комментарий!'));
        } else {
            if (empty($order_id) && !empty($user_id)) {
                $last_order = $this->orders->get_user_last_order($user_id);
                $order_id = (int)$last_order->id;
            }

            $comment = array(
                'manager_id' => $this->manager->id,
                'user_id' => $user_id,
                'order_id' => $order_id,
                'block' => $block,
                'text' => $text,
                'created' => date('Y-m-d H:i:s'),
            );

            if ($comment_id = $this->comments->add_comment($comment)) {

                if ($order = $this->orders->get_order((int)$order_id)) {
                    $manager = $this->managers->get_manager((int)$this->manager->id);
                    $this->soap->send_comment(array(
                        'manager' => $manager->name_1c,
                        'text' => $text,
                        'created' => date('Y-m-d H:i:s'),
                        'number' => $order->id_1c
                    ));
                }

                $this->json_output(array(
                    'success' => 1,
                    'created' => date('d.m.Y H:i:s'),
                    'text' => $text,
                    'manager_name' => $this->manager->name,
                ));
            } else {
                $this->json_output(array('error' => 'Не удалось добавить!'));
            }
        }
    }

    private function action_add_maratorium()
    {
        $user_id = $this->request->post('user_id', 'integer');
        $maratorium_id = $this->request->post('maratorium_id', 'integer');

        if (empty($maratorium_id)) {
            $this->json_output(array('error' => 'Выберите мораторий!'));
        } else {
            if ($maratorium = $this->maratoriums->get_maratorium($maratorium_id)) {
                $maratorium_date = time() + $maratorium->period;
                $this->users->update_user($user_id, array(
                    'maratorium_id' => $maratorium_id,
                    'maratorium_date' => date('Y-m-d H:i:s', $maratorium_date),
                ));

                $this->json_output(array(
                    'success' => 1,
                    'date' => date('d.m.Y H:i:s', $maratorium_date),
                ));
            } else {
                $this->json_output(array('error' => 'Неизвестный мораторий!'));
            }
        }
    }

    private function action_accept()
    {
        $order_id = $this->request->post('order_id', 'integer');
        if (!$order_id) {
            $this->json_output(['error' => 'Неизвестная заявка!']);
        }

        $order = $this->orders->get_order($order_id);
        if (!$order) {
            $this->json_output(['error' => 'Неизвестная заявка!']);
        }

        $now = date('Y-m-d H:i:s');

        $this->db->begin_transaction();

        $this->orders->assignManager($order_id, $this->manager->id);
        if ($this->db->affected_rows() === 0) {
            $this->db->rollback();

            $this->json_output([
                'error' => 'Заявка уже принята другим менеджером!',
            ]);
        }
        else {
            $this->orders->updateOrderExternal($order_id);

            $this->changelogs->add_changelog([
                'manager_id' => $this->manager->id,
                'created'    => $now,
                'type'       => 'status',
                'old_values' => serialize(['manager_id' => null]),
                'new_values' => serialize(['manager_id' => $this->manager->id]),
                'order_id'   => $order_id,
            ]);

            //LOGGING
            $this->db->query("
			INSERT INTO 
				verification_stats 
			SET 
				order_id='" . $order_id . "', 
				dates='" . date("Y-m-d H:i:s") . "',  
				manager_id='" . $this->manager->id . "',
				start_status='0'
		");

            $this->db->commit();

            $order = $this->orders->get_order($order_id);

            $this->soap->update_status_1c($order->id_1c, 'Рассматривается', $this->manager->name_1c, $order->amount, $order->percent);
            $this->json_output(['success' => 'Заявка принята!']);
        }
    }

    private function send_order_1c($order_id)
    {
        $order = $this->orders->get_order($order_id);

        //Отправка заявки в 1с со всеми заполнеными клиентом данными на 1, 2, 3 этапах
        $loan = array(
            'lastname' => (string)$order->lastname,
            'firstname' => (string)$order->firstname,
            'patronymic' => (string)$order->patronymic,
            'birth' => (string)$order->birth,
            'phone_mobile' => (string)$order->phone_mobile,
            'email' => (string)$order->email,
            'passport_serial' => (string)$order->passport_serial,
            'passport_date' => (string)$order->passport_date,
            'subdivision_code' => (string)$order->subdivision_code,
            'passport_issued' => (string)$order->passport_issued,

            'АдресРегистрацииИндекс' => (string)$order->Regindex,
            'Regregion' => (string)trim($order->Regregion . ' ' . $order->Regregion_shorttype),
            'Regdistrict' => (string)$order->Regdistrict,
            'Regcity' => (string)trim($order->Regcity . ' ' . $order->Regcity_shorttype),
            'Reglocality' => '',
            'Regstreet' => (string)trim($order->Regstreet . ' ' . $order->Regstreet_shorttype),
            'Regbuilding' => (string)$order->Regbuilding,
            'Reghousing' => (string)$order->Reghousing,
            'Regroom' => (string)$order->Regroom,

            'АдресФактическогоПроживанияИндекс' => (string)$order->Faktindex,
            'Faktregion' => (string)trim($order->Faktregion . ' ' . $order->Faktregion_shorttype),
            'Faktdistrict' => (string)$order->Faktdistrict,
            'Faktcity' => (string)trim($order->Faktcity . ' ' . $order->Faktcity_shorttype),
            'Faktlocality' => '',
            'Faktstreet' => (string)trim($order->Faktstreet . ' ' . $order->Faktstreet_shorttype),
            'Faktbuilding' => (string)$order->Faktbuilding,
            'Fakthousing' => (string)$order->Fakthousing,
            'Faktroom' => (string)$order->Faktroom,

            'site_id' => $order->order_id,
            'partner_id' => '',
            'partner_name' => 'Boostra',

            'amount' => (string)$order->amount,
            'period' => (string)$order->period,

            'utm_source' => $order->utm_source,
            'utm_medium' => $order->utm_medium,
            'utm_campaign' => $order->utm_campaign,
            'utm_content' => $order->utm_content,
            'utm_term' => $order->utm_term,
            'webmaster_id' => $order->webmaster_id,
            'click_hash' => $order->click_hash,

            'id' => '',
            'Car' => '',

            'МестоРождения' => (string)$order->birth_place,
            'ГородскойТелефон' => isset($order->landline_phone) ? (string)$order->landline_phone : '',
            'Пол' => (string)$order->gender,

            'СфераРаботы' => (string)$order->work_scope,

            'ДоходОсновной' => isset($order->income_base) ? (string)$order->income_base : '',
            'ДоходДополнительный' => isset($order->income_additional) ? (string)$order->income_additional : '',
            'ДоходСемейный' => isset($order->income_family) ? (string)$order->income_family : '',
            'ФинансовыеОбязательства' => isset($order->obligation) ? (string)$order->obligation : '',
            'ПлатежиПоКредитамВМесяц' => isset($order->other_loan_month) ? (string)$order->other_loan_month : '',
            'СколькоКредитов' => isset($order->other_loan_count) ? (string)$order->other_loan_count : '',
            'КредитнаяИстория' => isset($order->credit_history) ? (string)$order->credit_history : '',
            'МаксимальноОдобренныйРанееКредит' => isset($order->other_max_amount) ? (string)$order->other_max_amount : '',
            'ПоследнийОдобренныйРанееКредит' => isset($order->other_last_amount) ? (string)$order->other_last_amount : '',
            'БылоЛиБанкротство' => isset($order->bankrupt) ? (string)$order->bankrupt : '',
            'Образование' => isset($order->education) ? (string)$order->education : '',
            'СемейноеПоложение' => isset($order->marital_status) ? (string)$order->marital_status : '',
            'КоличествоДетей' => isset($order->childs_count) ? (string)$order->childs_count : '',
            'НаличиеАвтомобиля' => isset($order->have_car) ? (string)$order->have_car : '',
            'ВК' => isset($order->social_vk) ? (string)$order->social_vk : '',
            'Инст' => isset($order->social_inst) ? (string)$order->social_inst : '',
            'Фейсбук' => isset($order->social_fb) ? (string)$order->social_fb : '',
            'ОК' => isset($order->social_ok) ? (string)$order->social_ok : '',

            'ServicesSMS' => $order->service_sms,
            'ServicesInsure' => $order->service_insurance,
            'ServicesReason' => $order->service_reason,
        );

        /** Доделать эти параметры **/
        $loan['ОтказНаСайте'] = '';
        $loan['ПричинаОтказаНаСайте'] = '';

        $contact_person_name = array();
        $contact_person_phone = array();
        $contact_person_relation = array();
        if ($contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $order->user_id))) {
            foreach ($contactpersons as $contactperson) {
                $contact_person_name[] = (string)$contactperson->name;
                $contact_person_phone[] = (string)$contactperson->phone;
                $contact_person_relation[] = (string)$contactperson->relation;
            }
        }


        $loan['КонтактноеЛицоФИО'] = json_encode($contact_person_name);
        $loan['КонтактноеЛицоТелефон'] = json_encode($contact_person_phone);
        $loan['КонтактноеЛицоРодство'] = json_encode($contact_person_relation);

        if ($order->work_scope == 'Пенсионер') {
            $loan['Занятость'] = '';
            $loan['Профессия'] = '';
            $loan['МестоРаботы'] = '';
            $loan['СтажРаботы'] = '';
            $loan['ШтатРаботы'] = '';
            $loan['ТелефонОрганизации'] = '';
            $loan['ФИОРуководителя'] = '';

            $loan['АдресРаботы'] = '';
        } else {
            $loan['Занятость'] = isset($order->employment) ? (string)$order->employment : '';
            $loan['Профессия'] = isset($order->profession) ? (string)$order->profession : '';
            $loan['МестоРаботы'] = isset($order->workplace) ? (string)$order->workplace : '';
            $loan['СтажРаботы'] = isset($order->experience) ? (string)$order->experience : '';
            $loan['ШтатРаботы'] = isset($order->work_staff) ? (string)$order->work_staff : '';
            $loan['ТелефонОрганизации'] = isset($order->work_phone) ? (string)$order->work_phone : '';
            $loan['ФИОРуководителя'] = isset($order->workdirector_name) ? (string)$order->workdirector_name : '';

            $loan['АдресРаботы'] = $order->Workindex . ' ' . $order->Workregion . ', ' . $order->Workcity . ', ул.' . $order->Workstreet . ', д.' . $order->Workhousing;
            if (!empty($order->Workbuilding))
                $loan['АдресРаботы'] .= '/' . $order->Workbuilding;
            if (!empty($order->Workroom))
                $loan['АдресРаботы'] .= ', оф.' . $order->Workroom;
        }

        switch ($order->status):

            case '2':
                $loan['СтатусCRM'] = 'Одобрена';
                break;

            case '3':
                $loan['СтатусCRM'] = 'Отказ';
                break;

        endswitch;

        $loan['СуммаCRM'] = $order->amount;
        $loan['УИД_CRM'] = $order->tinkoff_id;

        $loan = (object)$loan;

        $resp = $this->soap->send_loan($loan);

        if (!empty($resp->return->id_zayavka)) {
            $this->orders->update_order($order_id, array('1c_id' => $resp->return->id_zayavka));

            $soap = $this->soap->get_uid_by_phone($order->phone_mobile);
            if (!empty($soap->result) && !empty($soap->uid)) {
                $this->users->update_user($order->user_id, array('UID' => $soap->uid));
            }
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($loan, $resp);echo '</pre><hr />';
    }

    private function action_approve()
    {
        $order_id = $this->request->post('order_id', 'integer');

        $update = array(
            'status' => 2,
            'manager_id' => $this->manager->id,
            'approve_date' => date('Y-m-d H:i:s'),
        );

        $is_order_decision_with_hyper_c = $this->request->post('is_order_decision_with_hyper_c');
        if (isset($is_order_decision_with_hyper_c)) {
            $this->order_data->set($order_id, $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C, (int)$is_order_decision_with_hyper_c);
        }

        $old_order = $this->orders->get_order($order_id);

        if (!empty($old_order)) {
            $result = $this->checkSspAndChReports($old_order);

            if (!empty($result['status']) && $result['status'] === 'error') {
                $this->json_output(['error' => $result['message'] ?? '']);
            }

            if ((int)$old_order->status === $this->orders::ORDER_STATUS_CRM_REJECT) {
                $this->json_output([
                    'error' => 'Ошибка! Отказ по заявке'
                ]);
            }

            $isOrderWithinMplResult = $this->isOrderPdnWithinMpl($old_order);
            if (empty($isOrderWithinMplResult['success'])) {
                $this->json_output([
                    'error' => $isOrderWithinMplResult['error'] ?? 'Ошибка при проверке вхождения ПДН в МПЛ. Обратитесь в тех поддержку'
                ]);
            }
        }

        $old_values = array();
        foreach ($update as $key => $val) {
            if ($old_order->$key != $val) {
                $old_values[$key] = $old_order->$key;
                $old_order->$key = $val;
            }
        }

        $log_update = array();
        foreach ($update as $k => $u) {
            if (isset($old_values[$k])) {
                $log_update[$k] = $u;
            }
        }

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => $order_id,
        ));

        //LOGGING
        $this->db->query("
			INSERT INTO 
				verification_stats 
			SET 
				order_id='" . $order_id . "', 
				dates='" . date("Y-m-d H:i:s") . "',  
				manager_id='" . $this->manager->id . "',
				start_status='1'
		");


        $this->orders->update_order($order_id, $update);

        $order = $this->orders->get_order((int)$order_id);

        $this->soap->update_status_1c($order->id_1c, 'Одобрено', $this->manager->name_1c, $order->amount, $order->percent, '', 0, $order->period);

//        $this->soap->set_order_manager($order->id_1c, $this->manager->name_1c);

        //отправка смс одобрение
        $sms_approve_status = $this->settings->sms_approve_status;
        if (!empty($sms_approve_status)) {
            $site_id = $this->users->get_site_id_by_user_id($order->user_id);
            $template = $this->sms->get_template($this->sms::AUTO_APPROVE_TEMPLATE_NOW, $site_id);
            $text_message = strtr($template->template, [
                '{{firstname}}' => $order->firstname,
                '{{amount}}' => $order->approve_amount ?: $order->amount,
            ]);

            $resp = $status = $this->smssender->send_sms($order->phone_mobile, $text_message, $site_id);
            $this->sms->add_message(
                [
                    'user_id' => $order->user_id,
                    'order_id' => $order->order_id,
                    'phone' => $order->phone_mobile,
                    'message' => $text_message,
                    'created' => date('Y-m-d H:i:s'),
                    'send_status' => $resp[1],
                    'delivery_status' => '',
                    'send_id' => $resp[0],
                    'type' => $this->smssender::TYPE_AUTO_APPROVE_ORDER,
                ]
            );

            if ($status) {
                $this->db->query("INSERT INTO sms_log SET phone='" . $order->phone_mobile . "', status='" . $status[1] . "', dates='" . date("Y-m-d H:i:s") . "', sms_id='" . $status[0] . "'");
            }
        }
        //END отправка смс

//        $this->send_order_1c($order_id);
        $this->users->update_loan_funnel_report(
            (int)$order->order_id,
            (int)$order->user_id,
            [
                'approved' => true,
                'approved_date' => date("Y-m-d")
            ]
        );

        $this->finroznica->send_user($this->users->get_user($old_order->user_id));
        if ($order->utm_source == 'vibery') {
            $this->post_back->sendApproveOrder($order);
        }

        $this->cross_orders->create($order->order_id);

        // Если займ автовыдача и есть промокод
        if ($order->loan_type != Orders::LOAN_TYPE_IL && $promocode_id = $this->order_data->read($order->order_id, 'promocode_id')) {
            $promocode = $this->promocodes->getInfoById($promocode_id);
            $this->promocodes->apply($order, $promocode);
        }

        $this->design->assign('order', $order);
    }

    private function isOrderPdnWithinMpl(stdClass $order): array
    {
        // 1. Если дев, то не считаем ПДН
        if ($this->helpers->isDev()) {
            return ['success' => true];
        }

        // 2. Если тестовый пользователь, то не считаем ПДН
        if ($this->user_data->isTestUser((int)$order->user_id)) {
            return ['success' => true];
        }

        $user = $this->users->get_user($order->user_id);
        if (empty($user)) {
            $this->logging(__METHOD__, '', 'Пользователь не найден', ['order' => $order, 'user' => $user], self::LOG_FILE);
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }

        $this->settings->setSiteId($user->site_id);
        $settings = $this->settings->organization_switch;

        // 3. Если ручеек выключен
        if (empty($settings['enabled'])) {
            $this->logging(__METHOD__, '', 'Ручеек отключен', ['order_id' => $order->order_id], self::LOG_FILE);
            return ['success' => true];
        }

        $orderOrgSwitchResult = $this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_RESULT);
        $orderOrgSwitchParentOrderId = $this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);

        // 4. Если нет решения ручейка, то просим верификатора отказать по заявке
        if (empty($orderOrgSwitchResult) && empty($orderOrgSwitchParentOrderId)) {
            $this->logging(__METHOD__, '', 'Проверка в ручейке еще не завершена', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return ['success' => false, 'error' => 'Ожидайте завершения проверки смены организации'];
        }

        $pdnCalculation = $this->pdnCalculation->getPdnRow((int)$order->order_id);

        $this->logging(__METHOD__, '', 'Взят расчет ПДН для проверки', ['order_id' => (int)$order->order_id, 'pdn_calculation' => $pdnCalculation], self::LOG_FILE);

        $isRcl = (bool)$this->order_data->read((int)$order->order_id, $this->order_data::RCL_LOAN);

        // 5. Если не найден расчет ПДН, то считаем ПДН
        if (empty($pdnCalculation)) {
            if (!empty($isRcl)) {
                $this->logging(__METHOD__, '', 'Заявка - ВКЛ, нет расчета ПДН, запрещаем одобрение', ['order_id' => (int)$order->order_id], self::LOG_FILE);
                return ['success' => false, 'error' => 'Заявка - ВКЛ, нет расчета ПДН. Обратитесь в тех поддержку'];
            } else {
                $this->logging(__METHOD__, '', 'Не найден расчет до выдачи. Запускаем расчет ПДН', ['order_id' => (int)$order->order_id], self::LOG_FILE);
                return $this->calculatePdn($order);
            }
        }

        $pdnCalculationResult = json_decode($pdnCalculation->result);

        // 6. Если заявка - ВКЛ, то не проверяем ПДН
        if (!empty($isRcl)) {
            if (isset($pdnCalculationResult->dbi)) {
                $this->logging(__METHOD__, '', 'Заявка - ВКЛ, не проверяем ПДН, разрешаем одобрение', ['order_id' => $order->order_id], self::LOG_FILE);
                return ['success' => true];
            } else {
                $this->logging(__METHOD__, '', 'Заявка - ВКЛ, некорректный ПДН, запрещаем одобрение', ['order_id' => (int)$order->order_id], self::LOG_FILE);
                return ['success' => false, 'error' => 'Заявка - ВКЛ, некорректный ПДН. Обратитесь в тех поддержку'];
            }
        }

        // 7. Если некорректный расчет ПДН, то пересчитываем ПДН
        if (!isset($pdnCalculationResult->pti_percent)) {
            $this->logging(__METHOD__, '', 'Некорректный расчет ПДН до выдачи. Запускаем новый расчет ПДН', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return $this->calculatePdn($order);
        }

        // 8. Если не вошли в МПЛ, то просим верификатора отказать по заявке
        if (empty($pdnCalculationResult->is_within_mpl)) {
            $this->logging(__METHOD__, '', 'Не вошли в МПЛ. Просим верификатора отказать по заявке', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return ['success' => false, 'error' => 'Не вошли в МПЛ из-за высокого ПДН. Переведите заявку в тех отказ'];
        }

        $lastSuccessAxiScoring = $this->scorings->getLastScoring([
            'order_id' => (int)$order->order_id,
            'type' => $this->scorings::TYPE_AXILINK_2,
            'status' => $this->scorings::STATUS_COMPLETED,
            'success' => 1
        ]);

        $this->logging(__METHOD__, '', 'Взят скоринг акси для проверки', ['order_id' => (int)$order->order_id, 'last_success_axi_scoring' => $lastSuccessAxiScoring], self::LOG_FILE);

        // 9. Если нет успешного акси, то просим верификатора перезапустить акси
        if (empty($lastSuccessAxiScoring)) {
            $this->logging(__METHOD__, '', 'Не найден успешный акси. Просим верификатора перезапустить акси', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return ['success' => false, 'error' => 'Не найден успешно выполненный одобренный акси. Перезапустите акси'];
        }

        $lastSuccessAxiScoringDate = date('Y-m-d', strtotime($lastSuccessAxiScoring->end_date));

        // 10. Если нет актуального скоринга акси, то просим верификатора перезапустить акси
        if ($lastSuccessAxiScoringDate < date('Y-m-d', strtotime('-7 days'))) {
            $this->logging(__METHOD__, '', 'Нет актуального скоринга акси. Просим верификатора перезапустить акси', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return ['success' => false, 'error' => 'Нет актуального скоринга акси. Перезапустите скоринг акси'];
        }

        // 11. Если акси проводился позже расчета ПДН, то запускаем новый расчет ПДН
        if (strtotime($lastSuccessAxiScoring->end_date) > strtotime($pdnCalculation->date_create)) {
            $this->logging(__METHOD__, '', 'Акси завершен позже расчета ПДН. Запускаем новый расчет ПДН', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return $this->calculatePdn($order);
        }

        $this->logging(__METHOD__, '', 'Вошли в МПЛ по расчету ПДН. Одобряем заявку', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        return ['success' => true];
    }

    /**
     * 1. Если Фрида с КИ Фрида, то при расчете ПДН отчеты не запросим (запросим только, если неактуальны)
     * 2. Если РЗС с КИ Фрида (от исходной заявки), то при расчете ПДН запросим КИ РЗС
     * 3. Если РЗС без КИ, то по идее будет флаг $this->order_data::AXI_WITHOUT_CREDIT_REPORTS и отчеты не запросятся
     */
    private function calculatePdn(stdClass $order): array
    {
        $flags = [
            $this->pdnCalculation::CALCULATE_PDN_BEFORE_ISSUANCE => 1
        ];

        $this->logging(__METHOD__, '', 'Начат расчет ПДН до выдачи', ['order_id' => (int)$order->order_id, 'flags' => $flags], self::LOG_FILE);

        // Расчет ПДН до выдачи
        $pdnCalculationResult = $this->pdnCalculation->run($order->order_uid, $flags);

        $this->logging(__METHOD__, '', 'Завершен расчет ПДН до выдачи', ['order_id' => (int)$order->order_id, 'flags' => $flags, 'pdn_calculation_result' => $pdnCalculationResult], self::LOG_FILE);

        if (empty($pdnCalculationResult) || !isset($pdnCalculationResult->pti_percent)) {
            $this->logging(__METHOD__, '', 'Некорректный расчет ПДН. Просим верификатора обратиться в тех поддержку', ['order_id' => (int)$order->order_id, 'pdn_calculation_result' => $pdnCalculationResult], self::LOG_FILE);
            return ['success' => false, 'error' => 'Некорректный расчет ПДН. Если ошибка сохраняется, обратитесь в тех поддержку'];
        }

        if (empty($pdnCalculationResult->is_within_mpl)) {
            $this->logging(__METHOD__, '', 'Не вошли в МПЛ по ПДН. Просим верификатора перевести заявку в тех отказ', ['order_id' => (int)$order->order_id, 'pdn_calculation_result' => $pdnCalculationResult], self::LOG_FILE);
            return ['success' => false, 'error' => 'Не вошли в МПЛ по ПДН. Переведите заявку в тех отказ'];
        }

        $this->logging(__METHOD__, '', 'При новом расчете ПДН вошли в МПЛ. Одобряем заявку', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        return ['success' => true];
    }

    /**
     * Проверяет актуальность ССП и КИ отчетов при одобрении заявки
     *
     * @param stdClass $order
     * @return string[]
     */
    private function checkSspAndChReports(stdClass $order): array
    {
        if (!$this->report->needCheckReports((int)$order->order_id)) {
            return ['status' => 'success'];
        }

        $lastReportScoring = $this->scorings->getLastScoring([
            'order_id' => $order->order_id,
            'type' => $this->scorings::TYPE_REPORT,
        ]);

        if (empty($lastReportScoring)) {
            return [
                'status' => 'error',
                'message' => 'Скоринг проверки ССП и КИ отчетов не был добавлен. Запустите скоринг или отключите проверку отчетов. При необходимости перезапустите скоринг акси.'
            ];
        } elseif (in_array($lastReportScoring->status, [$this->scorings::STATUS_NEW, $this->scorings::STATUS_PROCESS, $this->scorings::STATUS_WAIT])) {
            return [
                'status' => 'error',
                'message' => 'Скоринг проверки ССП и КИ отчетов еще не завершен. Ожидайте завершения или отключите проверку отчетов. При необходимости перезапустите скоринг акси.'
            ];
        } elseif (empty($lastReportScoring->success)) {
            return [
                'status' => 'error',
                'message' => 'Скоринг проверки ССП и КИ отчетов не пройден. Перезапустите скоринг или отключите проверку отчетов. При необходимости перезапустите скоринг акси.'
            ];
        }

        // Если успешный скоринг
        $body = (string)$this->scorings->get_scoring_body($lastReportScoring->id);

        $body = json_decode($body);

        // Если скоринг пройден успешно, но нет body, значит на момент выполнения скоринга согласно настройкам проводить
        // проверку ССП и КИ отчетов не нужно было
        if (empty($body)) {
            return ['status' => 'success'];
        }

        $sspReportResult = $this->checkReport($body->SSP_NBKI_REPORT_DATE ?? '', (string)$this->axi::SSP_REPORT);
        $chReportResult = $this->checkReport($body->NBKI_REPORT_DATE ?? '', (string)$this->axi::CH_REPORT);

        if ($sspReportResult['status'] === 'error' && $chReportResult['status'] === 'error') {
            return [
                'status' => 'error',
                'message' => 'Неактуальные ССП и КИ отчеты. ' . $sspReportResult['message'] . ' ' . $chReportResult['message'] . ' Перезапустите скоринг или отключите проверку отчетов.'
            ];
        } elseif ($sspReportResult['status'] === 'error') {
            return [
                'status' => 'error',
                'message' => 'Неактуальный ССП отчет. ' . $sspReportResult['message'] . ' ' . $chReportResult['message'] . ' Перезапустите скоринг или отключите проверку отчетов.'
            ];
        } elseif ($chReportResult['status'] === 'error') {
            return [
                'status' => 'error',
                'message' => 'Неактуальный КИ отчет. ' . $sspReportResult['message'] . ' ' . $chReportResult['message'] . ' Перезапустите скоринг или отключите проверку отчетов.'
            ];
        }

        return ['status' => 'success'];
    }

    /**
     * Проверить актуальность отчета
     *
     * @param string $reportDate
     * @param string $reportType
     * @return string[]
     */
    private function checkReport(string $reportDate, string $reportType): array
    {
        try {
            $reportDateTime = new DateTimeImmutable($reportDate);
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Некорректная дата создания ' . $this->axi->getReportTypeRus($reportType) . ' отчета.'
            ];
        }

        $isReportRelevant = $reportDateTime->diff(new DateTimeImmutable())->format("%a") < $this->axi::REPORTS_RELEVANCE_MAX_DAYS;

        return [
            'status' => $isReportRelevant ? 'success' : 'error',
            'message' => $this->axi->getReportTypeRus($reportType) . ' отчет от ' . $reportDateTime->format('d.m.Y H:i:s') . '.'
        ];
    }

    private function action_reject()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $reason_id = $this->request->post('reason_id', 'integer');

        if ($reason_id === $this->reasons::REASON_HYPER_C) {
            $this->order_data->set($order_id, $this->order_data::IS_ORDER_DECISION_WITH_HYPER_C, 1);
        }

        $update = array(
            'status' => 3,
            'manager_id' => $this->manager->id,
            'reason_id' => $reason_id,
            'reject_date' => date('Y-m-d H:i:s'),
        );

        $old_order = $this->orders->get_order($order_id);
        $old_values = array();
        foreach ($update as $key => $val)
            if ($old_order->$key != $update[$key])
                $old_values[$key] = $old_order->$key;

        $log_update = array();
        foreach ($update as $k => $u)
            if (isset($old_values[$k]))
                $log_update[$k] = $u;

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => $order_id,
            'user_id' => $old_order->user_id,
        ));

        //LOGGING
        $this->db->query("
			INSERT INTO 
				verification_stats 
			SET 
				order_id='" . $order_id . "', 
				dates='" . date("Y-m-d H:i:s") . "',  
				manager_id='" . $this->manager->id . "',
				start_status='0'
		");

        $this->orders->update_order($order_id, $update);

	    $this->leadgid->reject_actions($order_id);

        $order = $this->orders->get_order((int)$order_id);

        $reason = $this->reasons->get_reason($reason_id);
        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $this->manager->name_1c, 0, 1, $reason->admin_name);

        $this->soap1c->send_credit_doctor($order->id_1c);

//        $this->soap->set_order_manager($order->id_1c, $this->manager->name_1c);

//        $this->send_order_1c($order_id);

        // добавляем заявку на инд рассмотрение
        if ($this->settings->individual_settings['enabled']) {
            // проверяем КИ
            if (empty($old_order->have_close_credits)) {
                // проверяем ЧС
                if (!$this->blacklist->in($old_order->user_id)) {
                    $this->individuals->add_order(array(
                        'order_id' => $order_id,
                        'user_id' => $old_order->user_id,
                        'paid' => 0,
                        'created' => date('Y-m-d H:i:s'),
                        'status' => 1
                    ));
                    $individual_added = 1;
                }
            }
        }

        // отправляем заявку на кредитного доктора
        if (empty($individual_added))
            $this->cdoctor->send_order($order_id);

        $this->design->assign('order', $order);
    }

    private function action_waiting()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $reason_id = $this->request->post('reason_id', 'integer');

        $update = array(
            'status' => 7,
            'manager_id' => $this->manager->id,
            'reason_id' => $reason_id,
        );

        $old_order = $this->orders->get_order($order_id);
        $old_values = array();
        foreach ($update as $key => $val)
            if ($old_order->$key != $update[$key])
                $old_values[$key] = $old_order->$key;

        $log_update = array();
        foreach ($update as $k => $u)
            if (isset($old_values[$k]))
                $log_update[$k] = $u;

        $this->changelogs->add_changelog(array(
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($old_values),
            'new_values' => serialize($log_update),
            'order_id' => $order_id,
        ));

        //LOGGING
        $this->db->query("
			INSERT INTO 
				verification_stats 
			SET 
				order_id='" . $order_id . "', 
				dates='" . date("Y-m-d H:i:s") . "',  
				manager_id='" . $this->manager->id . "',
				start_status='1'
		");

        $this->orders->update_order($order_id, $update);

        $order = $this->orders->get_order((int)$order_id);

        $this->design->assign('order', $order);
    }

    private function action_send_sms()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $template_id = $this->request->post('template_id', 'integer');

        $order = $this->orders->get_order((int)$order_id);
        $site_id = $this->users->get_site_id_by_user_id($order->user_id);

        $template = $this->sms->get_template($template_id, $site_id);

        $resp = $this->smssender->send_sms(
            $order->phone_mobile,
            $template->template,
            $site_id,
            1
        );

        $this->sms->add_message(array(
            'user_id' => $order->user_id,
            'order_id' => $order->order_id,
            'phone' => $order->phone_mobile,
            'message' => $template->template,
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
            'new_values' => $template->template,
            'order_id' => $order_id,
        ));


        $this->design->assign('order', $order);
    }

    private function action_amount()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');
        $amount = $this->request->post('amount', 'integer');
        $period = $this->request->post('period', 'integer');
        $card_id = $this->request->post('card_id', 'integer');
        $card_type = $this->request->post('card_type', 'string');
        $loan_type = $this->request->post('loan_type', 'string') == 'IL' ? 'IL' : 'PDL';

        $bankId = null;
        if ($card_type === 'bank') {
            $bankId = $card_id;
            $card_id = 0;
            $card_type = $this->orders::CARD_TYPE_SBP;
        }

        $order = new StdClass();
        $order->id = $order_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $order->amount = $amount;
        $order->period = $period;
        $order->card_id = $card_id;
        $order->card_type = $card_type;
        $order->loan_type = $loan_type;

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->b2p = $isset_order->b2p;

        $this->design->assign('order', $order);

        $amount_error = array();

        if (empty($amount))
            $amount_error[] = 'empty_amount';
        if (empty($period))
            $amount_error[] = 'empty_period';
        if (empty($card_type))
            $amount_error[] = 'Некорректный тип карты';

        if (!empty($amount)) {
            if ($amount > $this->orders::PDL_MAX_AMOUNT && $loan_type == 'PDL') {
                $amount_error[] = 'amount_limit';
            }
            if ($loan_type == 'IL') {
                if ($amount < $this->orders::IL_MIN_AMOUNT || $amount > $this->orders::IL_MAX_AMOUNT) {
                    $amount_error[] = 'Допустимая сумма данного типа займа от '.$this->orders::IL_MIN_AMOUNT.' до '.$this->orders::IL_MAX_AMOUNT;
                }
            }
        }

        if ($loan_type == 'PDL') {
            $max_pdl_period = $this->orders::PDL_MAX_PERIOD;
            if ($isset_order->utm_source == $this->orders::UTM_SOURCE_CROSS_ORDER) {
                $max_pdl_period = $this->orders::PDL_CROSS_ORDER_MAX_PERIOD;
            }

            if ($period > $max_pdl_period) {
                $amount_error[] = 'Срок не может быть более '.$this->orders::PDL_MAX_PERIOD.' дней для PDL займа';
            }
        }
        if ($loan_type == 'IL' && $period < $this->orders::IL_MIN_PERIOD) {
            $amount_error[] = 'Срок не может быть менее '.$this->orders::IL_MIN_PERIOD.' дней для IL займа';
        }

        // проверяем что бы выдача не была в таймауте
        if ($p2pcredits = $this->best2pay->get_p2pcredits(['order_id' => $order_id])) {
            $p2pcredit = end($p2pcredits);
            if ($p2pcredit->status == 'TIMEOUT' || $p2pcredit->status == 'ERROR' || $p2pcredit->status == 'PENDING') {
                $amount_error[] = 'timeout';
            }
        }

        if (!empty($isset_order->contract_id) && (int)$isset_order->amount !== (int)$amount) {
            $amount_error[] = 'signed_contract_amount';
        }

        // Если ВКЛ, то возвращаем сумму из $this->order_data::RCL_AMOUNT
        $isRcl = (bool)$this->order_data->read($order_id, $this->order_data::RCL_LOAN);
        if ($isRcl) {
            $rclMaxAmount = $this->order_data->read($order_id, $this->order_data::RCL_MAX_AMOUNT);

            if ($amount > (int)$rclMaxAmount) {
                $amount_error[] = 'Сумма займа не может превышать максимальный лимит ВКЛ ' . $rclMaxAmount . ' руб.';
            }
        }

        $user = $this->users->get_user((int)$user_id);
        if (empty($order->b2p)) {
            $card_list = $this->soap->get_card_list($user->UID);
        } else {
            $card_list = $this->best2pay->get_cards(array('user_id' => $user->id));
        }
        $this->design->assign('card_list', $card_list);

        if (empty($amount_error)) {

            $update = array(
                'amount' => $amount,
                'approve_amount' => $amount,
                'period' => $period,
                'card_id' => $card_id,
                'card_type' => $card_type,
                'loan_type' => $loan_type,
            );

            if ($loan_type == 'PDL') {
                $update['max_amount'] = 0;
                $update['min_period'] = 0;
                $update['max_period'] = 0;
            } elseif ($loan_type == 'IL') {
                $update['max_amount'] = $amount;
                $update['min_period'] = $period;
                $update['max_period'] = $period;
            }

            $credit_doctor = $this->credit_doctor->getUserCreditDoctor($order->order_id, $order->user_id);

            if (!empty($credit_doctor)) {
                $credit_doctor_price = $credit_doctor->amount;

                if ($amount + $credit_doctor_price < 30000) {
                    $amount += $credit_doctor_price;
                }
            }

            if (!empty($isset_order->contract_id)) {
                $this->contracts->update_contract($isset_order->contract_id, [
                    'card_id' => $card_id,
                    'period' => $period,
                ]);
            }

            // Если не удалось выдать деньги, то при смене карты происходит перевыдача
            if ($card_id != $isset_order->card_id && (int)$isset_order->status === $this->orders::ORDER_STATUS_CRM_NOT_ISSUED) {

                // Уменьшаем счетчик расчетов ПДН перед выдачей, чтобы не было отказа из-за его превышения
                $pdnCalculationAttempts = $this->order_data->read((int)$order->order_id, $this->order_data::PDN_CALCULATION_ATTEMPTS);
                if (!empty($pdnCalculationAttempts)) {
                    $pdnCalculationAttempts--;
                    $this->order_data->set((int)$order->order_id, $this->order_data::PDN_CALCULATION_ATTEMPTS, $pdnCalculationAttempts);
                }

                $update['status'] = $this->orders::ORDER_STATUS_SIGNED;
                $manager = $this->managers->get_manager($isset_order->manager_id);
                $res = $this->soap->update_status_1c($isset_order->id_1c, 'Одобрено', $manager->name_1c, $amount, $isset_order->percent, '', 0, $period);
            } else if (in_array($isset_order->status, [2]) && ($isset_order->amount != $amount || $isset_order->period != $period)) {
                $manager = $this->managers->get_manager($isset_order->manager_id);
                $res = $this->soap->update_status_1c($isset_order->id_1c, 'Одобрено', $manager->name_1c, $amount, $isset_order->percent, '', 0, $period);
            }

            $old_values = array();
            foreach ($update as $key => $val)
                if ($isset_order->$key != $update[$key])
                    $old_values[$key] = $isset_order->$key;

            $log_update = array();
            foreach ($update as $k => $u)
                if (isset($old_values[$k]))
                    $log_update[$k] = $u;

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'period_amount',
                'old_values' => serialize($old_values),
                'new_values' => serialize($log_update),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->orders->update_order($order_id, $update);

            // проверим на наличие кросс-заявки и сменим карту
            if (!empty($log_update['card_id'])) {
                $this->updateCrossOrders($isset_order, [
                    'card_id' => $log_update['card_id'],
                ]);
            }

            if (!empty($log_update['card_type'])) {
                $this->updateCrossOrders($isset_order, [
                    'card_type' => $log_update['card_type'],
                ]);
            }

            if (!empty($log_update['card_id']) || !empty($log_update['card_type']) || !empty($bankId)) {
                // Обновляем/удаляем выбранный банк
                $this->order_data->set($order_id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE, $bankId);
            }
        }

        $this->design->assign('amount_error', $amount_error);

        $order_data = $this->order_data->readAll($order_id);

        $this->design->assign('order_data', $order_data);
        $this->design->assign('self_employee_document', $this->prepareSelfEmployeeDocument($order_data));

        $scoring_types = [];
        foreach ($this->scorings->get_types() as $st) {
            $scoring_types[$st->id] = $st;
        }

        $sbpAccounts = $this->sbpAccount->getSbpAccountsByUserId((int)$user->id);
        if (!empty($sbpAccounts)) {
            $b2pBanksId = array_column($sbpAccounts, 'member_id');
            $b2pBanks = $this->b2p_bank_list->get([
                'id' => $b2pBanksId
            ]);

            $b2pBanks = array_column($b2pBanks, null, 'id');

            foreach ($sbpAccounts as &$sbpAccount) {
                $sbpAccount->title = $b2pBanks[$sbpAccount->member_id]->title ?? '';
            }
            unset($sbpAccount);
        }

        $this->design->assign('sbp_accounts', $sbpAccounts);

        $this->design->assign('scoring_types', $scoring_types);
    }

    private function action_get_scorings()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $order_id = $this->request->post('order_id', 'integer');

        if (empty($order_id)) {
            echo json_encode(['error' => 'empty_order_id']);
            exit;
        }

        $order = $this->orders->get_order((int)$order_id);
        if (empty($order)) {
            echo json_encode(['error' => 'order_not_found']);
            exit;
        }

        $scoring_types = [];
        foreach ($this->scorings->get_types(['active' => 1]) as $st) {
            $scoring_types[$st->id] = $st;
        }

        $filtered_scorings_object = $this->scorings->get_scorings_by_order($order, $scoring_types);

        $this->design->assign('order', $order);
        $this->design->assign('scorings', $filtered_scorings_object->scorings);
        $this->design->assign('user_scorings', $filtered_scorings_object->user_scorings);
        $this->design->assign('scor_amount', $filtered_scorings_object->scor_amount);
        $this->design->assign('scor_period', $filtered_scorings_object->scor_period);
        $this->design->assign('scor_message', $filtered_scorings_object->scor_message);
        $this->design->assign('need_update_scorings', $filtered_scorings_object->need_update_scorings);
        $this->design->assign('inactive_run_scorings', $filtered_scorings_object->inactive_run_scorings);
        $this->design->assign('installment_scor_message', $filtered_scorings_object->installment_scor_message ?? '');
        $this->design->assign('installment_scor_amount', $filtered_scorings_object->installment_scor_amount ?? '');

        $order_data = $this->order_data->readAll($order_id);
        $this->design->assign('order_data', $order_data);
        $this->design->assign('self_employee_document', $this->prepareSelfEmployeeDocument($order_data));

        $this->design->assign('scoring_types', $scoring_types);
        $this->design->assign('has_hyper_c_scoring', $this->hasOrderHyperCScoring($order));
        
        $open_scorings = $this->request->get('open_scorings', 'integer') || $this->request->post('open_scorings', 'integer');
        $this->design->assign('open_scorings', $open_scorings);
        
        $result = [
            'scorings' => $filtered_scorings_object->scorings,
            'user_scorings' => $filtered_scorings_object->user_scorings,
            'scor_amount' => $filtered_scorings_object->scor_amount,
            'scor_period' => $filtered_scorings_object->scor_period,
            'scor_message' => $filtered_scorings_object->scor_message,
            'need_update_scorings' => $filtered_scorings_object->need_update_scorings,
            'inactive_run_scorings' => $filtered_scorings_object->inactive_run_scorings,
            'installment_scor_message' => $filtered_scorings_object->installment_scor_message ?? '',
            'installment_scor_amount' => $filtered_scorings_object->installment_scor_amount ?? '',
            'scorings_tables' => $this->design->fetch('order/scorings_tables.tpl'),
            'scorings_block' => $this->design->fetch('order/scorings_block.tpl'),
        ];

        header('Content-type: application/json');
        echo json_encode($result);
        exit;
    }

    private function action_get_credit_history()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $order_id = $this->request->post('order_id', 'integer');

        if (empty($order_id)) {
            echo json_encode(['error' => 'empty_order_id']);
            exit;
        }

        $order = $this->orders->get_order((int)$order_id);
        if (empty($order)) {
            echo json_encode(['error' => 'order_not_found']);
            exit;
        }

        $user = $this->users->get_user((int)$order->user_id);

        $sbpAccounts = $this->sbpAccount->getSbpAccountsByUserId((int)$user->id);
        if (!empty($sbpAccounts)) {
            $b2pBanksId = array_column($sbpAccounts, 'member_id');
            $b2pBanks = $this->b2p_bank_list->get([
                'id' => $b2pBanksId
            ]);

            $b2pBanks = array_column($b2pBanks, null, 'id');

            foreach ($sbpAccounts as &$sbpAccount) {
                $sbpAccount->title = $b2pBanks[$sbpAccount->member_id]->title ?? '';
            }
            unset($sbpAccount);
        }

        if (empty($order->b2p)) {
            $card_list = $this->soap->get_card_list($user->UID);
        } else {
            $card_list = $this->best2pay->get_cards(['user_id' => $user->id]);
        }
        if (!is_array($card_list)) {
            $card_list = [];
        }
        if ($this->manager->role == 'verificator_minus') {
            $card_list = array_filter($card_list, function ($item) {
                return empty($item->deleted) && empty($item->deleted_by_client) && $item->organization_id == $this->organizations::AKVARIUS_ID;
            });
        }
        $this->design->assign('card_list', $card_list);
        $this->design->assign('sbp_accounts', $sbpAccounts);
        $order->user = $user;
        $order->loan_history = $user->loan_history;

        // Получаем все заявки пользователя для отображения в истории
        $user_orders = $this->orders->get_orders(array('user_id' => $user->id));
        usort($user_orders, function ($a, $b) {
            return strtotime($a->date) - strtotime($b->date);
        });
        if ($this->manager->role == 'verificator_minus') {
            $user_orders = array_filter($user_orders, function ($item) {
                return $item->organization_id == $this->organizations::AKVARIUS_ID;
            });
        }

        $this->design->assign('user_orders', $user_orders);
        $this->design->assign('order', $order);
        $this->design->assign('config', $this->config);
        $this->design->assign('manager', $this->manager);

        $result = [
            'credit_history_html' => $this->design->fetch('order/credit_history.tpl'),
        ];

        header('Content-type: application/json');
        echo json_encode($result);
        exit;
    }

    /**
     * Загружает логи (changelogs) пользователя через AJAX.
     * Рендерит блок logs.tpl и возвращает его в JSON.
     */
    private function action_get_logs()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $order_id = $this->request->post('order_id', 'integer');

        if (empty($order_id)) {
            echo json_encode(['error' => 'empty_order_id']);
            exit;
        }

        $order = $this->orders->get_order((int)$order_id);
        if (empty($order)) {
            echo json_encode(['error' => 'order_not_found']);
            exit;
        }

        $changelog_types = $this->changelogs->get_types();
        $this->design->assign('changelog_types', $changelog_types);

        $order_statuses = $this->orders->get_statuses();
        $this->design->assign('order_statuses', $order_statuses);

        $filter = [
            'user_id' => $order->user_id
        ];

        $user_orders = $this->orders->get_orders(['user_id' => $order->user_id]);
        $order_ids = [];
        if (!empty($user_orders)) {
            foreach ($user_orders as $order_row) {
                $order_ids[] = $order_row->order_id;
            }
            $filter['order_ids'] = $order_ids;
            $filter['filter_type'] = 'OR';
        }

        if ($search = $this->request->post('search')) {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }

        if ($sort = $this->request->post('sort', 'string')) {
            $filter['sort'] = $sort;
            $this->design->assign('sort', $sort);
        }

        $changelogs = $this->changelogs->get_changelogs($filter);

        $managers = [];
        foreach ($this->managers->get_managers() as $m) {
            $managers[$m->id] = $m;
        }

        if ($changelogs) {
            foreach ($changelogs as $key => &$changelog) {
                if (in_array($changelog->type, ChangelogsView::LOGS_TYPE_TO_HIDE_LOGS) && in_array($changelog->manager_id, ChangelogsView::MANAGERS_TO_HIDE_LOGS)) {
                    unset($changelogs[$key]);
                    continue;
                }

                if (!empty($changelog->manager_id) && !empty($managers[$changelog->manager_id])) {
                    $changelog->manager = $managers[$changelog->manager_id];
                }

                if ($changelog->new_values instanceof stdClass) {
                    $changelog->new_values = (array)$changelog->new_values;
                }
                if ($changelog->old_values instanceof stdClass) {
                    $changelog->old_values = (array)$changelog->old_values;
                }
                if (is_string($changelog->new_values)) {
                    if (($changelog->new_values == '') || ($changelog->new_values == 'a:0:{}')) {
                        $changelog->new_values = [];
                    } else {
                        $changelog->new_values = ['string' => $changelog->new_values];
                    }
                }
                if (is_string($changelog->old_values)) {
                    if (($changelog->old_values == '') || ($changelog->old_values == 'a:0:{}')) {
                        $changelog->old_values = [];
                    } else {
                        $changelog->old_values = ['string' => $changelog->old_values];
                    }
                }
                if (!is_array($changelog->new_values)) {
                    $changelog->new_values = ['**error_decode_log**' => 'ошибка декодирования логов'];
                }
                if (!is_array($changelog->old_values)) {
                    $changelog->old_values = ['**error_decode_log**' => 'ошибка декодирования логов'];
                }

                foreach ($changelog->new_values as $k => $value) {
                    if (!isset($changelog->old_values[$k])) {
                        $changelog->old_values[$k] = '';
                    }
                }

                if (isset($changelog->old_values['manager_id']) && !empty($changelog->old_values['manager_id'])) {
                    $changelog->old_values['manager_id'] = $managers[$changelog->old_values['manager_id']]->name ?? $changelog->old_values['manager_id'];
                }
                if (isset($changelog->new_values['manager_id']) && !empty($changelog->new_values['manager_id'])) {
                    $changelog->new_values['manager_id'] = $managers[$changelog->new_values['manager_id']]->name ?? $changelog->new_values['manager_id'];
                }
            }
            unset($changelog);
        }

        $this->design->assign('changelogs', $changelogs);
        $this->design->assign('managers', $managers);
        $this->design->assign('order', $order);
        $this->design->assign('manager', $this->manager);

        $result = [
            'logs_html' => $this->design->fetch('order/logs.tpl'),
        ];

        header('Content-type: application/json');
        echo json_encode($result);
        exit;
    }

    private function action_get_documents()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $order_id = $this->request->post('order_id', 'integer');

        if (empty($order_id)) {
            echo json_encode(['error' => 'empty_order_id']);
            exit;
        }

        $order = $this->orders->get_order((int)$order_id);
        if (empty($order)) {
            echo json_encode(['error' => 'order_not_found']);
            exit;
        }

        $user = $this->users->get_user((int)$order->user_id);
        $order->user = $user;
        $order->loan_history = $user->loan_history;
        $user_balance = $this->users->get_user_balance(intval($user->id));

        $uid_docs = [];
        if ($user_balance->zaim_number != '' && $user_balance->zaim_number != 'Нет открытых договоров' && $user_balance->zaim_number != 'Ошибка') {
            if ($docs = $this->soap->get_documents($user_balance->zaim_number)) {
                foreach ($docs as $doc) {
                    $uid_doc = new StdClass();
                    $uid_doc->name = $doc->ТипДокумента;
                    $uid_doc->uid = $doc->УИДХранилища;
                    $uid_doc->hide = $doc->НеОтображать;

                    $uid_docs[] = $uid_doc;
                }
            }
        }

        $documents = [];
        $documents_not_types = [Documents::CONTRACT_DELETE_USER_CABINET];
        foreach ($this->documents->get_documents(['order_id' => $order_id, 'user_id' => $order->user_id]) as $d) {
            if (!in_array($d->type, $documents_not_types)) {
                $d->doc_url = $this->users->getLookerDomain($this->users->get_site_id_by_user_id($order->user_id));
                $documents[] = $d;
            }
        }

        $recurrent_doc_params = [
            'user_id' => $order->user_id,
            'params' => [
                'asp' => $order->accept_sms,
                'date' => $order->confirm_date,
            ],
        ];

        $removed_user_cabinet_docs = $this->documents->get_documents(
            [
                'type' => Documents::CONTRACT_DELETE_USER_CABINET,
                'user_id' => $order->user_id,
            ]
        );

        $additional_reference = [];
        if (!empty($order->loan_history)) {
            foreach ($order->loan_history as $item) {
                if (empty($item->number)) {
                    continue;
                }

                if (strpos($item->number, (string)$order_id) !== false) {
                    $additionalDoc = $this->soap->contractForms("$item->number");
                    if (!empty($additionalDoc) && !empty($additionalDoc['return'])) {
                        if (json_decode($additionalDoc['return'], true)) {
                            $additional_reference = [
                                'loanId' => $item->number
                            ];
                            break;
                        }
                    }
                }
            }
        }

        $mfoParams = (array)$this->organizations->get_organization($order->organization_id);
        $recurrent_doc_params['params'] = array_merge($recurrent_doc_params['params'], $mfoParams);

        $this->design->assign('order', $order);
        $this->design->assign('user', $user);
        $this->design->assign('config', $this->config);
        $this->design->assign('manager', $this->manager);
        $this->design->assign('uid_docs', $uid_docs);
        $this->design->assign('documents', $documents);
        $this->design->assign('recurrent_doc_name', $mfoParams['short_name']);
        $this->design->assign('recurrent_doc_url_params', http_build_query($recurrent_doc_params));
        $this->design->assign('removed_user_cabinet_docs', $removed_user_cabinet_docs);
        $this->design->assign('additional_reference', $additional_reference);
        $this->design->assign('creditworthiness_assessment', $this->hasOrderCreditworthinessAssessmentDocument($order));
        $this->design->assign('zaimNumber', $user_balance->zaim_number);
        $this->design->assign('asp_zaim_list', $this->users->getZaimListAsp((string)$user_balance->zaim_number));
        $this->design->assign('uploaded_docs', $this->documents->get_uploaded_documents($order->user_id, $order->order_id));

        $result = [];
        if ($this->manager->role == 'verificator_minus') {
            $minus_docs = $this->get_docs_for_minus($order, [
                'documents' => $documents,
                'uid_docs' => $uid_docs,
            ]);
            $this->design->assign('minus_docs', $minus_docs);
            $result['documents_minus_html'] = $this->design->fetch('order/documents_minus.tpl');
        } else {
            $result['documents_html'] = $this->design->fetch('order/documents.tpl');
        }

        header('Content-type: application/json');
        echo json_encode($result);
        exit;
    }

    private function action_get_comments()
    {
        if (ob_get_level()) {
            ob_clean();
        }
        error_reporting(0);
        ini_set('display_errors', 'Off');

        $order_id = $this->request->post('order_id', 'integer');

        if (empty($order_id)) {
            echo json_encode(['error' => 'empty_order_id']);
            exit;
        }

        $order = $this->orders->get_order((int)$order_id);
        if (empty($order)) {
            echo json_encode(['error' => 'order_not_found']);
            exit;
        }

        $commentsData = $this->comments->get_comments(array('order_id' => $order->order_id));

        foreach ($commentsData as $key => $comment) {
            if (in_array($comment->block, ChangelogsView::LOGS_TYPE_TO_HIDE_LOGS) && in_array($comment->manager_id, ChangelogsView::MANAGERS_TO_HIDE_LOGS)) {
                unset($commentsData[$key]);
            }
        }

        $comment_blocks = $this->comments->get_blocks();

        $managers = [];
        foreach ($this->managers->get_managers() as $manager) {
            $managers[$manager->id] = $manager;
        }

        // Получаем комменты из 1С
        $comments_1c = [];
        $blacklist_comments = [];

        if ($user = $this->users->get_user((int)$order->user_id)) {
            if ($comments_1c_response = $this->soap->get_comments($user->UID, $user->site_id)) {
                if (!empty($comments_1c_response->Комментарии)) {
                    foreach ($comments_1c_response->Комментарии as $comm) {
                        $comment_1c_item = new StdClass();

                        $comment_1c_item->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
                        $comment_1c_item->date = date('d.m.Y', strtotime($comm->Дата));
                        $comment_1c_item->time = date('H:i:s', strtotime($comm->Дата));
                        $comment_1c_item->text = $comm->Комментарий;
                        $comment_1c_item->block = $comm->Блок;
                        $comment_1c_item->color = $comm->color;

                        $comments_1c[] = $comment_1c_item;
                    }

                    usort($comments_1c, function ($a, $b) {
                        return strtotime($b->created) - strtotime($a->created);
                    });
                }

                if (!empty($comments_1c_response->ЧС)) {
                    foreach ($comments_1c_response->ЧС as $comm) {
                        $blacklist_comment = new StdClass();

                        $blacklist_comment->created = date('Y-m-d H:i:s', strtotime($comm->Дата));
                        $blacklist_comment->date = date('d.m.Y', strtotime($comm->Дата));
                        $blacklist_comment->time = date('H:i:s', strtotime($comm->Дата));
                        $blacklist_comment->text = $comm->Комментарий;
                        $blacklist_comment->block = $comm->Блок;

                        $blacklist_comments[] = $blacklist_comment;
                    }

                    usort($blacklist_comments, function ($a, $b) {
                        return strtotime($b->created) - strtotime($a->created);
                    });
                }
            }
        }

        $this->design->assign('order', $order);
        $this->design->assign('commentsData', $commentsData);
        $this->design->assign('comment_blocks', $comment_blocks);
        $this->design->assign('managers', $managers);
        $this->design->assign('comments_1c', $comments_1c);
        $this->design->assign('blacklist_comments', $blacklist_comments);

        // Группируем комментарии по блокам для отображения на вкладке заявки
        $comments_by_block = [];
        foreach ($commentsData as $comment) {
            if (!isset($comments_by_block[$comment->block])) {
                $comments_by_block[$comment->block] = [];
            }
            $comments_by_block[$comment->block][] = $comment;
        }

        // Рендерим HTML для каждого блока комментариев
        $comment_blocks_html = [];
        $blocks_to_render = [
            'order', 
            'amount', 
            'services', 
            'autodebit', 
            'files', 
            'socials',
            'passport',
            'workaddress',
            'work',
            'contactpersons',
            'faktaddress',
            'regaddress',
            'personal'
        ];
        foreach ($blocks_to_render as $block) {
            $this->design->assign('block_comments', $comments_by_block[$block] ?? []);
            $comment_blocks_html[$block] = $this->design->fetch('order/comment_block.tpl');
        }

        $result = [
            'comments_html' => $this->design->fetch('order/comments.tpl'),
            'comment_blocks' => $comment_blocks_html,
        ];

        $this->json_output($result);
    }

    /**
     * Получает список доп. услуг (иншуры, кредитный доктор, оракул и т.д.) через AJAX.
     * Рендерит шаблон design/manager/html/order/insures.tpl.
     */
    private function action_get_insures()
    {
        if (!ob_get_level()) {
            ob_start();
        }

        $order_id = $this->request->post('order_id', 'integer');
        if (empty($order_id)) {
            $this->json_output(['error' => 'empty_order_id']);
        }

        $order = $this->orders->get_order($order_id);
        if (empty($order)) {
            $this->json_output(['error' => 'order_not_found']);
        }

        $user = $this->users->get_user((int)$order->user_id);

        // Загрузка списка карт для возврата на карту
        if (!empty($order->b2p)) {
            $card_list = $this->best2pay->get_cards(['user_id' => $user->id]);
        }
        if (!is_array($card_list)) {
            $card_list = [];
        }
        $card_list = array_filter($card_list, function ($item) {
            return !empty($item->pan);
        });
        $this->design->assign('card_list', $card_list);

        if (empty($order->b2p)) {
            $insurances = $this->insurances->get_insurances(['order_id' => $order_id]);
            $this->design->assign('insurances', $insurances);
        } else {
            $insures = $this->best2pay->get_order_insures($order_id);
            foreach ($insures as $insure) {
                $insure->insurance = $this->insurances->get_order_insurance($order_id);
            }
            $this->design->assign('insures', $insures);
        }

        // Кредитный доктор
        $credit_doctor_items = $this->credit_doctor->getAllSuccessUserCreditDoctorByOrder($order_id);
        if (!is_array($credit_doctor_items)) {
            $credit_doctor_items = [];
        }
        array_walk($credit_doctor_items, function ($item) {
            $item->days_since_purchase = floor((time() - strtotime($item->date_added)) / 86400);
            $item->return_amount = (int)$item->amount_total_returned;
            $item->fully_returned = $item->amount == $item->amount_total_returned;
            $item->amount_left = $item->amount - $item->amount_total_returned;
            if (!empty($item->return_status) && isset($item->return_transaction_id)) {
                $type = $item->is_penalty
                    ? [$this->receipts::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR, $this->receipts::PAYMENT_TYPE_RETURN_PENALTY_CREDIT_DOCTOR_CHEQUE]
                    : [$this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR, $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_CHEQUE, $this->receipts::PAYMENT_TYPE_RETURN_CREDIT_DOCTOR_REQUISITES];
                $item->receipts = $this->receipts->getReturnedReceipts($item->order_id, $type);
                $item->return_receipt_url_download = $this->receipts->getReceiptUrlDownloadReturnCD($item->return_transaction_id);
                $item->return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
            }

            $item->policy_id = null;

            $credit_doctor_policy_docs = $this->documents->get_documents([
                'order_id' => $item->order_id,
                'user_id' => $item->user_id,
                'type' => Documents::CREDIT_DOCTOR_POLICY]);

            if (!empty($credit_doctor_policy_docs)) {
                foreach ($credit_doctor_policy_docs as $doc) {
                    $time_lag = abs(strtotime($item->date_added) - strtotime($doc->created));
                    if ($time_lag < 60 * 60) {
                        $item->policy_id = $doc->id;
                        break;
                    }
                }
            }
        });

        $this->design->assign('credit_doctor_items', $credit_doctor_items);

        // Звёздный Оракул
        $star_oracle_items = $this->star_oracle->getAllSuccessStarOracleByOrderId($order_id);
        if (!is_array($star_oracle_items)) {
            $star_oracle_items = [];
        }
        array_walk($star_oracle_items, function ($item) {
            $item->days_since_purchase = floor((time() - strtotime($item->date_added)) / 86400);
            $item->return_amount = (int)$item->amount_total_returned;
            $item->fully_returned = (int)$item->amount === (int)$item->amount_total_returned;
            $item->amount_left = $item->amount - $item->amount_total_returned;
            if (!empty($item->return_status) && isset($item->return_transaction_id)) {
                $item->receipts = $this->receipts->getReturnedReceiptsByPayment($item->order_id, $item->transaction_id, [$this->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE, $this->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE_CHEQUE, $this->receipts::PAYMENT_TYPE_RETURN_STAR_ORACLE_REQUISITES]);
                $item->return_receipt_url_download = $this->receipts->getReceiptUrlDownloadReturnCD($item->return_transaction_id);
                $item->return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
            }

            $item->policy_id = null;

            $star_oracle_policy_docs = $this->documents->get_documents([
                'order_id' => $item->order_id,
                'user_id' => $item->user_id,
                'type' => Documents::STAR_ORACLE_POLICY]);

            if (!empty($star_oracle_policy_docs)) {
                foreach ($star_oracle_policy_docs as $doc) {
                    $time_lag = abs(strtotime($item->date_added) - strtotime($doc->created));
                    if ($time_lag < 60 * 60) {
                        $item->policy_id = $doc->id;
                        break;
                    }
                }
            }
        });

        $this->design->assign('star_oracle_items', $star_oracle_items);

        $organizations = [];
        foreach ($this->organizations->getList() as $org) {
            $organizations[$org->id] = $org;
        }
        $this->design->assign('organizations', $organizations);

        // Мультиполис
        $multipolis_items = $this->multipolis->selectAll(['filter_order_id' => $order_id, 'filter_status' => 'SUCCESS']);
        if (!is_array($multipolis_items)) {
            $multipolis_items = [];
        }
        array_walk($multipolis_items, function ($item) {
            $item->days_since_purchase = floor((time() - strtotime($item->date_added)) / 86400);
            $item->return_amount = (int)$item->amount_total_returned;
            $item->fully_returned = $item->amount == $item->amount_total_returned;
            $item->amount_left = $item->amount - $item->amount_total_returned;
            if (!empty($item->return_status) && isset($item->return_transaction_id)) {
                $item->receipts = $this->receipts->getReturnedReceiptsByPayment($item->order_id, $item->payment_id, [$this->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS, $this->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS_CHEQUE, $this->receipts::PAYMENT_TYPE_RETURN_MULTIPOLIS_REQUISITES]);
                $item->return_receipt_url_download = $this->receipts->getReceiptUrlDownloadReturnCD($item->return_transaction_id);
                $item->return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
            }

            $item->policy_id = null;

            $multipolis_policy_docs = $this->documents->get_documents([
                'order_id' => $item->order_id,
                'type' => 'DOC_MULTIPOLIS']);

            if (!empty($multipolis_policy_docs)) {
                foreach ($multipolis_policy_docs as $doc) {
                    $time_lag = abs(strtotime($item->date_added) - strtotime($doc->created));
                    if ($time_lag < 60 * 60) {
                        $item->policy_id = $doc->id;
                        break;
                    }
                }
            }
        });

        $this->design->assign('multipolis_items', $multipolis_items);

        // Телемед
        $tv_medical_items = $this->tv_medical->getPaymentsWithInfo(['filter_order_id' => $order_id, 'filter_status' => 'SUCCESS']);
        if (!is_array($tv_medical_items)) {
            $tv_medical_items = [];
        }
        array_walk($tv_medical_items, function ($item) {
            $item->days_since_purchase = floor((time() - strtotime($item->date_added)) / 86400);
            $item->return_amount = (int)$item->amount_total_returned;
            $item->fully_returned = $item->amount == $item->amount_total_returned;
            $item->amount_left = $item->amount - $item->amount_total_returned;
            if (!empty($item->return_status) && isset($item->return_transaction_id)) {
                $item->receipts = $this->receipts->getReturnedReceiptsByPayment($item->order_id, $item->payment_payment_id, [$this->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL, $this->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL_CHEQUE, $this->receipts::PAYMENT_TYPE_RETURN_TV_MEDICAL_REQUISITES]);
                $item->return_receipt_url_download = $this->receipts->getReceiptUrlDownloadReturnCD($item->return_transaction_id);
                $item->return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
            }

            $item->policy_id = null;

            $tv_medical_policy_docs = $this->documents->get_documents([
                'order_id' => $item->order_id,
                'type' => 'ACCEPT_TELEMEDICINE']);

            if (!empty($tv_medical_policy_docs)) {
                foreach ($tv_medical_policy_docs as $doc) {
                    $time_lag = abs(strtotime($item->date_added) - strtotime($doc->created));
                    if ($time_lag < 60 * 60) {
                        $item->policy_id = $doc->id;
                        break;
                    }
                }
            }
        });

        // Безопасная сделка
        $safe_deal_items = $this->safe_deal->selectAll(['filter_order_id' => $order_id, 'filter_status' => 'SUCCESS']);
        array_walk($safe_deal_items, function ($item) {
            $item->days_since_purchase = floor((time() - strtotime($item->date_added)) / 86400);
            $item->return_amount = (int)$item->amount_total_returned;
            $item->fully_returned = (int)$item->amount === (int)$item->amount_total_returned;
            $item->amount_left = $item->amount - $item->amount_total_returned;
            if (!empty($item->return_status) && isset($item->return_transaction_id)) {
                $item->receipts = $this->receipts->getReturnedReceiptsByPayment($item->order_id, $item->transaction_id, [
                    $this->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL,
                    $this->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL_CHEQUE,
                    $this->receipts::PAYMENT_TYPE_RETURN_SAFE_DEAL_REQUISITES
                ]);
                $item->return_receipt_url_download = $this->receipts->getReceiptUrlDownloadReturnCD($item->return_transaction_id);
                $item->return_transaction = $this->best2pay->get_transaction($item->return_transaction_id);
            }
        });

        $this->design->assign('safe_deal_items', $safe_deal_items);

        $returnRequests = $this->serviceReturnRequests->getReturnRequestsForUI((int)$order_id);
        if (!is_array($returnRequests)) {
            $returnRequests = [];
        }
        $returnRequestsIndex = [];
        foreach ($returnRequests as $request) {
            $returnRequestsIndex[$request->service_type][$request->service_pk] = $request;
        }

        $servicesConfig = [
            'credit_doctor' => [
                'items' => &$credit_doctor_items,
                'pk' => 'id',
            ],
            'star_oracle' => [
                'items' => &$star_oracle_items,
                'pk' => 'id',
            ],
            'safe_deal' => [
                'items' => &$safe_deal_items,
                'pk' => 'id',
            ],
            'multipolis' => [
                'items' => &$multipolis_items,
                'pk' => 'id',
            ],
            'tv_medical' => [
                'items' => &$tv_medical_items,
                'pk' => 'payment_id',
            ],
        ];

        foreach ($servicesConfig as $serviceType => $options) {
            foreach ($options['items'] as &$item) {
                $pkField = $options['pk'];
                $serviceId = $item->$pkField ?? null;

                $item->return_request = ($serviceId !== null)
                    ? ($returnRequestsIndex[$serviceType][$serviceId] ?? null)
                    : null;
            }
            unset($item);
        }

        $this->design->assign('tv_medical_items', $tv_medical_items);
        $this->design->assign('return_requests', $returnRequests);
        $this->design->assign('order', $order);
        $this->design->assign('user', $user);
        $this->design->assign('manager', $this->manager);

        try {
            $insures_html = $this->design->fetch('order/insures.tpl');
        } catch (Throwable $exception) {
            if (ob_get_level()) {
                ob_clean();
            }

            $payload = [
                'error' => 'insures_render_failed',
            ];

            $payload['message'] = $exception->getMessage();
            $payload['file'] = $exception->getFile();
            $payload['line'] = $exception->getLine();

            $this->json_output($payload);
        }

        $result = [
            'insures_html' => $insures_html,
        ];

        if (ob_get_level()) {
            ob_clean();
        }

        $this->json_output($result);
    }

    /**
     * Получает список возвратов переплат по договору через AJAX.
     * Рендерит шаблон design/manager/html/html_blocks/tabs/overpayments_tab.tpl.
     */
    private function action_get_overpayments()
    {
        if (!ob_get_level()) {
            ob_start();
        }

        $order_id = $this->request->post('order_id', 'integer');
        if (empty($order_id)) {
            $this->json_output(['error' => 'empty_order_id']);
        }

        $order = $this->orders->get_order($order_id);
        if (empty($order)) {
            $this->json_output(['error' => 'order_not_found']);
        }

        $overpayment_returns = $this->serviceReturnRequests->getByOrderAndType((int)$order->order_id, 'overpayment');

        $this->design->assign('overpayment_returns', $overpayment_returns);
        $this->design->assign('order', $order);
        $this->design->assign('manager', $this->manager);

        $result = [
            'overpayments_html' => $this->design->fetch('html_blocks/tabs/overpayments_tab.tpl'),
        ];

        if (ob_get_level()) {
            ob_clean();
        }

        $this->json_output($result);
    }

    private function action_personal()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->lastname = trim($this->request->post('lastname'));
        $order->firstname = trim($this->request->post('firstname'));
        $order->patronymic = trim($this->request->post('patronymic'));
        $order->gender = trim($this->request->post('gender'));
        $order->birth = trim($this->request->post('birth'));
        $order->birth_place = trim($this->request->post('birth_place'));
        $order->email = trim($this->request->post('email'));

        $personal_error = array();

        if (empty($order->lastname))
            $personal_error[] = 'empty_lastname';
        if (empty($order->firstname))
            $personal_error[] = 'empty_firstname';
        if (empty($order->gender))
            $personal_error[] = 'empty_gender';
        if (empty($order->birth))
            $personal_error[] = 'empty_birth';

        if (!empty($order->lastname) && !validateCyrillicPlus($order->lastname)) {
            $personal_error[] = 'symbols_lastname';
        }
        if (!empty($order->firstname) && !validateCyrillicPlus($order->firstname)) {
            $personal_error[] = 'symbols_firstname';
        }
        if (!empty($order->patronymic) && !validateCyrillicPlus($order->patronymic)) {
            $personal_error[] = 'symbols_patronymic';
        }
        if (!empty($order->birth_place) && !validateCyrillicPlus($order->birth_place)) {
            $personal_error[] = 'symbols_birth_place';
        }

        if (empty($personal_error)) {
            $update = array(
                'lastname' => $order->lastname,
                'firstname' => $order->firstname,
                'patronymic' => $order->patronymic,
                'gender' => $order->gender,
                'birth' => $order->birth,
                'birth_place' => $order->birth_place,
                'email' => $order->email,
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
            if ($action == 'personal') {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'personal',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($log_update),
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                ));
                $this->users->update_user($user_id, $update);
                foreach ($log_update as $key => $new_val) {
                    $old_val = $old_values[$key];
                    $field_name = ClientView::FIELD_NAMES[$key];
                    $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'personal', $order_id);
                }


                // обновляем в 1с
                $isset_order = $this->orders->get_order((int)$order_id);

                $this->soap->update_fields($isset_order->id_1c, $update);
            } else if ($action == 'personal_agreement') {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'personal_agreement',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($log_update),
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                ));

                if ($this->users->select_user_agreement($user_id))
                    $this->users->update_user_agreement($user_id, $update);
                else
                    $this->users->add_user_agreement($user_id, $update);
            }
        }

        $this->design->assign('personal_error', $personal_error);

        if (!isset($isset_order))
            $isset_order = $this->orders->get_order((int)$order_id);

        $order->phone_mobile = $isset_order->phone_mobile;
        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

    }

    private function action_passport()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->passport_serial = trim($this->request->post('passport_serial'));
        $order->passport_date = trim($this->request->post('passport_date'));
        $order->subdivision_code = trim($this->request->post('subdivision_code'));
        $order->passport_issued = trim($this->request->post('passport_issued'));

        $passport_error = array();

        $site_id = $this->users->get_site_id_by_user_id($user_id);
        $passport_user_id = $this->users->get_passport_user($order->passport_serial, $site_id);
        if (!empty($passport_user_id) && $passport_user_id != $user_id) {
            $passport_error[$user_id] = $passport_user_id;
        }
        if (empty($order->passport_serial))
            $passport_error[] = 'empty_passport_serial';
        if (empty($order->passport_date))
            $passport_error[] = 'empty_passport_date';
        if (empty($order->subdivision_code))
            $passport_error[] = 'empty_subdivision_code';
        if (empty($order->passport_issued))
            $passport_error[] = 'empty_passport_issued';

        if (!empty($order->passport_issued) && !validateCyrillicPlus($order->passport_issued)) {
            $passport_error[] = 'symbols_passport_issued';
        }

        if (empty($passport_error)) {
            $update = array(
                'passport_serial' => $order->passport_serial,
                'passport_date' => $order->passport_date,
                'subdivision_code' => $order->subdivision_code,
                'passport_issued' => $order->passport_issued
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
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                ));

                foreach ($log_update as $key => $new_val) {
                    $old_val = $old_values[$key];
                    $field_name = ClientView::FIELD_NAMES[$key];
                    $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'passport', $order_id);
                }
                $this->users->update_user($user_id, $update);

                // обновляем в 1с
                $isset_order = $this->orders->get_order((int)$order_id);
                $this->soap->update_fields($isset_order->id_1c, $update);
            } else if ($action == 'passport_agreement') {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'passport_agreement',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($log_update),
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                ));

                if ($this->users->select_user_agreement($user_id))
                    $this->users->update_user_agreement($user_id, $update);
                else
                    $this->users->add_user_agreement($user_id, $update);
            }
        }

        $this->design->assign('passport_error', $passport_error);

        if (!isset($isset_order))
            $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

    }

    private function reg_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Regindex = trim($this->request->post('Regindex'));
        $order->Regregion = trim($this->request->post('Regregion'));
        $order->Regregion_shorttype = trim($this->request->post('Regregion_shorttype'));
        $order->Regcity = trim($this->request->post('Regcity'));
        $order->Regcity_shorttype = trim($this->request->post('Regcity_shorttype'));
        $order->Regstreet = trim($this->request->post('Regstreet'));
        $order->Regstreet_shorttype = trim($this->request->post('Regstreet_shorttype'));
        $order->Reghousing = trim($this->request->post('Reghousing'));
        $order->Regbuilding = trim($this->request->post('Regbuilding'));
        $order->Regroom = trim($this->request->post('Regroom'));

        $regaddress_error = array();

        if (empty($order->Regregion))
            $regaddress_error[] = 'empty_regregion';
        if (empty($order->Regcity))
            $regaddress_error[] = 'empty_regcity';
        if (empty($order->Regstreet))
            $regaddress_error[] = 'empty_regstreet';
        if (empty($order->Reghousing))
            $regaddress_error[] = 'empty_reghousing';

        if (!empty($order->Regregion) && !validateCyrillicPlus($order->Regregion)) {
            $regaddress_error[] = 'symbols_regregion';
        }
        if (!empty($order->Regcity) && !validateCyrillicPlus($order->Regcity)) {
            $regaddress_error[] = 'symbols_regcity';
        }
        if (!empty($order->Regstreet) && !validateCyrillicPlus($order->Regstreet)) {
            $regaddress_error[] = 'symbols_regstreet';
        }

        if (empty($regaddress_error)) {
            $timezone_id = $this->users->getTimezoneId($order->Regregion);

            $update = array(
                'Regindex' => $order->Regindex,
                'Regregion' => $order->Regregion,
                'Regregion_shorttype' => $order->Regregion_shorttype,
                'Regcity' => $order->Regcity,
                'Regcity_shorttype' => $order->Regcity_shorttype,
                'Regstreet' => $order->Regstreet,
                'Regstreet_shorttype' => $order->Regstreet_shorttype,
                'Reghousing' => $order->Reghousing,
                'Regbuilding' => $order->Regbuilding,
                'Regroom' => $order->Regroom,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = ClientView::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'regaddress', $order_id);
            }

            if (!empty($old_user->registration_address_id) && !empty($this->request->safe_post('Regregion'))) {
                (new UsersAddressService())->updateRegistrationAddress($old_user->registration_address_id, $this->request);
            }

            $this->users->update_user($user_id, $update);

            // обновляем в 1с
            $isset_order = $this->orders->get_order((int)$order_id);
            $this->soap->update_fields($isset_order->id_1c, $log_update);
        }

        $this->design->assign('regaddress_error', $regaddress_error);

        if (!isset($isset_order))
            $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }

    private function fakt_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = $this->orders->get_order((int)$order_id);
        $pdnCalculations = $this->pdnCalculation->getPdnCalculationsByOrderId([(int)$order_id]);

        if (!empty($pdnCalculations[0])) {
            require_once __DIR__ . '/../lib/autoloader.php';
            $userAddressDto = (new UsersAddressService())->createFactualAddressDtoFromRequest();

            $data = [
                'order_id' => (int)$order_id,
                'fakt_address' => json_encode($userAddressDto, JSON_UNESCAPED_UNICODE),
            ];

            $this->pdnCalculation->savePdnData($data, (int)$order->organization_id);
            return;
        }

        $order = new StdClass();
        $order->Faktregion = trim($this->request->post('Faktregion'));
        $order->Faktindex = trim($this->request->post('Faktindex'));
        $order->Faktregion_shorttype = trim($this->request->post('Faktregion_shorttype'));
        $order->Faktcity = trim($this->request->post('Faktcity'));
        $order->Faktcity_shorttype = trim($this->request->post('Faktcity_shorttype'));
        $order->Faktstreet = trim($this->request->post('Faktstreet'));
        $order->Faktstreet_shorttype = trim($this->request->post('Faktstreet_shorttype'));
        $order->Fakthousing = trim($this->request->post('Fakthousing'));
        $order->Faktbuilding = trim($this->request->post('Faktbuilding'));
        $order->Faktroom = trim($this->request->post('Faktroom'));

        $faktaddress_error = array();

        if (empty($order->Faktregion))
            $faktaddress_error[] = 'empty_faktregion';
        if (empty($order->Faktcity))
            $faktaddress_error[] = 'empty_faktcity';
        if (empty($order->Faktstreet))
            $faktaddress_error[] = 'empty_faktstreet';
        if (empty($order->Fakthousing))
            $faktaddress_error[] = 'empty_fakthousing';

        if (!empty($order->Faktregion) && !validateCyrillicPlus($order->Faktregion)) {
            $faktaddress_error[] = 'symbols_faktregion';
        }
        if (!empty($order->Faktcity) && !validateCyrillicPlus($order->Faktcity)) {
            $faktaddress_error[] = 'symbols_faktcity';
        }
        if (!empty($order->Faktstreet) && !validateCyrillicPlus($order->Faktstreet)) {
            $faktaddress_error[] = 'symbols_faktstreet';
        }

        if (empty($faktaddress_error)) {
            $update = array(
                'Faktindex' => $order->Faktindex,
                'Faktregion' => $order->Faktregion,
                'Faktregion_shorttype' => $order->Faktregion_shorttype,
                'Faktcity' => $order->Faktcity,
                'Faktcity_shorttype' => $order->Faktcity_shorttype,
                'Faktstreet' => $order->Faktstreet,
                'Faktstreet_shorttype' => $order->Faktstreet_shorttype,
                'Fakthousing' => $order->Fakthousing,
                'Faktbuilding' => $order->Faktbuilding,
                'Faktroom' => $order->Faktroom,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));
            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = ClientView::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'faktaddress', $order_id);
            }

            if (!empty($old_user->factual_address_id) && !empty($this->request->safe_post('Faktregion'))) {
                (new UsersAddressService())->updateFactualAddress($old_user->factual_address_id, $this->request);
            }

            $this->users->update_user($user_id, $update);

            // обновляем в 1с
            $isset_order = $this->orders->get_order((int)$order_id);
            $this->soap->update_fields($isset_order->id_1c, $log_update);
        }

        $this->design->assign('faktaddress_error', $faktaddress_error);

        if (!isset($isset_order))
            $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }

    private function contacts_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $contact_person_ids = array_map('trim', (array)$this->request->post('contact_person_id'));
        $contact_person_names = array_map('trim', (array)$this->request->post('contact_person_name'));
        $contact_person_phones = array_map('trim', (array)$this->request->post('contact_person_phone'));
        $contact_person_relations = array_map('trim', (array)$this->request->post('contact_person_relation'));
        $contact_person_comments = array_map('trim', (array)$this->request->post('contact_person_comment'));

//        $this->contactpersons->delete_user_contactpersons($user_id);
        foreach ($contact_person_ids as $i => $contact_person_id) {
            $contactperson = array(
                'user_id' => $user_id,
                'name' => $contact_person_names[$i],
                'phone' => $contact_person_phones[$i],
                'relation' => $contact_person_relations[$i],
                'comment' => $contact_person_comments[$i],
            );
            if (empty($contact_person_id)) {
                $this->contactpersons->add_contactperson($contactperson);

                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'contacts',
                    'old_values' => serialize(array()),
                    'new_values' => serialize($contactperson),
                    'order_id' => $order_id,
                    'user_id' => $user_id,
                ));
            } else {
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
                    $field_name = ClientView::FIELD_NAMES['contact_person_' . $key];
                    $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'contactpersons', $order_id);
                }
                $this->contactpersons->update_contactperson($contact_person_id, $contactperson);
            }

        }

        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $user_id));
        $this->design->assign('contactpersons', $contactpersons);


        $order = new StdClass();
        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

        // обновляем в 1с
        $contactpersons_1c = array();
        foreach ($contactpersons as $contactperson) {
            $contactperson_1c = new StdClass();

            $ex = explode(' ', $contactperson->name);

            $contactperson_1c->Фамилия = empty($ex[0]) ? '' : $ex[0];
            $contactperson_1c->Имя = empty($ex[1]) ? '' : $ex[1];
            $contactperson_1c->Отчество = empty($ex[2]) ? '' : $ex[2];
            $contactperson_1c->СтепеньРодства = $contactperson->relation;
            $contactperson_1c->ТелефонМобильный = $this->soap->format_phone($contactperson->phone);

            $contactpersons_1c[] = $contactperson_1c;
        }
        $this->soap->update_fields($isset_order->id_1c, array('contactpersons' => $contactpersons_1c));

    }

    private function workdata_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->work_scope = trim($this->request->post('work_scope'));
        $order->profession = trim($this->request->post('profession'));
        $order->work_phone = trim($this->request->post('work_phone'));
        $order->workplace = trim($this->request->post('workplace'));
        $order->workdirector_name = trim($this->request->post('workdirector_name'));
        $order->income_base = trim($this->request->post('income_base'));

        $workdata_error = array();

        if (empty($order->work_scope))
            $workaddress_error[] = 'empty_work_scope';
        if (empty($order->income_base))
            $workaddress_error[] = 'empty_income_base';

        if (empty($workdata_error)) {
            $update = array(
                'work_scope' => $order->work_scope,
                'profession' => $order->profession,
                'work_phone' => $order->work_phone,
                'workplace' => $order->workplace,
                'workdirector_name' => $order->workdirector_name,
                'income_base' => $order->income_base,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));
            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = ClientView::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'work', $order_id);

            }

            $this->users->update_user($user_id, $update);

            // обновляем в 1с
            $ord = $this->orders->get_order((int)$order_id);
            $this->soap->update_fields($ord->id_1c, $update);

            $order = $this->orders->get_order((int)$order_id);
            $pdnCalculations = $this->pdnCalculation->getPdnCalculationsByOrderId([(int)$order_id]);

            if (!empty($pdnCalculations[0])) {
                $data = [
                    'order_id' => (int)$order_id,
                    'income_base' => $update['income_base'],
                ];

                $this->pdnCalculation->savePdnData($data, (int)$order->organization_id);
            }
        }

        $this->design->assign('workdata_error', $workdata_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

        // обновляем в 1с
        $this->soap->update_fields($isset_order->id_1c, $log_update);
    }


    private function work_address_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->Workregion = trim($this->request->post('Workregion'));
        $order->Workcity = trim($this->request->post('Workcity'));
        $order->Workstreet = trim($this->request->post('Workstreet'));
        $order->Workhousing = trim($this->request->post('Workhousing'));
        $order->Workbuilding = trim($this->request->post('Workbuilding'));
        $order->Workroom = trim($this->request->post('Workroom'));

        $workaddress_error = array();

        if (empty($order->Workregion))
            $workaddress_error[] = 'empty_workregion';
        if (empty($order->Workcity))
            $workaddress_error[] = 'empty_workcity';
        if (empty($order->Workstreet))
            $workaddress_error[] = 'empty_workstreet';
        if (empty($order->Workhousing))
            $workaddress_error[] = 'empty_workhousing';

        if (empty($workaddress_error)) {
            $update = array(
                'Workregion' => $order->Workregion,
                'Workcity' => $order->Workcity,
                'Workstreet' => $order->Workstreet,
                'Workhousing' => $order->Workhousing,
                'Workbuilding' => $order->Workbuilding,
                'Workroom' => $order->Workroom,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            foreach ($log_update as $key => $new_val) {
                $old_val = $old_values[$key];
                $field_name = ClientView::FIELD_NAMES[$key];
                $this->comments->add_field_log($user_id, $this->manager->id, $field_name, $old_val, $new_val, 'workaddress', $order_id);
            }

            $this->users->update_user($user_id, $update);

        }

        $this->design->assign('workaddress_error', $workaddress_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);

        $ord = $this->orders->get_order($order_id);

        // обновляем в 1с
        $workaddress = $ord->Workregion . ', ' . $ord->Workcity . ', ' . $ord->Workstreet . ', д' . $ord->Workhousing;
        if (!empty($ord->Workbuilding))
            $workaddress .= ', стр.' . $ord->Workbuilding;
        if (!empty($ord->Workroom))
            $workaddress .= ', оф.' . $ord->Workroom;
        $this->soap->update_fields($isset_order->id_1c, array(
            'workaddress' => $workaddress
        ));
    }

    private function socials_action()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->social_fb = trim($this->request->post('social_fb'));
        $order->social_inst = trim($this->request->post('social_inst'));
        $order->social_vk = trim($this->request->post('social_vk'));
        $order->social_ok = trim($this->request->post('social_ok'));

        $socials_error = array();

        if (empty($socials_error)) {
            $update = array(
                'social_fb' => $order->social_fb,
                'social_inst' => $order->social_inst,
                'social_vk' => $order->social_vk,
                'social_ok' => $order->social_ok,
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
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('socials_error', $socials_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }


    private function action_images()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $statuses = $this->request->post('status');
        foreach ($statuses as $file_id => $status) {
            $update = array(
                'status' => $status,
                'id' => $file_id
            );

            $old_files = $this->users->get_file($file_id);
            $old_values = array();
            foreach ($update as $key => $val)
                $old_values[$key] = $old_files->$key;
            if ($old_values['status'] != $update['status']) {
                $this->changelogs->add_changelog(array(
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'images',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($update),
                    'user_id' => $user_id,
                    'order_id' => $order_id,
                    'file_id' => $file_id,
                ));
            }

            $this->users->update_file($file_id, array('status' => $status));
        }
        $isset_order = $this->orders->get_order((int)$order_id);

        $have_reject = 0;
        if ($files = $this->users->get_files(array('user_id' => $user_id))) {
            foreach ($files as $f)
                if ($f->status == 3)
                    $have_reject = 1;
        }
        if ($isset_order->status != 3) {
            if (empty($have_reject)) {
//                $this->orders->update_order($order_id, array('status' => 1));
                $update_status_1c = $this->orders::ORDER_UPDATE_1C_STATUS_NEW;
            } else {
                $this->orders->update_order($order_id, array('status' => 5));
                $update_status_1c = $this->orders::ORDER_UPDATE_1C_STATUS_CONSIDERED;
            }

            // меняем статус в 1С
            $manager = $this->managers->get_manager($isset_order->manager_id);
            $this->soap->update_status_1c($isset_order->id_1c, $update_status_1c, $manager->name_1c, $isset_order->amount, $isset_order->percent, '', 0, $isset_order->period);
        }

        $isset_order = $this->orders->get_order((int)$order_id);
        $order = new StdClass();
        $order->order_id = $order_id;
        $order->user_id = $user_id;

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $this->design->assign('order', $order);

        $files = $this->users->get_files(array('user_id' => $user_id, 'status' => array(1, 2, 3)));
        $this->design->assign('files', $files);
    }

    private function action_services()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $user_id = $this->request->post('user_id', 'integer');

        $order = new StdClass();
        $order->service_sms = (int)$this->request->post('service_sms');
        $order->service_insurance = (int)$this->request->post('service_insurance');
        $order->service_reason = (int)$this->request->post('service_reason');

        $services_error = array();

        if (empty($services_error)) {
            $update = array(
                'service_sms' => $order->service_sms,
                'service_insurance' => $order->service_insurance,
                'service_reason' => $order->service_reason,
            );

            $old_user = $this->users->get_user($user_id);

            $changeLogs = Helpers::getChangeLogs($update, $old_user);

            $this->changelogs->add_changelog(array(
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'services',
                'old_values' => serialize($changeLogs['old']),
                'new_values' => serialize($changeLogs['new']),
                'order_id' => $order_id,
                'user_id' => $user_id,
            ));

            $this->users->update_user($user_id, $update);
        }

        $this->design->assign('services_error', $services_error);

        $isset_order = $this->orders->get_order((int)$order_id);

        $order->status = $isset_order->status;
        $order->manager_id = $isset_order->manager_id;
        $order->order_id = $order_id;
        $order->user_id = $user_id;
        $this->design->assign('order', $order);
    }

    private function changeAutodebitParam()
    {
        try {
            $cardAutodebitParams = $this->request->post('card_autodebit_data');
            $sbpAutodebitParams = $this->request->post('sbp_autodebit_data');
            $orderId = $this->request->post('order_id', 'integer');
            $userId = $this->request->post('user_id', 'integer');
            $order = new StdClass();
            $uid = $this->users->getUserUidById($userId);

            if ($cardAutodebitParams) {
                $cards = $this->cardService->changeAutodebitParam($cardAutodebitParams, $userId, $orderId, (int)$this->manager->id, $uid);

                if ($cards) {
                    $this->design->assign('autodebit_cards', $cards);
                }
            }

            if ($sbpAutodebitParams) {
                $sbpAccounts = $this->sbpAccountService->changeAutodebitParam($sbpAutodebitParams, $userId, $orderId, (int)$this->manager->id, $uid);
                $this->design->assign('sbp_accounts', $sbpAccounts);
            }

            $isset_order = $this->orders->get_order((int)$orderId);
            $order->status = $isset_order->status;
            $order->manager_id = $isset_order->manager_id;
            $order->order_id = $orderId;
            $order->user_id = $userId;
            $this->design->assign('order', $order);
        } catch (Exception $e) {
            $this->simpla->logging(__METHOD__, '', '', $e->getFile() . PHP_EOL . $e->getLine() . PHP_EOL . $e->getMessage(), 'recurents_autodebit.txt');
        }
    }


//    private function get_payment_link($balance)
//    {
//        try {
//            $curl = curl_init();
//
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => "http://51.250.101.109/api/getPayLink?number={$balance->zaim_number}&password=BSTR123987",
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => '',
//                CURLOPT_MAXREDIRS => 10,
//                CURLOPT_TIMEOUT => 13,
//                CURLOPT_FOLLOWLOCATION => true,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => 'GET',
//            ));
//
//            $re = curl_exec($curl);
//
//            curl_close($curl);
//            $re = json_decode($re, true);
//        } catch (\Throwable $th) {
//            //throw $th;
//        }
//
//        if (isset($re['data'])) {
//            return $re['data'];
//        } else {
//            return 'error';
//        }
//
//    }

    public function get_balance()
    {
        $response = new StdClass();

        if (!($user_id = $this->request->post('user_id', 'integer'))) {
            $response->error = 'Не указан ID клиента';
            $this->json_output($response);
        }

        if (!($user = $this->users->get_user($user_id))) {
            $response->error = 'Клиент не найден';
            $this->json_output($response);
        }

        $blackListCheck = $this->blacklist->checkIsUserIn1cBlacklistSafe($user->UID);

        if (!$blackListCheck['ok']) {
            $response->error = 'Сервис 1С недоступен. Невозможно проверить ЧС в 1С.';
            $this->json_output($response);
        }

        if ($blackListCheck['in_blacklist']) {
            $response->error = 'Клиент находится в ч/с';
            $this->json_output($response);
        }

        $order_id = $this->request->post('order_id', 'integer');
        $needUpdate = $this->request->post('update');
        $order = $order_id ? $this->orders->get_order($order_id) : null;

        $userBalance = $this->users->get_user_balance($user->id);
        if ($userBalance && $order && ($userBalance->zayavka == $order->id_1c) && !$needUpdate) {
            $balance = $userBalance;
        } else {
            $userBalances = $this->users->updateUserBalanceAccordingTo1c($user);

            if (!$userBalances) {
                if ($needUpdate) {
                    $response->error = 'Сервер 1с не отвечает';
                    $this->json_output($response);
                }
                $balance = $userBalance;
            } else {
                $balance = null;
                if ($order) {
                    foreach ($userBalances as $ub) {
                        if (isset($ub['Заявка']) && $ub['Заявка'] == $order->id_1c) {
                            $balance = $this->users->make_up_user_balance($user->id, (object)$ub);
                            break;
                        }
                    }
                }

                $balance = $balance ?: $this->users->get_user_balance($user->id);

                if (empty($balance->last_update)) {
                    $balance->last_update = date('y-m-d H:i:s');
                }
            }
        }


        $rzs = $this->organizations->get_organization($this->organizations::RZS_ID);

        if (!$balance || ($this->manager->role == 'verificator_minus' && $balance->inn != $rzs->inn)) {
            $response->balance = (object)['zaim_number' => 'Нет открытых договоров'];
        } else {
            $balance->payment_date = date('d.m.Y', strtotime($balance->payment_date));
            $balance->zaim_date = date('d.m.Y', strtotime($balance->zaim_date));
            $balance->last_update = date('d.m.y H:i', strtotime($balance->last_update));

            if ($balance->loan_type == 'IL') {
                $balance->details = $this->soap->get_il_details($balance->zaim_number);
                $balance->details['БлижайшийПлатеж_Дата'] = date('d.m.Y', strtotime($balance->details['БлижайшийПлатеж_Дата']));
            }

            $response->balance = $balance;
            $link = $this->orders->getShortLink((int)$user->id, (string)$balance->zaim_number);
            $response->link = $link ? $this->config->front_url . '/pay/' . $link : "";

            $response->rcl = $this->getRcl($order_id);
        }

        $this->json_output($response);
    }

    private function return_insurance_action()
    {
        $response = new StdClass();
        $insurance_id = $this->request->post('insurance_id');
        $application_date = $this->request->post('application_date');
        $card_id = $this->request->post('card_id');
        if (empty($application_date)) {
            $response->error = 'Дата заявления не указана';
        } elseif ($insurance = $this->insurances->get_insurance($insurance_id)) {
            $application_date = date('Y-m-d H:i:s', strtotime($application_date));
            $response->data = [
                'number' => $insurance->number,
                'application_date' => date('YmdHis', strtotime($application_date)),
                'card_id' => $card_id,
            ];

            $result = $this->soap->return_insurance([
                'number' => $insurance->number,
                'application_date' => $application_date,
                'card_id' => $card_id,
            ]);
            $response->result = $result;

            if ($result) {
                $update_insurance = array(
                    'return_date' => date('Y-m-d H:i:s'),
                    'return_application_date' => $application_date,
                    'return_response' => $result,
                );
                if ($result == 'REFUNDED' || $result == 'Возврат произведен ранее') {
                    $update_insurance['return_status'] = 1;
                    $response->success = $result;
                } else {
                    $response->error = $result;
                }
                $this->insurances->update_insurance($insurance->id, $update_insurance);

                $response->update_insurance = $update_insurance;
            } else {
                $response->error = 'Не удалось выполнить оперцию';
            }
        } else {
            $response->error = 'Страховка не найдена';
        }

        $this->json_output($response);
    }

    private function return_insure_action()
    {
        $response = new StdClass();
        $insure_id = $this->request->post('insure_id');

        if ($insure = $this->best2pay->get_insure($insure_id)) {
            $result = $this->best2pay->return_insure($insure);

            if ($result)
                $response->success = $result;
            else
                $response->error = 'Не удалось выполнить оперцию';
        } else {
            $response->error = 'Страховка не найдена';
        }

        $this->json_output($response);
    }

    /**
     * Изменяет сумму заявки и создает сумму для будущей заявки
     * @return void
     */
    private function divide_order()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $amount = min($this->request->post('amount', 'integer'), 9900);
        $order = $this->orders->get_order($order_id);

        if ($scorista = $this->scorings->get_last_scorista_for_order($order_id)) {
            $scoring_body = json_decode($scorista->body);
            if (!empty($scoring_body->additional->decisionSum_without_PTI)) {
                $max_amount_by_scorista = (int)$scoring_body->additional->decisionSum_without_PTI;
            }
        }

        $max_amount = $max_amount_by_scorista ?? $order->amount;

        $this->orders->update_order($order_id, compact('amount'));
        $this->changelogs->add_changelog(
            [
                'manager_id' => $this->manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'amount_divide',
                'old_values' => $order->amount,
                'new_values' => $amount,
                'order_id' => $order_id,
                'user_id' => $order->user_id,
            ]
        );

        $result = $this->orders->addDividePreOrder($order_id, min($max_amount - $amount, 9900));
        $this->json_output(compact('result'));
    }

    /**
     * Формируем и отдаем в архиве досье о пользователе
     * @return void
     * @throws ErrorException
     */
    private function get_user_files()
    {
        $order_id = $this->request->post('order_id', 'integer');
        $order = $this->orders->get_order($order_id);

        $user_balance = $this->users->get_user_balance(intval($order->user_id));

        // получим документы
        $docs = $this->soap->get_documents($user_balance->zaim_number);
        $filter_docs = array_filter($docs, function ($item) {
            return in_array($item->ТипДокумента, ['Договор', 'Заявление о предоставление микрозайма', 'Анкета']);
        });

        $files = [];
        foreach ($filter_docs as $doc) {
            $filestorage = new Filestorage();
            $file_storage = $filestorage->load_document($doc->УИДХранилища, $doc->ТипДокумента);

            if (file_exists($_SERVER['DOCUMENT_ROOT'] . $file_storage)) {
                $files[] = $_SERVER['DOCUMENT_ROOT'] . $file_storage;
            }
        }

        // получим фото паспорта
        $get_files_filters = [
            'user_id' => $order->user_id,
            'status' => [1, 2, 3],
            'types' => ['passport1'],
        ];
        $user_files = $this->users->get_files($get_files_filters);

        if (!empty($user_files)) {
            $passport_file_url = $this->config->front_url . '/' . $this->config->users_files_dir . $user_files[0]->name;
            $passport_file_path = $this->config->root_dir . 'files/' . $order->user_id . '_passport_file_' . basename($passport_file_url);
            file_put_contents($passport_file_path, fopen($passport_file_url, 'r'));

            if (file_exists($passport_file_path)) {
                $files['passport'] = $passport_file_path;
            }
        }

        // Получим КИ по скористе займа
        $scoring = $this->scorings->get_last_scorista_for_order($order_id, true);
        if (!empty($scoring)) {
            $scoring_file = $this->config->root_dir . 'files/equifax/' . $scoring->scorista_id;
            if (file_exists($scoring_file)) {
                $files[] = $scoring_file;
            }
        }

        $asp_zaim_list = $this->users->getZaimListAsp((string)$user_balance->zaim_number);

        if (!empty($asp_zaim_list)) {

            $asp_zaim = $this->config->front_url . '/files/asp/' . $asp_zaim_list[0]->file_name;
            $asp_zaim_file_path = $this->config->root_dir . 'files/' . $order->user_id . '_asp_zaim_' . basename($asp_zaim);
            file_put_contents($asp_zaim_file_path, fopen($asp_zaim, 'r'));

            if (file_exists($asp_zaim_file_path)) {
                $files['asp_zaim'] = $asp_zaim_file_path;
            }
        }
        // запакуем архив
        $zip = new ZipArchive();
        $zip_name = time() . "_order_user_data.zip";

        if ($zip->open($zip_name, ZIPARCHIVE::CREATE) !== true) {
            throw new ErrorException('Sorry File is open...');
        }

        if (!empty($files)) {
            foreach ($files as $file) {
                $file_paths = explode('/', $file);
                $zip->addFile($file, end($file_paths));
            }

            $zip->close();

            if (file_exists($zip_name)) {
                $this->response->file_output($zip_name, 'application/zip');
            }

            if (!empty($files['passport'])) {
                unlink($files['passport']);
            }
            if (!empty($files['asp_zaim'])) {
                unlink($files['asp_zaim']);
            }
        } else {
            header('Location: ' . $this->config->root_url . '/order/' . $order_id);
        }
    }

    /**
     * Save order's custom select field
     * @return void
     */
    private function actionSaveSelect(): void
    {
        $orderId = $this->request->get('id', 'integer');
        $field = $this->request->post('field');
        $fieldValue = $this->request->post('value');

        $order = $this->orders->get_order((int)$orderId);
        if (empty($order)) {
            $this->json_output(['error' => 'Займ не найден!']);
        }

        $this->orders->update_order($orderId, [$field => $fieldValue]);
        $this->changelogs->add_changelog([
            'manager_id' => $this->manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'save_select',
            'old_values' => $field . ': ' . ($fieldValue ?? 'null'),
            'new_values' => $fieldValue,
            'order_id' => $orderId
        ]);

        $this->json_output(['success' => true]);
    }

    /**
     * Creates documents in database
     *
     * @param $user
     * @param $asp
     *
     * @return mixed document id
     */
    private function createDocumentAgreementOfASP($user, $order, $asp = null)
    {
        preg_match('@(\d{2}\s\d{2})\s(\d{6})@', $user->passport_serial, $passport);
        $passport = [
            'passport_serial' => $passport[1],
            'passport_number' => $passport[2]
        ];

        if (!$asp) {
            $this->db->query(
                "SELECT asp, created FROM b2p_payments WHERE user_id = {$user->id} AND asp <> '' LIMIT 1"
            );
            $asp = $this->db->result();
        }

        return $this->documents->create_document([
            'type' => $this->documents::ASP_AGREEMENT,
            'notification_title' => "Соглашение об АСП",
            'user_id' => $user->id,
            'order_id' => $order->order_id,
            'params' => [
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'patronymic' => $user->patronymic,
                'birth' => $user->birth,
                'passport_serial' => $passport['passport_serial'],
                'passport_number' => $passport['passport_number'],
                'registration_address' => $user->registration_address,
                'passport_issued' => $user->passport_issued,
                'passport_date' => $user->passport_date,
                'asp' => $asp->asp ?? '',
                'asp_date' => $asp->created ?? '',
            ]
        ]);
    }

    private function saveDocumentToFileStorage($document_id, $user, $url = null)
    {
        $file_url = $url ?? $this->config->root_url . '/document/' . $this->user->id . '/' . $document_id;
        $storage_uid = $this->filestorage->upload_file($file_url, 15);

        return $this->db->query(
            $this->db->placehold(
                "UPDATE s_documents SET filestorage_uid = ? WHERE id = ?",
                $storage_uid,
                $document_id
            )
        );
    }

    private function get_docs_for_minus($order, $documents)
    {
        $minus_docs = [];
        if ($contract = $this->contracts->get_contract_by_params(['order_id' => $order->order_id])) {
            if ($docs = $this->soap->get_documents($contract->number)) {
                foreach ($docs as $doc) {
                    if (empty($doc->{'НеОтображать'})) {

                        if (in_array($doc->{'ТипДокумента'}, ['ПП', 'Согласие БКИ', 'Частота взаимодействия'])) {
                            continue;
                        }

                        if ($doc->{'ТипДокумента'} == 'Рекурренты') {
                            $doc->{'ТипДокумента'} = 'Согласие на рекуррентные списания';
                        }

                        $d = (object)[
                            'name' => $doc->{'ТипДокумента'},
                            'url' => $this->config->front_url.'/user/docs/'.$doc->{'УИДХранилища'},
                        ];
                        $minus_docs[] = $d;
                    }
                }
            }
        }

        if (!empty($documents['documents'])) {
            $hide_docs = [
                'MICRO_ZAIM_FULL',
                'PENALTY_CREDIT_DOCTOR',
            ];
            foreach ($documents['documents'] as $doc) {
                if (!in_array($doc->type, $hide_docs)) {
                    $d = (object)[
                        'name' => $doc->name,
                        'url' => $this->config->front_url.'/document/'.$doc->user_id.'/'.$doc->id,
                    ];
                    $minus_docs[] = $d;
                }
            }
        }

        return $minus_docs;
    }

    /**
     * Обновить кросс-заяви
     * @param $isset_order
     * @param array $update
     * @return void
     */
    private function updateCrossOrders($isset_order, array $update = [])
    {
        $crossOrders = $this->orders->getCrossOrdersByMainOrderId((int)$isset_order->order_id);
        foreach ($crossOrders as $crossOrder)
        {
            if (!in_array($crossOrder->status, [
                $this->orders::ORDER_STATUS_CRM_APPROVED,   // Одобрена
                $this->orders::ORDER_STATUS_CRM_NOT_ISSUED, // Ошибка выдачи
                $this->orders::ORDER_STATUS_CRM_WAIT,       // Ожидает выдачу (Выдача отложена)
            ])) {
                continue;
            }

            // Если ошибка выдачи и поменяли карту - делаем повторную попытку выдачи
            if ($crossOrder->status == $this->orders::ORDER_STATUS_CRM_NOT_ISSUED) {
                if (!empty($update['card_id']) && empty($update['status'])) {
                    $update['status'] = $this->orders::ORDER_STATUS_CRM_WAIT;
                }
            }

            $old_values = array_intersect_key(array_diff_assoc((array)$crossOrder, $update), $update);
            $this->changelogs->add_changelog(
                [
                    'manager_id' => $this->manager->id,
                    'created' => date('Y-m-d H:i:s'),
                    'type' => 'period_amount',
                    'old_values' => serialize($old_values),
                    'new_values' => serialize($update),
                    'order_id' => $crossOrder->id,
                    'user_id' => $isset_order->user_id,
                ]
            );
            $this->orders->update_order($crossOrder->id, $update);
        }
    }

    /**
     * Есть ли для заявки "Лист оценки платежеспособности заемщика"
     *
     * @return void
     */
    private function hasOrderCreditworthinessAssessmentDocument(stdClass $order): bool
    {
        $pdnCalculations = $this->pdnCalculation->getPdnCalculationsByOrderId([(int)$order->order_id], (int)$order->organization_id);

        if (empty($pdnCalculations[0])) {
            return false;
        }

        $pdnCalculation = $pdnCalculations[0];

        /** @var array $necessaryPdnCalculationTypes типы расчетов ПДН, для которых есть документ "Лист оценки платежеспособности заемщика" */
        $necessaryPdnCalculationTypes = [3, 4];

        return in_array($pdnCalculation->pdn_calculation_type, $necessaryPdnCalculationTypes);
    }

    /**
     * Отсрочка по платежу
     * @throws Exception
     */
    private function paymentDeferment(): void
    {
        $orderId = (int)$this->request->post('order_id', 'integer');
        $order = $this->orders->get_order($orderId);

        if (!$order) {
            $this->json_output(['error' => 'Займ не найден!']);
        }

        if ($order->status_1c !== '5.Выдан') {
            $this->json_output(['error' => 'Функция недоступна при текущем статусе - ' . $order->status_1c]);
        }

        $loanData = $this->getLoanData($order);

        $errorMessage = null;

        if (time() > strtotime($loanData['ПланДата'])) {
            $errorMessage = 'Займ на просрочке!';
        } elseif ($this->credit_doctor->getReturnedCreditDoctorByOrder($order->order_id)) {
            $errorMessage = 'По данному займу был сделан возврат ФД';
        } elseif ($this->order_data->read($order->order_id, 'payment_deferment')) {
            $errorMessage = 'По данному займу делалась отсрочка ранее!';
        }

        if ($errorMessage) {
            $this->createDefermentDocument($order, $this->documents::PAYMENT_DEFERMENT_REJECT, $loanData);
            $this->json_output(['error' => $errorMessage]);
        }

        $result1c = $this->soap->paymentDeferment($loanData['НомерЗайма']);

        if (empty($result1c['response']['success'])) {
            $this->json_output(['error' => $result1c['response']['message'] ?? 'Ошибка при отправке в 1с']);
        }

        $this->order_data->set($orderId, $this->order_data::PAYMENT_DEFERMENT, 1);

        $this->createDefermentDocument($order, $this->documents::PAYMENT_DEFERMENT_APPROVE, $loanData);

        $this->json_output(['success' => 'Успешно']);
    }

    /**
     * Создает документ по отсрочке платежа (отказ или одобрение)
     *
     * @param object $order Данные займа
     * @param string $documentType Тип документа (PAYMENT_DEFERMENT_REJECT или PAYMENT_DEFERMENT_APPROVE)
     * @param array $loanData Данные о займе из 1С
     * @throws Exception
     */
    private function createDefermentDocument(object $order, string $documentType, array $loanData): void
    {
        $params = new StdClass();

        $planDate = $loanData['ПланДата'];
        $date = new DateTime($planDate);
        if ($documentType === Documents::PAYMENT_DEFERMENT_APPROVE) {
            $date->modify('+3 days');
        }
        $newDate = $date->format('d.m.Y');

        $params->lastname = $order->lastname;
        $params->firstname = $order->firstname;
        $params->patronymic = $order->patronymic;
        $params->birth = $order->birth;
        $params->passport_serial = $order->passport_serial;
        $params->passport_issued = $order->passport_issued;
        $params->passport_date = $order->passport_date;
        $params->subdivision_code = $order->subdivision_code;
        $params->phone_mobile = $order->phone_mobile;
        $params->accept_sms = $order->accept_sms;
        $params->insurer = $order->insurer;
        $params->organization_id = $order->organization_id;
        $params->zaim_date = $loanData['ДатаЗайма'];
        $params->payment_date = $newDate;
        $params->accept_date = $order->accept_date;
        $params->contract_number = $loanData['НомерЗайма'];

        $this->documents->create_document(
            [
                'type' => $documentType,
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'contract_number' => $loanData['НомерЗайма'],
                'params' => $params,
                'organization_id' => $order->organization_id,
            ]
        );
    }

    /**
     * Получает данные о займе из 1С
     */
    private function getLoanData(object $order)
    {

        $site_id = $this->users->get_site_id_by_user_id($order->user_id);
        $balances = $this->soap->get_user_balances_array_1c($order->user_uid, $site_id);

        foreach ($balances as $loan) {
            $loanParts = explode('-', $loan['НомерЗайма']);
            $loanOrderId = end($loanParts);

            if ((int)$loanOrderId === (int)$order->order_id) {
                return $loan;
            }
        }

        $this->json_output(['error' => 'По данному займу не найдены данные!']);
    }

    /**
     * Есть ли выполненный скоринг hyper_c у заявки
     * Нужно для отображения чекбокса "Решение принято с учетом Hyper-C" в попапе одобрения заявки
     *
     * @param $order
     * @return bool
     */
    private function hasOrderHyperCScoring($order): bool
    {
        if (!$this->scorings->isHyperEnabledForOrder($order)) {
            return false;
        }

        $scorings = $this->scorings->get_scorings([
            'order_id' => (int)$order->order_id,
            'type' => $this->scorings::TYPE_HYPER_C
        ]);

        return !empty($scorings);
    }

    private function getRcl(int $orderId): ?stdClass
    {
        $rclTranche = $this->rcl->get_tranche([
            'order_id' => $orderId,
        ]);

        if (empty($rclTranche->rcl_contract_id)) {
            return null;
        }

        $rclContract = $this->rcl->get_contract([
            'id' => $rclTranche->rcl_contract_id,
        ]);

        return $rclContract ?: null;
    }

    /**
     * Получает NBKI score из последнего завершённого скоринга AXI
     *
     * @param int $orderId ID заявки
     * @return int|null Балл NBKI или null если не найден
     */
    private function getNbkiScore(int $orderId): ?int
    {
        $lastAxiScoring = $this->scorings->getLastScoring([
            'order_id' => $orderId,
            'type' => $this->scorings::TYPE_AXILINK_2,
            'status' => $this->scorings::STATUS_COMPLETED
        ]);

        if (empty($lastAxiScoring->body)) {
            return null;
        }

        $axiBody = json_decode($lastAxiScoring->body);
        
        return !empty($axiBody->nbki_score) ? (int)$axiBody->nbki_score : null;
    }
}
