<?php

use api\helpers\UserHelper;
use boostra\services\RegionService;
use boostra\services\UsersAddressService;
use lib\VirtualCard\VirtualCardService;

require_once __DIR__ . '/View.php';
require_once __DIR__ . '/../api/Scorings.php';
require_once __DIR__ . '/../api/enums/ProfessionEnum.php';

class AccountView extends View
{
    use \api\traits\JWTAuthTrait;

    private $max_file_size = 15 * 1024 * 1024;

    const SCORINGS_LIST = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_REPORT,
        Scorings::TYPE_UPRID,
    ];

    /**
     * Скоринги при провале которых нужно сделать отказ по заявке
     * Отказ могло не сделать сразу т.к. скоринг выполнялся на этапе заполнения анкеты, до появления заявки
     *
     * Ключ - id скоринга
     * Значение - id причины отказа для заявки
     */
    const CAN_REJECT_SCORINGS = [
        Scorings::TYPE_BLACKLIST => '2',
        Scorings::TYPE_LOCATION => '14',
        Scorings::TYPE_AGE => '23',
        Scorings::TYPE_SCORISTA => '5',
        Scorings::TYPE_EFRSB => '22',
        Scorings::TYPE_WORK => '40',
    ];

    /** @var int Максимальное кол-во номеров телефон, которое можно поменять */
    private const MAX_PHONES_TO_CHANGE = 1;

    /** @var int Максимальное кол-во попыток изменить номер телефона за день*/
    private const MAX_ATTEMPTS_TO_CHANGE_PHONE_PER_DAY = 2;

    /** @var int Сколько дней нужно ждать до возможности повторно сменить номер телефона */
    private const MIN_PERIOD_TO_ADD_NEW_PHONE_IN_DAYS = 1;

    private const LOG_FILE = 'account_view.txt';

    public function fetch()
    {
        $this->jwtAuthValidate();

        if ($this->request->method('post'))
        {
            $stage = $this->request->post('stage');

            switch ($stage):

                case 'personal_data':
                    $this->save_personal_data();
                    break;

                case 'address_data':
                    $this->save_address_data();
                    break;

                case 'accept_data':
                    $this->save_accept_data();
                    break;

                case 'additional_data':
                    $this->save_additional_data();
                    break;

                case 'add_files':
                    $this->save_files();
                    break;

            endswitch;

        }

        $this->design->assign('phone', $this->user->phone_mobile);

        if (!empty($_SESSION['order_id'])) {
            $user_order = $this->orders->get_order($_SESSION['order_id']);

            $this->design->assign('user_order', $user_order);
        }

        $bki_consent = $this->user_data->read($this->user->id, 'bki_consent');
        $this->design->assign('bki_consent', $bki_consent);

        return $this->registrationProcess();
    }

    private function saveRegistrationDate() {
        $this->user_data->set($this->user->id, 'registation_end', date('Y-m-d H:i:s'));
    }

    /**
     * Основной процесс регистрации
     * @return string
     * @throws Exception
     */
    private function registrationProcess(): string
    {

        // Проверяем, нужно ли вернуть клиента на этап адреса из-за длительного отсутствия
        if ($this->shouldReturnToAddressStage()) {
            $this->handleDropoutReturn();
        }

        if (empty($this->user->personal_data_added)) {
            $body = $this->pagePersonalData();
        } elseif (empty($this->user->address_data_added)) {
            $body = $this->pageAddressData();
        } elseif (empty($this->user->accept_data_added)) {
            $body = $this->pageAcceptData();
        } else {
            //Пробуем принять решение о продаже
            $this->bonondo->tryToSell($this->user->id);
            $is_rejected_nk = $this->user_data->read($this->user->id, 'is_rejected_nk');
            // Нажимал ли пользователь на ссылку в Баноне
            $rejected_nk_visited = $this->user_data->read($this->user->id, 'rejected_nk_visited');
            // Пользователь идет по короткому флоу
            $isShortFlowUser = $this->short_flow->isShortFlowUser($this->user->id);
            // Одобренная сумма
            $decisionSum = $this->scorings->getApproveAmountScoring($this->user->id);

            if ($is_rejected_nk === '0') {
                $this->bonondo->userApprove($this->user->id);
            }

            if ($is_rejected_nk > 0 && $rejected_nk_visited == 0) {
                $this->bonondo->userDecline($this->user->id);
                $body = $this->pageRejected();
            } elseif (empty($this->user->additional_data_added)) {
                $body = $this->pageAdditionalData();
            } elseif ($is_rejected_nk === '0' && empty($this->user->card_added)) {
                // Если скоринг завершен (is_rejected_nk === 0), показываем этап привязки карты (с автоподписанием) до загрузки фото
                $body = $this->pageCard();
            } elseif ($decisionSum) {
                // Если скоринг завершен и известна сумма одобрения
                if (empty($this->user->additional_data_added)) {
                    $body = $this->pageAdditionalData();
                } elseif (empty($this->user->card_added)) {
                    $body = $this->pageCard();
                } elseif (empty($this->user->files_added) && Helpers::isFilesRequired($this->user)) {
                    $body = $this->pageFiles();
                }
            } elseif (empty($this->user->files_added) && Helpers::isFilesRequired($this->user)) {
                // Проверяем файлы даже без decisionSum, чтобы избежать бесконечного редиректа
                $body = $this->pageFiles();
            } elseif (!isset($is_rejected_nk) && !$isShortFlowUser) {
                // Ждём пока проведётся проверка клиента на ранний отказ
                // Если мы понимаем, что клиент точно отказной - карту можно продать партнёрам
                $body = $this->design->fetch('account_wait_scorings.tpl');
            } elseif (empty($this->user->card_added)) {
                $body = $this->pageCard();
            }
        }

        if (empty($body)) {
            // Для AUTOCONFIRM_2_FLOW - вызываем completeOrderIfDataFinished
            if ($last_order = $this->orders->get_last_order($this->user->id)) {
                $this->completeOrderIfDataFinished($last_order->id);
            }

            $this->request->redirect($this->config->root_url . '/user');
        }

        return $body;
    }

    // Функция нормализации названия региона
    public function normalizeRegionName($regionName): string
    {
        $regionName = mb_strtolower($regionName);
        $regionName = preg_replace('/^г\s+/ui', '', $regionName);
        $regionName = str_ireplace(
            [' область', ' край', ' республика', ' респ', ' город', ' г', ' ао', ' обл', ' авт. окр', ' автономный округ', 'автономная область'],
            '',
            $regionName
        );
        return trim($regionName);
    }

    private function save_personal_data()
    {
        $user = new StdClass();

        $user->passport_serial  = (string)$this->request->post( 'passportCode' );
        $user->passport_date    = (string)$this->request->post( 'passportDate' );

        $validPassportDate = Helpers::validate_passport_date($this->user->birth, $user->passport_date);
        if (!$validPassportDate) {
            $this->design->assign('error', 'invalid_passport_date');
            return;
        }

        $user->subdivision_code = (string)$this->request->post( 'subdivisionCode' );
        $user->passport_issued  = mb_convert_case( $this->request->post( 'passportWho' ), MB_CASE_UPPER );

        $user->passport_date = $this->users->tryFormatDate($user->passport_date);

        //        $user->email = (string)$this->request->post('email');
        $user->gender         = (string)$this->request->post( 'gender' );
        $user->birth_place    = mb_convert_case( $this->request->post( 'birth_place' ), MB_CASE_UPPER );
//        $user->marital_status = (string)$this->request->post( 'marital_status' );

        $user->phone_mobile = $this->user->phone_mobile;
        $user->firstname    = $this->user->firstname;
        $user->lastname     = $this->user->lastname;
        $user->patronymic   = $this->user->patronymic;

        $this->design->assign( 'user', $user );

        $passport_user_id = (int) $this->users->get_passport_user( $user->passport_serial, $this->user->id );

        if( empty( $this->is_admin ) && !empty( $passport_user_id ) && $passport_user_id !== (int)$this->user->id ){

            // Шифруем телефон
            $existing_user = $this->users->get_user( $passport_user_id );
            $existing_user->phone_mobile_obfuscated = preg_replace(
                '@(\d{2})\d*(\d{2})@',
                '+$1*******$2',
                $existing_user->phone_mobile
            );

            $this->design->assign('existing_user', $existing_user);

            $oldPhone = (string)$this->request->post( 'phone');
            $oldPhone = preg_replace('/\D/', '', $oldPhone);

            // Показываем модалку с вводом номера телефона
            if (empty($oldPhone)) {
                $this->design->assign('change_phone', true);
            } else {
                $this->checkCanUserChangePhone($existing_user, $oldPhone, $this->user->phone_mobile);
            }

        }elseif( empty( $user->passport_serial ) || ! $this->helpers::validatePassport( $user->passport_serial ) ){
            $this->design->assign( 'error', 'empty_passportCode' );
        }elseif( empty( $user->passport_date ) ){
            $this->design->assign( 'error', 'empty_passportDate' );
        }elseif( empty( $user->subdivision_code ) ){
            $this->design->assign( 'error', 'empty_subdivisionCode' );
        }elseif( empty( $user->passport_issued ) ){
            $this->design->assign( 'error', 'empty_passportWho' );
        }elseif( empty( $user->gender ) ){
            $this->design->assign( 'error', 'empty_gender' );
//        }elseif( empty( $user->marital_status ) ){
//            $this->design->assign( 'error', 'empty_marital' );
        }elseif( empty( $user->birth_place ) ){
            $this->design->assign( 'error', 'empty_birth_place' );
        }else{
            $user->personal_data_added      = 1;
            $user->personal_data_added_date = date( 'Y-m-d H:i:s' );
            $user->missing_real_date        = date( 'Y-m-d H:i:s' );

            $user = array_map( 'strip_tags', (array)$user );

            if( $this->users->update_user( $this->user->id, $user ) ){

	            if ((int)$this->user_data->read($this->user->id, "is_virtual_card_consent") === 1) {
		            $vcService = new VirtualCardService($this->config, $this->user->id, 0);
		            $vcService->registerVirtualCard();
	            }

                header( 'Location: ' . $this->config->root_url . '/account' );
                exit;
            }else{
                $this->design->assign( 'save_error', 1 );
            }
        }
    }
    public function save_address_data()
    {
        $user = new StdClass();

        $user->Regindex = (string)$this->request->post('Regindex');
        $user->Regregion = (string)$this->request->post('Regregion');
        $user->Regcity = (string)$this->request->post('Regcity');
        $user->Regstreet = (string)$this->request->post('Regstreet');
        $user->Reghousing = (string)$this->request->post('Reghousing');
        $user->Regbuilding = (string)$this->request->post('Regbuilding');
        $user->Regroom = (string)$this->request->post('Regroom');
        $user->Regregion_shorttype = (string)$this->request->post('Regregion_shorttype');
        $user->Regcity_shorttype = (string)$this->request->post('Regcity_shorttype');
        $user->Regstreet_shorttype = (string)$this->request->post('Regstreet_shorttype');

        $user->Faktindex = (string)$this->request->post('Faktindex');
        $user->Faktregion = (string)$this->request->post('Faktregion');
        $user->Faktcity = (string)$this->request->post('Faktcity');
        $user->Faktstreet = (string)$this->request->post('Faktstreet');
        $user->Fakthousing = (string)$this->request->post('Fakthousing');
        $user->Faktbuilding = (string)$this->request->post('Faktbuilding');
        $user->Faktroom = (string)$this->request->post('Faktroom');
        $user->Faktregion_shorttype = (string)$this->request->post('Faktregion_shorttype');
        $user->Faktcity_shorttype = (string)$this->request->post('Faktcity_shorttype');
        $user->Faktstreet_shorttype = (string)$this->request->post('Faktstreet_shorttype');

        $equal = $this->request->post('equal', 'integer');
        $this->design->assign('equal', $equal);

        if($equal)
        {
            $user->Faktindex = $user->Regindex;
            $user->Faktregion = $user->Regregion;
            $user->Faktcity = $user->Regcity;
            $user->Faktstreet = $user->Regstreet;
            $user->Fakthousing = $user->Reghousing;
            $user->Faktbuilding = $user->Regbuilding;
            $user->Faktroom = $user->Regroom;
            $user->Faktregion_shorttype = $user->Regregion_shorttype;
            $user->Faktcity_shorttype = $user->Regcity_shorttype;
            $user->Faktstreet_shorttype = $user->Regstreet_shorttype;
        }

        $this->design->assign('user', $user);

        if (empty($user->Regregion))
            $this->design->assign('error', 'empty_Regregion');
        elseif (empty($user->Regcity))
            $this->design->assign('error', 'empty_Regcity');
//        elseif (empty($user->Reghousing))
//            $this->design->assign('error', 'empty_Reghousing');
        elseif (empty($user->Faktregion))
            $this->design->assign('error', 'empty_Faktregion');
        elseif (empty($user->Faktcity))
            $this->design->assign('error', 'empty_Faktcity');
//        elseif (empty($user->Fakthousing))
//            $this->design->assign('error', 'empty_Fakthousing');
        else
        {
            try {
                $usersAddressService = new UsersAddressService();

                $registrationAddress = $usersAddressService->getRegistrationAddress($this->request);
                $factualAddress = $equal ? $registrationAddress : $usersAddressService->getFactualAddress($this->request);

                $user->registration_address_id = $usersAddressService->saveNewAddress($registrationAddress);
                $user->factual_address_id = $usersAddressService->saveNewAddress($factualAddress);

                $usersAddressService->saveOktmo($this->user->id, $registrationAddress);
            } catch (Throwable $e) {
                $this->logging(json_encode($_SERVER), 'registrationAddress', $e->getMessage() . $e->getTraceAsString() . $e->getFile() . $e->getLine(), [$registrationAddress ?? [], $factualAddress ?? []], 'users_addresses.txt');
            }

            $user->address_data_added = 1;
            $user->address_data_added_date = date('Y-m-d H:i:s');
            $user->missing_real_date = date('Y-m-d H:i:s');

            $user = array_map('strip_tags', (array)$user);

            $_SESSION['juicescore_session_id'] = $this->request->post('juicescore_session_id');
            if (empty($_SESSION['juicescore_session_id']) && !empty($_COOKIE['juicescore_session_id'])) {
                $_SESSION['juicescore_session_id'] = $_COOKIE['juicescore_session_id'];
            }

            if ($this->users->update_user($this->user->id, $user))
            {
                header('Location: '.$this->config->root_url.'/account');
                exit;
            }
            else
            {
                $this->design->assign('save_error', 1);
            }
        }
    }

    private function save_accept_data()
    {
        $user = new StdClass();

        $recomendation_amount = $this->users->get_recomendation_amount($this->user);
        $this->design->assign('user', $user);

        if ($recomendation_amount > 0)
        {
            $_SESSION['juicescore_session_id'] = $this->request->post('juicescore_session_id');
            if (empty($_SESSION['juicescore_session_id']) && !empty($_COOKIE['juicescore_session_id'])) {
                $_SESSION['juicescore_session_id'] = $_COOKIE['juicescore_session_id'];
            }
            $useragent = $this->request->post('juicescore_useragent') ?? $_SERVER['HTTP_USER_AGENT'];

            $local_time = (string)$this->request->post('local_time');

            $user->accept_data_added = 1;
            $user->accept_data_added_date = date('Y-m-d H:i:s');
            $user->missing_real_date = date('Y-m-d H:i:s');
            if ($this->users->update_user($this->user->id, $user))
            {
                $this->user = $this->users->get_user((int)$this->user->id);
                $period = empty($this->user->first_loan_period) ? 16 : $this->user->first_loan_period;

                if ($user_last_order = $this->orders->get_last_order($this->user->id))
                {
                    if ($user_last_order->organization_id == $this->organizations::BOOSTRA_ID) {
                        $organization_id = $this->organizations->get_base_organization_id(['user_id' => $this->user->id]);
                    } else {
                        $organization_id = $user_last_order->organization_id;
                    }
                    $organization = $this->organizations->get_organization($organization_id);

                    $order_id = $user_last_order->id;
                    $update_order = [
                        'amount' => (string)$recomendation_amount,
                        'period' => $period,
                        'date' => date('Y-m-d H:i:s'),
                        'order_uid' => exec($this->config->root_dir . 'generic/uidgen'),
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'juicescore_session_id' => $user_last_order->juicescore_session_id ?: $_SESSION['juicescore_session_id'] ?? '',
                        'utm_source' => empty($_COOKIE["utm_source"]) ? $this->settings->partner_for_onec_exchange : $_COOKIE["utm_source"],
                        'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                        'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                        'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                        'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                        'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                        'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],
                        'percent' => $period > $this->orders::MAX_PERIOD ? $this->orders::BASE_PERCENTS : 0,
                        'b2p' => 1,
                        'organization_id' => $organization->id
                    ];
                    $this->orders->update_order($order_id, $update_order);
                    $order = (array)$this->orders->get_order($order_id);
                }
                else
                {
                    $organization_id = $this->organizations->get_base_organization_id(['user_id' => $this->user->id]);
                    $organization = $this->organizations->get_organization($organization_id);
                    // создаем в базе order
                    $order = array(
                        'amount' => $recomendation_amount,
                        'period' => $period,
                        'user_id' => $this->user->id,
                        'ip' => $_SERVER['REMOTE_ADDR'],
                        'juicescore_session_id' => $_SESSION['juicescore_session_id'] ?? '',
                        'first_loan' => 1,
                        'date' => date('Y-m-d H:i:s'),
                        'local_time' => $local_time,

                        'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
                        'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                        'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                        'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                        'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                        'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                        'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],

                        'percent' => $period > $this->orders::MAX_PERIOD ? $this->orders::BASE_PERCENTS : 0,
                        'organization_id' => $organization->id,
                    );

                    $order['order_uid'] = exec($this->config->root_dir . 'generic/uidgen');;

                    if ($this->user->use_b2p)
                        $order['b2p'] = 1;

                    $order_id = $this->orders->add_order($order);
                    $this->user_data->set($this->user->id, 'bonon_wait_order_decision', $order_id);
                    $this->user_data->set($this->user->id, 'order_from_register', $order_id);
                    $this->order_data->set($order_id, $this->order_data::USER_AMOUNT, $this->user->first_loan_amount ?: $recomendation_amount);
                }
                $_SESSION['order_id'] = $order_id;

                $this->orders->saveFinkartaFp($order_id, $this->request->post('finkarta_fp'));

                if (!empty($useragent))
                    $this->order_data->set($order_id, $this->order_data::USERAGENT, $useragent);

                /** @var int $rejectedScoringType Если заполнено - заявка отправится в отказ из-за этого скоринга */
                $rejectedScoringType = null;
                $isAxiExist = false;
                if ($user_scorings = $this->scorings->get_scorings(array('user_id' => $this->user->id))) {
                    foreach ($user_scorings as $user_scoring) {
                        $this->scorings->update_scoring($user_scoring->id, array('order_id' => $order_id));

                        if ($user_scoring == $this->scorings::TYPE_AXILINK_2) {
                            $isAxiExist = true;
                        }

                        if ($user_scoring->status == $this->scorings::STATUS_COMPLETED && $user_scoring->success == 0) {
                            // Скоринг провален
                            if (!empty(self::CAN_REJECT_SCORINGS[$user_scoring->type])) {
                                if ($user_scoring->type == $this->scorings::TYPE_SCORISTA &&
                                    !$this->scorings->isScoristaAllowed((object)$order)) {
                                    continue;
                                }

                                // Заявка идёт в отказ
                                $rejectedScoringType = $user_scoring->type;
                            }
                        }
                    }
                }

                if (!$isAxiExist) {
                    $this->scorings->add_scoring([
                        'user_id' => $this->user->id,
                        'order_id' => $order_id,
                        'type' => $this->scorings::TYPE_AXILINK_2,
                    ]);
                }

                //Отправка заявки в 1с со всеми заполнеными клиентом данными на 1, 2, 3 этапах
                $loan = array(
                    'УИД' => $order['order_uid'],
                    'ДатаЗаявки' => date('YmdHis', strtotime($order['date'])),
                    'ИННОрганизации' => $organization->inn,
                    'lastname' => (string)$this->user->lastname,
                    'firstname' => (string)$this->user->firstname,
                    'patronymic' => (string)$this->user->patronymic,
                    'birth' => (string)$this->user->birth,
                    'phone_mobile' => (string)$this->user->phone_mobile,
                    'email' => (string)$this->user->email,
                    'passport_serial' => (string)$this->user->passport_serial,
                    'passport_date' => (string)$this->user->passport_date,
                    'subdivision_code' => (string)$this->user->subdivision_code,
                    'passport_issued' => (string)$this->user->passport_issued,

                    'АдресРегистрацииИндекс' => (string)$this->user->Regindex,
                    'Regregion' => (string)trim($this->user->Regregion.' '.$this->user->Regregion_shorttype),
                    'Regdistrict' => (string)$this->user->Regdistrict,
                    'Regcity' => (string)trim($this->user->Regcity.' '.$this->user->Regcity_shorttype),
                    'Reglocality' => '',
                    'Regstreet' => (string)trim($this->user->Regstreet.' '.$this->user->Regstreet_shorttype),
                    'Regbuilding' => (string)$this->user->Regbuilding,
                    'Reghousing' => (string)$this->user->Reghousing,
                    'Regroom' => (string)$this->user->Regroom,

                    'АдресФактическогоПроживанияИндекс' => (string)$this->user->Faktindex,
                    'Faktregion' => (string)trim($this->user->Faktregion.' '.$this->user->Faktregion_shorttype),
                    'Faktdistrict' => (string)$this->user->Faktdistrict,
                    'Faktcity' => (string)trim($this->user->Faktcity.' '.$this->user->Faktcity_shorttype),
                    'Faktlocality' => '',
                    'Faktstreet' => (string)trim($this->user->Faktstreet.' '.$this->user->Faktstreet_shorttype),
                    'Faktbuilding' => (string)$this->user->Faktbuilding,
                    'Fakthousing' => (string)$this->user->Fakthousing,
                    'Faktroom' => (string)$this->user->Faktroom,

                    'site_id' => 'Boostra',
                    'partner_id' => '',
                    'partner_name' => 'Boostra',

                    'amount' => (string)$recomendation_amount,
                    'period' => empty($this->user->first_loan_period) ? 16 : (string)$this->user->first_loan_period,

                    'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
                    'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                    'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                    'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                    'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                    'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                    'click_hash' => empty($_COOKIE["click_hash"]) ? '' : $_COOKIE["click_hash"],

                    'id' => '',
                    'car' => '',
                    'IntervalNumber' => '',
                    'СтатусCRM' => '',
                    'СуммаCRM' => (string)$recomendation_amount,
                    'УИД_CRM' => $order['order_uid'],

                    'МестоРождения' => (string)$this->user->birth_place,
                    'ГородскойТелефон' => (string)$this->user->landline_phone,
                    'Пол' => (string)$this->user->gender,
                    'ДевичьяФамилияМатери' => '',

                    'СфераРаботы' => (string)$this->user->work_scope,

                    'ДоходОсновной' => (string)$this->user->income_base,
                    'ДоходДополнительный' => (string)$this->user->income_additional,
                    'ДоходСемейный' => (string)$this->user->income_family,
                    'ФинансовыеОбязательства' => (string)$this->user->obligation,
                    'ПлатежиПоКредитамВМесяц' => (string)$this->user->other_loan_month,
                    'СколькоКредитов' => (string)$this->user->other_loan_count,
                    'КредитнаяИстория' => (string)$this->user->credit_history,
                    'МаксимальноОдобренныйРанееКредит' => (string)$this->user->other_max_amount,
                    'ПоследнийОдобренныйРанееКредит' => (string)$this->user->other_last_amount,
                    'БылоЛиБанкротство' => (string)$this->user->bankrupt,
                    'Образование' => (string)$this->user->education,
                    'СемейноеПоложение' => '',
                    'КоличествоДетей' => (string)$this->user->childs_count,
                    'НаличиеАвтомобиля' => (string)$this->user->have_car,
                    'НаличиеНедвижимости' => (int)$this->user->has_estate,
                    'ВК' => (string)$this->user->social_vk,
                    'Инст' => (string)$this->user->social_inst,
                    'Фейсбук' => (string)$this->user->social_fb,
                    'ОК' => (string)$this->user->social_ok,

                    'ServicesSMS' => 0, //$this->user->service_sms,
                    'ServicesInsure' => $this->user->service_insurance,
                    'ServicesReason' => 0, //$this->user->service_reason,
                );

                $negative_scoring = 0;
                $scoring_reason = '';
                /*
                $scorings = $this->scorings->get_scorings(array('user_id'=>$this->user->id));
                foreach ($scorings as $scoring)
                {
                    if (empty($scoring->success))
                    {
                        $negative_scoring = 1;
                        $scoring_reason = $this->scorings->get_reason($scoring->type);
                    }
                }
                */
                $loan['ОтказНаСайте'] = $negative_scoring;
                $loan['ПричинаОтказаНаСайте'] = $scoring_reason;

                $contact_person_name = array();
                $contact_person_phone = array();
                $contact_person_relation = array();
                if ($contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$this->user->id)))
                {
                    foreach ($contactpersons as $contactperson)
                    {
                        $contact_person_name[] = (string)$contactperson->name;
                        $contact_person_phone[] = (string)$contactperson->phone;
                        $contact_person_relation[] = (string)$contactperson->relation;
                    }
                }


                $loan['КонтактноеЛицоФИО'] = json_encode($contact_person_name);
                $loan['КонтактноеЛицоТелефон'] = json_encode($contact_person_phone);
                $loan['КонтактноеЛицоРодство'] = json_encode($contact_person_relation);



                if ($this->user->work_scope == 'Пенсионер')
                {
                    $loan['Занятость'] = '';
                    $loan['Профессия'] = '';
                    $loan['МестоРаботы'] = '';
                    $loan['СтажРаботы'] = '';
                    $loan['ШтатРаботы'] = '';
                    $loan['ТелефонОрганизации'] = '';
                    $loan['ФИОРуководителя'] = '';

                    $loan['АдресРаботы'] = '';
                }
                else
                {
                    $loan['Занятость'] = (string)$this->user->employment;
                    $loan['Профессия'] = (string)$this->user->profession;
                    $loan['МестоРаботы'] = (string)$this->user->workplace;
                    $loan['СтажРаботы'] = (string)$this->user->experience;
                    $loan['ШтатРаботы'] = (string)$this->user->work_staff;
                    $loan['ТелефонОрганизации'] = (string)$this->user->work_phone;
                    $loan['ФИОРуководителя'] = (string)$this->user->workdirector_name;

                    $loan['АдресРаботы'] = $this->user->Workindex.' '.$this->user->Workregion.', '.$this->user->Workcity.', ул.'.$this->user->Workstreet.', д.'.$this->user->Workhousing;
                    if (!empty($this->user->Workbuilding))
                        $loan['АдресРаботы'] .= '/'.$this->user->Workbuilding;
                    if (!empty($this->user->Workroom))
                        $loan['АдресРаботы'] .= ', оф.'.$this->user->Workroom;
                }

                $loan = (object)$loan;
                $resp = $this->soap->send_loan($loan);

                if ($resp->return->id_zayavka == 'Не принято') {
                    sleep(3);
                    $loan->utm_source = 'Boostra';

                    $resp = $this->soap->send_loan($loan);
                }

                if (!empty($resp->return->id_zayavka) && $resp->return->id_zayavka != 'Не принято')
                {
                    $j = 10;
                    do {
                        sleep(2);
                        $uid_resp = $this->soap->get_uid_by_phone($this->user->phone_mobile);
                        $j--;
                    } while (empty($uid_resp->uid) && $j > 0);

                    if (!empty($uid_resp->uid)) {

                        $update['1c_id'] = $resp->return->id_zayavka;

                        // Если по заявке уже отказали - не возвращаем ей статус 1
                        if (!empty($order) && (int)$order['status'] !== $this->orders::STATUS_REJECTED) {
                            $update['status'] = $this->orders::STATUS_NEW;
                        }

                        $this->orders->update_order($order_id, $update);
                        $this->users->update_user($this->user->id, array('uid'=>$uid_resp->uid));
                        $this->user->uid = $uid_resp->uid;
                    }
                }
                else
                {
                    $this->design->assign('error', 'loan_not_accepted');
                }

                // Во время заполнения анкеты (до появления заявки) один из скорингов дал отказ
                if (!empty($rejectedScoringType)) {
                    $this->rejectOrder($order_id, $rejectedScoringType);
                }

                header('Location: '.$this->config->root_url.'/account');
                exit;
            }
            else
            {
                $this->design->assign('save_error', 1);
            }
        }
        else
        {
            $this->design->assign('save_error', 1);
        }

    }

    private function save_additional_data()
    {
        $organization_id = $this->organizations->get_base_organization_id(['user_id' => $this->user->id]);

        $user = new StdClass();

        $user->profession = (string)$this->request->post('profession');
        $user->work_scope = (string)$this->request->post('work_scope');
        $user->workplace = (string)$this->request->post('workplace');
        $user->work_phone = (string)$this->request->post('work_phone');
        $user->workdirector_name = (string)$this->request->post('workdirector_name');
        $user->income_base = (string)$this->request->post('income_base');
        $user->education = (string)$this->request->post('education');

        $user->has_estate = (int)$this->request->post('has_estate');

        $user->Workindex = (string)$this->request->post('Regindex');
        $user->Workregion = (string)$this->request->post('Regregion');
        $user->Workcity = (string)$this->request->post('Regcity');
        $user->Workstreet = (string)$this->request->post('Regstreet');
        $user->Workhousing = (string)$this->request->post('Reghousing');
        $user->Workbuilding = (string)$this->request->post('Regbuilding');
        $user->Workroom = (string)$this->request->post('Regroom');
        $user->Workregion_shorttype = (string)$this->request->post('Regregion_shorttype');
        $user->Workcity_shorttype = (string)$this->request->post('Regcity_shorttype');
        $user->Workstreet_shorttype = (string)$this->request->post('Regstreet_shorttype');

        $contact_person = array();
        $contact_person['name'] = (string)$this->request->post('contact_person_name');
        $contact_person['phone'] = (string)$this->request->post('contact_person_phone');
        $contact_person['relation'] = (string)$this->request->post('contact_person_relation');

        if (!empty($is_virtual_card_consent = $this->request->post('virtual_card'))) {
            $this->user_data->set($this->user->id, "is_virtual_card_consent", $is_virtual_card_consent);
	        $vcService = new VirtualCardService($this->config, $this->user->id, 0);
	        $vcService->registerVirtualCard();
        }

        $this->design->assign('user', $user);

        /*if (empty($user->work_scope))
            $this->design->assign('error', 'empty_work_scope');
        elseif (($user->work_scope != 'Пенсионер' && $user->work_scope != 'Безработный') && empty($user->profession))
            $this->design->assign('error', 'empty_profession');
        elseif (($user->work_scope != 'Пенсионер' && $user->work_scope != 'Безработный') && empty($user->workplace))
            $this->design->assign('error', 'empty_workplace');
        elseif (($user->work_scope != 'Пенсионер' && $user->work_scope != 'Безработный') && empty($user->work_phone))
            $this->design->assign('error', 'empty_work_phone');
        elseif (($user->work_scope != 'Пенсионер' && $user->work_scope != 'Безработный') && empty($user->workdirector_name))
            $this->design->assign('error', 'empty_workdirector_name');
        elseif (($user->work_scope != 'Пенсионер' && $user->work_scope != 'Безработный') && empty($user->Workregion))
            $this->design->assign('error', 'empty_Workregion');
        elseif (($user->work_scope != 'Пенсионер' && $user->work_scope != 'Безработный') && empty($user->Workcity))
            $this->design->assign('error', 'empty_Workcity');
        elseif (($user->work_scope != 'Пенсионер' && $user->work_scope != 'Безработный') && empty($user->Workhousing))
            $this->design->assign('error', 'empty_Workhousing');*/
        if ($user->income_base == '')
            $this->design->assign('error', 'empty_income_base');
        elseif ($user->education == '')
            $this->design->assign('error', 'empty_education');
        /*elseif (empty($contact_person['name']))
            $this->design->assign('error', 'empty_contact_person_name');
        elseif (empty($contact_person['phone']))
            $this->design->assign('error', 'empty_contact_person_phone');*/
        else
        {
            $_SESSION['juicescore_session_id'] = $this->request->post('juicescore_session_id');
            if (empty($_SESSION['juicescore_session_id']) && !empty($_COOKIE['juicescore_session_id'])) {
                $_SESSION['juicescore_session_id'] = $_COOKIE['juicescore_session_id'];
            }
            $useragent = $this->request->post('juicescore_useragent') ?? $_SERVER['HTTP_USER_AGENT'];

            $local_time = (string)$this->request->post('local_time');

            $user->additional_data_added = 1;
            $user->additional_data_added_date = date('Y-m-d H:i:s');
            $user->missing_real_date  = date('Y-m-d H:i:s');

            $user = array_map('strip_tags', (array)$user);

            if ($this->users->update_user($this->user->id, $user))
            {
                $this->tryAddHyperC();

                $this->user = $this->users->get_user((int)$this->user->id);

                $this->contactpersons->delete_user_contactpersons($this->user->id);
                //$this->contactpersons->add_contactperson(array_merge(array('user_id'=>$this->user->id), $contact_person));

                $is_user_credit_doctor = $this->request->post('is_user_credit_doctor', 'integer');

                if ($last_order = $this->orders->get_last_order($this->user->id)) {

                    $cross_orders_offset = 0;
                    while (!empty($last_order) && $last_order->utm_source == $this->orders::UTM_SOURCE_CROSS_ORDER) {
                        $this->soap->set_order_complete($last_order->id);

                        $cross_orders_offset++;
                        $last_order = (object)$this->orders->get_previous_order($this->user->id, $cross_orders_offset);
                    }

                    $this->orders->saveFinkartaFp($last_order->id, $this->request->post('finkarta_fp'));

                    // Если по заявке уже отказали, или она уже выдана - не возвращаем ей статус 1
                    $exception_statuses = [
                        $this->orders::STATUS_REJECTED,
                        $this->orders::STATUS_CONFIRMED,
                        $this->orders::STATUS_APPROVED,
                    ];
                    if (!in_array($last_order->status, $exception_statuses))
                    {
                        $update_order = [
                            'date' => date('Y-m-d H:i:s'),
                            'is_user_credit_doctor' => $is_user_credit_doctor,
                            'b2p' => $this->user->use_b2p,
                            'status' => 1,
                            'percent' => $last_order->period > $this->orders::MAX_PERIOD ? $this->orders::BASE_PERCENTS : 0,
                        ];
                        if ($last_order->organization_id == $this->organizations::BOOSTRA_ID) {
                            $update_order['organization_id'] = $organization_id;
                        }
                        $this->orders->update_order($last_order->id, $update_order);

                        $this->events->add_event(array(
                            'user_id' => $this->user->id,
                            'event' => $is_user_credit_doctor ? $this->events::ORDER_CD_ENABLED : $this->events::ORDER_CD_DISABLED,
                            'created' => date('Y-m-d H:i:s'),
                        ));

                        $last_order_array = (array)$last_order;

                        if ($last_order_array['1c_id']) {
                            // фикс для старыx заявок
                            $resp = $this->orders->check_order_1c($last_order_array['1c_id']);
                            if (!empty($resp->return->Статус) && in_array($resp->return->Статус, ['2.Отказано', '7.Технический отказ'])) {
                                $this->soap->update_status_1c($last_order_array['1c_id'], 'Рассматривается', '', $last_order_array['amount'], $last_order_array['percent']);
                            }
                        }
                    }
                }
                $_SESSION['order_id'] = $last_order->id;

                if (!empty($useragent))
                    $this->order_data->set($last_order->id, $this->order_data::USERAGENT, $useragent);

                if ($user_scorings = $this->scorings->get_scorings(array('user_id' => $this->user->id)))
                    foreach ($user_scorings as $user_scoring)
                        $this->scorings->update_scoring($user_scoring->id, array('order_id' => $last_order->id));

                // добавляем скоринги в задание
                $scoring_data = array(
                    'user_id' => $this->user->id,
                    'order_id' => $last_order->id,
                    'status' => Scorings::STATUS_NEW,
                    'created' => date('Y-m-d H:i:s'),
                );

                $activeScoringsType = $this->scorings->get_types(['active' => 1]);
                $activeScoringsTypeId = array_column($activeScoringsType, 'id');

                foreach (self::SCORINGS_LIST as $type) {
                    $scoring_data['type'] = $type;

                    // Если скоринг выключен - не добавляем его
                    if (!in_array($scoring_data['type'], $activeScoringsTypeId))
                        continue;

                    // Не добавляем уже существующие скоринги
                    $already_exists = false;
                    // Если скоринг старше суток - нужно его пересоздать
                    $is_old = false;
                    if (!empty($user_scorings)) {
                        foreach ($user_scorings as $scoring) {
                            // Отдельно отслеживаем завершённую скористу и акси
                            if ($scoring->type == $this->scorings::TYPE_SCORISTA ||
                                $scoring->type == $this->scorings::TYPE_AXILINK_2) {
                                if ($scoring->status == $this->scorings::STATUS_COMPLETED &&
                                    $scoring->success == 1) {
                                    // Обновляем балл в заявке, если он пустой
                                    // ИЛИ если это скориста (Затираем балл акси)
                                    if (empty($last_order->scorista_ball) || $scoring->type == $this->scorings::TYPE_SCORISTA) {
                                        $this->orders->update_order($last_order->id, [
                                            'scorista_ball' => $scoring->scorista_ball
                                        ]);
                                    }
                                }
                            }

                            if ($scoring->type == $type && $scoring->status != $this->scorings::STATUS_ERROR) {
                                $already_exists = true;

                                if ($scoring->status == $this->scorings::STATUS_COMPLETED) {
                                    // Выбираем первую не пустую дату (Обычно end_date)
                                    $end_date = new DateTime($scoring->end_date ?? $scoring->start_date ?? $scoring->created);
                                    // Сравниваем с текущей датой
                                    $now = new DateTime();
                                    $interval = $now->diff($end_date);
                                    // Если с времени выполнения скоринга прошло больше суток - он старый
                                    $is_old = $interval->days >= 1 || $interval->h >= 24;

                                    if (!$is_old) {
                                        // Скоринг актуален
                                        if ($scoring->success == 0) {
                                            // Скоринг провален
                                            if (!empty(self::CAN_REJECT_SCORINGS[$scoring->type])) {
                                                if (
                                                    $scoring->type == $this->scorings::TYPE_SCORISTA &&
                                                    !$this->scorings->isScoristaAllowed($last_order)
                                                ) {
                                                    continue;
                                                }

                                                // При провале надо отказать
                                                $this->rejectOrder($last_order->id, $scoring->type);
                                            }
                                        }
                                        else {
                                            // Скоринг успешен, проверяем нужно ли добавлять скористу и акси
                                            $this->scorings->tryAddScoristaAndAxi($last_order->id);
                                        }
                                    }
                                }

                                break;
                            }
                        }
                    }

                    if (!$already_exists || $is_old) {
                        $this->scorings->add_scoring($scoring_data);
                    }
                }

                // Доп.проверка на проваленную скористу
                foreach ($user_scorings as $scoring) {
                    if ($scoring->type == $this->scorings::TYPE_SCORISTA &&
                        $scoring->status == $this->scorings::STATUS_COMPLETED &&
                        $scoring->success == 0) {
                        if ($this->scorings->isScoristaAllowed($last_order)) {
                            $this->rejectOrder($last_order->id, $scoring->type);
                        }
                    }
                }

                if (empty($this->settings->dbrain_auto)) {
                    $this->settings->dbrain_auto = $last_order->id;
                    $this->orders->update_order($last_order->id, ['autoretry' => 2]);
                }

                $service_insurance = $this->request->post('service_insurance', 'integer');
                $this->users->update_user($this->user->id, ['service_insurance' => $service_insurance]);
                $this->post_back->sendPostBackNewOrder($last_order);

                try {
                    $this->soap->change_order_insure($last_order->{'1c_id'}, $this->user->uid, $service_insurance);
                } catch (Throwable $e) {}

                $this->checkStatusMissing($this->user->id, $this->user->phone_mobile);

                if (!$this->canUserAccessLk($last_order)) {
                    $this->users->update_user($this->user->id, [
                        'card_added' => 0,
                    ]);

                    header('Location: '.$this->config->root_url.'/account');
                    exit;
                }

	            $is_virtual_card = (int)$this->request->post('is_virtual_card');
	            $this->users->update_user($this->user->id, ['is_virtual_card_consent' => $is_virtual_card]);

	            if ($is_virtual_card === 1) {
		            // Регистрация виртуальной карты
		            $vcService = new VirtualCardService($this->config, $this->user->id, (int)$last_order->id);
		            $vcService->registerVirtualCard();
	            }

                header('Location: '.$this->config->root_url.'/user');
                exit;
            }
            else
            {
                $this->design->assign('save_error', 1);
            }
        }

    }

    public function save_files()
    {
        if (!empty($this->user->uid))
        {
            $returned = $this->notify->soap_send_files($this->user->id);
            if ($returned->return == 'OK')
            {
                $files = $this->users->get_files(array('user_id'=>$this->user->id, 'status'=>0));
                foreach ($files as $file)
                {

                    $this->users->update_file($file->id, array('status' => 1));

                    // удаляем оригинальные файлы, оставляем только ресайзы
                    if (file_exists($this->config->root_dir.$this->config->original_images_dir.$file->name))
                        unlink($this->config->root_dir.$this->config->original_images_dir.$file->name);

                }
                $this->users->update_user($this->user->id, array('file_uploaded' => 1, 'files_added'=>1));

                header('Location: '.$this->config->root_url.'/account');
            }
            else
            {
                $this->design->assign('error', 'error_upload');
            }

        }

    }

    private function canUserAccessLk(stdClass $last_order): bool
    {
        $cards = $this->best2pay->get_cards([
            'user_id' => $this->user->id,
            'deleted' => 0,
            'deleted_by_client' => 0,
        ]);

        $sbpAccounts = $this->users->getSbpAccounts((int)$this->user->id);

        $hasActiveOrder = (int)$last_order->status === $this->orders::ORDER_STATUS_CRM_ISSUED &&
            $last_order->{'1c_status'} !== $this->orders::ORDER_1C_STATUS_CLOSED;

        if (empty($cards) && empty($sbpAccounts) && !$hasActiveOrder) {
            return false;
        }

        return true;
    }

    /**
     * Проверка на сущ в отвлаах
     *
     * @param string|int $user_id
     * @param string|int $phone
     * @return void
     */
    private function checkStatusMissing($user_id, $phone): void
    {
        if (!$user_id) {
            return;
        }

        // проверка есть ли оно в отвалах
        $missing = $this->checkMissingUserDB($user_id);
        if (!$missing) {
            return;
        }

        // запрос в VOX есть ли он там
        $resVox = $this->send([
            "status" => json_encode(["ongoing"]),
            "campaign_id" => 963,
            "per-page" => 0,
            "phone" => $phone,
        ], 'searchContacts');


        // проверка на сущ
        if ($resVox["success"] && (count($resVox["result"]) > 0)) {
            $this->send([
                'id' => 332,
                'contacts' => json_encode(
                    [$phone]
                ),
                'comment' => 'вышел из отвала',
            ], 'addDncContacts', true);
        }
    }

    /**
     * Проверка отвал ил пользователь ещё
     *
     * @param $user_id
     * @return true
     */
    private function checkMissingUserDB($user_id): bool
    {
        $query = $this->db->placehold("SELECT 
            id as user_id
            FROM __users 
            WHERE  id  = $user_id ");

        $filesAdded = 'OR files_added = 0';
        if (!Helpers::isFilesRequired($this->user))
            $filesAdded = '';

        $query .= $this->db->placehold(" AND 
            (personal_data_added = 0
            OR address_data_added = 0
            OR accept_data_added = 0
            OR card_added = 0
            $filesAdded
            OR additional_data_added = 0
            ");
        $this->db->query($query);
        $result = json_decode(json_encode($this->db->result()), true);

        return (count((array) $result) > 0);
    }

    /**
     * Проверяет, завершено ли заполнение всех данных пользователем,
     * и если да - вызывает set_order_complete и фиксирует дату завершения регистрации
     *
     * @param int $order_id
     * @return void
     */
    private function completeOrderIfDataFinished(int $order_id): void
    {
        // Обновляем данные пользователя
        $this->user = $this->users->get_user((int)$this->user->id);

        // Если шаги НЕ завершены — выходим
        if (!$this->isRegistrationComplete($this->user)) {
            return;
        }

        $is_rejected_nk  = $this->user_data->read($this->user->id, 'is_rejected_nk');
        $autoConfirmType = $this->user_data->read($this->user->id, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW);
        $hasSbpAccountOrSelectedBank = $this->checkSetSbpOrBankSpb($order_id);

        $cards = $this->best2pay->get_cards([
           'user_id' => $this->user->id,
           'deleted' => 0,
           'deleted_by_client' => 0
        ]);

        if (!$hasSbpAccountOrSelectedBank && empty($cards) && !$is_rejected_nk) {
            // Возвращаем пользователя на выбор банка/привязку СБП
            $this->users->update_user($this->user->id, ['card_added' => 0]);
            $this->request->redirect($this->config->root_url . '/account');
        }

        // Все проверки пройдены — фиксируем дату завершения регистрации
        $this->saveRegistrationDate();

        // Если активный флоу НЕ равен ACTIVE_AUTOCONFIRM_FLOW_VALUE — завершаем
        if ($autoConfirmType !== $this->user_data::ACTIVE_AUTOCONFIRM_FLOW_VALUE) {
            $this->soap->set_order_complete($order_id);
        }
    }


    /**
     * Проверяет, подписаны ли документы АСП
     * @param int $order_id ID заявки
     * @return bool true если документы подписаны
     */
    private function isAspDocumentsSigned(int $order_id): bool
    {
        $aspSigned = $this->order_data->read($order_id, $this->order_data::AUTOCONFIRM_ASP);
        return !empty($aspSigned);
    }

    // Проверка завершенности шагов регистрации
    private function isRegistrationComplete(object $user): bool
    {
        return !empty($user->personal_data_added)
            && !empty($user->address_data_added)
            && !empty($user->accept_data_added)
            && !empty($user->additional_data_added)
            && !empty($user->card_added)
            && (Helpers::isFilesRequired($user) ? !empty($user->files_added) : true);
    }

    private function checkSetSbpOrBankSpb (int $orderId): bool
    {
        $sbpAccounts = $this->users->getSbpAccounts((int)$this->user->id);
        $bankIdForSbpIssuance = (int)$this->order_data->read($orderId, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);

        return !empty($sbpAccounts) || $bankIdForSbpIssuance > 0;
    }

    /**
     * Send API
     *
     * @param $data
     * @return array
     */
    private function send(array $data, string $method, bool $is_dnc = false)
    {
        $data["domain"] = 'boostra2023';
        $data["access_token"] = '92f5c9e3ea66018f60700a1e7f9f51be37d68758895df31b7feafe95b1eb02eb';

        $url = !$is_dnc ? 'https://kitapi-ru.voximplant.com/api/v3/agentCampaigns/' : 'https://kitapi-ru.voximplant.com/api/v3/dnc/';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'PHP-MCAPI/2.0',
            CURLOPT_POST => 1,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url . $method,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ]);

        $result = curl_exec($curl);
        curl_close($curl);

        return json_decode($result, true);
    }

    /**
     * Отказ по заявке по указанной причине.
     * Используется для скорингов которые могут отказать, но завершились до появления заявки.
     * @param int $order_id
     * @param int $scoring_type
     * @return void
     */
    function rejectOrder($order_id, $scoring_type)
    {
        if (empty($order_id) || empty($scoring_type))
            return;

        $order = $this->orders->get_order($order_id);
        if (empty($order) || $order->status != $this->orders::STATUS_NEW)
            return;

        if (
            $scoring_type == $this->scorings::TYPE_SCORISTA &&
            $this->scorings->isHyperEnabledForOrder($order)
        ) {
            return;
        }

        $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

        $reason_id =  self::CAN_REJECT_SCORINGS[$scoring_type];

        $update_order = [
            'status' => $this->orders::STATUS_REJECTED,
            'manager_id' => $tech_manager->id,
            'reason_id' => $reason_id,
            'reject_date' => date('Y-m-d H:i:s')
        ];
        $this->orders->update_order($order_id, $update_order);
        $this->leadgid->reject_actions($order_id);

        $changeLogs = Helpers::getChangeLogs($update_order, $order);
        $this->changelogs->add_changelog([
            'manager_id' => $tech_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'status',
            'old_values' => serialize($changeLogs['old']),
            'new_values' => serialize($changeLogs['new']),
            'order_id' => $order_id,
            'user_id' => $order->user_id,
        ]);

        $reason = $this->reasons->get_reason($reason_id);
        $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED_FOR_SEND, $tech_manager->name_1c, 0, 1, $reason->admin_name);

        if (!empty($order->is_user_credit_doctor))
            $this->soap->send_credit_doctor($order->id_1c);

        $this->soap->send_order_manager($order->id_1c, $tech_manager->name_1c);

        // отправляем заявку на кредитного доктора
        $this->cdoctor->send_order($order_id);

        // Останавливаем выполнения других скорингов по этой заявки
        $type = $this->scorings->get_type($scoring_type);
        $this->scorings->stopOrderScorings((int)$order_id, ['string_result' => 'Причина: скоринг ' . $type->admin_name]);
    }

    private function checkCanUserChangePhone(stdClass $existingUser, string $oldPhone, string $newPhone): void
    {
        $this->logging(__METHOD__, '', 'Смена номера при совпадении: ', ['new_user' => $this->user, 'existing_user' => $existingUser], self::LOG_FILE);

        // 1. Проверяем кол-во попыток сменить номера у нового пользователя и существующего за сегодня
        if (!$this->checkAttemptsAmountToChangePhone($existingUser)) {
            $this->showError('Достигнут лимит изменений номера телефона. Попробуйте позже.');
            return;
        }

        // 2. Проверка корректности старого номера телефона
        if ($existingUser->phone_mobile !== $oldPhone) {
            $this->showError('Сейчас нельзя изменить номер телефона.');
            return;
        }

        // 3. Проверка, что новый и существующий пользователь не заблокирован
        if (!empty($this->user->blocked) || !empty($existingUser->blocked)) {
            $this->showError('К сожалению, нельзя изменить номер телефона.');
            return;
        }

        $oldPhonesProperty = $this->user_data->get((int)$existingUser->id, $this->user_data::OLD_PHONES);
        $oldPhones = [];
        if (!empty($oldPhonesProperty)) {
            $oldPhones = json_decode($oldPhonesProperty->value);

            if (!is_array($oldPhones)) {
                $oldPhones = [];
            }
        }

        // 4. Проверяем кол-во успешно измененных номеров телефона
        if (!$this->checkChangedPhonesAmount($oldPhones, $newPhone)) {
            $this->showError('Достигнут лимит изменений номера телефона.');
            return;
        }

        $existingUserId = (int)$existingUser->id;

        // 5. Проверяем дату последнего изменения номера
        if (!$this->checkLastPhoneChangeDate($oldPhonesProperty)) {
            $this->showError('Вы уже недавно меняли номер телефона.');
            return;
        }

        // 6. Проверяем, что у существующего пользователя нет заявок в статусе Одобрено или Выдано
        if (!$this->checkOrdersStatus($existingUser)) {
            $this->showError('В данный момент нельзя изменить номер телефона.');
            return;
        }

        // 7. Проверяем, что новый телефон принадлежит существующему пользователю
        try {

            if ($this->user_data->isTestUser((int)$this->user->id)) {
                $this->logging(__METHOD__, '', '', 'Тестовый пользователь', self::LOG_FILE);
                $idxDecision = $this->idx::SUCCESS;
            } else {
                // Проверяем принадлежность нового номера телефона существующему клиенту
                $idxDecision = $this->idx->getIdxDecision($existingUser, $newPhone);
            }

        } catch (Throwable $e) {
            $error = [
                'Ошибка: ' . $e->getMessage(),
                'Файл: ' . $e->getFile(),
                'Строка: ' . $e->getLine(),
                'Подробности: ' . $e->getTraceAsString()
            ];
            $this->logging(__METHOD__, '', '', ['error' => $error], self::LOG_FILE);
            $this->showError('К сожалению, возникла ошибка. Повторите позднее или обратитесь в поддержку.');
            return;
        }

        if (empty($idxDecision)) {
            $idxDecision = $this->idx::NO_DECISION;
        }

        $this->user_data->set($existingUserId, $this->user_data::IDX_DECISION, $idxDecision);

        switch ($idxDecision) {

            case $this->idx::FAIL:
                $this->showError('Неуспешная верификация. Повторите позднее или обратитесь в поддержку. ');
                break;
            case $this->idx::NO_DECISION:
                $this->showError('Проверяем принадлежность номера телефона. Повторите позднее или обратитесь в поддержку.');
                break;
            case $this->idx::SUCCESS:
                if (!in_array($existingUser->phone_mobile, $oldPhones)) {
                    $oldPhones[] = $existingUser->phone_mobile;
                    $this->user_data->set((int)$existingUser->id, $this->user_data::OLD_PHONES, json_encode($oldPhones));
                }

                // Авторизовать в ЛК существующего пользователя
                $this->authenticateUser($existingUser, $newPhone);

                // Отправить на старый номер смс о смене номера
                $this->sendSmsSuccessChangePhone($existingUser);
                break;
            default:
                $this->logging(__METHOD__, '', 'Некорректный результат проверки IDX', ['idx_decision' => $idxDecision], 'account_view.txt');
                $this->showError('Возникла ошибка. Повторите позднее или обратитесь в поддержку.');
                break;
        }
    }

    private function checkAttemptsAmountToChangePhone(stdClass $existingUser): bool
    {
        $attemptsAmountToChangePhoneForExistingUser = $this->user_data->get((int)$this->user->id, $this->user_data::ATTEMPTS_AMOUNT_TO_CHANGE_PHONE);
        $attemptsAmountToChangePhoneForNewUser = $this->user_data->get((int)$existingUser->id, $this->user_data::ATTEMPTS_AMOUNT_TO_CHANGE_PHONE);

        $curDate = date('Y-m-d');
        $attemptAmountExistingUser = 0;
        if (!empty($attemptsAmountToChangePhoneForExistingUser) && date('Y-m-d', $attemptsAmountToChangePhoneForExistingUser->updated) !== $curDate) {
            $attemptAmountExistingUser = (int)$attemptsAmountToChangePhoneForExistingUser->value;
        }

        $attemptAmountNewUser = 0;
        if (!empty($attemptsAmountToChangePhoneForNewUser) && date('Y-m-d', $attemptsAmountToChangePhoneForNewUser->updated) !== $curDate) {
            $attemptAmountNewUser = (int)$attemptsAmountToChangePhoneForNewUser->value;
        }

        if (
            $attemptAmountExistingUser >= self::MAX_ATTEMPTS_TO_CHANGE_PHONE_PER_DAY ||
            $attemptAmountNewUser >= self::MAX_ATTEMPTS_TO_CHANGE_PHONE_PER_DAY
        ) {
            return false;
        }

        $this->user_data->set((int)$this->user->id, $this->user_data::ATTEMPTS_AMOUNT_TO_CHANGE_PHONE, ++$attemptAmountExistingUser);
        $this->user_data->set((int)$existingUser->id, $this->user_data::ATTEMPTS_AMOUNT_TO_CHANGE_PHONE, ++$attemptAmountNewUser);

        return true;
    }

    private function checkChangedPhonesAmount(array $oldPhones, string $newPhone): bool
    {
        if (count($oldPhones) < self::MAX_PHONES_TO_CHANGE) {
            return true;
        }

        // Если уже изменено максимальное кол-во телефонов, то разрешаем менять только на телефон, на который уже меняли
        return in_array($newPhone, $oldPhones);
    }

    private function checkLastPhoneChangeDate(?stdClass $oldPhonesProperty): bool
    {
        if (empty($oldPhonesProperty)) {
            return true;
        }

        $phoneUpdated = new DateTimeImmutable($oldPhonesProperty->updated);
        $curDate = new DateTimeImmutable();

        return $curDate->diff($phoneUpdated)->days > self::MIN_PERIOD_TO_ADD_NEW_PHONE_IN_DAYS;
    }

    private function checkOrdersStatus(stdClass $existingUser): bool
    {
        $orders = $this->orders->get_orders([
            'user_id' => (int)$existingUser->id
        ]);

        if (empty($orders)) {
            return true;
        }

        foreach ($orders as $order) {
            if (
                (int)$order->status == $this->orders::STATUS_APPROVED ||
                in_array($order->status_1c, [$this->orders::ORDER_1C_STATUS_APPROVED, $this->orders::ORDER_1C_STATUS_CONFIRMED])
            ) {
                return false;
            }
        }

        return true;
    }

    private function showError(string $errorText = ''): void
    {
        $this->logging(__METHOD__, '', '', ['result' => 'error', 'message' => $errorText], self::LOG_FILE);

        $this->design->assign('error', 'allready_exists');

        if (!empty($errorText)) {
            $this->design->assign('error_text', $errorText);
        }

        // Разлогинить пользователя
        unset($_SESSION['user_id']);
        unset($_SESSION['user_info']);
        unset($_SESSION['state']);
        unset($_SESSION[$this->account_contract::SESSION_KEY]);
        unset($_SESSION['passport_user']);
        unset($_SESSION['restricted_mode']);
        unset($_SESSION['restricted_mode_logout_hint']);
        setcookie('auth_jwt_token', null, time()-1, '/');
    }

    private function sendSmsSuccessChangePhone(stdClass $existingUser): void
    {
        $text_message = 'В ваш аккаунт вошли. Если это не вы, позвоните на телефон: 88003333073';

        $text = iconv('UTF-8', 'cp1251', $text_message);
        $result = $this->notify->send_sms($existingUser->phone_mobile, $text);
        $this->sms->add_message([
            'user_id' => (int)$existingUser->id,
            'phone' => $existingUser->phone_mobile,
            'message' => $text_message,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $result[1],
            'delivery_status' => '',
            'send_id' => $result[0],
            'type' => $this->sms::TYPE_SUCCESS_CHANGE_PHONE,
        ]);
    }

    private function authenticateUser(stdClass $existingUser, string $newPhone): void
    {
        // Изменить номер телефона существующего пользователя
        $update = [
            'phone_mobile' => $newPhone
        ];
        $this->users->update_user((int)$existingUser->id, $update);
        $this->soap->update_fields($existingUser->uid, $update);

        // Добавить user_id существующего пользователя в сессиию
        $_SESSION['user_id'] = (int)$existingUser->id;
        setcookie('user_id', (int)$existingUser->id, time() + 86400 * 365, '/');
        UserHelper::getJWTToken($this->config->jwt_secret_key, (int)$existingUser->id, 'auth_jwt_token', $this->config->jwt_expiration_time, true);

        // Удалить нового созданного пользователя
        $this->user_data->set((int)$this->user->id, $this->user_data::ATTEMPTS_AMOUNT_TO_CHANGE_PHONE);
        $this->users->delete_user((int)$this->user->id);

        $this->logging(__METHOD__, '', 'Удален пользователь с id: ' . $this->user->id, 'Id существующего клиента: ' . $existingUser->id, self::LOG_FILE);

        // Редирект в ЛК
        header('Location: ' . $this->config->root_url . '/user');
    }

    private function pageAddressData()
    {
        $this->checkEmailRedirect();

        if (($step = $this->request->get('step')) && $step == 'personal') {
            $this->users->update_user($this->user->id, array('personal_data_added' => 0));
            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        $autoConfirm2Flow = $this->user_data->read($this->user->id, $this->user_data::AUTOCONFIRM_2_FLOW);
        if (empty($autoConfirm2Flow)) {
            $this->users->initAutoConfirm2NewUser($this->user->id, $this->user);
        }

        // Получаем данные адресов из сессии (приоритет) и БД (fallback)
        $regCity = $_SESSION['user_info']['addresses'][2]['city'] ?? $this->user->Regcity ?? '';
        $regStreet = $_SESSION['user_info']['addresses'][2]['street'] ?? $this->user->Regstreet ?? '';
        $regHouse = $_SESSION['user_info']['addresses'][2]['house'] ?? $this->user->Reghousing ?? '';
        $regZipCode = $_SESSION['user_info']['addresses'][2]['zipCode'] ?? $this->user->Regindex ?? '';
        
        $faktCity = $_SESSION['user_info']['addresses'][1]['city'] ?? $this->user->Faktcity ?? '';
        $faktStreet = $_SESSION['user_info']['addresses'][1]['street'] ?? $this->user->Faktstreet ?? '';
        $faktHouse = $_SESSION['user_info']['addresses'][1]['house'] ?? $this->user->Fakthousing ?? '';
        $faktZipCode = $_SESSION['user_info']['addresses'][1]['zipCode'] ?? $this->user->Faktindex ?? '';

        $addressesEqual = (
            $regZipCode === $faktZipCode &&
            $regCity === $faktCity &&
            $regStreet === $faktStreet &&
            $regHouse === $faktHouse
        );

        $allFieldsEmpty = (
            empty($regZipCode) && empty($regCity) && empty($regStreet) &&
            empty($faktZipCode) && empty($faktCity) && empty($faktStreet)
        );

        $equal = $addressesEqual || $allFieldsEmpty;

        
        $this->design->assign('equal', $equal ? 1 : 0);

        $this->design->assign('regions', (new RegionService())->getRegions());
        $this->design->assign('factual_region', $_SESSION['user_info']['addresses'][1]['region'] ?? $this->user->Faktregion ?? '');
        $this->design->assign('registration_region', $_SESSION['user_info']['addresses'][2]['region'] ?? $this->user->Regregion ?? '');

        $this->design->assign('residence_settlement', $_SESSION['user_info']['addresses'][1]['settlement'] ?? '');
        $this->design->assign('residence_city', $faktCity);
        $this->design->assign('residence_house', $faktHouse);
        $this->design->assign('residence_street', $faktStreet);
        $this->design->assign('residence_apartment', $_SESSION['user_info']['addresses'][1]['apartment'] ?? '');
        $this->design->assign('residence_building', $_SESSION['user_info']['addresses'][1]['building'] ?? '');
        $this->design->assign('residence_zipCode', $faktZipCode);

        $this->design->assign('registration_settlement', $_SESSION['user_info']['addresses'][2]['settlement'] ?? '');
        $this->design->assign('registration_city', $regCity);
        $this->design->assign('registration_house', $regHouse);
        $this->design->assign('registration_street', $regStreet);
        $this->design->assign('registration_apartment', $this->user->Regroom ?: $_SESSION['user_info']['addresses'][2]['apartment'] ?? '');
        $this->design->assign('registration_building', $_SESSION['user_info']['addresses'][2]['building'] ?? '');
        $this->design->assign('registration_zipCode', $regZipCode);

        $body = $this->design->fetch('account_address_data.tpl');

        return $body;
    }

    private function pagePersonalData()
    {
        $this->checkEmailRedirect();

        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $this->user->id));

        $this->design->assign('contact_person_name', isset($contactpersons[0]->name) ? $contactpersons[0]->name : '');
        $this->design->assign('contact_person_phone', isset($contactpersons[0]->phone) ? $contactpersons[0]->phone : '');
        $this->design->assign('contact_person_relation', isset($contactpersons[0]->relation) ? $contactpersons[0]->relation : '');

        $this->design->assign('contact_person2_name', isset($contactpersons[1]->name) ? $contactpersons[1]->name : '');
        $this->design->assign('contact_person2_phone', isset($contactpersons[1]->phone) ? $contactpersons[1]->phone : '');
        $this->design->assign('contact_person2_relation', isset($contactpersons[1]->relation) ? $contactpersons[1]->relation : '');

        $this->design->assign('contact_person3_name', isset($contactpersons[2]->name) ? $contactpersons[2]->name : '');
        $this->design->assign('contact_person3_phone', isset($contactpersons[2]->phone) ? $contactpersons[2]->phone : '');
        $this->design->assign('contact_person3_relation', isset($contactpersons[2]->relation) ? $contactpersons[2]->relation : '');

        $this->design->assign('passport_serial', $_SESSION['user_info']['passport_data']['serialNumber'] ?? '');
        $this->design->assign('passport_date', $_SESSION['user_info']['passport_data']['issueDate'] ?? '');
        $this->design->assign('subdivision_code', $_SESSION['user_info']['passport_data']['unitCode'] ?? '');
        $this->design->assign('passport_issued', $_SESSION['user_info']['passport_data']['unitName'] ?? '');
        $this->design->assign('birth_place', $_SESSION['user_info']['passport_data']['birthPlace'] ?? '');
        $this->design->assign('gender', $_SESSION['user_info']['gender'] ?? '');

        $body = $this->design->fetch('account_personal_data.tpl');

        return $body;
    }

    private function pageFiles()
    {
        $this->checkEmailRedirect();

        if (($step = $this->request->get('step')) && $step == 'accept') {
            $this->users->update_user($this->user->id, ['accept_data_added' => 0]);
            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        $isCyberityVerificationEnabled = $this->cyberity->isCyberityVerificationEnabled();
        $userVerification = $this->cyberity->getUserVerification([
            'user_id' => $this->user->id,
        ]);

        $this->design->assign('hide_delete_passport_photo_button', $isCyberityVerificationEnabled && !empty($userVerification));

        $isOrganic = $this->users->checkUtmSource($this->user->id);

        if (
            $isCyberityVerificationEnabled &&
            $isOrganic &&
            (empty($userVerification) || !in_array($userVerification->status, [$this->cyberity::STATUS_PROGRESS, $this->cyberity::STATUS_COMPLETED]))
        ) {

            if (empty($userVerification)) {
                $this->cyberity->insertUserVerification([
                    'user_id' => (int)$this->user->id,
                    'status' => $this->cyberity::STATUS_NEW,
                    'date_create' => date('Y-m-d H:i:s'),
                    'date_update' => date('Y-m-d H:i:s')
                ]);
            }

            $body = $this->design->fetch('self_verification.tpl');
            return $body;
        }

        $user_files = $this->users->get_files(array('user_id' => $this->user->id));

        $have_new_file = 0;
        $face1_file = null;
        $face2_file = null;
        $selfi_file = null;
        $passport1_file = null;
        $passport2_file = null;
        $passport3_file = null;
        $passport4_file = null;

        foreach ($user_files as $kk => $ufile) {
            if ($ufile->type == 'face1') {
                $face1_file = $ufile;
                unset($user_files[$kk]);
            } elseif ($ufile->type == 'face2') {
                $face2_file = $ufile;
                unset($user_files[$kk]);
            } elseif ($ufile->type == 'selfi') {
                $selfi_file = $ufile;
                unset($user_files[$kk]);
            } elseif ($ufile->type == 'passport1') {
                $passport1_file = $ufile;
                unset($user_files[$kk]);
            } elseif ($ufile->type == 'passport2') {
                $passport2_file = $ufile;
                unset($user_files[$kk]);
            } elseif ($ufile->type == 'passport3') {
                $passport3_file = $ufile;
                unset($user_files[$kk]);
            } elseif ($ufile->type == 'passport4') {
                $passport4_file = $ufile;
                unset($user_files[$kk]);
            }
        }
        $this->design->assign('face1_file', $face1_file);
        $this->design->assign('face2_file', $face2_file);
        $this->design->assign('selfi_file', $selfi_file);
        $this->design->assign('passport1_file', $passport1_file);
        $this->design->assign('passport2_file', $passport2_file);
        $this->design->assign('passport3_file', $passport3_file);
        $this->design->assign('passport4_file', $passport4_file);
        $this->design->assign('passport_files', $user_files);

        $this->design->assign('max_file_size', $this->max_file_size);


        if (!empty($_SESSION['yandex_target'])) {
            unset($_SESSION['yandex_target']);
            $this->design->assign('yandex_target', 1);
        }
        $body = $this->design->fetch('account_files_data.tpl');

        return $body;
    }

    private function pageCard()
    {
        $this->checkEmailRedirect();

        $organization_id = $this->organizations->get_base_organization_id();
        $this->design->assign('organization_id', $organization_id);

        if ($cards = $this->best2pay->get_cards(array('user_id' => $this->user->id, 'organization_id' => $organization_id))) {
            $reset_card = reset($cards);

            $order_card_id = $reset_card->id;
        }

        $last_order = $this->orders->get_last_order($this->user->id);
        $hasSbpAccountOrSelectedBank = $this->checkSetSbpOrBankSpb($last_order->id);

        $hasActiveOrder = (
            $last_order->status == $this->orders::ORDER_STATUS_CRM_ISSUED &&
            $last_order->{'1c_status'} != $this->orders::ORDER_1C_STATUS_CLOSED
        );

        // Если карта уже есть И есть СБП счета или выбранный банк - завершаем этап
        // ИЛИ если есть активный выданный займ
        if ((!empty($cards) && $hasSbpAccountOrSelectedBank) || $hasActiveOrder) {
            $this->users->update_user($this->user->id, array(
                'card_added' => 1,
                'card_added_date' => date('Y-m-d H:i:s'),
                'missing_real_date' => date('Y-m-d H:i:s')
            ));

            $cross_orders_offset = 0;
            while (!empty($last_order) && $last_order->utm_source == $this->orders::UTM_SOURCE_CROSS_ORDER) {
                $this->orders->update_order($last_order->id, array(
                    'card_id' => $order_card_id,
                    'b2p' => 1,
                ));

                $cross_orders_offset++;
                $last_order = (object)$this->orders->get_previous_order($this->user->id, $cross_orders_offset);
            }

            if (!empty($last_order) && !$hasActiveOrder) {
                $update_order = [
                    'date' => date('Y-m-d H:i:s'),
                    'card_id' => 0,
                    'card_type' => $this->orders::CARD_TYPE_SBP,
                    'b2p' => 1,
                ];
                if ($last_order->organization_id == $this->organizations::BOOSTRA_ID) {
                    $update_order['organization_id'] = $this->organizations->get_base_organization_id(['user_id' => $this->user->id]);
                }
                $this->orders->update_order($last_order->id, $update_order);
            }

            $_SESSION['yandex_target'] = 1;

            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        $lastScoristaScoring = $this->scorings->get_last_scorista_for_user($this->user->id, true);

        if (!empty($lastScoristaScoring) && (int)$lastScoristaScoring->order_id === (int)$last_order->id) {
            $lastScoristaScoring->body = json_decode($this->scorings->get_scoring_body((int)$lastScoristaScoring->id));
            $this->design->assign('has_success_scorista', true);
            $this->design->assign('scorista', $lastScoristaScoring);
            $this->design->assign('approve_amount', $lastScoristaScoring->body->additional->decisionSum ?? $last_order->amount);
        }

        // Сумма одобрения — отдельная бизнес-логика
        $decisionSum = (int) $this->scorings->getApproveAmountScoring($this->user->id);

        // Показываем блок автоподписания только если включён флоу 2.0 И сумма положительная И НЕТ карт
        $autoConfirm2Enabled = $this->user_data->read( $this->user->id,  $this->user_data::AUTOCONFIRM_2_FLOW);

        if ($autoConfirm2Enabled && empty($cards)) {
            try {
                $this->design->assign('decisionSum', $decisionSum);

                $get_params = [
                    'params' => [
                        'percent' => $last_order->percent,
                        'period'  => $last_order->period,
                        'amount'  => $decisionSum,
                    ],
                    'user_id' => $this->user->id,
                ];

                $this->design->assign('individual_url', $this->config->root_url . '/preview/IND_USLOVIYA?' . http_build_query($get_params));

                $promo_block = $this->promocodes->promoCodeModeAutoConfirmNewUser($last_order);
                $this->design->assign('promo_block', $promo_block);

                if (!empty($last_order->promocode)) {
                    $promocode = $this->promocodes->getInfoById($last_order->promocode);
                    if (!empty($promocode) && !empty($promocode->promocode)) {
                        $this->design->assign('promo_code', $promocode->promocode);
                    }
                }

                $this->design->assign('show_auto_confirm_2_asp', 1);
            } catch (Throwable $e) {
                // Если не удалось подготовить блок автоподписания — просто пропускаем его
                $this->design->assign('show_auto_confirm_2_asp', 0);
                $this->logging('AutoConfirm ASP block error', '', $e->getMessage() . $e->getTraceAsString(), '', self::LOG_FILE);
            }
        } else {
            $this->design->assign('show_auto_confirm_2_asp', 0);
        }

        $user_from_partner_api = $this->user_data->read($this->user->id, $this->user_data::PARTNER_USER_RESPONSE);
        $this->design->assign('user_from_partner_api', $user_from_partner_api);

        $this->design->assign('order', $last_order);

        $bankIdForSbpIssuance = $this->order_data->read($last_order->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);
        $autoConfirmType = $this->user_data->read($this->user->id, $this->user_data::ACTIVE_AUTOCONFIRM_FLOW);

        // Проверяем наличие СБП счетов пользователя
        $userSbpAccounts = $this->users->getSbpAccounts($this->user->id);
        $hasSbpAccounts = !empty($userSbpAccounts);

        // Проверяем наличие сохраненных банков (по умолчанию для пользователя и для заявки)
        $userDefaultBankId = $this->user_data->read($this->user->id, $this->user_data::DEFAULT_BANK_ID_FOR_SBP_ISSUANCE);
        $hasUserDefaultBank = !empty($userDefaultBankId);
        $hasOrderBank = !empty($bankIdForSbpIssuance);

        $needShowBankSelect =
            !$hasSbpAccounts  // нет СБП счетов
            && empty($bankIdForSbpIssuance)  // нет выбранного банка СБП
            && (
                !empty($cards) // есть карты
                || (empty($cards) && ( // нет карт И условия автоподтверждения
                        $autoConfirmType === $this->user_data::ACTIVE_AUTOCONFIRM_2_FLOW_VALUE
                        || ($autoConfirmType === $this->user_data::ACTIVE_AUTOCONFIRM_FLOW_VALUE && $this->user->utm_source === 'test123')
                    ))
            );


        // Единые флаги начального отображения блоков
        $showSelectBank = (bool)$needShowBankSelect; // вычислен ранее
        $this->design->assign('auto_confirm_type', $autoConfirmType);

        $utmSource = $this->user->utm_source ?? 'Boostra';

        // Показываем привязку карты только если карты НЕТ
        if (empty($cards)) {
            if ($autoConfirmType === $this->user_data::ACTIVE_AUTOCONFIRM_2_FLOW_VALUE) {
                // Карта при AUTOCONFIRM_2_FLOW: после выбора банка
                $showCardAdd = !$this->user_data->read((int)$this->user->id, $this->user_data::AUTOCONFIRM_2_FLOW) && !$showSelectBank;
            } elseif ($autoConfirmType === $this->user_data::ACTIVE_AUTOCONFIRM_FLOW_VALUE && $utmSource === 'test123') {
                // Тестовая ветка: банк -> карта -> подписание
                $showCardAdd = true;
            } else {
                // Обычный AUTOCONFIRM_FLOW: карты не показываем
                $showCardAdd = false;
            }
        } else {
            // Карта уже есть - не показываем привязку
            $showCardAdd = false;
        }

        // Показываем привязку СБП-счёта, если доступно и карта не показывается
        $canAddSbpAccount = $this->best2pay->canAddSbpAccount((int)$this->user->id);
        $this->design->assign('can_add_sbp_account', $canAddSbpAccount);
        if ($canAddSbpAccount) {
            $addSbpLink = $this->best2pay->get_link_add_sbp((int)$this->user->id);
            $this->design->assign('add_sbp_link', $addSbpLink);
        }
        $showSbpAttach = (!$showSelectBank)
            && ($autoConfirmType !== $this->user_data::ACTIVE_AUTOCONFIRM_2_FLOW_VALUE)
            && (bool)$canAddSbpAccount
            && (!$showCardAdd);

        // временная проверка для тестирования нового флоу (без выбора банка)
        if (in_array($utmSource, array_map('trim', $this->settings->disable_bank_selection_utm_sources ?? []))) {
            $showSbpAttach = false;
            $showCardAdd = empty($cards);

            $needShowBankSelect =
                !$hasSbpAccounts      // нет СБП счетов
                && !$hasUserDefaultBank  // нет банка по умолчанию у пользователя
                && !$hasOrderBank     // нет выбранного банка для заявки
                && !empty($cards);    // есть карты
        }

        $this->design->assign('show_select_bank_for_sbp', (int)$needShowBankSelect);
        $this->design->assign('show_sbp_attach', (int)$showSbpAttach);
        $this->design->assign('show_card_add', (int)$showCardAdd);

        $isSafetyFlow = $this->users->isSafetyFlow($this->user);
        $this->design->assign('isSafetyFlow', $isSafetyFlow);

        $isAllowedTestLeadgid = in_array($this->user->utm_source, $this->settings->mark_418_test_leadgids);
        $this->design->assign('isAllowedTestLeadgid', $isAllowedTestLeadgid);

        $this->design->assign('isTestUser', $this->user_data->isTestUser($this->user->id));

        // Получаем список банков СБП
        $sbpBanks = $this->b2p_bank_list->getSbpBanks();
        $this->design->assign('sbp_banks', $sbpBanks);

        $body = $this->design->fetch('account_card_data.tpl');

        return $body;
    }

    private function pageAdditionalData()
    {
        $this->checkEmailRedirect();

        if (($step = $this->request->get('step')) && $step == 'personal') {
            $this->users->update_user($this->user->id, array('personal_data_added' => 0));
            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $this->user->id));
        $work_full_address = array_filter([
            $this->user->Workindex,
            $this->user->Workregion,
            $this->user->Workcity,
            $this->user->Workstreet,
            $this->user->Workhousing,
            $this->user->Workbuilding,
            $this->user->Workroom,
        ], function ($item) {
            return trim($item);
        });

        $this->design->assign('work_full_address', implode(',', $work_full_address));

        $this->design->assign('contact_person_name', isset($contactpersons[0]->name) ? $contactpersons[0]->name : '');
        $this->design->assign('contact_person_phone', isset($contactpersons[0]->phone) ? $contactpersons[0]->phone : '');
        $this->design->assign('contact_person_relation', isset($contactpersons[0]->relation) ? $contactpersons[0]->relation : '');

        $this->design->assign('contact_person2_name', isset($contactpersons[1]->name) ? $contactpersons[1]->name : '');
        $this->design->assign('contact_person2_phone', isset($contactpersons[1]->phone) ? $contactpersons[1]->phone : '');
        $this->design->assign('contact_person2_relation', isset($contactpersons[1]->relation) ? $contactpersons[1]->relation : '');

        $this->design->assign('contact_person3_name', isset($contactpersons[2]->name) ? $contactpersons[2]->name : '');
        $this->design->assign('contact_person3_phone', isset($contactpersons[2]->phone) ? $contactpersons[2]->phone : '');
        $this->design->assign('contact_person3_relation', isset($contactpersons[2]->relation) ? $contactpersons[2]->relation : '');


        $last_order = $this->orders->get_last_order($this->user->id);
        $credit_doctor = $this->credit_doctor->getCreditDoctor((int)$last_order->amount);

        if ($last_order->percent == 0) {
            $discount_rate = 0;
        } else {
            $discount_rate = $last_order->percent - ($last_order->percent * $this->settings->additional_services_settings['amount_of_discount'] / 100);
        }

        if ($discount_rate < 0) {
            $discount_rate = 0;
        }

        $this->design->assign('discount_rate', $discount_rate);
        $this->design->assign('credit_doctor_amount', $this->credit_doctor->numberToWords($credit_doctor->price));
        $this->design->assign('professions', \api\enums\ProfessionEnum::getAvailableValues());

        $body = $this->design->fetch('account_additional_data.tpl');

        return $body;
    }

    private function pageAcceptData()
    {
        $this->checkEmailRedirect();

        $this->save_accept_data();

        if (($step = $this->request->get('step')) && $step == 'address') {
            $this->users->update_user($this->user->id, array('address_data_added' => 0));
            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        $recomendation_amount = $this->users->get_recomendation_amount($this->user);
        $amount = $this->user->first_loan_amount ?: $recomendation_amount;
        $this->design->assign('amount', $amount);

        if ($this->user->first_loan_period > $this->orders::MAX_PERIOD)
            $this->design->assign('percent', $this->user->first_loan_amount * $this->user->first_loan_period * $this->orders::BASE_PERCENTS / 100);
        else
            $this->design->assign('percent', 0);

        $this->design->assign('period', empty($this->user->first_loan_period) ? 16 : $this->user->first_loan_period);
        $partner_amount = max(1000, ceil(($this->user->first_loan_amount * 0.75) / 1000) * 1000);
        $this->design->assign('partner_amount', $partner_amount);

        if ($amount <= 0) {
            $body = $this->design->fetch('account_accept_data.tpl');
        }

        return $body;
    }

    private function pageRejected()
    {
        $this->checkEmailRedirect();

        $last_order = $this->orders->get_last_order($this->user->id);
        if (empty($last_order)) {
            // Заявка ещё не создана (Такого не должно быть), возвращаем в стандартный флоу
            $this->user_data->set($this->user->id, 'is_rejected_nk', 0);
            $this->user_data->set($this->user->id, 'bonon_no_order', 1);
            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        // Отказной НК - отображаем привязку карты к сервису партнёра
        //$partner_new_url = $this->bonondo->createClientUrlForOrder($last_order, $this->bonondo::NK_POSTFIX);
        //$partner_url = ($partner_new_url ?: $this->user_data->read($this->user->id, 'rejected_nk_url'));
        $partner_url = ($this->user_data->read($this->user->id, 'rejected_nk_url') ?: $this->bonondo->createClientUrlForOrder($last_order, $this->bonondo::NK_POSTFIX));
        if (empty($partner_url)) {
            // Не удалось создать ссылку, логируем и возвращаем клиента в стандартный флоу
            $this->logging('Empty partner url', 'User: ' . $this->user->id, $last_order, '', 'bonondo_page.txt');

            $this->user_data->set($this->user->id, 'is_rejected_nk', 0);
            $this->user_data->set($this->user->id, 'bonon_empty_url', 1);
            header('Location: ' . $this->config->root_url . '/account');
            exit;
        }

        $this->user_data->set($this->user->id, 'rejected_nk_url', $partner_url);
        $this->post_back->sendPostBackNewOrder($last_order);
        $this->design->assign('partner_url', $partner_url);
        return $this->design->fetch('account_partner_card_data.tpl');
    }

    private function tryAddHyperC()
    {
        $orders = $this->orders->get_orders([
            'user_id' => (int)$this->user->id,
        ]);

        if (empty($orders)) {
            return;
        }

        foreach ($orders as $order) {
            if (!$this->orders->isCrossOrder($order)) {
                $this->scorings->tryAddHyperC((int)$order->id);
            }
        }
    }

    /**
     * Проверяет, нужно ли вернуть клиента на этап адреса из-за длительного отсутствия
     * Использует такую же логику как в скорингах - проверка на 24 часа
     * @return bool
     * @throws Exception
     */
    private function shouldReturnToAddressStage(): bool
    {

        $utm_source = trim($this->user->utm_source ?? '');
        if (!in_array($utm_source, array_map('trim', $this->settings->returning_users_flow_utm_sources ?? []))) {
            return false;
        }

        if (empty($this->user->address_data_added)) {
            return false;
        }

        if (empty($this->user->missing_real_date)) {
            return false;
        }

        $lastActivityDate = new DateTime($this->user->missing_real_date);
        $now = new DateTime();
        $interval = $now->diff($lastActivityDate);

        return $interval->days >= 1 || $interval->h >= 24;
    }

    /**
     * Обрабатывает возвращение отвала на этап адреса
     */
    private function handleDropoutReturn(): void
    {
        $updateData = [
            'address_data_added' => 0,
            'address_data_added_date' => null,
            
            'accept_data_added' => 0,
            'accept_data_added_date' => null,

            'additional_data_added' => 0,
            'additional_data_added_date' => null,
            
            'card_added' => 0,
            'card_added_date' => null,
            
            'missing_real_date' => date('Y-m-d H:i:s')
        ];

        $this->users->update_user($this->user->id, $updateData);

        $last_order = $this->orders->get_last_order($this->user->id);
        if ($last_order) {
            $this->order_data->set($last_order->id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);
        }

        $this->user = $this->users->get_user((int)$this->user->id);

    }
}
