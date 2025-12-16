<?php

require_once 'Simpla.php';
require_once 'Orders.php';
require_once __DIR__ . '/../app/Services/ReturnExtraService.php';

use App\Services\ReturnExtraService;

class CreditDoctor extends Simpla
{
    const AMOUNT = 9000;
    const PERIOD = 90;
    const FULL_PERCENT = 36.649;
    const ORDER_NOTE = 'КД';
    const URL = 'https://cd.kreditoff-net.ru/';

    /**
     * Статус новой записи о КД
     */
    const CREDIT_DOCTOR_STATUS_NEW = 'NEW';

    /**
     * Статус КД когда отправлен в 1С
     */
    const CREDIT_DOCTOR_STATUS_SEND = 'SEND';

    /**
     * Статус оплаченного КД
     */
    const CREDIT_DOCTOR_STATUS_SUCCESS = 'SUCCESS';

    const RESTRICTION_REASONS = [
        'Недействительный паспорт',
        'Признаки мошенничества в заявке',
        'Нет постоянной прописки'
    ];

    const SMS_SESSION_KEY = 'credit_doctor_sms';

    private ReturnExtraService $service;

    public function __construct()
    {
        parent::__construct();

        $app = \App\Core\Application\Application::getInstance();
        $this->service = $app->make(ReturnExtraService::class);
    }

    public function get_restriction_reason_ids() {
        $reasons = $this->reasons->get_reasons([
            'name' => self::RESTRICTION_REASONS,
        ]);

        return array_map(function($reason) {
            return $reason->id;
        }, $reasons);
    }

