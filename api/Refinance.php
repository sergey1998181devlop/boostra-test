<?php

require_once('Simpla.php');

class Refinance extends Simpla
{
    const REFINANCE_INTERVAL = 1;
    const REFINANCE_ORGANIZATION_ID = 13;
    const UTM_SOURCE = 'refinance';
    const SMS_SESSION_KEY = 'refinance_sms_code';

    const LOG_FILE = 'axi_refinance.txt';

    const LOG_DEFAULT_FILE = 'refinance.txt';

    /** Если сумма ОД + проценты + пени + штраф меньше этой сумыы, то рефинанс не выдаём */
    private const REFINANCE_THRESHOLD_AMOUNT = 10_000;

    private $refinance_organization;
    private $refinance_settings;

    private array $purchase_config_data = [];

    public function __construct()
    {
        parent::__construct();

        $this->refinance_settings = $this->settings->refinance_settings;
        $this->refinance_organization = $this->organizations->get_organization(self::REFINANCE_ORGANIZATION_ID);
        $this->purchase_config_data = $this->config->PURCHASE_BY_SECTOR_CARD ?? [];
    }

    public function checkOrganizationRefinanceAvailable($organizationId): bool
    {
        // Т.к. рефинанс доступен сейчас только для тестовых юзеров
        // Фильтр по организациям не делаем, и возращаем true для всех организаций
        // TODO: после тестов вернуть фильтр обратно для актуальных МКК
        return true;
    }

    public function get_refinance($balance, $user, $pay_day = null, $pay_term = null)
    {
        if ($this->refinance_settings['enabled']) {

            if ($balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni + $balance->penalty < 10000) {
                return false;
            }

            if ($this->check_payment_date($balance->payment_date) && $this->checkMiniScoringRef($user->uid)) {
                $params = $this->get_params($balance, $pay_day, $pay_term);

                $contract = $this->contracts->get_contract_by_params([
                    'number' => $balance->zaim_number
                ]);

                if ($order = $this->getRefinanceOrder($user->id)) {
                    if ($order->note) {
                        $order->note = is_string($order->note) ? json_decode($order->note, true) : $order->note;
                    }
                    
                    if (is_array($order->note)) {
                        $params = $order->note;
                    }

                    $params['order'] = (array)$order;
                    $params['show_refinance'] = $this->needToShowRefinance($user->id);
                    $params['documents'] = $this->getRefinanceDocuments($contract ? $contract->number : '');

                    return $params;
                }

                $params['order'] = null;
                $params['show_refinance'] = true;
                $params['documents'] = $this->getRefinanceDocuments($contract ? $contract->number : '');

                return $params;
            }
        }
        
        return false;
    }

    public function checkMiniScoringRef($uid)
    {
        $result = $this->soap->MiniScoringRef($uid);

        if (isset($result['ПрисутствуетВБанкротстве']) && $result['ПрисутствуетВБанкротстве'] == 'Да') {
            return false;
        }

        if (isset($result['ПрисутствуетВМошеничестве']) && $result['ПрисутствуетВМошеничестве'] == 'Да') {
            return false;
        }

        return true;
    }

    public function get_params($balance, $pay_day = null, $pay_term = null)
    {
        // Если pay_day не передан — берем текущий день месяца
        $pay_day = (int) $pay_day ?: date('d');
        $pay_date = $this->get_pay_date($pay_day);

        $payTerm = $pay_term != null ? $pay_term - $this->get_date_diff($pay_date) : 60 - $this->get_date_diff($pay_date);

        $payments = $this->get_payments($balance);
        $everypayment = $this->get_everypayment($payments['refinance_amount'], $payTerm);

        $params = [
            'pay_term' => $pay_term,
            'pay_day' => $pay_day,
            'percent' => $this->refinance_settings['percent'],
            'pay_count' => $this->refinance_settings['pay_count'],
            'pay_period' => $this->refinance_settings['pay_period'],
            'first_pay' => $payments['first_pay'],
            'first_pay_percent' => $this->refinance_settings['first_pay'] / 100,
            'refinance_amount' => $payments['refinance_amount'],
            'everypayment' => $everypayment,
            'organization_id' => $this->organizations::RZS_ID,
        ];

        return $params;
    }

