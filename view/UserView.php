<?PHP

use api\asp\AspHelper;
use api\helpers\BalanceHelper;
use api\services\OrderService;
use api\traits\JWTAuthTrait;
use App\Core\Application\Application;
use App\Modules\Marketing\Services\FindzenBannerService;
use App\Modules\NewYearPromotion\Services\NewYearPromotionService;
use App\Modules\ShortLink\Services\ShortLinkService;
use App\Modules\Referral\Services\ReferralService;
use App\Services\User\ZeroDiscountService;
use App\Services\ReturnExtraService;
use App\Services\ReturnCoefficientService;

require_once dirname(__DIR__) . '/api/addons/FinancialDoctorApi.php';
require_once dirname(__DIR__) . '/app/Modules/ShortLink/Services/ShortLinkService.php';
require_once dirname(__DIR__) . '/app/Modules/Referral/Services/ReferralService.php';
require_once dirname(__DIR__) . '/app/Modules/Marketing/Services/FindzenBannerService.php';

require_once('api/Scorings.php');
require_once('api/helpers/OrderStatusHelper.php');
require_once('View.php');

class UserView extends View
{
    use JWTAuthTrait;

    const PAGE_ACTION_HISTORY = 'history';

    /** @var array Список первичных проверок для заявки НК или НК повторника (``have_close_credits`` = 0) */
    const SCORINGS_LIST_NK = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_TERRORIST_CHECK,
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_REPORT,
        Scorings::TYPE_UPRID,
    ];

    /** @var array Список первичных проверок для заявки ПК (``have_close_credits`` = 1) */
    const SCORINGS_LIST_PK = [
        Scorings::TYPE_BLACKLIST,
        Scorings::TYPE_TERRORIST_CHECK,
        Scorings::TYPE_AXILINK_2,
        Scorings::TYPE_REPORT,
        Scorings::TYPE_UPRID,
    ];

    /** @var array массив заявок из 1C
     *
     * Пример элемента
     *'024513243' => (object) array(
     * 'return' =>
     * (object) array(
     * 'НомерЗаявки' => '024513243',
     * 'Статус' => '5.Выдан',
     * 'Комментарий' => '',
     * 'ОфициальныйОтвет' => '',
     * 'Сумма' => '',
     * 'ПредложениеДействуетДо' => '',
     * 'Файл' => '',
     * 'Скориста' => 'Нет данных',
     * 'ФайлBase64' => ''
     * ),
     * )
     */
    private array $check_order_1c_cache = [];

    private ShortLinkService $shortLinkService;
    private ReferralService $referralService;
    private FindzenBannerService $findzenBannerService;
    private NewYearPromotionService $promoService;

    private const LOG_FILE = 'user_view.txt';

    public function __construct()
    {
        $app = Application::getInstance();
        $this->shortLinkService = $app->make(ShortLinkService::class);
        $this->referralService = $app->make(ReferralService::class);
        $this->findzenBannerService = $app->make(FindzenBannerService::class);
        $this->promoService = $app->make(NewYearPromotionService::class);
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    function fetch()
    {
        if (!empty($_SESSION['exitpool_completed']))
            $this->design->assign('exitpool_completed', 1);

        if (!empty($_SESSION['success_add_data'])) {
            $_SESSION['success_add_data'] = NULL;
            $this->design->assign('success_add_data', 1);
        }

        $this->checkEmailRedirect();

        $this->jwtAuthValidate();

        $this->sms_auth_validate_sms->delete($this->user->phone_mobile);

        if (!empty($this->user->blocked)) {
            unset($_SESSION['user_id']);
            header('Location: ' . $this->config->root_url);
            exit();
        }

        $bki_consent = $this->user_data->read($this->user->id, 'bki_consent');
        $this->design->assign('bki_consent', $bki_consent);


        if ($this->request->get('delete_account')) {
            $this->soap->delete_user($this->user->uid);
            $this->users->update_user($this->user->id, array('enabled' => 0));

            unset($_SESSION['user_id']);

            header('Location: ' . $this->config->root_url);
            exit();
        }

        if ($this->short_flow->isShortFlowUser((int)$this->user->id)) {
            $short_flow_stage = $this->short_flow->getRegisterStage($this->user->id);
            if (!empty($short_flow_stage) && $short_flow_stage != ShortRegisterView::STAGE_FINAL) {
                header('Location: ' . $this->config->root_url . '/register');
                exit();
            }
        }

        if (empty($this->user->personal_data_added)
            || empty($this->user->address_data_added)
            || empty($this->user->additional_data_added)
            || (empty($this->user->files_added) && Helpers::isFilesRequired($this->user))
            || (empty($this->user->card_added))) {
            header('Location: ' . $this->config->root_url . '/account');
            exit();
        }

        // проверим пользователя, пришел ли он с сервиса
        $is_esia = $this->user_data->read($this->user->id, $this->user_data::IS_ESIA_NEW_USER);
        $is_t_id = $this->user_data->read($this->user->id, $this->user_data::IS_TID_NEW_USER);

        if (($is_esia || $is_t_id) && !$this->users->validateUserFields($this->user)) {
            $this->request->redirect($this->config->root_url . '/account');
        }

        //ID юзера, на котором, примерно, заработало новое флоу продажи отказного трафика
        $is_rejected_nk = $this->user_data->read($this->user->id, 'is_rejected_nk');
        if(!isset($is_rejected_nk) && $this->user->id > 3663000) {
            $this->user_data->set($this->user->id, 'is_rejected_nk', 0);
            $this->user_data->set($this->user->id, 'bonon_flow_skipped', 1);
        }

        // верификация кабутек(cyberity), перенаправляем повторно загрузить фото.
        $needPhotoVerification = $this->isNeedPhotoVerification((int)$this->user->id);
        if($needPhotoVerification) {
            header('Location: ' . $this->config->root_url . '/user/upload');
            exit();
        }

        if ($files = $this->users->get_files(array('user_id' => $this->user->id))) {
            if (count($files) > 5)
                $this->users->update_user($this->user->id, array('file_uploaded' => 1));
        }

        $hasUnacceptedAgreement = $this->show_unaccepted_agreement_modal();
        // Блокировка всех действий вне белого списка если не принято соглашение об изменении данных
        if ($hasUnacceptedAgreement)
        {
            $actions_white_list = [
                'download_credit_doctor_contract',
//                'download_credit_rating_contract',
                self::PAGE_ACTION_HISTORY
            ];
            $action = $this->request->get('action');
            if (!empty($action) && !in_array($action, $actions_white_list))
            {
                header('Location: '.$this->config->root_url.'/user');
                exit();
            }
        }

        if ($this->request->get('action') == 'download_credit_doctor_contract')
        {
            $this->credit_doctor->download_individual_contract_pdf($this->user);
            exit(0);
        }

        if ($this->request->get('action') == 'download_credit_rating_contract') {
            $this->credit_rating->download_individual_contract_pdf($this->user);
            exit(0);
        }

        if ($this->request->get('action') == 'edit_amount') {

            $order_id = $this->request->post('order_id', 'integer');
            $edit_amount = $this->request->post('edit_amount', 'integer');
            $edit_period = $this->request->post('edit_period', 'integer');

            $last_order = $this->orders->get_order($order_id);
            if (empty($last_order)) {
                $this->request->json_output(['error' => 'undefined_order']);
            }

            // изменение суммы займа доступно только для IL заявок
            if (empty($last_order->max_period) && (empty($last_order->max_amount) || $last_order->max_amount <= 30_000)) {
                $this->request->json_output(['error' => 'bad_loan_type']);
            }

            // Проверка суммы, она должна входить в разрешённый диапазон
            $amount_range = $this->orders->getAmountEditRange($last_order);
            if (empty($amount_range)) {
                $this->request->json_output(['error' => 'bad_amount']);
            }
            if ($edit_amount < $amount_range['min'] || $edit_amount > $amount_range['max']) {
                $this->request->json_output(['error' => 'bad_amount']);
            }
            
            if ($this->user->id != $last_order->user_id) {
                $this->request->json_output(['error' => 'undefined_order']);
            }
            
            if ($last_order->status != $this->orders::STATUS_APPROVED) {
                $this->request->json_output(['error' => 'fail_status']);
            }

            $old_amount = $last_order->amount;
            $old_loan_type = $last_order->loan_type;
            $last_order->amount = $edit_amount;
            $last_order->period = $edit_period;

            $loan_type = $this->installments->get_loan_type($edit_period);
            $this->orders->update_order($last_order->id, ['loan_type' => $loan_type]);
            $last_order->loan_type = $loan_type;

            $calculatePdlPriceOnDangerousFlowResult = $this->orders->calculatePdlPriceOnDangerousFlow((array)$last_order, $this->user);

            $this->changelogs->add_changelog([
                'manager_id' => 0,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'edit_amount',
                'old_values' => serialize(['amount' => $old_amount, 'request_amount' => $edit_amount, 'loan_type' => $old_loan_type]),
                'new_values' => serialize(['amount' => $calculatePdlPriceOnDangerousFlowResult['amount'], 'loan_type' => $loan_type]),
                'order_id' => $last_order->id,
                'user_id' => $last_order->user_id,
            ]);
            
            $result = $this->orders->editAmount($last_order->id_1c, (int)$last_order->id, $calculatePdlPriceOnDangerousFlowResult['amount'], $edit_period);
            $this->request->json_output(compact('result'));
        }

        if ($this->request->get('action') == 'credit_doctor_accepted') {
            $this->credit_doctor->save_individual_contract(
                $this->user,
                $this->request->post('order_id')
            );

            return false;
        }

        if ($this->request->get('action') == 'contact_me') {
            try {
                $this->notifyApi->contactMe([
                    'external_id' => $this->user->id,
                    'uid' => $this->user->uid,
                ]);
            } catch (Exception $e) {
                $this->logging(__METHOD__, $this->config->contact_me_url, $e->getMessage(), $this->user->id, 'notify.txt');

                return json_encode([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }

            echo json_encode(['success' => true]);
            exit();
        }

        if ($this->request->get('action') === 'ab_test') {
            $ab_key = $this->request->get('ab_key');
            $meta = ['ab_key' => $ab_key];
            $this->ab_test_service->logClick($this->user->id, $meta);
            exit();
        }

        if ($this->request->get('action') === 'notification_subscribe') {
            try {
                $this->notifyApi->subscribeToWebNotification([
                    'external_id' => $this->user->id,
                    'uid' => $this->user->uid,
                    'subscription' => $this->request->post('subscription'),
                ]);
            } catch (Exception $e) {
                $this->logging(__METHOD__, $this->config->web_notification_subscribe_url, $e->getMessage(), $this->user->id, 'notify.txt');

                return json_encode([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
            }

            echo json_encode(['success' => true]);
            exit();
        }

        if ($this->request->get('action') === 'refinance_get_params') {
            try {
                $order_id = $this->request->post('order_id', 'integer');
                $last_order = $this->orders->get_order($order_id);
                if (empty($last_order)) {
                    $this->request->json_output(['error' => 'Произошла ошибка при получении заявки']);
                    return;
                }

                if ($this->user->id != $last_order->user_id) {
                    $this->request->json_output(['error' => 'Произошла ошибка при получении заявки']);
                    return;
                }

                $orderBalance = $this->users->get_user_balance_1c_normalized($this->user->id, function ($balance) use ($last_order) {
                    return $balance['Заявка'] == $last_order->{'id_1c'};
                });

                if (empty($orderBalance)) {
                    $this->request->json_output(['error' => 'Произошла ошибка при получении баланса']);
                    return;
                }

                $payPeriod = $this->request->post('pay_term', 'integer');
                $payDay = $this->request->post('pay_day', 'integer');
                $params = $this->refinance->get_params($orderBalance, $payDay, $payPeriod);
                echo json_encode([
                    'success' => true,
                    'params' => $params,
                ]);
            } catch (\Throwable $e) {
                echo json_encode([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
            die();
        }

        if ($this->request->get('action') == 'refinance_scoring_aksi') {
            try {
                $code = $this->request->post('code', 'string');

                if ($error = $this->refinance->check_sms($code)) {
                    $this->request->json_output([
                        'success' => false,
                        'error' => $error,
                    ]);
                    return;
                }

                if ($order = $this->refinance->getRefinanceOrder($this->user->id)) {
                    $contract = $this->refinance->getRefinanceCurrentContract($order->id);

                    $params = [];

                    if (is_string($order->note)) {
                        $params = json_decode($order->note, true);
                    } elseif (is_array($order->note)) {
                        $params = $order->note;
                    }

                    try {
                        $result = $this->refinance->scoring_aksi($order);
                    } catch (\Throwable $e) {
                        $result = [
                            'success' => false,
                            'message' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ];
                    }

                    $this->request->json_output([
                        'success' => $result && isset($result['success']) ? $result['success'] : false,
                        'error_message' => $result && isset($result['message']) ? $result['message'] : '',
                        'result' => $result,
                        'order' => $order,
                        'contract' => $contract,
                        'refinance_amount' => $params['refinance_amount'] ?? 0,
                        'first_pay' => $params['first_pay']
                    ]);
                    return;
                }

                $old_order_id = $this->request->post('order_id', 'integer');
                $pay_day = $this->request->post('pay_day', 'integer');
                $pay_term = $this->request->post('pay_term', 'integer');
                $card_id = $this->request->post('card_id', 'integer');
                $local_time = $this->request->post('local_time');
                $code = $this->request->post('code', 'string');
                $old_order = $this->orders->get_order($old_order_id);

                if (empty($old_order)) {
                    $this->request->json_output([
                        'success' => false,
                        'error' => 'Произошла ошибка при получении заявки',
                    ]);
                    return;
                }

                $balance = $this->users->get_user_balance_1c_normalized($old_order->user_id, function ($balance) use ($old_order) {
                    return $balance['Заявка'] == $old_order->id_1c;
                });

                if (empty($balance)) {
                    $this->request->json_output([
                        'success' => false,
                        'error' => 'Произошла ошибка при получении баланса',
                    ]);
                    return;
                }

                $params = $this->refinance->get_params($balance, $pay_day, $pay_term);
                $params = array_merge($params, [
                    'card_id' => $card_id,
                    'local_time' => $local_time,
                    'accept_sms' => $code,
                ]);

                $order = $this->refinance->create_order($old_order, $params);
                $result = $this->refinance->scoring_aksi($order);

                $this->request->json_output([
                    'success' => $result && isset($result['success']) ? $result['success'] : false,
                    'error_message' => $result && isset($result['message']) ? $result['message'] : '',
                    'result' => $result,
                    'order' => $order,
                    'contract' => $this->contracts->get_contract_by_params(['order_id' => $old_order_id]),
                    'refinance_amount' => $params['refinance_amount'],
                    'first_pay' => $params['first_pay']
                ]);
                return;
            } catch (\Throwable $e) {
                $this->request->json_output([
                    'success' => false,
                    'error' => $e->getMessage(),
                ]);
                return;
            }
        }

        if ($this->request->get('action') == 'credit_rating_form_submitted') {
            $user_cards = $this->get_user_cards($this->user);

            $this->design->assign('user_approved', (int)$this->users->getUserApprove($this->user->id));
            $this->design->assign('cards', $user_cards);
            $this->design->assign('has_default_card', $this->is_default_card_set($user_cards));
            $this->design->assign('use_b2p', $this->settings->b2p_enabled || $this->user->use_b2p);
            header("Content-type: text/html; charset=UTF-8");
            print $this->design->fetch('credit_rating/credit_rating_contract.tpl');
            exit(0);
        }

        if ($this->request->get('action') == 'credit_rating_send_sms') {
            header("Content-type: application/json; charset=UTF-8");
            echo json_encode($this->credit_rating->send_credit_rating_sms($this->user));
            exit();
        }

        if ($this->request->get('action') === 'referral') {
            $this->downloadDocsForReferral();
            exit();
        }

        if ($redirect = $this->NewLk->check_redirect($this->user)) {
            $this->design->assign('redirect', $redirect);
        }

        // Если есть закрытые займы или отказные, проверку игнорируем https://tracker.yandex.ru/MARK-817
        $hasRejectOrIssuedOrders = $this->orders->hasRejectOrIssuedOrders($this->user->id);
        $need_add_fields = [];

        if (!$hasRejectOrIssuedOrders) {
            $need_add_fields = $this->check_need_add_fields();
        }

        $this->design->assign('need_add_fields', $need_add_fields);

        if ($issued_loans = $this->soap->DebtForFIO((array)$this->user)) {
            $issued_loans = array_filter($issued_loans, function ($var) {
                return $var->ОстатокОД > 0;
            });
        }
        $this->design->assign('have_issued_loans', count($issued_loans));

        if (!$hasUnacceptedAgreement && $this->request->post('repeat_first_loan')) {
            if ($this->user->fake_order_error < 0 && $this->user->phone_mobile != '79608251384') {
                $request_service_insurance = $this->request->post('service_insurance', 'integer');
                $request_service_sms = $this->request->post('service_sms', 'integer');

                if (empty($request_service_insurance)) {
                    $this->users->update_user($this->user->id, array('fake_order_error' => $this->user->fake_order_error + 1));
                } else {
                    $this->users->update_user($this->user->id, array('fake_order_error' => 0, 'service_insurance' => 1));
                    // Отправляем в 1с запрос на обновление галочек
                    $last_order = (array)$this->orders->get_last_order($this->user->id);
                    $this->soap->change_order_services($last_order['1c_id'], $this->user->uid);
                }
                header('Location: /user');
                exit;
            }
        }

        $notsend_files = array();
        $reject_files = array();
        if ($user_files = $this->users->get_files(array('user_id' => $this->user->id, 'status' => 0))) {
            foreach ($user_files as $user_file) {
                if ($user_file->status == 0)
                    $notsend_files[] = $user_file;
                if ($user_file->status == 3)
                    $reject_files[] = $user_file;
            }
        }
        $this->design->assign('notsend_files', $notsend_files);
        $this->design->assign('reject_files', $reject_files);

        $installment_enabled = $this->installments->check_enabled($this->user);
        $this->design->assign('installment_enabled', $installment_enabled);

        $exitpool_questions = $this->exitpools->get_questions();
        $this->design->assign('exitpool_questions', $exitpool_questions);

        $this->checkAutoConfirmNewUser();

        $user_balance_1c = new stdClass();

        if (!empty($this->user->uid) && $this->user->uid != 'Error') {
            $user_balance = $this->users->get_user_balance($this->user->id);
            #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user_balance);echo '</pre><hr />';
            if (!empty($this->is_developer) || !empty($this->is_admin) || strtotime($user_balance->last_update) < time() - 60 * 10) {
                try {
                    $user_balance_1c = $this->users->get_user_balance_1c($this->user->uid, true);
                    $user_balance_1c = $this->users->make_up_user_balance($this->user->id, $user_balance_1c->return);

                    #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user_balance_1c);echo '</pre><hr />';

                    if (empty($user_balance))
                        $balance_id = $this->users->add_user_balance($user_balance_1c);
                    else
                        $balance_id = $this->users->update_user_balance($user_balance->id, $user_balance_1c);
                } catch (SoapFault $fault) {
//                    if ($this->is_developer){
                        #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($fault);echo '</pre><hr />';
//                    }
                    throw new Exception('Не удалось получить баланс'
                        . PHP_EOL . $fault->getFile()
                        . PHP_EOL . $fault->getLine()
                        . PHP_EOL . $fault->getMessage()
                    );
                    exit;
                }
            }

            if ($user_balance->buyer == 'БИКЭШ') {
                if ($this->request->get('cession') == 'shown') {
                    $this->users->update_user_balance($user_balance->id, ['is_cession_shown' => 1]);
                }

                if (!$user_balance->is_cession_shown) {
                    try {
                        $re = $this->soap->get_cession_document($user_balance->zaim_number);

                        if (empty($re)) {
                            throw new Exception('Не удалось получить документ/');
                            exit;
                        }

                        //if (isset($re->return)) {
                        $result_cession = file_put_contents("files/contracts/Cess/{$user_balance->zaim_number}.pdf", base64_decode($re->return));

                        if ($result_cession == false) {
                            throw new Exception('Не удалось получить документ.');
                            exit;
                        }
                        //}
                    } catch (SoapFault $fault) {
                        throw new Exception('Не удалось получить документ');
                        exit;
                    }
                }
            }

            $quantity_loans = json_decode($this->user->quantity_loans);
            if ($quantity_loans->КоличествоЗаймовЗаГод >= 9) {
                $quantity_loans_block = date('Y-m-d H:i:s', strtotime($quantity_loans->ДатаПервогоЗаймаЗаГод) + 86400 * 365);
                if (time() < strtotime($quantity_loans_block)) {
                    $this->design->assign('quantity_loans_block', $quantity_loans_block);
                }
            }

            $isSafetyFlow = $this->users->isSafetyFlow($this->user);
            $this->design->assign('isSafetyFlow', $isSafetyFlow);

            $isAllowedTestLeadgid = in_array($this->user->utm_source, $this->settings->mark_418_test_leadgids);
            $this->design->assign('isAllowedTestLeadgid', $isAllowedTestLeadgid);

            $this->design->assign('isTestUser', $this->user_data->isTestUser($this->user->id));
        }

        $this->design->assign('pdn_doc', $this->users->getExcessedPdn($this->user->id));

        // Передаем в шаблон
        if (!empty($this->user->client))
            $this->design->assign('name', $this->user->client);
        $this->design->assign('email', $this->user->email);

        // для определенного списка клиентов делаем акцию на 3 дня - можно закрытть долг оплатив только ОД
        if (time() > strtotime('2021-11-17') && time() < strtotime('2021-11-20')) {
            $indulgensia_list = array_map('trim', file($this->config->root_dir . 'indulgensia.txt'));
            if (array_search($this->user->phone_mobile, $indulgensia_list) !== false)
                $this->design->assign('indulgensia', 1);
        }

        $page_action = $this->request->get('action');
        $balance = $this->users->get_user_balance($this->user->id);
        $graceAmount =  !empty($balance->sum_od_with_grace) || !empty($balance->sum_percent_with_grace);
        $this->design->assign('graceAmount', $graceAmount);
        $this->design->assign('balance', $balance);
        $restricted_mode = ($_SESSION['restricted_mode'] ?? 0) == 1;
        $friend_restricted_mode = ($_SESSION['friend_restricted_mode'] ?? 0) == 1;
        $busy_cards = [];
        if ($restricted_mode) {
            $orders = [];
        } else {
            $orders = $this->orders->get_orders([
                'user_id' => $this->user->id,
                'limit' => 1000,
            ]);
            foreach ($orders as $order) {
                if (!$order->status_1c
                    || in_array($order->status_1c, $this->orders::IN_PROGRESS_STATUSES)
                    || ($order->status_1c == '5.Выдан'
                        && $order->id_1c == $balance->zayavka
                        && $balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni > 0)) {
                    $busy_cards[$order->card_id] = true;
                }
            }
        }
        $this->design->assign('busy_cards', $busy_cards);
        if ($page_action == self::PAGE_ACTION_HISTORY) {
//				$orders = $this->orders->get_orders(array('user_id'=>$this->user->id, 'status' => array('0','1','3')));
//				$current_orders = $this->orders->get_orders(array('user_id'=>$this->user->id, 'status' => '2'));


            $this->design->assign('orders', $orders);

//			$this->design->assign('current_orders', $current_orders);
            $this->design->assign('action', self::PAGE_ACTION_HISTORY);
        } else {
            $this->design->assign('action', 'user');
        }

        if (!empty($this->user->id)) {

            $restricted_mode = $_SESSION['restricted_mode'] == 1;

            $prefix = 'Уважаемый';
            if ($this->user->gender == 'female') {
                $prefix = 'Уважаемая';
            }
            $this->design->assign('salute_prefix', $prefix);

            // Приветствие
            if ($restricted_mode){
                $salute = "{$this->user->firstname} {$this->user->patronymic} " . mb_substr($this->user->lastname, 0, 1) . ".";
            } else {
                $salute = "{$this->user->lastname} {$this->user->firstname} {$this->user->patronymic}";
            }

            $this->design->assign('salute', $salute);

            $amount = $this->request->post('amount', 'integer');
            $period = $this->request->post('period', 'integer');
            $cardId = $this->request->post('card_id') ?? $this->request->post('card');
            $cardType = $this->request->post('card_type') ?: $this->orders::CARD_TYPE_CARD;

            $b2p = $this->request->post('b2p', 'integer');
            $juicescore_session_id = (string)$this->request->post('juicescore_session_id');
            if (empty($juicescore_session_id) && !empty($_COOKIE['juicescore_session_id'])) {
                $juicescore_session_id = $_COOKIE['juicescore_session_id'];
            }
            $useragent = $this->request->post('juicescore_useragent') ?? $_SERVER['HTTP_USER_AGENT'];
            $local_time = (string)$this->request->post('local_time');

            $last_scorista_scoring = $this->scorings->get_last_scorista_for_user($this->user->id, true);

            //проверяем был ли куплен кредитный рейтинг
            $has_pay_credit_rating = $this->scorings->hasPayCreditRating((int)$this->user->id);
            $this->design->assign('view_score', $has_pay_credit_rating);

            //если есть пройденная скориста пишем балл в переменную
            if ($last_scorista_scoring && !empty($last_scorista_scoring->scorista_id)) {
                $score = min($last_scorista_scoring->scorista_ball, 750);

                // костыль для соответствия результатов на странице рейтинга
                //$score = $this->credit_rating->get_rating_file_number($score_min);

                $this->design->assign('score', $score);
                $this->design->assign('score_data', $this->scorings->getScoreColorAndName($score));
            }

            if ($user_discount = $this->discounts->get_active_discount($this->user->phone_mobile)) {
                $this->design->assign('user_discount', $user_discount);
            }

            $credits_history = $this->soap->get_user_credits($this->user->uid);
            $this->user->loan_history = $this->users->save_loan_history($this->user->id, $credits_history);

            $credits_history = $this->user->loan_history;

            if (empty($credits_history)) {
                $user_discount = (object)array(
                    'end_date' => null,
                    'percent' => 0,
                    'max_period' => $this->orders::MAX_PERIOD,
                );
                $this->design->assign('user_discount', $user_discount);
            }

            $last_order = (array)$this->orders->get_last_order($this->user->id);

            $this->design->assign(
                'friend_payment_enabled',
                (int) $this->order_data->read((int) $last_order['id'], 'friend_payment_enabled')
            );

            if (!empty($useragent) && !empty($last_order))
            {
                $this->order_data->set(
                    $last_order['order_id'] ?? $last_order['id'],
                    $this->order_data::USERAGENT,
                    $useragent
                );
            }

            $user_data = $this->user_data->readAll($this->user->id);
            $this->design->assign('user_data', $user_data);

            $reason = $this->reasons->get_reason($last_order['reason_id']);
            //Ранее не покупал кредитный рейтинг
            // Убрал КР
            if (0 && !$has_pay_credit_rating) {
                $has_loans = array_filter((array)$orders, function ($item) {
                    return $item->confirm_date;
                });
                if (empty($last_order) //Клиент подаёт заявку первый раз
                    //Новый клиент (ранее не было закрытых договоров). Получил отказ по крайней заявке
                    || empty($has_loans) && !empty($reason)
                    //Повторный клиент (есть хотя бы один закрытый договор займа). Отказ по крайней заявке. Мораторий
                    || !($balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni > 0)
                    && !empty($has_loans) && !empty($reason) && $reason->maratory) {
                    $this->design->assign('show_rating_banner', 1);
                }

            }

            // повторная заявка
            if(!empty($amount) && !$hasUnacceptedAgreement)
            {
                if (!empty($this->is_looker))
                    return false;

                if ($last_order['utm_source'] === $this->orders::UTM_RESOURCE_AUTO_APPROVE && in_array($last_order['status'], [$this->orders::STATUS_NEW, $this->orders::STATUS_APPROVED]) && !$last_order['credit_getted']) {
                    return false;
                }

                // Фикс подачи сразу нескольких заявок с разных вкладок
                if ($last_order['status'] == $this->orders::STATUS_NEW) {
                    return false;
                }

                if ($this->request->post('credit_doctor_form_submitted') !== null) {
                    $order_id = $this->credit_doctor->handle_credit_doctor_form(
                        $this->user,
                        $cardId,
                        $b2p,
                        $local_time,
                        $cardType
                    );

                    $this->design->assign('contract_link', '/user?action=download_credit_doctor_contract');
                    $this->design->assign('order_id', $order_id);

                    header("Content-type: text/html; charset=UTF-8");
                    print $this->design->fetch('credit_doctor/credit_doctor_contract.tpl');
                    exit(0);
                }

                $service_recurent = $this->request->post('service_recurent', 'integer');
                $service_sms = $this->request->post('service_sms', 'integer');
                $service_insurance = 0;//$this->request->post('service_insurance', 'integer');
                $service_reason = $this->request->post('service_reason', 'integer');
                $service_doctor = $this->request->post('service_doctor', 'integer');
                $is_user_credit_doctor = $this->request->post('is_user_credit_doctor', 'integer');
                $sms = $this->request->post('sms', 'string');
                $bank_id = $this->request->post('bank_id', 'integer');

                if ($this->request->hasPost('virtual_card')) {
                    $isVirtualCardConsent = $this->request->post('virtual_card', 'integer');
                    $this->user_data->set($this->user->id, "is_virtual_card_consent", (int)$isVirtualCardConsent);
                } else {
                    $isVirtualCardConsent = (int)$this->user_data->read($this->user->id, "is_virtual_card_consent") === 1;
                }

                $isVirtualCardEnabled = $this->settings->vc_enabled
                    && isset($_COOKIE['utm_campaign'])
                    && $_COOKIE['utm_campaign'] === 'vctest'
                    && $isVirtualCardConsent;

                if ($isVirtualCardEnabled) {
                    $this->virtualCard->forUser($this->user->id)->create();
                }

                // проверим пользователя на наличие условий и выключим допы
                $notOverdueLoan = \api\helpers\UserHelper::hasNotOverdueLoan($this, $this->user);
                if (!$notOverdueLoan) {
                    $is_user_credit_doctor = 0;
                }

                // ШКД (Штрафной Кредитный Доктор): форс КД на dangerous PDL flow.
                // Намеренно переопределяет hasNotOverdueLoan выше — на dangerous flow
                // КД включается принудительно как штрафная услуга (is_penalty=1),
                // независимо от overdue-статуса. Исключение: whitelist-пользователи.
                // См. accept_credit.php → is_penalty, createPenaltyCreditDoctorDocument()
                $currentLoanType = $this->installments->get_loan_type($period);
                $tempOrder = ['amount' => $amount, 'loan_type' => $currentLoanType];
                $isPdlOnDangerousFlow = $this->orders->isPdlOnDangerousFlow($tempOrder, $this->user);
                $allowedByWhitelist = $this->users->allowedByWhitelist((int)$this->user->id);

                if ($isPdlOnDangerousFlow && !$allowedByWhitelist) {
                    $is_user_credit_doctor = 1;
                    $service_doctor = 1;
                }

                $this->users->update_user($this->user->id, array(
                    'service_recurent' => $service_recurent,
                    'service_sms' => $service_sms,
                    'service_insurance' => $service_insurance,
                    'service_reason' => $service_reason,
                    'service_doctor' => $service_doctor
                ));

                $credits_history = $this->soap->get_user_credits($this->user->uid);
                $this->user->loan_history = $this->users->save_loan_history($this->user->id, $credits_history);
                $this->logging('credits_history', '', $credits_history, $this->user->loan_history, 'history.txt');

                /*if (empty($service_insurance) && $this->user->phone_mobile != '79608251384' && $this->user->phone_mobile != '79167788257')
                {
                    if (empty($quantity_loans->КоличествоЗаймовЗаГод) || $quantity_loans->КоличествоЗаймовЗаГод < 3)
                    {
                        if (empty($quantity_loans->КоличествоЗаймовЗаГод))
                        {
                            if ($this->user->fake_order_error < 100)
                            {
                                $this->users->update_user($this->user->id, array('fake_order_error' => $this->user->fake_order_error + 1));

                                $_SESSION['fake_order_amount'] = $amount;
                                $_SESSION['fake_order_period'] = $period;
                                header('Location: '.$this->config->root_url.'/user');
                                exit;
                            }

                        }
                        elseif ($this->user->fake_order_error < 2)
                        {
                            $this->users->update_user($this->user->id, array('fake_order_error' => $this->user->fake_order_error + 1));

                            $_SESSION['fake_order_amount'] = $amount;
                            $_SESSION['fake_order_period'] = $period;
                            header('Location: '.$this->config->root_url.'/user');
                            exit;
                        }
                    }

                }*/

                $percent = $this->orders::BASE_PERCENTS;
                if (empty($credits_history)) {


                    if ($period <= $this->orders::MAX_PERIOD)
                        $percent = 0;


                } else {
                    if ($user_discount = $this->discounts->get_active_discount($this->user->phone_mobile)) {
                        if ($period <= $user_discount->max_period)
                            $percent = $user_discount->percent;
                    }
                }

                $order_uid = exec($this->config->root_dir . 'generic/uidgen');

                // Если пользователь выбрал банк И НЕ выбрал СБП счет,
                // то принудительно устанавливаем $cardId = 0 и $cardType = $this->orders::CARD_TYPE_SBP
                if (
                    !empty($bank_id)
                    && (empty($cardId) || $cardType === $this->orders::CARD_TYPE_CARD)
                ) {
	                $cardId = 0;
                    $cardType = $this->orders::CARD_TYPE_SBP;

                    $selectedBank = $this->b2p_bank_list->getOne([
                        'id' => $bank_id,
                        'has_sbp' => 1
                    ]);

                    if (empty($selectedBank)) {
                        $this->logging(__METHOD__, '', 'Некорректный банк', ['user' => $this->user, 'bank_id' => $bank_id, 'selected_bank' => $selectedBank], 'user_view.txt');
                        return false;
                    }
                }

                if ($isVirtualCardEnabled) {
                    $cardType = $this->orders::CARD_TYPE_VIRT;
                }

                $order = array(
                    'user_id' => $this->user->id,
                    'card_id' => $cardId,
                    'card_type' => $cardType,
                    'amount' => $amount,
                    'period' => $period,
                    'percent' => $percent,
                    'b2p' => $b2p,
                    'order_uid' => $order_uid,
                    'is_user_credit_doctor' => $is_user_credit_doctor,
                    'first_loan' => 0,
                    'date' => date('Y-m-d H:i:s'),
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'comment' => (string)$this->request->post('card'),
                    'juicescore_session_id' => $juicescore_session_id,
                    'local_time' => $local_time,

                    'utm_source' => empty($_COOKIE["utm_source"]) ? 'Boostra' : $_COOKIE["utm_source"],
                    'utm_medium' => empty($_COOKIE["utm_medium"]) ? 'Site' : $_COOKIE["utm_medium"],
                    'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? 'C1_main' : $_COOKIE["utm_campaign"],
                    'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                    'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                    'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                    'click_hash' => empty($_COOKIE['click_hash']) ? '' : $_COOKIE['click_hash'],

                    'autoretry' => 0,
                    'loan_type' => $this->installments->get_loan_type($period),
                    'organization_id' => $this->organizations->get_base_organization_id(['check_last_report_date' => 1, 'order_id' => $last_order['id']]),
                );
                $order_id = $this->orders->add_order($order);

                if (!empty($_SESSION['vid'])) {
                    $this->order_data->set(
                        $order_id,
                        $this->order_data::VISITOR_ID,
                        $_SESSION['vid']
                    );
                }
                $this->logging(__METHOD__, '', 'Создана заявка из ЛК', ['order' => $order, 'order_id' => $order_id, 'bank_id' => $bank_id], 'user_view.txt');

                $this->user_data->set($this->user->id, 'bonon_wait_order_decision', $order_id);
                $this->order_data->set($order_id, $this->order_data::USER_AMOUNT, $amount);

                if (!empty($bank_id)) {
                    $this->order_data->set($order_id, $this->order_data::BANK_ID_FOR_SBP_ISSUANCE, $bank_id);
                }

                if ($isVirtualCardEnabled) {
                    $this->order_data->set($order_id, $this->order_data::CREATED_AT_VIRTUAL_CARD_TIMESTAMP, time());
                }

                $this->orders->saveFinkartaFp($order_id, $this->request->post('finkarta_fp'));
                $this->events->add_event(array(
                    'user_id' => $this->user->id,
                    'event' => $is_user_credit_doctor ? $this->events::ORDER_CD_ENABLED : $this->events::ORDER_CD_DISABLED,
                    'created' => date('Y-m-d H:i:s'),
                ));

                $autoretry = $this->users->check_autoretry($this->user->id, $order_id);

                if (!empty($credits_history))
                {
                    $this->orders->update_order($order_id, ['have_close_credits' => 1]);
                    $this->blocked_adv_sms->deleteItemByUserId((int)$this->user->id);
                }

                $this->logging('add_order', $order_id, $credits_history, $this->user->loan_history, 'history.txt');

                $soap_zayavka = $this->soap->send_repeat_zayavka([
                    'amount' => $amount,
                    'period' => $period,
                    'user_id' => $this->user->id,
                    'card' => $cardType == $this->orders::CARD_TYPE_CARD ? $cardId : '',
                    'b2p' => $b2p,
                    'order_uid' => $order_uid,
                    'organization_id' => $order['organization_id'],
                    'utm_source' => empty($_COOKIE["utm_source"]) ? '' : $_COOKIE["utm_source"],
                    'utm_medium' => empty($_COOKIE["utm_medium"]) ? '' : $_COOKIE["utm_medium"],
                    'utm_campaign' => empty($_COOKIE["utm_campaign"]) ? '' : $_COOKIE["utm_campaign"],
                    'utm_content' => empty($_COOKIE["utm_content"]) ? '' : $_COOKIE["utm_content"],
                    'utm_term' => empty($_COOKIE["utm_term"]) ? '' : $_COOKIE["utm_term"],
                    'webmaster_id' => empty($_COOKIE["webmaster_id"]) ? '' : $_COOKIE["webmaster_id"],
                    'click_hash' => empty($_COOKIE['click_hash']) ? '' : $_COOKIE['click_hash'],
                ]);


                setcookie("utm_source", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_medium", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_campaign", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_content", null, time() - 1, '/', 'boostra.ru');
                setcookie("utm_term", null, time() - 1, '/', 'boostra.ru');
                setcookie("webmaster_id", null, time() - 1, '/', 'boostra.ru');
                setcookie("click_hash", null, time() - 1, '/', 'boostra.ru');

                if (empty($soap_zayavka->return->id_zayavka)) {
                    $this->orders->update_order($order_id, array('status' => 3, 'note' => strval($soap_zayavka->return->Error)));

	                $savedOrder = $this->orders->get_order($order_id);
                    $this->virtualCard->forUser($this->user->id)->delete();
                }
                else {
                    if (!empty($sms)) {
                        if (!$this->order_data->read($order_id, $this->order_data::RCL_LOAN)) {
                            $this->order_data->set($order_id, $this->order_data::AUTOCONFIRM_ASP, $sms);
                        }
                    }

                    if (!empty($autoretry)) {
                        $this->orders->update_order($order_id, array('autoretry' => $autoretry));
                    }

                    // Переводим заявку в статус Новая
                    $this->orders->update_order($order_id, array('status' => $this->orders::ORDER_STATUS_CRM_NEW, '1c_id' => $soap_zayavka->return->id_zayavka));

                    // Добавляем скоринги
                    $scoring_data = [
                        'user_id' => $this->user->id,
                        'order_id' => $order_id,
                        'status' => Scorings::STATUS_NEW,
                        'created' => date('Y-m-d H:i:s'),
                    ];

                    $scorings_list = empty($credits_history) ? self::SCORINGS_LIST_NK : self::SCORINGS_LIST_PK;

                    $activeScoringsType = $this->scorings->get_types(['active' => 1]);
                    $activeScoringsTypeId = array_column($activeScoringsType, 'id');

                    foreach ($scorings_list as $type) {
                        $scoring_data['type'] = $type;

                        // Если у типа скоринга выставлено active = 1, то добавляем скоринга
                        if (in_array($scoring_data['type'], $activeScoringsTypeId)) {
                            $this->scorings->add_scoring($scoring_data);
                        }
                    }

                    // Показываем заявку верификаторам
                    $this->soap->set_order_complete($order_id);

                    //  Сохранение заявления о предоставлении микрозайма
                    foreach (
                        [
                            $this->documents::MICRO_ZAIM, //  Без ШКД
                            $this->documents::MICRO_ZAIM_FULL //  С ШКД
                        ] as $document_type
                    ) {

                        $docAmount = $amount;

                        // Убираем из заявления сумму допов на опсном флоу для PDL
                        if (
                            $this->orders->isPdlOnDangerousFlow($order, $this->user) &&
                            $this->orders->isExceedingMaxLimit($order, $this->user) &&
                            $document_type === $this->documents::MICRO_ZAIM
                        ) {
                            $docAmount -= $this->orders->getAdditionalServicesPrice($order, $this->user);
                        }

                        $document_id = $this->documents->create_document([
                            'user_id' => $this->user->id,
                            'order_id' => $order_id,
                            'type' => $document_type,
                            'organization_id' => $order['organization_id'],
                            'params' => $this->docs->getMicroZaimParamsByUser($this->user, $docAmount, $is_user_credit_doctor)
                        ]);
                        $file_url = $this->config->root_url . '/document/' . $this->user->id . '/' . $document_id;
                        $storage_uid = $this->filestorage->upload_file($file_url);
                        $this->documents->update_document($document_id, [
                            'filestorage_uid' => $storage_uid,
                        ]);
                    }


                    if (!$this->post_back->hasPostBackByOrderId((int)$order_id, 'hold')) {
                        if (in_array($order['utm_source'], $this->post_back::REPEAT_UTM_SOURCE) || empty($credits_history)) {
                            $order['id'] = $order_id;
                            $order['id_1c'] = $soap_zayavka->return->id_zayavka;
                            $this->post_back->sendNewOrder($order);
                        }
                    }
                }
                if ($order_id && isset($_SESSION['time']) && isset($_SESSION['user_ip'])) {
                    $this->users->update_loan_funnel_report($_SESSION['time'],$_SESSION['user_ip'],true,[
                        "order_request" => true,
                        'order_date' => date("Y-m-d"),
                        'order_id' => $order_id
                    ]);
                }
                if (empty($this->user->use_b2p) && empty($this->settings->b2p_enabled) && !empty($service_recurent)) {
                    $card_list = $this->notify->soap_get_card_list($this->user->uid);
                    if (!empty($card_list)) {
                        foreach ($card_list as $card) {
                            if ($card->Status == 'A') {
                                $this->soap->auto_debiting($this->user->uid, $card->CardId, 1);
                            }
                        }
                    }
                }

                $_SESSION['fake_order_amount'] = null;
                $_SESSION['fake_order_period'] = null;
                $this->users->update_user($this->user->id, array('fake_order_error' => 0));

                $this->user->loan = $order_id;
                $_SESSION['order_id'] = $order_id;




                /** постбек на лидгид за повторников
                 * if(!empty($_COOKIE['utm_source']) && $_COOKIE['utm_source'] == 'leadgid')
                 * {
                 *
                 * $leadgid_link = 'http://go.leadgid.ru/aff_lsr?offer_id=4806&adv_sub='.$order_id.'&transaction_id='.$_COOKIE["click_hash"].'&status=pending';
                 * $ch = curl_init($leadgid_link);
                 * curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                 * curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                 * curl_setopt($ch, CURLOPT_TIMEOUT, 15);
                 * $res = curl_exec($ch);
                 * curl_close($ch);
                 * }
                 *
                 * setcookie("utm_source", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_medium", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_campaign", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_content", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("utm_term", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("webmaster_id", null, time() - 1, '/', 'boostra.ru');
                 * setcookie("click_hash", null, time() - 1, '/', 'boostra.ru');
                 */
                setcookie("checked", null, time() - 1, '/', 'boostra.ru');


                $this->design->assign('success', true);
                unset($this->request->post);

                // Отключаем дополнительные услуги
                if ($notOverdueLoan) {
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_TV_MED, 1);
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_MULTIPOLIS, 1);
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_PARTIAL_REPAYMENT, 1);
                    $this->order_data->set($order_id, OrderData::ADDITIONAL_SERVICE_REPAYMENT, 1);
                }

                header('Location: ' . $this->config->root_url . '/user');
                exit;
            }

            $this->enableVirtualCard();

            if ($page_action == self::PAGE_ACTION_HISTORY) {
                $user_id = $this->user->id;

                $user = $this->users->get_user(intval($user_id));

                $this->design->assign('user', $user);
                $this->design->assign('action', self::PAGE_ACTION_HISTORY);

                $orders = $this->orders->get_orders(array('user_id' => $user_id));
                foreach ($orders as $order) {
                    $this->orders->update_1c_status($order);
                }
                $orders = $this->orders->get_orders(array('user_id' => $this->user->id));

                $this->design->assign('orders', $orders);

                $body = $this->design->fetch('user.tpl');

                return $body;
            } else {
                $user_id = $this->user->id;
                $user = $this->user;
                ///$user = $this->users->get_user(intval($user_id));
                setcookie("user_id", $user_id, time() + 3600, '/');

                $last_lk_visit_time = $this->users->get_user_last_lk_visit_time($user_id);
                $this->users->set_user_last_lk_visit_time($user_id);

                $user->balance = $this->users->get_user_balance(intval($user_id));

                /**
                 * Начинамм логику банеров в ЛК
                 * @var Кол-во дней просрочки $due_days
                 */

                // Если получили баланс клиента и есть дата возврата
                $response_balances = $this->soap->get_user_balances_array_1c($this->user->uid) ?? [];

                $current_loan = array_filter($response_balances, function($item) use ($user) {
                    return $item['НомерЗайма'] == $user->balance->zaim_number;
                });
                if (
                    isset($current_loan) &&
                    isset($current_loan[0]) &&
                    !empty($current_loan[0]['ПланДата'])
                ) {
                    //Получаем разницу между датой возврата и текущей даты
                    $diff = date_diff(new DateTime($current_loan[0]['ПланДата']), new DateTime(date('Y-m-d 00:00:00')));
                    if ($diff->invert == 1) {
                        // Если разница вперёд добавляем минус, проще на фронте разобрать
                        if ($diff->days > 2) {
                            //Если человек не дошёл до -2 ему банер не нужен
                            $due_days = 'not';
                        } else {
                            $due_days = '-' . $diff->days;
                        }
                    } else {
                        $due_days = $diff->days;
                        if ($due_days >= 31 && $due_days <= 90) {
                            $due_days = 'not';
                        }
                        if ($due_days == 0 && $due_days != 'not') {
                            $due_days = '-1';
                        }
                    }
                } else {
                    $due_days = 'not';
                }

                if ($due_days == 0 && $due_days != 'not') {
                    $due_days = "0";
                }

                if ($user->balance->zaim_number == 'Нет открытых договоров') {
                    $due_days = 'not';
                }

                if (isset($_COOKIE['current_loan_debug']) && $_COOKIE['current_loan_debug'] === '35396e2747c17542gftRe') {
                    echo "<pre>";
                    print_r($response_balances);
                    echo "</pre>";
                }

                $this->design->assign('saler_info', [
                    'sale_info' => $current_loan[0] ? $current_loan[0]['ИнформацияОПродаже'] ?? '' : '',
                    'name' => $current_loan[0] ? $current_loan[0]['Покупатель'] ?? '' : '',
                    'phone_number' => $current_loan[0] ? $current_loan[0]['ПокупательТелефон'] ?? '' : '',
                ]);

                // Обрабатываем utm взыскания
                $this->processCollectionUtm($user, $user_balance_1c);
                #echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($testCollection);echo '</pre><hr />';


                // Определяем ВКЛ-транш
                $zaim_order = null;
                $is_rcl = false;
                if (!empty($user->balance->zayavka)) {
                    $zaim_order = $this->orders->get_order_by_1c($user->balance->zayavka);
                    if ($zaim_order) {
                        $is_rcl = $this->rcl->isRclTranche($zaim_order->id);
                    }
                }
                $this->design->assign('is_rcl', $is_rcl);

                if ($is_rcl) {
                    $prolongation_available = $this->rcl->isProlongationAvailable(
                        $user->balance->zaim_date
                    ) && ($user->balance->ostatok_percents + $user->balance->ostatok_peni) > 0;
                } else {
                    $prolongation_available = $user->balance->prolongation_count < 5;
                }
                // Настройки с https://manager.boostra.ru/prolongation_settings
                if ($prolongation_available) {
                    $prolongation_visible_settings = $this->settings->prolongation_visible;
                    if ($due_days != 'not' && $days = intval($due_days)) {
                        $setting = $prolongation_visible_settings[$days];
                        if (!empty($setting['enabled'])) {
                            // Скрытие баннера
                            $last_scorista = $this->scorings->get_last_scorista_for_user($user_id, true);
                            if ($last_scorista->scorista_ball >= $setting['min_ball'] && $last_scorista->scorista_ball <= $setting['max_ball']) {
                                $prolongation_available = false;
                            }
                        }
                        else {
                            // Замена текста для баннера
                            $prolongation_text_settings = $this->settings->prolongation_text;
                            $prolongation_text = $prolongation_text_settings[$days];
                            if (!empty($prolongation_text)) {
                                $this->design->assign('prolongation_text', $prolongation_text);
                            }
                        }
                    }
                }

                $this->design->assign('prolongation_amount', strval($user->balance->prolongation_amount));
                $this->design->assign('prolongation_available', $prolongation_available);
                $this->design->assign('due_days', strval($due_days));
                // Чистые дни просрочки для Findzen
                $clear_due_days = $this->findzenBannerService->getClearDueDays($response_balances, (string)$user->balance->zaim_number);
                $this->design->assign('clear_due_days', strval($clear_due_days));

                $today = strtotime(date('Y-m-d 00:00:00'));
                if (strtotime($user->balance->payment_date) >= $today)
                    $prolongation_insure_percent = 15;
                elseif (strtotime($user->balance->payment_date) <= ($today + 86400 * 8))
                    $prolongation_insure_percent = 25;
                else
                    $prolongation_insure_percent = 25;

                $this->design->assign('prolongation_insure_percent', $prolongation_insure_percent);

                if (!empty($_COOKIE['paypage'])) {
                    $user_balance_rest = $user->balance->ostatok_od + $user->balance->ostatok_percents + $user->balance->ostatok_peni;
                    if ($user_balance_rest == 0)
                        $this->design->assign('repeat_approve_message', 1);
                }

                $overdue = $user->balance->expired_days;

                $this->design->assign('overdue', $overdue);

                $user->balance->calc_percents = $is_rcl ? 0 : $this->users->calc_percents($user->balance);

                if ($user->balance->sale_info == 'Договор продан' && !in_array($user->balance->buyer, ['Правовая защита', 'Правовая защита ООО', 'БИКЭШ'])) {
                    $user->balance->sale_info = 'Договор перепродан';
                    $user->balance->sale_number = $user->balance->zaim_number;
                    $user->balance->zaim_number = '';
                }
                if ($zaim_order) {
                    $this->design->assign('zaim_order', $zaim_order);
                }

                // блок по скорингу
                $negative_scoring = null;

                if ($scorings = $this->scorings->get_scorings(array('user_id' => $user_id))) {
                    foreach ($scorings as $scoring)
                        if (empty($scoring->success))
                            $negative_scoring = $scoring;
                }

                if (!empty($negative_scoring)) {

                    $scoring_time = strtotime($negative_scoring->created);
                    if ((time() - 43200) < $scoring_time) {
                        $next_scoring_time = $scoring_time + 43200;
                        $next_scoring_date = date('Y-m-d H:i:s', $next_scoring_time);

                        $this->design->assign('scoring_block', $next_scoring_date);
                    }

                }

                $cards = $this->get_user_cards($user);

                $cross_orders = [];
                $cross_orders_offset = 0;
                do {
                    if (empty($cross_orders_offset)) {
                        $last_order = (array)$this->orders->get_last_order($user_id);
                    } else {
                        $last_order = (array)$this->orders->get_previous_order($user_id, $cross_orders_offset);
                    }

                    if (!empty($last_order) && !empty($last_order['1c_id'])) {
                        if (!in_array($last_order['status'], [5, 8, 9, 11])) // проверить причину отказа
                        {
                            $resp = $this->check_order_1c($last_order['1c_id']);

                            $stat = $resp->return->Статус;
                            $comment = $resp->return->Комментарий;
                            $official_response = $resp->return->ОфициальныйОтвет;
                            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($returnnnn);echo '</pre><hr />';
                            switch ($stat):

                                case 'Новая':
                                case '1.Рассматривается':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'comment' => $comment,
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;
                                    break;
                                case '2.Отказано':
                                case '7.Технический отказ':
                                case 'Не определено':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'status' => Orders::STATUS_REJECTED,
                                        'comment' => $comment,
                                        'official_response' => $official_response,
                                    );

                                    if (empty($last_order['reason_id'])) {
                                        $update['reason_id'] = $this->reasons::REASON_END_TIME;
                                        $this->logging(__METHOD__, '', 'Установление причины отказа "Истёк срок действия"', ['last_order' => $last_order, 'update' => $update], self::LOG_FILE);
                                    }

                                    if (empty($last_order['reject_date'])) {
                                        $update['reject_date'] = date('Y-m-d H:i:s');
                                    }

                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['status'] = Orders::STATUS_REJECTED;
                                    $last_order['comment'] = $comment;
                                    $last_order['official_response'] = $official_response;
                                    break;

                                case '3.Одобрено':
                                case '4.Готов к выдаче':
                                    $update = [
                                        '1c_status' => $stat,
                                        'comment'   => $comment,
                                    ];

                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;

                                    $last_order['approved_amount'] = preg_replace("/[^0-9]/", '', $resp->return->Сумма);
                                    $last_order['approved_period'] = $resp->return->ПредложениеДействуетДо;
                                    $last_order['approved_file'] = $this->documents->save_pdf($resp->return->{'ФайлBase64'}, $resp->return->{'НомерЗаявки'}, 'Preview_Contracts');
                                    $last_order['approve_max_amount'] = $last_order['max_amount'] ?: $last_order['approve_amount'] ?: $last_order['approved_amount'];

                                    $last_order = $this->orders->calculatePdlPriceOnDangerousFlow($last_order, $this->user);

                                    if (!($last_order['user_amount'] = (int)$this->order_data->read((int)$last_order['id'], $this->order_data::USER_AMOUNT))) {
                                        $last_order['user_amount'] = max(4000, $last_order['approve_max_amount'] - 1000);
                                    }

                                    $showExtraService = $this->credit_doctor->isVisible($this->user->id, (int)$last_order['id']);
                                    $this->design->assign('showExtraService', $showExtraService);
                                    $this->logging('Show extra service', 'User: ' . $this->user->id, $showExtraService, $this->user, 'dop.txt');

                                    $credit_doctor = $this->credit_doctor->getCreditDoctor((int)$last_order['amount'], empty($credits_history));
                                    $credit_doctor_tariffs = $this->credit_doctor->getTariffs();
                                    $this->design->assign('credit_doctor_amount', $this->credit_doctor->numberToWords($credit_doctor->price));
                                    $this->design->assign('credit_doctor_tariffs', $credit_doctor_tariffs);

                                    $star_oracle = $this->star_oracle->getStarOracle((int)$last_order['id'], $this->user->id);
                                    $tv_medical = $this->tv_medical->getVItaMedPrice((int)$last_order['amount'], empty($credits_history));
                                    $tv_medical_price = $this->tv_medical::ISSUANCE_AMOUNT;

                                    try {
                                        $app = \App\Core\Application\Application::getInstance();

                                        /** @var ReturnExtraService $returnExtraService */
                                        $returnExtraService = $app->make(ReturnExtraService::class);

                                        $returnsFD = $returnExtraService->hasDoctorReturn($this->user->id);
                                        $last_order['has_fd_return'] = $returnsFD > 0;
                                    } catch (\Throwable $e) {
                                        $last_order['has_fd_return'] = false; // на случай ошибок — считаем, что возвратов не было
                                    }

                                    // Сохраняем сумму допов
                                    $last_order['dop_sum'] = 0;
                                    if (!empty($showExtraService['financial_doctor']['enable']) && !empty($credit_doctor->price)) {
                                        $last_order['dop_sum'] += (int) $credit_doctor->price;
                                    }
                                    
                                    if (!empty($showExtraService['tv_medical']['enable']) && $tv_medical_price > 0) {
                                        $last_order['dop_sum'] += $tv_medical_price;
                                    }

                                    // Отнимаем доп ТОЛЬКО если:
                                    // - тип займа PDL
                                    // - есть активный доп
                                    // - не было возврата ФД
                                    // - approve_max_amount + dop_sum > 30000
                                    $approve = (int) ($last_order['approve_max_amount'] ?? 0);
                                    $dop     = (int) ($last_order['dop_sum'] ?? 0);
                                    $loanType = strtoupper((string) ($last_order['loan_type'] ?? 'PDL'));

                                    if (
                                        $loanType === 'PDL' &&
                                        !$last_order['has_fd_return'] &&
                                        $dop > 0 &&
                                        ($approve + $dop > 30000)
                                    ) {
                                        // клиент увидит 30 000 - сумма допов
                                        $last_order['display_amount'] = max(0, 30000 - $dop);
                                    } else {
                                        // иначе показываем обычную сумму
                                        $last_order['display_amount'] = $approve;
                                    }

                                    if ($this->order_data->read($last_order['id'], $this->order_data::RCL_LOAN)) {
                                        $rcl_amount = $this->order_data->read($last_order['id'], $this->order_data::RCL_AMOUNT);
                                        $amount_string = $this->documents->convertAmountToString($last_order['display_amount']);
                                        $rcl_amount_string = $this->documents->convertAmountToString($rcl_amount);
                                        $rcl_max_amount = $this->order_data->read($last_order['id'], $this->order_data::RCL_MAX_AMOUNT);
                                        $rcl_max_amount_string = $this->documents->convertAmountToString($rcl_max_amount);
                                        $this->design->assignBulk([
                                            'rcl_loan' => $last_order['id'],
                                            'has_closed_tranche' => $this->rcl->hasClosedTranche($last_order['user_id']),
                                            'amount' => $last_order['display_amount'],
                                            'rcl_amount' => $rcl_amount,
                                            'amount_string' => $amount_string,
                                            'rcl_amount_string' => $rcl_amount_string,
                                            'rcl_max_amount' => $rcl_max_amount,
                                            'rcl_max_amount_string' => $rcl_max_amount_string,
                                            'rcl_order_id' => $last_order['id'],
                                        ]);
                                    }

                                    break;

                                case '5.Выдан':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'comment' => $comment,
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;
                                    break;

                                case '6.Закрыт':
                                    $update = array(
                                        '1c_status' => $stat,
                                        'comment' => $comment,
                                    );
                                    $last_order['status_1c'] = $last_order['1c_status'] = $stat;
                                    $last_order['comment'] = $comment;
                                    break;

                            endswitch;

                            if (!empty($update) && !$hasUnacceptedAgreement){
                                $this->orders->update_order($last_order['id'], $update);

                                if ($stat === '3.Одобрено' || $stat === '4.Готов к выдаче') {
                                    $amount_of_discount = $this->settings->additional_services_settings['amount_of_discount'];
                                    if ($last_order['percent'] == 0) {
                                        $discount_rate = 0;
                                    } else if ($last_order['utm_source'] == 'crm_auto_approve') {
                                        $discount_rate = $this->orders::BASE_PERCENTS;
                                    } else {
                                        $discount_rate = $last_order['percent'] - ($last_order['percent'] * $this->settings->additional_services_settings['amount_of_discount'] / 100);
                                    }
                                    if ($discount_rate < 0) {
                                        $discount_rate = 0;
                                    }
                                    $configured_term = $this->settings->additional_services_settings['configured_term'];

                                    $new_contracts = $this->get_new_contracts($last_order['id']);
                                    $last_order['gray_approved_file'] = $new_contracts['gray_contract'];
                                    $last_order['green_approved_file'] = $new_contracts['green_contract'];
                                    $last_order['new_approved_file'] = $new_contracts['new_contract'];

                                    $this->design->assign('amount_of_discount', $amount_of_discount);
                                    $this->design->assign('discount_rate', $discount_rate);
                                    $this->design->assign('configured_term', $configured_term);

                                    $this->design->assign('gray_approved_file', $last_order['gray_approved_file']);
                                    $this->design->assign('green_approved_file', $last_order['green_approved_file']);
                                    $this->design->assign('new_approved_file', $last_order['new_approved_file']);
                                }
                            }

                        }

                        $last_order_time = strtotime($last_order['date']);

                        $this->design->assign(
                            'first_time_visit_after_rejection',
                            $last_lk_visit_time && ($last_lk_visit_time < date('Y-m-d H:i:s', $last_order_time))
                        );

                        // индивидуальное рассмотрение
                        if ($this->settings->individual_settings['enabled']) {
                            if ($individual = $this->individuals->get_individual_order($last_order['id'])) {
                                $last_order['individual'] = $individual;
                            }
                        }

                        if ($cdoctor = $this->cdoctor->get_order_item($last_order['id'])) {
                            if ($cdoctor->cdoctor_status == 'paid' || $cdoctor->cdoctor_status == 'new') {
                                $last_order['cdoctor'] = $cdoctor;
                            }
                        }

                        if (
                            $last_order['status'] == Orders::STATUS_REJECTED
                            && !in_array($last_order['reason_id'], $this->credit_doctor->get_restriction_reason_ids())
                            && !$this->credit_doctor->is_user_has_opened_doctor($this->user)
                        ) {
                            if ($this->request->get('credit_doctor')) {
                                $this->design->assign('credit_doctor_allowed', 1);
                            } else {
                                $this->design->assign('credit_doctor_banner_show', 0);
                            }
                        }

                        // если это автоодобрение получим информацию по нему
                        if ($last_order['utm_source'] == 'crm_auto_approve') {
                            $user->auto_approve_order = $this->orders->getAutoApproveOrderByOrderId((int)$last_order['id']);
                        }

                        if ($date_5days_maratorium = $this->users->check_5days_maratorium($this->user->id)) {
                            $new_order_maratorium = $date_5days_maratorium;
                            $this->design->assign('new_order_maratorium', $date_5days_maratorium);
                        }
                    }

                    if ($last_order['utm_source'] == 'cross_order') {
                        if (in_array($last_order['1c_status'], ['3.Одобрено'])) {
                            $cross_orders[] = $last_order;
                        }
                        $cross_orders_offset++;
                    }

                } while ($last_order['utm_source'] == 'cross_order');

                $partner_postfix = $this->user->phone_mobile ? "&p={$this->user->phone_mobile}" : '';
                $partner_postfix .= "&utm_source2={$this->user->utm_source}";
                $user_scorista = $this->scorings->get_scorings([
                    'type' => $this->scorings::TYPE_SCORISTA,
                    'status' => $this->scorings::STATUS_COMPLETED,
                    'user_id' => $this->user->id,
                ]);
                if(!empty($user_scorista)) {
                    $user_scorista = array_reverse($user_scorista);
                    $partner_postfix .= "&sc={$user_scorista[0]->scorista_ball}";
                }

                $reason_block = null;
                $repeat_loan_block = null;
                $next_loan_mandatory = null;
                $new_order_maratorium = $new_order_maratorium ?? null;

                /** Блок по отказу на 12 часов*/
                if ((isset($last_order) && $last_order['1c_status'] == '2.Отказано')
                    || $last_order['status'] == $this->orders::STATUS_NOT_ISSUED) {
                    $reason = $this->reasons->get_reason($last_order['reason_id']);
                    if (!empty($reason) && $reason->maratory > 0) {
                        if ($reason->maratory == 999) {
                            $reason_block = 999;
                        } else {
                            if (time() < strtotime($last_order['date']) + 86400 * $reason->maratory)
                                $reason_block = date('Y-m-d H:i:s', strtotime($last_order['date']) + 86400 * $reason->maratory);
                        }
                        if (!empty($reason_block) && $last_order['status'] != $this->orders::STATUS_NOT_ISSUED) {
                            // Не делаем переадресацию на витрины в ЛК если клиент продан в бонон или у него отказ по самозапрету
                            // 2 признака продажи в бонон - reason_id (старые заявки) или s_order_data
                            if (!in_array($reason->id, [$this->reasons::REASON_CARD_SELLED_TO_BONON, $this->reasons::REASON_SELF_DEC])) {

                                $partner_href = $this->partner_href->getActualItem((int)$last_order['have_close_credits'], 'bonon-shop-window-decline');
                                $background_href = $this->partner_href->getActualItem((int)$last_order['have_close_credits'], 'bonon-background-decline');
                                $this->design->assign('partner_href', $partner_href->href ? "{$partner_href->href}{$partner_postfix}" : '');
                                $this->design->assign('background_href', $background_href->href ? "{$background_href->href}{$partner_postfix}" : '');
                                $this->design->assign('view_partner_href', !empty($partner_href->href));
                                $this->design->assign('client_suffix', '-decline' . ((int)$last_order['have_close_credits'] ? ':pk' : ':nk'));
                                $this->partner_href->addStatistic((int)$this->user->id, (int)$partner_href->id);
                            }

                            $this->design->assign('reason_block', $reason_block);
                            $user->not_rating_maratorium_valid = $this->users->getNoApprovedUserNotMoratorium($this->user->id);
                        }
                    } elseif ($reason->id == $this->reasons::REASON_REMOVED_FROM_BLACKLIST) {
                        $promoCode = $this->promocodes->getLastUnusedPromoCode($user->phone_mobile);
                        
                        if ($promoCode && $promoCode->is_mandatory_issue) {
                            $next_loan_mandatory = true;
                            $this->design->assign('next_loan_mandatory', true);
                        }
                    } elseif (time() - (43200) < $last_order_time) {
                        $next_loan_time = $last_order_time + 43200;
                        $next_loan_date = date('Y-m-d H:i:s', $next_loan_time);

                        $repeat_loan_block = $next_loan_date;
                        $this->design->assign('repeat_loan_block', $next_loan_date);
                    }
                }

                $reasonForFlags = $reason ?? $this->reasons->get_reason($last_order['reason_id'] ?? 0);

                if (empty($reason_block) && !empty($last_order['reason_id'])) {
                    $reason_block = OrderStatusHelper::calculateReasonBlockDate(
                        $reasonForFlags,
                        $last_order['date'] ?? ''
                    );
                    if ($reason_block) {
                        $this->design->assign('reason_block', $reason_block);
                    }
                }
                $orderForForm = !empty($user->order) ? $user->order : $last_order;
                $order1cStatus = $orderForForm['1c_status'] ?? ($orderForForm['status_1c'] ?? '');
                $filesOk = $user->file_uploaded || Helpers::isFilesRequired($user);

                $flags = OrderStatusHelper::getNewOrderFormFlags([
                    'last_order' => $last_order,
                    'user' => $user,
                    'reason' => $reasonForFlags,
                    'reason_block' => $reason_block ?? null,
                    'repeat_loan_block' => $repeat_loan_block ?? null,
                    'next_loan_mandatory' => $next_loan_mandatory ?? null,
                    'new_order_maratorium' => $new_order_maratorium ?? null,
                    'order1c_status' => (string)$order1cStatus,
                    'files_ok' => $filesOk,
                ]);

                $this->design->assign('show_moratorium_only', $flags['show_moratorium_only']);
                $this->design->assign('can_show_new_order_form', $flags['can_show_new_order_form']);

                $loans_count = empty($last_order) ? 0 : \api\helpers\UserHelper::userLoansCount($this, $this->user->id, new DateTime($last_order['date']));
                if ($this->request->get('action') == 'verify_card') {
                    header('HTTP/1.1 302 Found');
                    if($this->user_data->read($this->user->id, 'bonon_verify_order_id') == $last_order['id']) {
                        $url_postfix = $loans_count ? $this->bonondo::PK_POSTFIX : $this->bonondo::NK_ACC_POSTFIX;
                        $partner_url = $this->bonondo->createClientUrlForOrder((object)$last_order, $url_postfix);
                        if (!empty($partner_url)) {
                            // Не удалось создать ссылку, логируем и возвращаем клиента в стандартный флоу
                            $this->user_data->set($this->user->id, 'rejected_pk_url', $partner_url);
                            header("Location: {$partner_url}");
                        } else {
                            $this->logging('Empty partner url (PK)', 'User: ' . $this->user->id, $last_order, '', 'bonondo_pk_page.txt');
                            header("Location: /user");
                        }
                    } else {
                        header("Location: /user");
                    }
                    $this->user_data->set($this->user->id, 'bonon_verify_order_id', 0);
                    exit();
                }

                $bonon_wait_order = $this->user_data->read($this->user->id, 'bonon_wait_order_decision');
                if($bonon_wait_order > 0 && !empty($last_order) && $this->settings->bonon_enabled
                    && !in_array($last_order['status'], [$this->orders::ORDER_STATUS_CRM_CORRECTION, $this->orders::ORDER_STATUS_CRM_CORRECTED])
                    && $last_order['status'] >= $this->orders::STATUS_APPROVED && $last_order['id'] == $bonon_wait_order) {

                    $this->user_data->set($this->user->id, 'bonon_wait_order_decision', -$bonon_wait_order);
                    if($last_order['status'] == $this->orders::STATUS_REJECTED) {
                        $reason  = $this->reasons->get_reason($last_order['reason_id']);
                        $setting = $this->bonondo->getBononSourceSetting($this->user->utm_source, $this->user->utm_medium);
                        $skip_nk_utms  = explode(',', $this->settings->bonon_excluded_utms ?? '');
                        $bonon_skipped = $loans_count || empty($setting) || in_array($this->user->utm_source, $skip_nk_utms);
                        // Проверки для органики
                        if ($this->users->checkUtmSource($this->user->id) && !$bonon_skipped) {
                            // Клиент - органика
                            $dayOfWeek = date('N');
                            if ($dayOfWeek < 6) {
                                // Рабочий день
                                $currentHour = date('G');
                                if ($currentHour >= 10 && $currentHour <= 17) {
                                    // Промежуток между 10 и 17 МСК, в это время действует безопасный флоу
                                    // Органика пропускает этап с проверкой на необходимость продажи
                                    $bonon_skipped = true;
                                }
                            }
                        }
                        if(!$bonon_skipped && $reason && $reason->maratory
                            && !isset($user_data['rejected_nk_url']) && !isset($user_data['rejected_pk_url'])
                            && !in_array($reason->id, [$this->reasons::REASON_SELF_DEC])) {
                                $this->db->query("SELECT '{$last_order['reject_date']}' + INTERVAL {$reason->maratory} DAY > NOW() is_moratory");
                                if($this->db->result('is_moratory') || $reason->maratory == 999) {
                                    $scorista = $this->scorings->get_scorings([
                                        'order_id' => $last_order['id'],
                                        'status' => $this->scorings::STATUS_COMPLETED,
                                        'type' => $this->scorings::TYPE_SCORISTA,
                                    ]);
                                    $scorista_rating = array_reduce($scorista ?? [], fn($rating, $item) => max($rating, $item->scorista_ball), 0);
                                    if($scorista_rating < 500) {
                                        $this->user_data->set($this->user->id, 'bonon_verify_order_id', $bonon_wait_order);
                                        return $this->design->fetch('user_partner_card.tpl');
                                    }
                                }
                        }
                    }
                }

                $hideSuccessBlock = false;

                if ($cross_orders = $this->cross_orders->update_cross_orders($cross_orders, $last_order)) {
                    if ($isAutoAcceptCrossOrders = $this->cross_orders->isAutoAccept($isSafetyFlow, $last_order)) {
                        $totalApproveAmount = $last_order['utm_source'] == 'cross_order' ? 0 : $last_order['amount'];
                        foreach ($cross_orders as $co) {
                            $totalApproveAmount += $co['amount'];
                        }
                    }

                    $hideSuccessBlock = count(array_filter($cross_orders, function ($order) {
                        return $order['status'] == $this->orders::STATUS_WAIT_CARD;
                    })) > 0;

                    foreach ($cross_orders as &$co) {
                        $coReason = !empty($co['reason_id']) ? $this->reasons->get_reason($co['reason_id']) : null;
                        $co['_flags'] = OrderStatusHelper::getViewFlags($co, $coReason, $last_order);
                    }
                    unset($co);

                    $this->design->assign('totalApproveAmount', $totalApproveAmount);
                    $this->design->assign('isAutoAcceptCrossOrders', $isAutoAcceptCrossOrders);
                }

                $this->design->assign('hideSuccessBlock', $hideSuccessBlock);

                $organizations = [];
                foreach ($this->organizations->getList() as $organization) {
                    $organizations[$organization->id] = $organization->short_name;
                }
                $this->design->assign('organizations', $organizations);
                
                if ($last_order['credit_getted'] && !$cross_orders[0]['credit_getted']) {
                    $this->design->assign('cross_orders_up', 1);
                }

                if (!empty($last_order['first_loan'])) {
                    if ($this->user->fake_order_error < 100) {
                        if (empty($this->user->service_insurance)) {
                            //$this->design->assign('view_fake_first_order', 1);

                        }
                    }
                }

                if ($this->user->fake_order_error == 0) {
                    $user->not_rating_maratorium_valid = $this->users->getNoApprovedUserNotMoratorium($this->user->id);
                }

                $user->order = $last_order;

                if (!empty($user->order)) {
                    $user->order['payment_refuser'] = $this->order_data->read($user->order['id'], $this->order_data::PAYMENT_REFUSER);
                    $user->order['is_new_card_linked'] = $this->order_data->read($user->order['id'], $this->order_data::IS_NEW_CARD_LINKED);

                    $orderReason = !empty($user->order['reason_id']) ? $this->reasons->get_reason($user->order['reason_id']) : null;
                    $user->order['_flags'] = OrderStatusHelper::getViewFlags($user->order, $orderReason);
                }

                $this->design->assign('use_b2p', (int)($this->settings->b2p_enabled || $user->use_b2p));
//    			$this->design->assign('files', $types);
                $this->design->assign('meta_title', 'Кабинет заемщика - ' . $user->firstname . ' ' . $user->patronymic);
                $this->design->assign('action', 'user');
                $this->design->assign('user', $user);
                $this->design->assign('is_new_client', $last_order['first_loan'] ?? 0);
                $this->design->assign('user_lk_page', true);

                // мультиполис
                $multipolis_amount = $this->multipolis->getMultipolisAmount($user);
                $this->design->assign('multipolis_amount', $multipolis_amount);

                // проверим покупку КД
                $licenses = $this->credit_doctor->getLicensesByUserId($user->id);
                $creditDoctorRecords = $this->credit_doctor->getAllCreditDoctorRecordsWithReturnsByUserId($user->id);

                $filteredCreditDoctorRecords = array_filter($creditDoctorRecords, function ($record) {
                    return $record->amount_total_returned < $record->amount;
                });
                $this->design->assign('has_credit_doctor', !empty($filteredCreditDoctorRecords));

                $activeLicense = null;

                if (!empty($licenses)) {
                    foreach ($licenses as $license) {
                        $relatedService = null;
                        foreach ($creditDoctorRecords as $record) {
                            if ($record->order_id == $license->order_id) {
                                $relatedService = $record;
                                break;
                            }
                        }

                        $isAmountFullyReturned = $relatedService && $relatedService->amount_total_returned >= $relatedService->amount;

                        if ($isAmountFullyReturned) {
                            $this->credit_doctor->updateLicenseByLicenseId($license->id, [
                                'active' => 0,
                            ]);
                            $license->active = 0;
                        }

                        $isLicenseEndingUnset = is_null($license->ending) || strtotime($license->ending) === strtotime('0000-00-00 00:00:00');
                        $isLicenseExpired = !$isLicenseEndingUnset && strtotime($license->ending) <= strtotime($license->created_at);

                        if (
                            $license->active &&
                            !$isLicenseExpired &&
                            !$isAmountFullyReturned
                        ) {
                            $activeLicense = $license;
                            break;
                        }
                    }
                }

                // Финансовый консультант - показываем ссылку если есть лицензия ФД с ключом и был возврат CD в текущем займе
                if (!empty($last_order['id']) && $this->credit_doctor->hasReturnByOrderId((int)$last_order['id'])) {
                    $fdLicenseKey = $this->credit_doctor->getLicenseKeyByOrderId((int)$last_order['id']);
                    if ($fdLicenseKey) {
                        $this->design->assign('show_chat_dop', true);
                        $this->design->assign('fd_license_key', $fdLicenseKey);
                    }
                }

                if(!empty($_SESSION['full_payment_amount_done']) && empty($this->credit_doctor->getLicenseByUserId($this->user->id))){
                    $this->design->assign('full_payment_amount_done', $_SESSION['full_payment_amount_done']);
                }
                $userGift = $this->users->getGifts($this->user->id);
                $promoGift = $this->users->getGifts((int) $_SESSION['user_id'], true);
                $payCredit = $userGift && $this->checkPayCredit($userGift);
                $promocode = null;

                if ($promoGift && !is_null($promoGift->status)) {
                    $promocode = $promoGift->promocode;
                }

                $this->design->assign('has_license', !empty($activeLicense));
                $this->design->assign('payCredit', $payCredit);
                $this->design->assign('userGift', $userGift);
                $this->design->assign('promoGift', $promoGift);
                $this->design->assign('promocode', $promocode);
                $this->design->assign('license_url', $activeLicense ? sprintf(FinancialDoctorApi::LOGIN_URL, $activeLicense->license_key) : null);

                $banners_count = count(array_filter([
                    !empty($filteredCreditDoctorRecords),
                ]));
                $this->design->assign('banners_count', $banners_count);

                //проверка на баннер с мотивацией
                $motivation_banner = $this->orders->getMotivationBannerData($last_order, $user);
                $this->design->assign('motivation_banner', $motivation_banner);

                //проверка на режим отображения промокодов
                $promo_block = $this->promocodes->promocodeMode($this->user->id);
                $this->design->assign('promo_block', $promo_block);

                /*
                if (empty($user->skip_credit_rating) && $user->additional_data_added == 1 && $last_order['status'] != 2) {
                    header('Location: ' . $this->config->root_url . '/user/credit_rating');
                    exit();
                }
                */

                $utmSource = $this->users->checkUtmSource($user_id);

                $this->design->assign('isOrganic', $utmSource);

                // выполним поиск, и проверим есть ли в базе разделенный займ, который не обработан до конца
                $filter_divide_order = [
                    'filter_not_statuses' => [
                        $this->orders::DIVIDE_ORDER_STATUS_CLOSED,
                        $this->orders::DIVIDE_ORDER_STATUS_ERROR,
                        $this->orders::DIVIDE_ORDER_STATUS_CLOSED_BY_ONE,
                    ],
                    'filter_user_id' => $this->user->id,
                ];

                $divide_order = $divide_pre_order_is_new = $divide_pre_order_accept_date = null;

                // Все отчеты отображаются во вьюхе для разбитых займов
                // Все отчеты берутся из 1с
                // @todo Нормализовать отображение списка всех отчетов, для всех ситуаций
                $all_orders = new StdClass();
                $all_orders->orders = [];

                // получим актуальную информацию из 1С по каждой заявке
//                $response_balances = $this->soap->get_user_balances_array_1c($this->user->uid);
                // создаем заявки для займов акадо
                foreach ($response_balances as $response_balance) {
                    $this->acado->create_order($this->user->id, $response_balance);
                }

                $organization_id = $this->users->get_organization_id($response_balances);
                $this->design->assign('organization_id', $organization_id);
                $sbp_accounts = $this->users->getSbpAccounts($this->user->id);
                $this->design->assign('user_has_sbp', !empty($sbp_accounts));
                $canAddSbpAccount = $this->best2pay->canAddSbpAccount((int)$this->user->id);
                $this->design->assign('can_add_sbp_account', $canAddSbpAccount);

                $canAttach = (empty($sbp_accounts) || $this->best2pay->canAddSbpAccount((int)$this->user->id))
                    && isset($this->settings->sbp_enabled[$organization_id]);

                $this->design->assign('sbp_attach', $canAttach);

                $previous_order = $this->orders->get_previous_order($user->id);
                // Есть автоодобренная заявка
                if (!empty($previous_order) && !empty($user->auto_approve_order)) {
                    // Были займы только в другой МКК
                    $autoapprove_other_org = !$this->organizations->is_our_card($previous_order->organization_id);
                    $this->design->assign('autoapprove_other_org', $autoapprove_other_org);

                    // Требуем перепривязку карты для принятия денег с автоодобрения, если ранее карта была привязана к другой организации
                    $autoapprove_card_reassign = false;
                    $last_order_card = null;
                    /** @var array $previous_cards ВСЕ карты клиента, даже удалённые */
                    $previous_cards = $this->best2pay->get_cards(['user_id' => $user->id]) ?: [];
                    foreach ($previous_cards as $card) {
                        if ($card->id == $previous_order->card_id) {
                            $last_order_card = $card;
                            $this->design->assign('last_order_card', $last_order_card);

                            if (!$this->organizations->is_our_card($last_order_card->organization_id)) {
                                $autoapprove_card_reassign = true;
                                $autoapprove_wrong_card = true;
                                // А тут смотрим только актуальные карты
                                foreach ($cards as $card) {
                                    if ($this->organizations->is_our_card($card->organization_id)) {
                                        //  Была привязана новая карта
                                        $autoapprove_card_reassign = false;
                                        if ($card->pan == $last_order_card->pan) {
                                            //  ВСЁ ОК, Была привязана новая карта с таким же pan
                                            $autoapprove_wrong_card = false;
                                            //  Обновляем в автозаявке карту на привязанную, если ещё не обновили
                                            if ($last_order['card_id'] != $card->id) {
                                                $this->orders->update_order($last_order['id'], [
                                                    'card_id' => $card->id
                                                ]);
                                                $last_order['card_id'] = $card->id;
                                            }
                                            break;
                                        }
                                    }
                                }
                                $this->design->assign('autoapprove_wrong_card', $autoapprove_wrong_card);
                            }
                            break;
                        }
                    }
                    if (empty($last_order_card) && $autoapprove_other_org)
                        $autoapprove_card_reassign = true;
                    $this->design->assign('autoapprove_card_reassign', $autoapprove_card_reassign);
                }

                $this->design->assign('is_user_order_taken', Helpers::isTaken($user->order));

                if (!empty($user->order)) {
                    $likezaim = $this->likezaim->check($user->order, $response_balances);
                    $this->design->assign('likezaim', $likezaim);
                }

                $this->design->assign('loan_buyers', $this->parseLoanBuyers($response_balances));

                if ($divide_order_data = $this->orders->getDivideOrders($filter_divide_order, false)) {

                    $divide_order = new StdClass();
                    $divide_order->orders = [];

                    $divide_order->data = $divide_order_data;
                    $divide_pre_order_is_new = in_array($divide_order->data->status, $this->orders::DIVIDE_ORDER_STATUSES_IS_NEW);
                    $order = $this->orders->get_crm_order($divide_order_data->main_order_id);
                    $divide_order->orders[$order->order_uid] = new StdClass();
                    $divide_order->orders[$order->order_uid]->order = $order;
                    $divide_order->orders[$order->order_uid]->balance = $user->balance;
                }

                if (!empty($divide_order_data->divide_order_id)) {
                    $order = $this->orders->get_crm_order($divide_order_data->divide_order_id);
                    $divide_order->orders[$order->order_uid] = new StdClass();
                    $divide_order->orders[$order->order_uid]->order = $order;
                }

                foreach ($response_balances as $balance) {
                    $order_1c_id = $balance["Заявка"];
                    $order_data = new StdClass();

                    if (!empty($balance['НомерЗайма'])) {
                        if ($contract = $this->contracts->get_contract_by_params(['number'=>$balance['НомерЗайма']])) {
                            $order = $this->orders->get_crm_order($contract->order_id);
                        }
                    } elseif (!empty($order_1c_id)) {
                        $order = $this->orders->get_order_by_1c($order_1c_id);
                    } else {
                        continue;
                    }

                    $last_prolongation_payment = $this->best2pay->get_payments(
                        [
                            'order_id' => $order->id ?? $order->order_id,
                            'prolongation' => 1,
                            'reason_code' => 1
                        ],
                        false
                    );

                    $order->is_sum_hidden_after_prolongation = false;
                    if (
                        $last_prolongation_payment &&
                        strtotime($last_prolongation_payment->operation_date) > strtotime('-3 hours')
                    ) {
                        $order->is_sum_hidden_after_prolongation = true;
                    }
                    
                    if (!empty($divide_order->orders) && array_key_exists($order->order_uid, $divide_order->orders)) {
                        continue;
                    }
                    $order_data->order = $order;

                    $order_id = isset($order_data->order->id) ? $order_data->order->id : (isset($order_data->order->order_id) ? $order_data->order->order_id : null);

                    $organizationId = $this->users->getOrganizationIdByOrderId($order_id);

                    $this->design->assign('organizationId', $organizationId);

                    $all_orders->orders[$order->order_uid]= $order_data;
                }

                // СРКВ: проверка блокировки допов на оплате (история возвратов)
                $srkvTvMedBlocked = false;
                $srkvConciergeBlocked = false;
                try {
                    $srkv = Application::getInstance()->make(ReturnCoefficientService::class);
                    $srkvTvMedBlocked    = $srkv->shouldBlockService((int)$this->user->id, ReturnCoefficientService::SERVICE_TV_MEDICAL, ReturnCoefficientService::STAGE_PAYMENT);
                    $srkvConciergeBlocked = $srkv->shouldBlockService((int)$this->user->id, ReturnCoefficientService::SERVICE_MULTIPOLIS, ReturnCoefficientService::STAGE_PAYMENT);
                } catch (Throwable $e) {
                    log_warning('SRKV: payment block check failed', ['error' => $e->getMessage()]);
                }

                $set_balance = function ($order_array) use ($response_balances, $all_orders, $user, $srkvConciergeBlocked) {
                    $order_balance = array_filter($response_balances, function ($item) use ($order_array) {
                        return $item['Заявка'] == $order_array->order->{'id_1c'};
                    });
                    $balance_1c = (object)array_shift($order_balance);
                    $order_array->balance = $this->users->make_up_user_balance($this->user->id, $balance_1c);
                    $order_id = $order_array->order->id ?? $order_array->order->order_id ?? null;
                    $order_array->is_rcl = $order_id ? $this->rcl->isRclTranche((int)$order_id) : false;
                    $order_array->balance->calc_percents = $order_array->is_rcl ? 0 : $this->users->calc_percents($order_array->balance);
                    $order_array->multipolis_amount = $this->multipolis->getMultipolisAmount($order_array,$this->multipolis::DEFAULT_PROLONGATION_DAY,(int)$order_array->order->order_id);
                    $order_array->due_days = OrderService::calculateDueDays(
                        $balance_1c->{'ПланДата'} ?? null
                    );
                    $this->design->assign('contract_number', $order_array->balance->zaim_number);
                    // Чистые дни просрочки для Findzen по конкретному договору
                    $order_array->clear_due_days = $this->findzenBannerService->getClearDueDays(
                        $response_balances, $order_array->balance->zaim_number
                    );

                    $order_array->wheel_available = in_array($order_array->order->organization_id, [
                        $this->organizations::LORD_ID,
                        $this->organizations::FINLAB_ID,
                        $this->organizations::RZS_ID,
                        $this->organizations::MOREDENEG_ID,
                        $this->organizations::FRIDA_ID,
                        $this->organizations::FASTFINANCE_ID,
                    ]);
                    $order_array->newyear_promo = $this->getNewYearPromo($order_array);

                    // проверяем возможность рефинансирования
                    $order_array->refinance = $this->refinance->checkOrganizationRefinanceAvailable($order_array->order->organization_id)
                        ? $this->refinance->get_refinance($order_array->balance, $this->user)
                        : null;

                    $p2pcredits = $this->best2pay->get_p2pcredits(['order_id' => $order_array->order->order_id, 'status' => 'APPROVED'], false);
                    $order_array->balance->p2pcredits_amount = $p2pcredits ? $p2pcredits->amount : null;

                    if ($order_array->balance->loan_type == 'IL') {
                        $zaim_summ = (float)$order_array->balance->zaim_summ;
                        $all_orders->has_il_order = 1;
                        $order_array->balance->details = $this->soap->get_il_details($order_array->balance->zaim_number);
                        $order_array->balance->need_accept = $this->installments->check_accept($order_array->balance->zaim_date);

                        $order_array->balance->details['multipolis_amount'] = 0;
                        if ($p2pcredits && !$srkvConciergeBlocked) {
                            $order_array->balance->details['multipolis_amount'] = (int)($zaim_summ / ($order_array->balance->details['КоличествоПлатежей']) * $this->multipolis::IL_DOP_RATE * (int)$order_array->order->additional_service_multipolis);
                        }

                        $schedule_payments = $this->soap->get_schedule_payments($order_array->balance->zaim_number);
                        $payments = end($schedule_payments)->{'Платежи'} ?? [];

                        $totalPercentage = 0;
                        foreach ((array)$payments as $payment) {
                            $totalPercentage += (float)($payment->{'СуммаПроцентов'} ?? 0);
                        }
                        $totalPercentage = round($totalPercentage, 2);

                        // Вычисляем комиссию 
                        $order_array->balance->details['fee'] = $this->best2pay->calculateFee(
                            $order_array->order,
                            $user,
                            $this->star_oracle::ACTION_TYPE_FULL_PAYMENT
                        );
                        // расчет общей суммы без комиссией (Это полная сумма, которую вы бы заплатили по графику, без досрочного погашения: сумма займа + проценты + ДОП-консьерж(при каждой оплате по графику) )
                        $subtotal = $zaim_summ * (1 + $this->multipolis::IL_DOP_RATE) + $totalPercentage;
                        // Финальная сумма с комиссией
                        $order_array->balance->details['total_amount'] = (int)round(
                            $subtotal * (1 + $order_array->balance->details['fee'])
                        );
                        
                    }
                };
                if ($divide_order) {
                    array_walk($divide_order->orders, $set_balance);
                    $graceAmountDivide = false;
                    foreach ($divide_order->orders as $order) {
                        if (!empty($order->balance->sum_with_grace)) {
                            $graceAmountDivide = true;
                            break;
                        }
                    }
                    $this->design->assign('graceAmountDivide', $graceAmountDivide);
                }

                if ($all_orders->orders) {
                    array_walk($all_orders->orders, $set_balance);
                }

                array_walk($all_orders->orders, function(&$order) use ($user) {
                    $res = $this->best2pay->getSbpStatus($user->id, $order->order->order_id);

                    if (!empty($res) && empty($res[0]->operation_id)){
                        $order->failed_sbp = 1;
                    }
                });

                // получим документы из 1С
                if ($divide_order) {
                    array_walk($divide_order->orders, function($d_order) {
                        $this->addDocumentsToOrder($d_order);
                    });
                }

                if ($all_orders->orders) {
                    array_walk($all_orders->orders, function($d_order) {
                        $this->addDocumentsToOrder($d_order);
                    });
                }
                
                $vitaMedTariffs = $this->tv_medical->getAllVitaMedPrices();

                foreach ($all_orders->orders as $order) {
                    $utc_payment_date = strtotime($order->balance->payment_date);
                    $utc_now = strtotime(date('Y-m-d 00:00:00'));

                    if ($utc_now > $utc_payment_date) {
                        $this->design->assign('loan_expired', 1);

                        // Добавим новую проверку подписи просроченного займа
                        if (!empty($order->balance->zayavka)) {
                            $status_zaim = $this->users->getZaimAspStatus($order->balance->zaim_number);
                            $hide_asp_modal = !empty($_SESSION['hide_asp_modal']) && $_SESSION['hide_asp_modal'] == $user->id;
                            $show_asp_modal = !$status_zaim && !$hide_asp_modal;
                            $this->design->assign('show_asp_modal', $show_asp_modal);

                            if (!isset($_SESSION['hide_asp_modal'])) {
                                $_SESSION['hide_asp_modal'] = $user->id;
                            }
                        }
                    }

                    $order->vitamed_disabled = $this->orders->shouldDisableVitamed($order->balance->zaim_number);
                    // Проверяем scorista_ball для prolongation_tv_medical_price
                    $order->prolongation_tv_medical_price = $vitaMedTariffs[1]->price;
                    if ($order->order->scorista_ball >= $this->tv_medical::SCORISTA_BALL_FREE_THRESHOLD || $order->vitamed_disabled || $srkvTvMedBlocked) {
                        $order->prolongation_tv_medical_price = 0;
                    }
                }


                if ($divide_order) {
                    // возьмем данные о разделении
                    $divide_pre_order = $this->orders->getDividePreOrder((int)$divide_order_data->main_order_id);
                } else {
                    // возьмем данные о разделении
                    $divide_pre_order = $this->orders->getDividePreOrder((int)$last_order['id']);
                }

                // если разделенный займ не в работе (не одобрен)
                if ($divide_pre_order_is_new || !$divide_order) {
                    $divide_pre_order_accept_date = date('d.m.Y', strtotime('+ 1 day'));
                }

                $user_balance = $this->users->get_user_balance($this->user->id);
                $amount = intval($user_balance->ostatok_od + $user_balance->ostatok_percents + $user_balance->ostatok_peni);

                $vitaMedPrice = $this->tv_medical->getVItaMedPrice($amount);
                $tv_medical_tariffs = $this->tv_medical->getAllTariffs();

                // СРКВ: блокировка ВМ — обнуляем цены, передаём флаг в шаблон
                if ($srkvTvMedBlocked) {
                    if ($vitaMedPrice) {
                        $vitaMedPrice->price = 0;
                    }
                    $tv_medical_price = 0;
                }

                $starOraclePrice = $this->star_oracle->getStarOraclePrice($amount);
                $star_oracle_tariffs = $this->star_oracle->getAllTariffs();

                $asp_link_params = [
                    'params' => Documents::getParamsForContractDeletedUser($this->user),
                ];

                $userId = intval($this->user->id);
                $isFirstOrderAndProlongation = $this->checkOrderAndProlongation($userId);

                if ($isFirstOrderAndProlongation) {
                    $isFirstOrderAndProlongation = 1;
                } else {
                    $isFirstOrderAndProlongation = 0;
                }

                $this->design->assign('is_first_order', $isFirstOrderAndProlongation);

                $asp_contract_delete_user_link = $this->config->root_url . '/preview/contract_delete_user_cabinet?' . http_build_query($asp_link_params);

                $akvariusExpiredDays = BalanceHelper::getDebtInDays(
                    $this->users->get_user_balance(
                        $this->user->id,
                        ['inn' => '9714011290'] // только аква
                    )
                );


                $skip_nk_utms = explode(',', $this->settings->bonon_excluded_utms ?? '');
                if(\api\helpers\UserHelper::userHasOverduedDays($this, $this->user->id, 1)) {
                    $partner_href_expired = $this->partner_href->getActualItem(1, 'bonon-shop-window-overdue');
                    $this->design->assign('client_suffix', '-overdue:pk');
                    if(\api\helpers\UserHelper::userHasOverduedDays($this, $this->user->id, 4)) {
                        $comeback = $this->partner_href->getActualItem(1, 'bonon-comeback-overdue');
                        $this->design->assign('partner_href_expired', $partner_href_expired->href ? "{$partner_href_expired->href}{$partner_postfix}" : '');
                    } else {
                        $skip_banner = $this->bannerRejectionTrafficABTest();
                        $this->design->assign('ab_key', $this->ab_test_service::PARTNER_BANNER_AB);
                        if(!$skip_banner) {
                            $comeback = $this->partner_href->getActualItem(1, 'bonon-comeback-overdue');
                            $this->design->assign('partner_href_expired', $partner_href_expired->href ? "{$partner_href_expired->href}{$partner_postfix}" : '');
                        }
                    }
                } elseif(\api\helpers\UserHelper::userHasUpcomingPayment($this, $this->user->id, 2)) {
                    $comeback = $this->partner_href->getActualItem(1, 'bonon-comeback-refinance');
                    $partner_href = $this->partner_href->getActualItem(1, 'bonon-shop-window-refinance');
                    $this->design->assign('disable_partner_href_autoredirect', true);
                    $this->design->assign('partner_href', $partner_href->href ? "{$partner_href->href}{$partner_postfix}" : '');
                    $this->design->assign('view_partner_href', !empty($partner_href->href));
                    $this->design->assign('client_suffix', '-refinance:pk');
                } elseif($last_order['status'] == 3) {
                    $comeback = $this->partner_href->getActualItem((int)$last_order['have_close_credits'], 'bonon-comeback-decline');
                    $this->design->assign('client_suffix', '-decline' . ((int)$last_order['have_close_credits'] ? ':pk' : ':nk'));
                }

                $this->design->assign('comeback_url', (isset($comeback) && $comeback->href) ? "{$comeback->href}{$partner_postfix}" : '');
                $this->design->assign('akvarius_expired_days', $akvariusExpiredDays);

                // проверим пользователя на наличие условий и выключим допы
                $notOverdueLoan = \api\helpers\UserHelper::hasNotOverdueLoan($this, $this->user);
                $this->design->assign('notOverdueLoan', $notOverdueLoan);

                $this->design->assign('asp_type_remove_account', AspHelper::ASP_TYPE_CONFIRM_REMOVE_ACCOUNT);
                $this->design->assign('asp_contract_delete_user_link', $asp_contract_delete_user_link);
                $this->design->assign('vita_med', $vitaMedPrice);
                $this->design->assign('star_oracle', $starOraclePrice);
                $this->design->assign('tv_medical_price', $tv_medical_price ?? $vitaMedTariffs[1]->price);
                $this->design->assign('multipolis_amount', $multipolis_amount);
                $this->design->assign('tv_medical_id', $vitaMedTariffs[1]->id);
                $this->design->assign('tv_medical_tariffs', $tv_medical_tariffs);
                $this->design->assign('srkv_tv_med_blocked', $srkvTvMedBlocked);
                $this->design->assign('srkv_concierge_blocked', $srkvConciergeBlocked);
                $this->design->assign('star_oracle_tariffs', $star_oracle_tariffs);
                $this->design->assign('divide_order', $divide_order);
                $this->design->assign('all_orders', $all_orders);
                $this->design->assign('divide_pre_order', $divide_pre_order);
                $this->design->assign('divide_pre_order_is_new', $divide_pre_order_is_new);
                $this->design->assign('divide_pre_order_accept_date', $divide_pre_order_accept_date);

                $this->design->assign('user_return_credit_doctor', (int)($this->users->getUserReturnExtraService($this->user->id,'credit_doctor') > 3));
                $this->design->assign('applied_promocode', !empty($last_order['promocode']) ? $this->promocodes->getInfoById($last_order['promocode']) : null);
                $this->design->assign('last_order', $last_order);
                $isLastOrderAutoApproved = $last_order['utm_source'] === $this->orders::UTM_RESOURCE_AUTO_APPROVE;
                $this->design->assign('is_last_order_auto_approved', $isLastOrderAutoApproved);
                $this->design->assign('order_id', $last_order['id']);
                $this->design->assign('cross_orders', $cross_orders);
                $this->design->assign('asp_code_already_sent', $_SESSION['asp_code_already_sent'] ?? false);
                $this->design->assign('wheel_available', false);
                $this->design->assign('can_see_refinance_button', (bool)$this->user_data->read($this->user->id, 'test_refinance'));

                /** Прогресс бар */
                $this->design->assign('progress_bar_available', (bool)$this->settings->progress_bar_available);
                $this->design->assign('slider_interact', (bool)$this->overdue_slider_service->hasInteract($this->user->id) ? 1 : 0);
                $this->design->assign('click_info', (bool)$this->overdue_slider_service->hasClicked($this->user->id) ? 1 : 0);

                /** Реферальная ссылка */
                $canShowRefererBanner = $this->referralService->canShowRefererBanner((int) $this->user->id);
                $this->design->assign('canShowRefererBanner', $canShowRefererBanner);
                $this->design->assign('referer_url', $canShowRefererBanner ? $this->referralService->getRefererUrl($this->user) : null);

                /** баннер на ФинДзен */
                $this->design->assign('findzen_overdue_days', $this->findzenBannerService->getOverdueDays());
                $this->design->assign('findzen_url', $this->findzenBannerService->getUniqueTargetUrl(
                    $this->settings->url_findzen,
                    $this->user->uid ?? '',
                    $this->users->isSafetyFlow($this->user)
                ));

                $mfoParams['params'] = (array) $this->organizations->get_organization($organization_id);
                $userRecurrentData = [
                    'lastname'  => $this->user->lastname,
                    'firstname'  => $this->user->firstname,
                    'patronymic'  => $this->user->patronymic,
                    'birthday'  => $this->user->birthday,
                    'passport_serial'  => $this->user->passport_serial,
                    'passport_issued'  => $this->user->passport_issued,
                    'passport_code'  => $this->user->passport_code,
                    'passport_date'  => $this->user->passport_date,
                    'registration_address'  => $this->user->registration_address,
                    'asp'  => $last_order['$last_order'],
                    'date' => $last_order['confirm_date'],
                ];
                $mfoParams = array_merge($mfoParams, $userRecurrentData);
                $this->design->assign('mfo_params', $mfoParams);

                $this->organizations->assign_to_design();

                $this->design->assign('restricted_mode', (int)$restricted_mode);
                $this->design->assign('friend_restricted_mode', (int)$friend_restricted_mode);

                $restricted_mode_logout_hint = $restricted_mode
                    && ($user->balance->zaim_number === 'Нет открытых договоров'
                        || $_SESSION['restricted_mode_logout_hint'] == 1);

                $this->design->assign('restricted_mode_logout_hint', (int)$restricted_mode_logout_hint);

                $cards = array_filter( (array) $cards, function( $card ) use ($organization_id){
                    return empty( $card->deleted );
                } );
                $this->design->assign('cards', $cards);
                $this->design->assign('has_default_card', $this->is_default_card_set($cards));
                $this->design->assign('complaint_topics', $this->tickets->getTopics());

                $this->design->assign('has_vk', !empty($this->vk_api->get($user_id)));

                // добавим кастомную метрику
                $this->custom_metric->addMetricAction($this->custom_metric::GOAL_USER_LOGIN_LK, 1);

                // определим нужно ли показывать ссылку на займы для ИП и ООО
                $active_company__orders = $this->company_orders->getItems(['user_id' => $user_id, 'status' => [
                    $this->company_orders::STATUS_NEW,
                    $this->company_orders::STATUS_REJECT,
                    $this->company_orders::STATUS_APPROVED,
                ]]);
                $check_day_limit_co = $this->company_orders->checkShowHref();

                $active_loans = array_filter($response_balances, function($item) {
                    return !empty($item['НомерЗайма']) && $item['НомерЗайма'] != 'Нет открытых договоров';
                });

                $this->design->assign('has_active_loans', !empty($active_loans));
                $this->design->assign('show_company_form', $check_day_limit_co && empty($active_company__orders) && !empty($active_loans));

                $this->design->assign('payment_methods_btn', $this->settings->payment_methods_btn);

                // признак блокировки рекламных смс
                $blocked_adv_sms = $this->blocked_adv_sms->getItemByUserId($user_id);
                $this->design->assign('blocked_adv_sms', $blocked_adv_sms);

                $orderForChoosingCard = $this->getOrderForChoosingCard($last_order, $cross_orders, $divide_order);
                $this->design->assign('order_for_choosing_card', $orderForChoosingCard);

                /** На основании этого заказа мы определяем какая карта выбрана card_id, если это карта Boostra выводим предупреждение */
                if(isset($orderForChoosingCard['card_id'])) {
                    $cardForOrder = $this->best2pay->get_card($orderForChoosingCard['card_id']);
                    if(isset($cardForOrder->organization_id) && ((int)$cardForOrder->organization_id === Organizations::BOOSTRA_ID)) {
                        $this->design->assign('is_need_choose_card', true);
                    }
                }

                $this->addSbpAccounts($orderForChoosingCard);
                $this->addSbpBanks();
                $this->addSelectedBank($orderForChoosingCard);

                // проверим пользователя на наличие условий и выключим допы
                $notOverdueLoan = \api\helpers\UserHelper::hasNotOverdueLoan($this, $this->user);

                if (!empty($last_order)) {
                    $this->design->assign('last_order_data', $this->order_data->readAll($last_order['id']));
                }

                // Логируем вход в ЛК для новогодней акции
                $this->logNewYearLkOpen();

                $centrifugo_jwt_token = \api\helpers\JWTHelper::generateToken($this->config->CENTRIFUGO['hmac_secret_key'], (int)$this->user->id);
                $this->design->assign('centrifugo_jwt_token', $centrifugo_jwt_token);

                // проверим закрытие займа и задачу для автозаявки
                // Если таск в кроне не обработан и он есть, то отображаем заглушку в ЛК
                if ($last_order['1c_status'] === Orders::ORDER_1C_STATUS_CLOSED) {
                    if ($orderAutoApproveTask = $this->orders_auto_approve->getActiveTask((int)$this->user->id))
                    {
                        $dateCreatedTask = new DateTime($orderAutoApproveTask->date_added);
                        $dateEndViewCounter = new DateTime('-15 minutes');
                        $seconds = $dateCreatedTask->getTimestamp() - $dateEndViewCounter->getTimestamp();

                        // отобразим время сколько осталось от 15 минут
                        if ($seconds > 0) {
                            $this->design->assign('auto_approve_seconds_task', $seconds);
                        }
                    }
                }

                if (!empty($_COOKIE['utm_source'])) {
                    $this->userUtm->create($this->user->id);
                }
                $autoconfirm_enabled = $this->autoconfirm->is_enabled($this->user);

                $this->checkShowRepeatAutoConfirmModal((int)$last_order['id']);

                $show_payment_options_modal = isset($_GET['payment-options']) && !empty($this->user->id);
                $this->design->assign('show_payment_options_modal', $show_payment_options_modal);

                $this->design->assign('autoconfirm_enabled', $autoconfirm_enabled);

                $this->design->assign('has_cancelled_payment_rs', $this->payment->getHasCancelledPaymentRs($user_id));

                $this->design->assign('payment_rs_data', $this->payment->getPaymentRsData($user_id));

                $this->design->assign('faq_highlight_enabled', $this->settings->faq_highlight_enabled);
                $this->design->assign('faq_highlight_delay', $this->settings->faq_highlight_delay);

                $isRecurringPaymentSoEnabled = $this->users->isRecurringPaymentSoEnabled($this->user->id);

                $this->design->assign('is_recurring_payment_so_enabled', $isRecurringPaymentSoEnabled);

                $notificationType = null;
                $tickets = $this->tickets->getClientTickets($this->user->id);
                foreach ($tickets as $ticket) {
                    if ($ticket->notify_user) {
                        if ($ticket->status_id == 3) {
                            $notificationType = 'pause';
                        } elseif ($ticket->status_id == 4) {
                            $notificationType = 'resolved';
                        }
                    }
                }

                if ($notificationType) {
                    $this->design->assign('notification_type', $notificationType);
                }

                if (!empty($this->users->isUserOldClientOrOldRegister($this->user))) {
                    $this->design->assign('is_old_client_or_old_register', 1);
                }

                $this->design->assign('individual_max_amount_doc_url', $this->getIndividualMaxAmountDocUrl());
                if (!empty($last_order) && $this->order_data->read($last_order['id'], $this->order_data::RCL_LOAN)) {
                    $rcl_ind_params = [
                        'params' => [
                            'percent' => $last_order['percent'],
                            'period' => $last_order['period'],
                            'amount' => $last_order['amount'],
                        ],
                        'user_id' => $this->user->id,
                        'organization_id' => $last_order['organization_id'],
                    ];
                    $rcl_ind_params['params']['rcl_amount'] =
                        $this->order_data->read($last_order['id'], $this->order_data::RCL_AMOUNT) ?: $last_order['amount'];
                    $rcl_ind_params['params']['amount_string'] = $this->documents->convertAmountToString($rcl_ind_params['params']['amount']);
                    $rcl_ind_params['params']['rcl_amount_string'] = $this->documents->convertAmountToString($rcl_ind_params['params']['rcl_amount']);
                    $rcl_ind_params['params']['amount_string'] = $this->documents->convertAmountToString($last_order['amount']);
                    $this->design->assign('individual_url', $this->config->root_url . '/preview/rcl_ind_usloviya?' . http_build_query($rcl_ind_params));
                }
                else {
                    $this->design->assign('individual_url', $this->getIndividualWithoutAmountDocUrl());
                }

                if($this->settings->zero_discount_enabled){
                    $this->design->assign('zeroDiscount', ZeroDiscountService::handle($orders));
                }

                if ($this->showPopupToRepeatIssuanceOrder()) {
                    $this->design->assign('show_popup_to_repeat_issuance_order', true);
                }

                // Скрываем блок нет карт если попал под фитчу (MARK-585) + выбрал банк
                if ($this->users->skipSelectCardStep($this->user)) {
                    $bankIdForSbpIssuance = $this->order_data->read($last_order['id'], $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);
                    $this->design->assign('hide_no_card_block', $bankIdForSbpIssuance);
                }

                // Сделаем проверку на то, нужно ли добавлять карту под ручеек, (MARK-864) пока только ПК
                if ($last_order['have_close_credits'] == 1 && ($organization_id_for_river = $this->organizations->getOrganizationIdForRiver($this->user))) {
                    $this->design->assign('organization_id_for_river', $organization_id_for_river);
                }

                return $this->design->fetch('user.tpl');
            }
        }

        return false;
    }

    private function enableVirtualCard() {
        $isVirtualCardConsent = (int)$this->user_data->read($this->user->id, "is_virtual_card_consent") === 1;
        $this->design->assign('is_virtual_card_consent', $isVirtualCardConsent);

        $cardData = $this->virtualCard->forUser($this->user->id)->status();
        $cardStatus = $cardData['status'] ?? null;

        $this->design->assign('is_virtual_card_active', in_array($cardStatus, ['pending', 'active']));

        $isVirtualCardEnabled = $this->settings->vc_enabled
            && isset($_COOKIE['utm_campaign'])
            && $_COOKIE['utm_campaign'] === 'vctest';

        $this->design->assign('is_virtual_card_enabled', (bool)$isVirtualCardEnabled);

        if ($isVirtualCardEnabled) {
            $userCards = $this->best2pay->get_cards(array('user_id' => $this->user->id));

            $this->design->assign('virtual_card_data', $cardData);
            $this->design->assign('user_cards', $userCards);
        }
    }

    /**
     * Получает ссылку ПК, автоподпсианий для договора индивидуальных условий, максимальная сумма
     * @return string
     */
    private function getIndividualMaxAmountDocUrl(): string
    {
        $params = [
            'user_id' => $this->user->id,
            'params' => [
                'percent' => Orders::BASE_PERCENTS,
                'period' => Orders::MAX_AMOUNT_FIRST_LOAN,
                'amount' => Orders::PDL_MAX_AMOUNT,
            ]
        ];

        return $this->config->root_url . "/preview/IND_USLOVIYA?" . http_build_query($params);
    }

    /**
     * Генерируем ссылку на автоподписание без суммы
     * @return string
     */
    private function getIndividualWithoutAmountDocUrl(): string
    {
        $get_params = [
            'params' => [
                'hide_user_data' => 1,
            ],
            'user_id' => $this->user->id,
        ];
        return $this->config->root_url . '/preview/IND_USLOVIYA?' . http_build_query($get_params);
    }

    /**
     * @param $user_id
     * @return bool
     */
    public function checkOrderAndProlongation($user_id): bool
    {
        if (!empty($user_id)) {
            $first_contract = $this->orders->isFirstOrder($user_id);
            $prolongation_zero = $this->users->isProlongationZero($user_id);

            return ($first_contract && $prolongation_zero);
        }
        return false;
    }

    private function get_user_cards($user)
    {
        if ($user->uid == "Error") {
            return [];
        }

        $cards = [];
        $b2p_enabled = $this->settings->b2p_enabled || $this->user->use_b2p;
        if ($b2p_enabled) {
            return array_map(function ($card) {
                $this->set_is_default_card($card);

                $card->autodebiting = false;
                $card->rebill_id = false;

                return $card;
            }, $this->best2pay->get_cards([
                'user_id' => $user->id,
                'deleted' => 0,
                'deleted_by_client' => 0,
            ]));
//            alter table b2p_cards
//    add deleted_by_client tinyint(1) default 0 null;
        }

        $soap_cards = $this->notify->soap_get_card_list($user->uid);

        if ($soap_cards) {
            foreach ($soap_cards as $card) {
                if ($card->Status == 'A') {
                    $new_card = new stdClass();
                    $new_card->id = $card->CardId;
                    $new_card->pan = $card->Pan;
                    $new_card->autodebiting = $card->AutoDebiting ?? 0; // @todo этого признака нет в АПИ Тинька https://acdn.tinkoff.ru/static/documents/merchant_api_protocoI_e2c_v2.pdf стр. 30
                    $new_card->rebill_id = $card->RebillId;

                    $this->set_is_default_card($new_card);

                    $cards[] = $new_card;
                }
            }
        }
        /*
                // Получение ссылки для привязки карты через 1с
                  $add_card = $this->notify->soap_add_card($user->uid);
                  $user->add_card = $add_card->PaymentURL;
        */

        // получаем ссылку на привязку карты через тиньков
        $add_card = $this->tinkoff->add_card($user->uid);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($add_card);echo '</pre><hr />';
        // костыль для неправильно обьединенных терминалов
        if (isset($add_card['error']) && $add_card['error'] == 'Найдено больше одного CustomerKey') {
            $this->tinkoff->remove_customer($user->uid);
            $add_card = $this->tinkoff->add_card($user->uid);
        }
        $user->add_card = $add_card['PaymentURL'];

        return $cards;
    }

    private function set_is_default_card($card)
    {
        $card->default = isset($_COOKIE['card_pay_id']) && $_COOKIE['card_pay_id'] == $card->id;
    }

    private function is_default_card_set($cards)
    {
        foreach ($cards as $card) {
            if ($card->default) {
                return true;
            }
        }

        return false;
    }

    private function get_new_contracts($order_id)
    {
        $order = $this->orders->get_crm_order($order_id);
//        $manager = $this->managers->get_crm_manager($order->manager_id);

        //gray button
        $gray = $this->check_order_1c($order->id_1c);

        // хз что это за блок ниже, 3 раза однои тоже вызывалось
        return [
            'gray_contract' => $gray->return->Файл,
            'green_contract' => $gray->return->Файл,
            'new_contract' => $gray->return->Файл,
        ];
    }

    /**
     * Извлекает информацию о переданных долгах (цессии/агенты) из балансов от 1С.
     *
     * Строка портфеля вида "Агент 01.01.2024" парсится для получения наименования агента и даты передачи.
     *
     * @param array<int|string, array<string, mixed>> $responseBalances Массив балансов пользователя от 1С.
     *
     * @return array<int, array<string, string>> Массив с разобранными данными агентских договоров.
     */
    private function parseLoanBuyers(array $responseBalances): array
    {
        $loanBuyers = [];

        foreach ($responseBalances as $responseBalance) {
            if (
                empty($responseBalance['Портфель']) ||
                empty($responseBalance['НомерЗайма']) ||
                empty($responseBalance['ДатаЗайма'])
            ) {
                continue;
            }

            foreach ((array)$responseBalance['Портфель'] as $buyerData) {
                if (!is_string($buyerData) || !preg_match('@^(.+?)\s(\d{2}\.\d{2}\.\d{2,4})$@', trim($buyerData), $matches)) {
                    continue;
                }

                $buyerOrganization = $matches[1];
                $buyDate = date('d.m.Y', strtotime($matches[2]));

                switch ($buyerOrganization) {
                    case 'МБА':
                        $loanBuyerName = 'ООО "М.Б.А. ФИНАНСЫ"';
                        break;
                    case 'Boostra':
                        $loanBuyerName = 'ООО "БИКЭШ"';
                        break;
                    case 'СКА':
                        $loanBuyerName = 'ООО "Сибирское коллекторское агентство" 88006008384';
                        break;
                    default:
                        $loanBuyerName = $buyerOrganization;
                }

                $loanBuyers[] = [
                    'loan_number' => (string)$responseBalance['НомерЗайма'],
                    'loan_date' => date('d.m.Y', strtotime((string)$responseBalance['ДатаЗайма'])),
                    'loan_buy_date' => $buyDate,
                    'loan_buyer_name' => $loanBuyerName,
                ];
            }
        }

        return $loanBuyers;
    }

    private function check_need_add_fields()
    {
        /** Дозаполнение полей */
        $need_add_fields = array();
        if (empty($this->user->lastname))
            $need_add_fields[] = 'lastname';
        if (empty($this->user->firstname))
            $need_add_fields[] = 'firstname';
        //if (empty($this->user->patronymic))
        //    $need_add_fields[] = 'patronymic';

        if (empty($this->user->gender))
            $need_add_fields[] = 'gender';
        if (empty($this->user->birth))
            $need_add_fields[] = 'birth';
        if (empty($this->user->birth_place))
            $need_add_fields[] = 'birth_place';
//        if (empty($this->user->marital_status))
//            $need_add_fields[] = 'marital_status';

        if (empty($this->user->passport_serial))
            $need_add_fields[] = 'passport_serial';
        if (empty($this->user->passport_date))
            $need_add_fields[] = 'passport_date';
        if (empty($this->user->subdivision_code))
            $need_add_fields[] = 'subdivision_code';
        if (empty($this->user->passport_issued))
            $need_add_fields[] = 'passport_issued';

        if (empty($this->user->Regindex) || empty($this->user->Regregion))
            $need_add_fields[] = 'regaddress';

        if (empty($this->user->Faktindex) || empty($this->user->Faktregion))
            $need_add_fields[] = 'faktaddress';

//        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $this->user->id));
//        if (empty($contactpersons))
//            $need_add_fields[] = 'contactpersons';

//        if (empty($this->user->work_scope))
//            $need_add_fields[] = 'work_scope';
        if (empty($this->user->income_base))
            $need_add_fields[] = 'income_base';

        if ($this->user->work_scope != 'Пенсионер') {
//            if (empty($this->user->Workregion) || empty($this->user->Workhousing))
//                $need_add_fields[] = 'workaddress';

            if (/*empty($this->user->work_scope) ||*/ empty($this->user->profession) || empty($this->user->workplace))
                $need_add_fields[] = 'workdata';
        }

        return $need_add_fields;
    }

    /**
     * Получить заявку для "Выбрать карту"
     *
     * @param $last_order
     * @param $cross_orders
     * @param $divide_order
     * @return array
     */
    private function getOrderForChoosingCard($last_order, $cross_orders, $divide_order): array
    {
        if (!empty($cross_orders) && is_array($cross_orders)) {
            foreach ($cross_orders as $cross_order) {
                if (empty($cross_order['noactive'])) {
                    return (array)$cross_order;
                }
            }
        }

        if (!empty($divide_order) && is_object($divide_order)) {
            $mainOrder = $this->orders->get_order($divide_order->data->main_order_id);

            if (!empty($mainOrder)) {
                return (array)$mainOrder;
            }
        }

        return (array)$last_order;
    }

    function checkPayCredit($data) {
        if ($data->got_gift == 0) {
            if (empty($data->sms_time) || (time() - strtotime($data->sms_time)) <= 86400) {
                return true;
            }
        }
        return false;
    }

    /**
     * Получитьпоследний ответ из кабутек
     */
    private function getLastVerification(int $userId): object
    {
        $cyberityScorings = $this->scorings->get_scorings([
            'user_id' => $userId,
            'type' => $this->scorings::TYPE_CYBERITY,
            'status' => [
                $this->scorings::STATUS_COMPLETED
            ],
            'success' => 0
        ]);
        $lastCyberityScoring = array_shift($cyberityScorings);
        $body = $this->scorings->get_scoring_body((int)$lastCyberityScoring->id);

        return (object)json_decode((string)$body, true);
    }

    /**
     * Метод проверки верификации кабутек
     */
    private function isNeedPhotoVerification(int $userId): bool
    {
        $body = $this->getLastVerification($userId);
        if(!empty($body->reviewResult->reviewAnswer)){
            return ($body->reviewResult->reviewAnswer ==='RED');
        }

        return false;
    }

    /** Обрабатываем utm, пришедшее с колекшна */
    public function processCollectionUtm(\StdClass $user, \StdClass $userBalance)
    {
        $utm = $_COOKIE['source_for_pay'];
        if (!$utm) {
            return;
        }

        $collectionPromo = strpos($utm, 'collection_promo') !== false && $userBalance->discount_amount > 0;
        if (!$collectionPromo) {
            $this->design->assign('collectionPromo', false);

            return;
        }

        $this->design->assign('collectionPromo', true);

        /** Дополнительные скрипты js для модалки конкретной акции */
        $additionalScripts = [];
        /** Заголовок модалки */
        $collectionPromoTitle = null;
        /** Сообщение в модалке */
        $collectionPromoMessage = null;
        /** Название доки с правилами акции */
        $collectionPromoDoc = null;

        $userBalance = $user->balance;
        /** Старая сумма до акции */
        $collectionPromoOldAmount =
            $userBalance->ostatok_od
            + $userBalance->ostatok_percents
            + $userBalance->ostatok_peni
            + $userBalance->penalty;

        /** Сумма со скидкой по акции */
        $collectionPromoNewAmount = $collectionPromoOldAmount - $user->balance->discount_amount;

        /** Акция к 8 марта */
        if (strpos($utm, '08_march') !== false) {
            $collectionPromoTitle = 'Лёгкость этой весны! ';
            $collectionPromoSubTitle = 'Погасите задолженность — порадуйте себя!';
            $collectionPromoMessage = '8 марта — для вас, а не для тревог!';
            $collectionPromoDoc = '8-march';
            $additionalScripts[] = 'showRose';
        }

        /** Акция к НГ */
        if (strpos($utm, 'happy_new_year') !== false) {
            $collectionPromoTitle = 'В Новый год без долгов';
            $collectionPromoSubTitle = '';
            $collectionPromoMessage = 'Начните Новый год без долгов!';
            $collectionPromoDoc = 'happy-new-year';
            $additionalScripts[] = 'snowFlakes';
        }

        $this->design->assign('additional_scripts', $additionalScripts);
        $this->design->assign('collectionPromoTitle', $collectionPromoTitle);
        $this->design->assign('collectionPromoSubTitle', $collectionPromoSubTitle);
        $this->design->assign('collectionPromoMessage', $collectionPromoMessage);
        $this->design->assign('collectionPromoDoc', $collectionPromoDoc);

        $this->design->assign('collectionPromoOldAmount', $collectionPromoOldAmount);
        $this->design->assign('collectionPromoNewAmount', $collectionPromoNewAmount);

        return [
            '$additionalScripts' => $additionalScripts,
            '$collectionPromoTitle' => $collectionPromoTitle,
            '$collectionPromoMessage' => $collectionPromoMessage,
            '$collectionPromoDoc' => $collectionPromoDoc,
            '$collectionPromoOldAmount' => $collectionPromoOldAmount,
            '$collectionPromoNewAmount' => $collectionPromoNewAmount,
        ];
    }

    /**
     * Проверим пользователя на наличие признака к переходу на страницу подписания документов для автовыдачи НК
     * @return void
     */
    private function checkAutoConfirmNewUser()
    {
        if (!$this->user_data->read($this->user->id, $this->user_data::AUTOCONFIRM_FLOW)) {
            return;
        }

        $this->request->redirect($this->config->root_url . '/autoconfirm-asp');
    }

    private function addSbpAccounts(array $orderForChoosingCard): void
    {
        $canAddSbpAccount = $this->best2pay->canAddSbpAccount((int)$this->user->id);
        if (empty($canAddSbpAccount)) {
            return;
        }

        $sbpAccounts = $this->users->getSbpAccounts((int)$this->user->id);

        if (!empty($sbpAccounts)) {

            // Добавляем название банков
            $b2pBanksId = array_column($sbpAccounts, 'member_id');
            $b2pBanks = $this->b2p_bank_list->get([
                'id' => $b2pBanksId
            ]);

            $b2pBanks = array_column($b2pBanks, null, 'id');

            foreach ($sbpAccounts as &$sbpAccount) {
                $sbpAccount->title = $b2pBanks[$sbpAccount->member_id]->title;
            }

            unset($sbpAccount);

            $this->design->assign('sbp_accounts', $sbpAccounts);
        }

        if (!empty((int)$orderForChoosingCard['card_id']) && $orderForChoosingCard['card_type'] === $this->orders::CARD_TYPE_SBP) {
            $this->design->assign('selected_sbp_account_id', (int)$orderForChoosingCard['card_id']);
        }
    }

    private function addSbpBanks()
    {
        if ($this->b2p_bank_list->canShowSbpBanks()) {
            $b2pSbpBanks = $this->b2p_bank_list->getSbpBanks();

            if (!empty($b2pSbpBanks)) {
                $this->design->assign('b2p_sbp_banks', $b2pSbpBanks);
            }
        }
    }

    private function addSelectedBank(array $orderForChoosingCard)
    {
        $bankId = $this->user_data->read((int)$this->user->id, $this->user_data::DEFAULT_BANK_ID_FOR_SBP_ISSUANCE);

        if (empty($bankId)) {
            $bankId = $this->order_data->read((int)$orderForChoosingCard['id'], $this->order_data::BANK_ID_FOR_SBP_ISSUANCE);

            if (empty($bankId)) {
                return;
            }
        }

        $selectedBank = $this->b2p_bank_list->getOne([
            'id' => $bankId,
            'has_sbp' => 1
        ]);

        if (!empty($selectedBank)) {
            $this->design->assign('selected_bank', $selectedBank);
        }
    }

    /**
     * Проверяем можно ли показать окно автоподписания для кнопки погасить заём полностью
     *
     * @param int $order_id Id заявки
     * @return void
     */
    private function checkShowRepeatAutoConfirmModal(int $order_id)
    {
        // првоерим подпсиывался ли пользователь ранее на автовыдачу
        $repeat_order_auto_confirm_asp = $this->order_data->read($order_id, $this->order_data::REPEAT_ORDER_AUTO_CONFIRM_ASP);
        $is_auto_confirm_crm_auto_approve_order = $this->users->isAutoConfirmCrmAutoApproveOrder($this->user);
        $this->design->assign('show_repeat_order_auto_confirm_asp', !$repeat_order_auto_confirm_asp && $is_auto_confirm_crm_auto_approve_order);
    }

    private function check_order_1c($order_1c_id)
    {
        if (!empty($response = $this->check_order_1c_cache[$order_1c_id])) {
            return $response;
        }

        $response = $this->orders->check_order_1c($order_1c_id);

        $this->check_order_1c_cache[$order_1c_id] = $response;

        return $response;
    }

    private function addDocumentsToOrder(&$d_order)
    {
        $d_order->order = (object)$d_order->order;

        $resp = $this->check_order_1c($d_order->order->id_1c);
        if (empty($resp)) {
            return;
        }

        $d_order->approved_file = $this->documents->save_pdf($resp->return->{'ФайлBase64'}, $resp->return->{'НомерЗаявки'}, 'Preview_Contracts');

        $order_id = isset($d_order->order->id) ? $d_order->order->id : (isset($d_order->order->order_id) ? $d_order->order->order_id : null);

        $status_1c = $resp->return->Статус;
        $this->orders->update_order($order_id, ['1c_status' => $status_1c]);
        $d_order->status_1c = $status_1c;
    }

    private function bannerRejectionTrafficABTest()
    {
        $group = $this->ab_test_service->getGroup($this->user->id);

        $this->ab_test_service->logLkOpen($this->user->id);

        return $group == $this->ab_test_service::BANNER_GROUP_CONTROL;
    }

    public function downloadDocsForReferral()
    {
        $path = $this->config->root_dir .'files/docs/referral/Положение акции Скидка за привлечение boostra.ru.pdf';

        if (!is_file($path) || !is_readable($path)) {
            http_response_code(404);
            exit('File not found');
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Правила_акции.pdf"');
        header('Content-Length: ' . (string) filesize($path));
        header('Cache-Control: private, max-age=3600');
        header('X-Content-Type-Options: nosniff');

        $chunk = 8192;
        $fh = fopen($path, 'rb');
        while (!feof($fh)) {
            echo fread($fh, $chunk);
        }
        fclose($fh);
        exit;
    }

    /**
     * Проверяем, нужно ли показывать попап с просьбой выбрать другой банк или привязать другой СБП счет при неуспешной выдаче для автоматический перевыдачи
     */
    private function showPopupToRepeatIssuanceOrder(): bool
    {
        $ordersToCheck = [];

        // Для возможности перевыдачи проверяется последняя заявка клиента
        $lastOrder = $this->orders->get_last_order((int)$this->user->id);

        if (empty($lastOrder)) {
            return false;
        }

        $ordersToCheck[] = $lastOrder;

        // Если последняя заявка кросс-ордер, то проверяем также по его основной заявке
        if ($this->orders->isCrossOrder($lastOrder)) {
            $mainOrder = $this->orders->get_order($lastOrder->utm_medium);

            if (!empty($mainOrder)) {
                $ordersToCheck[] = $mainOrder;
            }
        }

        foreach ($ordersToCheck as $order) {
            if ($this->orders->canRepeatIssuanceNotIssuedOrder($order)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Обработка новогодней акции
     *
     * @param object $all_orders
     * @param object|null $divide_order
     * @return object|null
     */
    private function getNewYearPromo($order): ?object
    {
        // Проверяем, включена ли новогодняя акция в настройках
        if (empty((int)$this->settings->newyear_promotion_enabled)) {
            logger('newyear_promo_check')->warning('Новогодняя акция отключена в настройках');
            return null;
        }

        if ((empty($order->order->order_id) && empty($order->order->id)) || empty($order->balance)) {
            logger('newyear_promo_check')->warning('Не полные данные заявки/пользователя для Новогодней акции', [
                'user_id' => $this->user->id ?? null,
                'order' => $order,
            ]);
            return null;
        }

        if (!$this->user_data->read($this->user->id, $this->user_data::TEST_USER)) {
            return null;
        }

        $userId = $this->user->id ?? null;
        $orderId = $order->order->order_id ?? $order->order->id ?? null;

        if (empty($userId) || empty($orderId)) {
            logger('newyear_promo_check')->warning('Не полные данные заявки/пользователя для Новогодней акции', [
                'user_id' => $userId,
                'order_id' => $orderId,
            ]);
            return null;
        }

        // Проверяем, участвует ли пользователь в акции (единственный источник истины - баланс из 1С)
        logger('newyear_promo_check')->info('Проверка isUserInPromo', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'balance_exists' => !empty($order->balance),
            'discount_date' => $order->balance->discount_date ?? null,
            'sum_with_grace' => $order->balance->sum_with_grace ?? null,
            'ostatok_od' => $order->balance->ostatok_od ?? null,
            'ostatok_percents' => $order->balance->ostatok_percents ?? null,
        ]);

        if (!$this->promoService->isUserInPromo($userId, $orderId, $order->balance)) {
            logger('newyear_promo_check')->warning('Пользователь не участвует в Новогодней акции', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_date' => $order->balance->discount_date ?? null,
                'sum_with_grace' => $order->balance->sum_with_grace ?? null,
                'ostatok_od' => $order->balance->ostatok_od ?? null,
                'ostatok_percents' => $order->balance->ostatok_percents ?? null,
            ]);
            return null;
        }

        // Проверяем, активна ли скидка (использует discount_date из 1С как единственный источник истины)
        $remainingTime = $this->promoService->getRemainingTime($order->balance);
        $isDiscountActive = $this->promoService->isDiscountActive($order->balance);

        logger('newyear_promo_check')->info('Проверка isDiscountActive', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'discount_date' => $order->balance->discount_date ?? null,
            'remaining_time' => $remainingTime,
            'is_discount_active' => $isDiscountActive,
        ]);

        // Если время акции недействительно - не показываем баннер вообще
        if (!$isDiscountActive) {
            logger('newyear_promo_check')->info('Новогодняя акция не активна для пользователя', [
                'user_id' => $userId,
                'order_id' => $orderId,
                'discount_date' => $order->balance->discount_date ?? null,
                'remaining_time' => $remainingTime,
            ]);
            return null;
        }

        // Проверяем, была ли активирована скидка (для отображения состояния баннера)
        $discountActivated = $this->promoService->hasEvent($userId, $orderId, $this->promoService::EVENT_DISCOUNT_BUTTON_CLICKED);

        // Рассчитываем скидку и сумму со скидкой
        // Единственный источник истины - баланс из 1С
        $totalDebt = (float)($order->balance->ostatok_od ?? 0)
            + (float)($order->balance->ostatok_percents ?? 0)
            + (float)($order->balance->ostatok_peni ?? 0)
            + (float)($order->balance->penalty ?? 0);

        // Получаем скидку из баланса (sum_percent_with_grace)
        $discountAmount = $this->promoService->getDiscountAmount($order->balance);

        // Получаем сумму со скидкой из баланса (sum_with_grace)
        $discountCalculation = $this->promoService->calculateTotalWithDiscount($order->balance);
        $totalWithDiscount = $discountCalculation['total_with_discount'];

        // Получаем оставшееся время (использует discount_date из 1С, если есть, иначе возвращает 0)
        $remainingTime = $this->promoService->getRemainingTime($order->balance);

        // Получаем время активации скидки
        $discountStartedAt = null;
        $activationEvent = $this->promoService->getLastEvent($userId, $orderId, $this->promoService::EVENT_DISCOUNT_BUTTON_CLICKED);
        $discountStartedAt = $activationEvent ? $activationEvent->created_at : null;

        // Определяем ссылку на PDF файл в зависимости от организации
        $pdfUrl = null;
        $organizationId = (int)($order->order->organization_id ?? 0);

        switch ($organizationId) {
            case $this->organizations::AKVARIUS_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_Аквариус_2025.pdf';
                break;
            case $this->organizations::LORD_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_Лорд_2025.pdf';
                break;
            case $this->organizations::RZS_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_РЗС_2025.pdf';
                break;
            case $this->organizations::FINLAB_ID:
                $pdfUrl = '/files/docs/newyear_promo/Условия акции_Финлаб_2025.pdf';
                break;
        }

        logger('newyear_promo_check')->info('Новогодняя акция активна для пользователя', [
            'user_id' => $userId,
            'order_id' => $orderId,
            'is_discount_active' => $isDiscountActive,
            'discount_activated' => $discountActivated,
            'discount_amount' => $discountAmount,
            'total_debt' => $totalDebt,
            'total_with_discount' => $totalWithDiscount,
            'remaining_time' => $remainingTime,
            'discount_started_at' => $discountStartedAt,
            'pdf_url' => $pdfUrl,
            'discount_date' => $order->balance->discount_date ?? null
        ]);

        // Передаем данные в шаблон
        return (object)[
            'is_active' => $isDiscountActive,
            'discount_amount' => $discountAmount,
            'total_debt' => $totalDebt,
            'total_with_discount' => $totalWithDiscount,
            'remaining_time' => $remainingTime,
            'discount_activated' => $discountActivated,
            'discount_started_at' => $discountStartedAt,
            'pdf_url' => $pdfUrl,
        ];
    }

    /**
     * Логирует вход в ЛК для новогодней акции
     *
     * @return void
     */
    private function logNewYearLkOpen(): void
    {
        try {
            // Получаем все заказы пользователя
            $orders = $this->orders->get_orders([
                'user_id' => $this->user->id,
                'limit' => 2,
            ]);

            foreach ($orders as $order) {
                if ($this->promoService->isUserInPromo($this->user->id, $order->id)) {
                    $this->promoService->logLkOpen($this->user->id, $order->id);
                    break;
                }
            }
        } catch (\Throwable $e) {
            logger('newyear_promo')->error('Error logging LK open', [
                'user_id' => $this->user->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