    public function is_user_has_opened_doctor($user)
    {
        $query = $this->db->placehold("SELECT COUNT(*) as count 
            FROM __user_balance 
            WHERE user_id = ? AND zaim_summ = 9000 AND percent = 0.1", $user->id);
        $this->db->query($query);
        $count = $this->db->result('count');

        return $count == true;
    }

    public function handle_credit_doctor_form($user, $card_id, $b2p, $local_time, $card_type)
    {
        $order = [
            'user_id' => $user->id,
            'card_id' => $card_id,
            'card_type' => $card_type,
            'amount' => self::AMOUNT,
            'period' => self::PERIOD,
            'percent' =>  round(self::FULL_PERCENT / 365, 3),
            'b2p' => $b2p,
            'status' => Orders::STATUS_FILLING,

            'first_loan' => 0,
            'date' => date('Y-m-d H:i:s'),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'comment' => (string)$card_id,
            'local_time' => $local_time,
            'is_credit_doctor' => 1,

            'utm_source' => 'Boostra',
            'utm_medium' => 'Site',
            'utm_campaign' => 'C1_main',
            'utm_content' => '',
            'utm_term' => '',
            'webmaster_id' => '',
            'click_hash' => '',
        ];

        $order_id = $this->orders->add_order($order);

        if (!$this->is_developer) {
            $user_phone = $user->phone_mobile;
            $this->amo_crm->send_lead("{$user->lastname} {$user->firstname} {$user->patronymic}", $user_phone);
        }

        $code = mt_rand(1000, 9999);
        $_SESSION[self::SMS_SESSION_KEY] = $code;

        $sms_text = 'Ваш код для подписания договора на "Кредитныи доктор": ' . $code;
        if ($this->is_developer)
        {
            error_log(__FILE__.':'.__LINE__.': '.var_export($sms_text, true));
            return $order_id;
        }

        $msg = iconv('utf-8', 'cp1251', $sms_text);

        $send_result = $this->notify->send_sms($user_phone, $msg, 'Boostra.ru', 1);
        if (!is_numeric($send_result))
        {
            error_log(__FILE__.':'.__LINE__.': '.var_export($sms_text, true));
            return $order_id;
        }

        $this->send_sms($sms_text, $user_phone);

        return $order_id;
    }

    public function download_individual_contract_pdf($user)
    {
        $contract_params = $this->documents->get_document_params();
        $tpl_name = $contract_params[Documents::CONTRACT_CREDIT_DOCTOR][Documents::KEY_TEMPLATE];

        foreach ($this->get_contract_document_params($user) as $param => $value)
        {
            $this->design->assign($param, $value);
        }
        $tpl = $this->design->fetch('pdf/' . $tpl_name);

        $this->pdf->create(
            $tpl,
            $contract_params[Documents::CONTRACT_CREDIT_DOCTOR][Documents::KEY_NAME],
            $contract_params[Documents::CONTRACT_CREDIT_DOCTOR][Documents::KEY_TEMPLATE]
        );
    }

    public function save_individual_contract($user, $order_id)
    {
        $sms_code = $_SESSION[self::SMS_SESSION_KEY];

        $this->save_contract_document($user, $order_id, $sms_code);

        $this->orders->update_order($order_id, [
            'accept_sms' => $sms_code,
            'accept_date' => date('Y-m-d H:i:s'),
        ]);

        $this->amo_crm->send_lead("{$user->lastname} {$user->firstname} {$user->patronymic}", $user->phone_mobile, true);

        $order = $this->orders->get_order(intval($order_id));
        $this->send_order_soap($order);
        $this->soap->send_credit_doctor_order($order, $sms_code);
        $this->send_receipt($user, $order);

        $sms_text = "Вы оформили целевой займ на оплату сервиса «Кредитный доктор»! Дальнейшая регистрация в сервисе по ссылке https://t.me/creditdoctor_bot";
        $this->send_sms($sms_text, $user->phone_mobile);
    }

    private function save_contract_document($user, $order_id, $sms_code)
    {
        $this->documents->create_document([
            'user_id' => $user->id,
            'order_id' => $order_id,
            'type' => Documents::CONTRACT_CREDIT_DOCTOR,
            'params' => $this->get_contract_document_params($user, $sms_code),
        ]);
    }

    private function get_contract_document_params($user, $sms_code = '')
    {
        $formatter = new IntlDateFormatter('ru_RU', IntlDateFormatter::LONG, IntlDateFormatter::NONE);

        $loan_first_payment = (new DateTime("+30 days now"))->format("d.m.Y");
        $loan_second_payment = (new DateTime("+60 days now"))->format("d.m.Y");
        $loan_last_day = (new DateTime("+90 days now"))->format("d.m.Y");
        $short_name = $user->lastname . ' ' . mb_substr($user->firstname, 0, 1) . '. ' . mb_substr($user->patronymic, 0, 1) . '.';
        $current_date_day_quoted = preg_replace("/(\d+)(\s.*)/", "«$1»$2", $formatter->format(new DateTime()));

        return [
            'number' => 0,
            'city' => $user->Regcity_shorttype . '. ' . trim(str_replace($user->Regcity_shorttype, '', $user->Regcity)),
            'current_date' => $current_date_day_quoted,
            'full_name' => "{$user->lastname} {$user->firstname} {$user->patronymic}",
            'short_name' => $short_name,
            'birth' => $user->birth,
            'passport_serial' => $user->passport_serial,
            'registration_address' => $user->registration_address,
            'loan_last_day' => $loan_last_day,
            'loan_first_payment' => $loan_first_payment,
            'loan_second_payment' => $loan_second_payment,
            'sms_code' => $sms_code,
        ];
    }

    private function send_order_soap($order)
    {
        $odin_s_order_result = $this->soap->send_repeat_zayavka(
            $order->amount,
            $order->period,
            $order->user_id,
            $order->card_id,
            $order->b2p
        );

        if (empty($odin_s_order_result->return->id_zayavka))
        {
            $order->status = Orders::STATUS_REJECTED;
            $order->note = strval($odin_s_order_result->return->Error);

            $new_order_fields = ['status' => $order->status, 'note' => $order->note];
        }
        else
        {
            $order->status = Orders::STATUS_APPROVED;
            $order->id_1c = $odin_s_order_result->return->id_zayavka;

            $new_order_fields = ['status' => $order->status, '1c_id' => $order->id_1c, 'sent_1c' => 1];
        }

        $this->orders->update_order($order->id, $new_order_fields);
    }

    private function send_receipt($user, $order) {
        $service = new stdClass();
        $service->Sum = 900000;
        $service->Service = 'Подписка на сервис "Кредитный доктор"';

        $input_data = new stdClass();
        $input_data->UID = $user->uid;
        $input_data->Services = [$service];
        $input_data->FIO = "{$user->lastname} {$user->firstname} {$user->patronymic}";
        $input_data->Zaim = $order->id;

        $passport_serial = str_replace(' ', '', $user->passport_serial);
        $serial = substr($passport_serial, 0, 4);
        $number = substr($passport_serial, 4, 6);
        $input_data->PassportSer = $serial;
        $input_data->PassportNum = $number;

        $this->cloudkassir->send_receipt_lagutkin($input_data, 'al');
    }

    private function send_sms($sms_text, $user_phone)
    {
        $msg = iconv('utf-8', 'cp1251', $sms_text);

        $send_result = $this->notify->send_sms($user_phone, $msg, 'Boostra.ru', 1);
        if (!is_numeric($send_result))
        {
            $this->logging(
                __METHOD__, "", ['phone' => $user_phone, "msg" => $msg], $send_result, 'credit_doctor_sms.txt'
            );
        }

        $this->sms->add_message([
            'phone' => $user_phone,
            'message' => $sms_text,
            'send_id' => $send_result,
        ]);
    }

    /**
     * Метод для конвертации суммы КД из числового формата в буквенный
     * @param $number
     * @return string
     */
    public function numberToWords($number): string
    {
        $numberWords = [
            300 => "триста",
            550 => "пятьсот пятьдесят",
            1150 => "одна тысяча сто пятьдесят",
            1200 => "одна тысяча двести",
            2350 => "две тысячи триста пятьдесят",
            2400 => "две тысячи четыреста",
            2450 => "две тысячи четыреста пятьдесят",
            2790 => "две тысячи семьсот девяносто",
            3350 => "три тысячи триста пятьдесят",
            3400 => "три тысячи четыреста",
            3450 => "три тысячи четыреста пятьдесят",
            3790 => "три тысячи семьсот девяносто",
            4250 => "четыре тысячи двести пятьдесят",
            4300 => "четыре тысячи триста",
            4350 => "четыре тысячи триста пятьдесят",
            4790 => "четыре тысячи семьсот девяносто",
            4950 => "четыре тысячи девятьсот пятьдесят",
            5000 => "пять тысяч",
            5050 => "пять тысяч пятьдесят",
            6150 => "шесть тысяч сто пятьдесят",
            6200 => "шесть тысяч двести",
            6250 => "шесть тысяч двести пятьдесят",
            6300 => "шесть тысяч триста",
        ];

        return $numberWords[$number] ?? (string)$number;
    }

    /**
     * Получает стоимость сервиса
     * @param int $amount
     * @param bool $is_new_client
     * @param int|null $user_id
     * @return object|null
     * @throws Exception
     */
    public function getCreditDoctor(int $amount, bool $is_new_client = true, int $user_id = null): ?object
    {
        return $this->service->getServicePrice($amount, $is_new_client, $user_id);
    }

    /**
     * Проверяет доступность сервиса для пользователя
     */
    public function isVisible($userId): array
    {
        return $this->service->checkVisibility($userId);
    }

    /**
     * CreditDoctor::getCreditDoctorIdByPenaltyPrice()
     * Получает id КД по размеру ШтрафногоКД
     * @param int $penalty_price
     * @return int credit_doctor_condition_id
     */
    public function getCreditDoctorIdByPenaltyPrice(int $penalty_price)
    {
        $query = $this->db->placehold('SELECT id FROM __credit_doctor_conditions WHERE penalty_price = ?',
            $penalty_price);
        $this->db->query($query);
        return $this->db->result('id');
    }

    /**
     * Get count of credit doctor for user
     * @param int $userId
     * @return int
     */
    protected function getCountCreditDoctor(int $userId): int
    {
        $query = $this->db->placehold("SELECT COUNT(o.user_id) cnt FROM __credit_doctor_to_user
            WHERE user_id = ? AND `status` = ? GROUP BY user_id", $userId, self::CREDIT_DOCTOR_STATUS_SUCCESS);
        $this->db->query($query);
        return $this->db->result('cnt');
    }

    /**
     * Get url for credit secret page
     * @param int $userId
     * @param int $creditDoctorId
     * @return string
     */
    public function getUrl(int $userId, int $creditDoctorId): string
    {
        $countDoctor = $this->getCountCreditDoctor($userId) + 1;
        return self::URL . rtrim(base64_encode(base64_encode($userId . '|' .$creditDoctorId . '|' . $countDoctor)), '=');
    }

    /**
     * Get Lessons for Api Credit Doctor
     * @param int $userId
     * @return array
     */
    public function getApiData(int $userId): array
    {
        $selectedUserId = 1633118;
        $selectedLevelForSelectedUser = 2;
        $main = $this->db->placehold("INNER JOIN __credit_doctor_condition_to_lessons cl ON FIND_IN_SET(cl.condition_id,a.condition_ids)");
        $custom = $this->db->placehold("INNER JOIN __credit_doctor_condition_to_lessons cl ON FIND_IN_SET(cl.condition_id, '6,10')");

        $selected = $userId === $selectedUserId ? $custom : $main;
        
        $res = [];
        $query = $this->db->placehold("SELECT
                DISTINCT l.id,
                l.ordering,
                ll.ordering AS level_ordering,
                l.level_id,
                l.title,
                l.description,
                l.url,
                l.cover,
                l.type,
                ll.title AS `level` 
                FROM (
                    SELECT 
                        (IF(user_id = ?, ?, SUM(`cnt`))) as level,
                        GROUP_CONCAT(credit_doctor_condition_id) `condition_ids` 
                    FROM (
                        SELECT 
                            user_id,
                            COUNT(user_id) as cnt, 
                            credit_doctor_condition_id 
                        FROM 
                            __credit_doctor_to_user 
                        WHERE user_id = ? AND `status` = ? 
                        GROUP BY credit_doctor_condition_id,user_id
                    ) a
                ) a
                $selected
                INNER JOIN __credit_doctor_lessons l ON l.id = cl.lesson_id AND l.level_id <= a.level
                INNER JOIN __credit_doctor_levels ll ON ll.id = l.level_id
                ORDER BY level_ordering, ordering",
            $selectedUserId,
            $selectedLevelForSelectedUser,
            $userId,
            self::CREDIT_DOCTOR_STATUS_SUCCESS,
        );
        $this->db->query($query);
        $data = $this->db->results();
        if ($data) {
            foreach ($data as $val) {
                if (!isset($res[$val->level_id])) {
                    $res[$val->level_id] = [];
                }
                $res[$val->level_id][] = $val;
            }
            $res = array_values($res);
        }
        return $res;
    }

    /**
     * Get all levels CreditDoctor
     * @return array
     */
    public function getLevels(): array
    {
        $query = $this->db->placehold('SELECT title FROM __credit_doctor_levels ORDER BY ordering');
        $this->db->query($query);
        return $this->db->results('title');
    }

    /**
     * Добавляет информацию о КД к пользователю
     * @param array $data
     * @return mixed
     */
    public function addUserCreditDoctorData(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __credit_doctor_to_user SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет информацию о КД пользователя
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updateUserCreditDoctorData(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE __credit_doctor_to_user SET ?% WHERE id = ?", $data, $id);
        $this->db->query($query);
    }

    /**
     * Получает запись о купленном КД по заявке
     * @param int $order_id
     * @param string $status
     * @return false|int
     */
    public function getSuccessUserCreditDoctor(int $userId)
    {
        $query = $this->db->placehold(
            'SELECT * 
         FROM __credit_doctor_to_user 
         WHERE user_id = ? 
           AND status = ? 
           AND (amount_total_returned IS NULL OR amount_total_returned < amount)',
            $userId,
            self::CREDIT_DOCTOR_STATUS_SUCCESS
        );
        $this->db->query($query);
        
        return $this->db->result();
    }

    /**
     * Удаляет запись о КД
     * @param int $id
     * @return void
     */
    public function deleteUserCreditDoctor(int $id)
    {
        $query = $this->db->placehold("DELETE FROM __credit_doctor_to_user WHERE id = ?", $id);
        $this->db->query($query);
    }

    /**
     * Get All Lessons for Api Credit Doctor
     * @return array
     */
    public function getAllLessons(): array
    {
        $res = [];
        
        $query = $this->db->placehold(
            "SELECT  l.id,
                    l.ordering,
                    ll.ordering AS level_ordering,
                    l.level_id,
                    l.title,
                    l.description,
                    l.url,
                    l.cover,
                    l.type,
                    ll.title    AS `level`
                FROM s_credit_doctor_lessons l
            LEFT JOIN s_credit_doctor_levels ll ON ll.id = l.level_id
            ORDER BY level_ordering, ordering;"
        );
        $this->db->query($query);
        $data = $this->db->results();
        if ($data) {
            foreach ($data as $val) {
                if (!isset($res[$val->level_id])) {
                    $res[$val->level_id] = [];
                }
                $res[$val->level_id][] = $val;
            }
            $res = array_values($res);
        }
        return $res;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getAllSuccessCreditDoctorRecordsByUserId(int $userId): array
    {
        $query = $this->db->placehold(
            'SELECT cdtu.* 
         FROM __credit_doctor_to_user AS cdtu
         LEFT JOIN s_finansdoctor_license_keys AS fdlk 
         ON cdtu.order_id = fdlk.order_id
         WHERE cdtu.user_id = ? 
         AND cdtu.status = ? 
         AND cdtu.date_added >= ? 
         AND fdlk.order_id IS NULL
         ORDER BY cdtu.date_added DESC',
            $userId,
            self::CREDIT_DOCTOR_STATUS_SUCCESS,
            '2024-09-01 00:00:00'
        );
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getAllCreditDoctorRecordsWithReturnsByUserId(int $userId): array
    {
        $query = $this->db->placehold(
            'SELECT cdtu.*, 
                IFNULL(cdtu.amount_total_returned, 0) AS amount_total_returned 
         FROM __credit_doctor_to_user AS cdtu
         WHERE cdtu.user_id = ? 
         AND cdtu.status = ?',
            $userId,
            self::CREDIT_DOCTOR_STATUS_SUCCESS
        );
        $this->db->query($query);

        return $this->db->results();
    }

    /**
     * @param $user_id
     * @param bool $active
     * @return array|false
     */
    public function getLicensesByUserId($user_id, bool $active = true)
    {
        $query = $this->db->placehold(
            "SELECT * FROM s_finansdoctor_license_keys WHERE user_id = ? and active = ? ORDER BY created_at DESC",
            $user_id,
            (int)$active
        );
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * @param $user_id
     * @return false|int
     */
    public function getLicenseByUserId($user_id)
    {
        $query = $this->db->placehold(
            "SELECT * FROM s_finansdoctor_license_keys WHERE user_id = ? AND active = 1",
            $user_id
        );
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @param $order_id
     * @return false|int
     */
    public function getLicenseByOrderId($order_id)
    {
        $query = $this->db->placehold(
            "SELECT * FROM s_finansdoctor_license_keys WHERE order_id = ? AND active = 1",
            $order_id
        );
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * @param $licenseData
     * @return mixed
     */
    public function saveLicense($licenseData)
    {
        $query = $this->db->placehold("INSERT INTO s_finansdoctor_license_keys SET ?%", $licenseData);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * @param $licenseId
     * @param $licenseData
     * @return mixed
     */
    public function updateLicenseByLicenseId($licenseId, $licenseData)
    {
        $query = $this->db->placehold("UPDATE s_finansdoctor_license_keys SET ?% WHERE id = ?", $licenseData, $licenseId);
        $this->db->query($query);
        return $this->db->affected_rows();
    }

    public function getTariffs()
    {
        $query = $this->db->placehold('SELECT price,max(license_key_days) as license_key_days FROM __credit_doctor_conditions where price_group is null group by price ORDER BY price');
        $this->db->query($query);
        return $this->db->results();
    }

}