    private function get_pay_date(int $pay_day)
    {
        $today = new \DateTime();
        $currentDay = (int)$today->format('d');
        $currentMonth = (int)$today->format('m');
        $currentYear = (int)$today->format('Y');

        if ($pay_day >= $currentDay) {
            // Дата оплаты в этом месяце
            $pay_date = (new DateTime())
                ->setDate($currentYear, $currentMonth, $pay_day)
                ->setTime(0, 0, 0)
                ->format('Y-m-d');
        } else {
            // Дата оплаты в следующем месяце
            $nextMonth = $currentMonth == 12 ? 1 : $currentMonth + 1;
            $nextYear = $currentMonth == 12 ? $currentYear + 1 : $currentYear;
            $nextDate = (new DateTime())
                ->setDate($nextYear, $nextMonth, 1)
                ->setTime(0, 0, 0);
            // Проверка на корректность дня (например, 31 февраля)
            $daysInNextMonth = (int)$nextDate->format('t');
            $pay_day_next = min($pay_day, $daysInNextMonth);

            $pay_date = (new DateTime())
                ->setDate($nextYear, $nextMonth, $pay_day_next)
                ->setTime(0, 0, 0)
                ->format('Y-m-d');
        }

        return $pay_date;
    }

    private function get_date_diff(string $date): int
    {
        $today = new DateTime();
        $pay_date_obj = new DateTime($date);
        $interval = $today->diff($pay_date_obj);

        return (int)$interval->format('%a');
    }
    
    private function get_everypayment($amount, $pay_term = null)
    {
        $period_percent = (float)$this->refinance_settings['percent'] / 100 * (int)($this->refinance_settings['pay_period']);
        $payments_count = ceil($pay_term / (int)($this->refinance_settings['pay_period']));
        $coef = pow(1 + $period_percent, $payments_count) / (pow(1 + $period_percent, $payments_count) - 1);

        return ceil($amount * $period_percent * $coef);
    }
    
    private function get_payments($balance)
    {
        $total_debt = $balance->ostatok_od + $balance->ostatok_percents + $balance->ostatok_peni + $balance->penalty;
        $refinance_amount = round(round(($total_debt - ($total_debt * $this->refinance_settings['first_pay'] / 100)) / 100) * 100);
        $first_pay = round($total_debt - $refinance_amount);

        return compact('first_pay', 'refinance_amount');
    }
    
    private function check_payment_date($payment_date)
    {
        $payment_date = date_create(date('Y-m-d', strtotime($payment_date)));
        $today_date = date_create(date('Y-m-d'));
        $interval = date_diff($payment_date, $today_date);

        return $today_date > $payment_date && $interval->format('%a') > $this->refinance_settings['days_overdue'];
    }
    
    public function create($payment)
    {
        $order = $this->orders->get_order($payment->order_id);
        if (empty($order)) {
            $this->logging(__METHOD__, 'Order not found', ['order_id' => $payment->order_id], ['error' => 'Order not found'], self::LOG_DEFAULT_FILE);
            return false;
        }
        if ($order && empty($order->webmaster_id)) {
            $this->logging(
                __METHOD__,
                'Old order can not be found',
                ['order_id' => $payment->order_id],
                ['error' => 'Old order can not be found'],
                self::LOG_DEFAULT_FILE
            );
            return false;
        }

        if (empty($order->complete) || empty($order->confirm_date)) {
            $this->orders->update_order($order->id, [
                'complete' => 1,
                'confirm_date' => date('Y-m-d H:i:s'),
                'status' => 10
            ]);
            $order = $this->orders->get_order($order->id);
        }

        $register_id = 0;
        $operation_id = 0;

        $p2p_credit_id = $this->create_p2pcredit($order);
        $contract_id = $this->create_contract($order);
        $refinanceContract = $this->contracts->get_contract($contract_id);
        $oldContract = $this->contracts->get_contract_by_params(['order_id' => $order->webmaster_id]);

        $this->contracts->make_issuance($contract_id, date('Y-m-d H:i:s'));

        try {
            $purchaseBySectorCardData = array_merge([
                'amount' => round($payment->amount * 100),
                'description' => 'Пополнение счёта по договору ' . ($oldContract ? $oldContract->number : ''),
            ], $this->getPurchaseBySectorCardData($order, $oldContract));

            $additionalData = [
                'number' => ($refinanceContract ? $refinanceContract->number : ''),
                'reference' => $payment->id,
            ];

            $response = $this->best2pay->purchaseBySectorCard($purchaseBySectorCardData, $additionalData);
        } catch (\Exception $e) {
            $this->logging(
                __METHOD__,
                'purchaseBySectorCard',
                ['order_id' => $order->id, 'amount' => $payment->amount],
                ['error' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ]],
                self::LOG_DEFAULT_FILE
            );
            return false;
        }

        $xml = $response ? simplexml_load_string($response) : null;

        $this->logging(
            __METHOD__,
            'create',
            array_merge($purchaseBySectorCardData, $additionalData),
            [$xml],
            self::LOG_DEFAULT_FILE
        );

        if ($xml && (string)$xml->order_state === 'COMPLETED' && (string)$xml->state === 'APPROVED') {
            $register_id = (string)$xml->order_id;
            $operation_id = (string)$xml->id;
        }

        if ($p2p_credit_id && $register_id && $operation_id) {
            $this->best2pay->update_p2pcredit($p2p_credit_id, [
                'operation_id' => $operation_id,
                'register_id' => $register_id,
            ]);
        }

        try {
            $refinanceData = [
                'contract' => $refinanceContract,
                'old_contract' => $oldContract,
                'payment' => $payment,
                'register_id' => $register_id,
                'operation_id' => $operation_id,
            ];

            $refinancingResult = $this->soap->Refinancing($refinanceData);

            if (!empty($refinancingResult['id']) && !empty($refinancingResult['status'])) {
                $this->orders->update_order($order->id, [
                    '1c_id' => $refinancingResult['id'],
                    '1c_status' => $refinancingResult['status'],
                ]);
            }

            $this->logging(
                __METHOD__,
                'refinancing',
                $refinanceData,
                $response,
                self::LOG_DEFAULT_FILE
            );
        } catch (\Throwable $throwable) {
            $this->logging(
                __METHOD__,
                'Refinancing SOAP error',
                ['contract' => (array)$refinanceContract, 'old_contract' => $oldContract],
                ['error' => [
                    'file' => $throwable->getFile(),
                    'line' => $throwable->getLine(),
                    'message' => $throwable->getMessage(),
                ]],
                self::LOG_DEFAULT_FILE
            );

            // TODO: Сделать запись в новую таблицу для ошибок по рефинансу при отправке в 1с
            return false;
        }
    }
    
    public function create_order($old_order, array $params)
    {
        $tech_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);

        $old_contract = $this->contracts->get_contract_by_params(['order_id' => $old_order->id]);
        if (empty($old_contract)) return null;

        $new_order = [];

        $new_order['user_id'] = $old_order->user_id;
        $new_order['b2p'] = $old_order->b2p;
        $new_order['status'] = $this->orders::STATUS_NEW;
        $new_order['ip'] = $_SERVER['REMOTE_ADDR'];
        $new_order['local_time'] = !empty($params['local_time']) ? $params['local_time'] : null;
        $new_order['date'] = date('Y-m-d H:i:s');
        $new_order['amount'] = $params['refinance_amount'];
        // Общий срок займа
        $new_order['period'] = $params['pay_term'];
        $new_order['payment_period'] = $params['pay_period'];
        $new_order['loan_type'] = $this->orders::LOAN_TYPE_IL;
        $new_order['percent'] = $params['percent'];
        $new_order['manager_id'] = $tech_manager->id;
        $new_order['organization_id'] = $params['organization_id'];

        $new_order['card_id'] = !empty($params['card_id']) ? $params['card_id'] : '';
        $new_order['is_user_credit_doctor'] = 0;
        $new_order['autoretry'] = 0;

        $new_order['utm_medium'] = $old_contract->number;
        $new_order['note'] = json_encode($params);
        $new_order['webmaster_id'] = $old_order->id;
        
        $new_order['order_uid'] = exec($this->config->root_dir . 'generic/uidgen');
        $new_order['complete'] = 0;
        $new_order['accept_sms'] = isset($params['accept_sms']) ? $params['accept_sms'] : '';

        $order_id = $this->orders->add_order($new_order);
        if ($order_id) {
            $this->order_data->set($order_id, self::UTM_SOURCE, 1);
        }

        $response = $this->soap->send_repeat_zayavka([
            'amount' => $new_order['amount'], 
            'period' => $new_order['period'], 
            'user_id' => $new_order['user_id'], 
            'b2p' => 1, 
            'order_uid' => $new_order['order_uid'],
            'organization_id' => $new_order['organization_id'],
            'utm_source' => $new_order['utm_source'],
            'utm_medium' => $new_order['utm_medium'],
            'webmaster_id' => $new_order['webmaster_id'],
        ]);

        if (!empty($response->return->id_zayavka)) {
            $this->orders->update_order($order_id, ['1c_id' => $response->return->id_zayavka]);
        }

        return $this->orders->get_order($order_id);
    }
    
    private function create_p2pcredit($order)
    {
        $params = [
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'date' => date('Y-m-d H:i:s'),
            'register_id' => 0,
            'operation_id' => 0,
            'amount' => $order->amount,
            'body' => serialize([
                'refinance' => true,
            ]),
            'response' => serialize([
                'refinance' => true,
            ]),
            'status' => 'APPROVED',
            'complete_date' => date('Y-m-d H:i:s'),
            'sent' => 2
        ];

        return $this->best2pay->add_p2pcredit($params);
    }
    
    private function create_contract($order)
    {
        if ($contract_id = $this->contracts->create_new_contract($order->id)) {
            $this->orders->update_order($order->id, ['contract_id' => $contract_id]);
        }

        return $contract_id;
    }

    public function checkActiveRefinance($uid, $innOrg)
    {
        $result = $this->soap->CheckActiveRefinans($uid, $innOrg);

        if (isset($result['НаличиеАктивногоРефинанса'])) {
            if ($result['НаличиеАктивногоРефинанса'] == 'Да') {
                return true;
            }
        } else {
            return false;
        }

        return false;
    }

    public function getRefinanceOrder($user_id, array $params = [])
    {
        $order_data_filter = [];
        $order_data_filter[] = [
            'key' => self::UTM_SOURCE,
            'value' => 1
        ];

        $order_filter = array_merge([
            'user_id' => $user_id,
            'status' => $this->orders::STATUS_NEW,
        ], $params);

        return $this->orders->get_order_by_params_and_order_data($order_filter, $order_data_filter);
    }

    public function check_sms($code): string
    {
        if (empty($code)) {
            return 'Укажите код из СМС';
        }

        if (empty($_SESSION[self::SMS_SESSION_KEY])) {
            $timeLeft = 0;

            if (!empty($_SESSION['sms_time']) && ($_SESSION['sms_time'] + 30) > time()) {
                $timeLeft = $_SESSION['sms_time'] + 30 - time();
            }

            return 'Код из СМС не найден. Отправьте СМС повторно' . ($timeLeft ? sprintf(' через %d сек.', $timeLeft) : '');
        }

        if (!empty($_SESSION['sms_time']) && time() > ($_SESSION['sms_time'] + 300)) {
            return 'Код просрочен. Отправьте СМС повторно';
        }

        if ($_SESSION[self::SMS_SESSION_KEY] != $code) {
            return 'Неверный код из СМС ' . $_SESSION[self::SMS_SESSION_KEY];
        }

        return '';
    }

    public function send_refinance_sms($user)
    {
        $result = [];

        if (!empty($_SESSION['sms_time']) && ($_SESSION['sms_time'] + 30) > time()) {
            $result['error'] = 'sms_time';
            $result['time_left'] = $_SESSION['sms_time'] + 30 - time();
        } else {
            $code = mt_rand(1000, 9999);
            $_SESSION[self::SMS_SESSION_KEY] = $code;

            if (!empty($this->is_developer) || !empty($this->is_admin)) {
                $result['mode'] = 'developer';
                $result['developer_code'] = $code;
            }

            if (($result['mode'] ?? null) !== 'developer') {
                $sms_text = 'Ваш код для рефинансирования:' . $code;
                $msg = iconv('utf-8', 'cp1251', $sms_text);
                $user_phone = $user->phone_mobile;
                $send_result = $this->notify->send_sms($user_phone, $msg, 'Boostra.ru', 1);
                if (!is_numeric($send_result)) {
                    $this->logging(
                        __METHOD__,
                        "",
                        ['phone' => $user_phone, "msg" => $msg],
                        $send_result,
                        'refinance_send_sms.txt'
                    );
                }
                $result['sms_id'] = $this->sms->add_message([
                    'phone' => $user_phone,
                    'message' => $sms_text,
                    'send_id' => $send_result,
                ]);
            }

            $_SESSION['sms_time'] = time();
            if (empty($_SESSION['sms_time'])) {
                $result['time_left'] = 0;
            } else {
                $result['time_left'] = ($_SESSION['sms_time'] + 30) - time();
            }

            $result['success'] = true;
        }

        return $result;
    }

    public function getRefinanceCurrentContract($order_id, $only_order_contract = false)
    {
        $order = $this->orders->get_order($order_id);

        if (empty($order)) return false;
        if (!$this->getRefinanceOrder($order->user_id, ['id' => $order_id])) return false;
        if ($only_order_contract) {
            return empty($order->contract_id) ? null : $this->contracts->get_contract($order->contract_id);
        }
        if (!empty($order->contract_id)) {
            return $this->contracts->get_contract($order->contract_id);
        }

        return $this->getRefinanceOriginalContract($order_id);
    }

    private function getRefinanceOriginalContract($order_id)
    {
        $order = $this->orders->get_order($order_id);
        if (empty($order)) return false;
        return $this->contracts->get_contract_by_params(['order_id' => $order->webmaster_id]);
    }

    public function scoring_aksi($order)
    {
        $applicationIdTries = 0;
        $scoring_axi_id = null;

        if (empty($order)) {
            return ['success' => false, 'error' => 'Заказ не найден'];
        }

        if ($this->checkBlackList($order)) {
            $this->orders->delete_order($order->id);

            return ['success' => false, 'message' => 'В запросе отказано'];
        }

        if ($this->user_data->read($order->user_id, $this->user_data::TEST_USER)) {
            return ['success' => true];
        }

        // Если скоринг по этой заявке есть - работать с ним (сегодняшний)
        if ($scoring = $this->scorings->get_scoring_by_filter([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'created' => date('Y-m-d'),
            'type' => $this->scorings::TYPE_AXILINK_2,
        ])) {
            $scoring_axi_id = $scoring->id ?? null;
        } else {
            // Добавляем запись о скоринге типа AXILINK_2
            $scoring_axi = [
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'type' => $this->scorings::TYPE_AXILINK_2,
                'status' => $this->scorings::STATUS_NEW
            ];

            if ($scoring_axi_id = $this->scorings->add_scoring($scoring_axi)) {
                $this->run_scoring($scoring_axi_id);
            }
        }

        if (empty($scoring_axi_id)) {
            return ['success' => false, 'error' => 'Не удалось создать запись скоринга'];
        }

        $final_decision = null;
        $reason_key = null;

        try {
            do {
                $scoring = $this->scorings->get_scoring($scoring_axi_id);
                $result = $this->scorings->get_scoring_body($scoring_axi_id);

                if (!$this->scoringCorrectStatus($scoring) || empty($result)) {
                    usleep(500000); // 0.5 секунды ожидания перед следующим запросом
                    $applicationIdTries++;
                    continue;
                }

                $json_result = json_decode($result, true);

                $this->logging(
                    __METHOD__,
                    'AXI response',
                    [
                        'scoring_axi_id' => $scoring_axi_id,
                        'order' => (array)$order,
                    ],
                    $json_result,
                    self::LOG_FILE
                );

                if (!empty($json_result['name'])) {
                    $final_decision = $json_result['name'];

                    if (!empty($json_result['message']) && strpos($json_result['message'], ';') !== false) {
                        $reason_key = explode(";", $json_result['message'], 2)[0];
                    }

                    break;
                }

                usleep(500000); // 0.5 секунды ожидания перед следующим запросом
                $applicationIdTries++;
            } while ($applicationIdTries < 10);
        } catch (\Throwable $th) {
            $this->logging(__METHOD__, 'AXI error', ['scoring_axi_id' => $scoring_axi_id], ['message' => $th->getMessage()], self::LOG_FILE);
            return ['success' => false, 'error' => 'Ошибка при получении данных от AXI'];
        }

        if (empty($final_decision) && empty($reason_key)) {
            return ['success' => false, 'message' => 'Не удалось получить финальное решение или причину отказа от AXI'];
        }

        // Проверяем на наличие самозапрета
        if ($reason_key === 'SSP_SELFDEC') {
            return ['success' => false, 'message' => 'Самозапрет найден'];
        }

        if ($final_decision !== 'Approve') {
            return ['success' => false, 'message' => 'Заявка не одобрена'];
        }

        $scoring_report = array(
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'type' => $this->scorings::TYPE_REPORT,
            'status' => $this->scorings::STATUS_NEW
        );

        $this->scorings->add_scoring($scoring_report);

        return ['success' => true];
    }

    private function run_scoring($scoring_axi_id)
    {
        $this->scorings->update_scoring($scoring_axi_id, [
            'status' => $this->scorings::STATUS_PROCESS,
            'start_date' => date('Y-m-d H:i:s')
        ]);
    }

    private function scoringCorrectStatus(object $scoring)
    {
        if (empty((array) $scoring)) return false;

        return $scoring->status == $this->scorings::STATUS_ERROR ||
        $scoring->status == $this->scorings::STATUS_COMPLETED;
    }

    private function getApplicationId($order, &$applicationIdTries = 0): string
    {
        if (empty($order)) return '';
        if ($applicationIdTries > 10) return '';

        $orderCrm = $this->orders->get_crm_order($order->id);

        try {
            $application_id = 'SSP_' . $applicationIdTries . $order->id;
            $applicationIdTries++;
            $result = $this->axi->getRefinance($orderCrm, $application_id);

            if (empty($result) || (is_string($result) && strpos(strtolower($result), 'error') !== false)) {
                usleep(500000);
                return $this->getApplicationId($order, $applicationIdTries);
            }

            return $result;
        } catch (\Throwable $th) {
            $this->logging(__METHOD__, 'AXI error', ['application_id' => $application_id], $th->getMessage(), self::LOG_FILE);
            return '';
        }
    }

    private function checkBlackList($order): bool
    {
        if (empty($order)) return false;

        $userUid = $this->users->get_user_uid($order->user_id);

        $filters = $this->db->placehold('WHERE b.user_id = ?', $order->user_id);

        $sql = "SELECT b.* 
            FROM __blacklist b
            LEFT JOIN __managers m ON m.id = b.manager_id
            LEFT JOIN __users u ON u.id = b.user_id 
            {$filters}";

        $this->db->query($sql);

        return $this->db->num_rows() > 0 || $this->check1cBlackList($userUid->uid);
    }

    private function check1cBlackList($uid): bool
    {
        $data = $this->soap->generateObject(['ContragentUID' => $uid]);

        $responseData = $this->soap->requestSoap($data, 'WebSignal', 'isContragentInBlacklist');
        if (isset($responseData['response'])) {
            return (bool) $responseData['response'];
        }

        return false;
    }

    private function needToShowRefinance($user_id): bool
    {
        $order = $this->getRefinanceOrder($user_id);
        $contract = $order ? $this->getRefinanceCurrentContract($order->id, true) : null;
        $payment = $order ? $this->best2pay->get_payment_by_params(['order_id' => $order->id, 'user_id' => $user_id]) : null;

        // Если пустая заявка, контракт или платеж - предлагаем рефинанс
        return empty($payment) || empty($contract) || empty($order);
    }

    private function getRefinanceDocuments(string $contractNumber): array
    {
        if (empty($contractNumber)) {
            return [];
        }

        $files = [
            'Application' => 'Заявление на рефинансирование',
            'Contract' => 'Порядок предоставления программы рефинансирования'
        ];

        $fileDirectory = ucfirst(self::UTM_SOURCE) . '/' . $contractNumber;
        $rootDirectory = $this->config->root_dir . 'files/contracts/' . $fileDirectory;

        if (!is_dir($rootDirectory)) {
            mkdir($rootDirectory, 0755, true);
        }

        $filesExistsCount = count(
            array_filter($files, function ($fileName) use ($rootDirectory) {
                return file_exists($rootDirectory . '/' . $fileName . '.pdf');
            })
        );
        $fileUrl = $this->config->root_url . '/files/contracts/';

        if ($filesExistsCount === count($files)) {
            $documents = [];

            foreach ($files as $name) {
                $documents[] = [
                    'link' => $fileUrl . $fileDirectory . '/' . $name . '.pdf',
                    'name' => $name
                ];
            }

            return $documents;
        }

        $response = $this->soap->getRefinancingDocuments($contractNumber);
        $response = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        $documents = [];
        if (!isset($response['Status']) || $response['Status'] !== 'OK' || empty($response['Files'])) {
            return $documents;
        }

        foreach ($response['Files'] as $key => $base64) {
            if (!isset($files[$key])) {
                continue;
            }

            if (empty($base64)) {
                continue;
            }

            $fileName = $files[$key];

            $documents[] = [
                'link' => $fileUrl . $this->documents->save_pdf($base64, $fileName, ucfirst(self::UTM_SOURCE) . '/' . $contractNumber),
                'name' => $fileName
            ];
        }

        return $documents;
    }

    private function getPurchaseBySectorCardData($order, $old_contract): array
    {
        $old_order = $this->orders->get_order($order->id);
        $order_crm = $this->orders->get_crm_order($order->webmaster_id);

        if (empty($order_crm)) {
            $this->logging(__METHOD__, 'CRM order not found', ['order_id' => $order->webmaster_id], ['error' => 'CRM order not found'], self::LOG_DEFAULT_FILE);
            return [];
        }

        $balance = $this->users->get_user_balance_1c_normalized($old_order->user_id, function ($balance) use ($old_order) {
            return $balance['Заявка'] == $old_order->id_1c;
        });

        if (empty($balance)) {
            $this->logging(__METHOD__, 'Balance not found', ['user_id' => $old_order->user_id, 'order_id' => $old_order->id], ['error' => 'Balance not found'], self::LOG_DEFAULT_FILE);
            return [];
        }

        $address = [];
        if (!empty($order_crm->Regregion)) {
            $address[] = $order_crm->Regregion.', ';
        }

        if (!empty($order_crm->Regregion_shorttype)) {
            $address[] = $order_crm->Regregion_shorttype;
        }

        if (!empty($order_crm->Regcity_shorttype)) {
            $address[] = $order_crm->Regcity_shorttype;
        }

        if (!empty($order_crm->Regcity)) {
            $address[] = $order_crm->Regcity.', ';
        }

        if (!empty($order_crm->Regstreet_shorttype)) {
            $address[] = $order_crm->Regstreet_shorttype;
        }

        if (!empty($order_crm->Regstreet)) {
            $address[] = $order_crm->Regstreet.', ';
        }

        if (!empty($order_crm->Reghousing)) {
            $address[] = 'д. '. $order_crm->Reghousing;
        }

        if (!empty($order_crm->Regbuilding)) {
            $address[] = 'стр. '. $order_crm->Regbuilding;
        }

        if (!empty($order_crm->Regroom)) {
            $address[] = 'кв. '. $order_crm->Regroom;
        }

        $result = [];
        $result['P008-1'] = $balance->client;
        $result['fio'] = $balance->client;

        if ($old_contract) {
            if ($old_contract->amount > 15000) {
                $result['P008-2'] = implode(' ', $address); // Адрес плательщика (если сумма больше 15000 рублей) (адрес клиента)
            }

            $result['P024'] = 'Пополнение счёта по договору '.$old_contract->number;
        }

        if (!empty($this->purchase_config_data[$order->organization_id])) {
            $purchase_pay_data = $this->purchase_config_data[$order->organization_id];

            $result = array_merge($result, $purchase_pay_data);
        }

        return $result;
    }
}