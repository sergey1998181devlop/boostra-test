<?php

require_once( __DIR__ . '/../vendor/autoload.php');
require_once 'Simpla.php';

/**
 * Class UserCreditDoctor
 */
class UserCreditDoctor extends Simpla
{
    const SMS_SESSION_KEY = 'user_credit_doctor_send';
    const SMS_DELAY = 30;
    /**
     * Услуги
     */
    public const ORDER_ITEMS = [
        1 => [
            'price' => 799,
            'description' => 'Избавиться от долгов самостоятельно по индивидуальной стратегии',
            'url' => 'https://t.me/+4Hkucybb73Q5NDJi',
        ],
        2 => [
            'price' => 5900,
            'description' => 'Избавиться от долгов вместе с группой единомышленников, успешно прошедших этот путь',
            'url' => 'https://t.me/+1M7A12xeyw80MDIy',
        ],
        3 => [
            'price' => 14900,
            'description' => 'Надежно пройти эту сложную ситуацию вместе с персональным экспертом',
            'url' => 'https://t.me/+-UiO4CUHjkE0ZWVi',
        ],
    ];
    /**
     * Получает запись о прохождение КВИЗА
     * @param int $user_id
     * @return false|int
     */
    public function getFormCreditDoctorByUserId(int $user_id)
    {
        $query = $this->db->placehold("SELECT * FROM __credit_doctor_form WHERE user_id = ?", $user_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавляем запись о прохождении квиза Кредитного доктора
     * @param array $data
     * @return mixed
     */
    public function addFormCreditDoctor(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __credit_doctor_form SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет запись о прохождении квиза Кредитного доктора
     * @param int $user_id
     * @param array $data
     * @return mixed
     */
    public function updateFormCreditDoctor(int $user_id, array $data)
    {
        $query = $this->db->placehold("UPDATE __credit_doctor_form SET ?% WHERE user_id = ?", $data, $user_id);
        return $this->db->query($query);
    }

    /**
     * Проверяет наличие записи о прохождении квиза Кредитного доктора для пользователя
     * @param int $user_id
     * @return false|int
     */
    public function hasFormByUserId(int $user_id)
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM __credit_doctor_form WHERE user_id = ?) as r", $user_id);
        $this->db->query($query);
        return $this->db->result('r');
    }

    /**
     * @param string $email
     * @param int $user_id
     * @return false|int
     */
    public function hasUserEmail(string $email, int $user_id)
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM __credit_doctor_form WHERE user_id !=  AND email = ?) as r", $user_id, $email);
        $this->db->query($query);
        return $this->db->result('r');
    }

    /**
     * Отправка SmS
     * @param $user
     * @return array
     */
    public function send_sms($user)
    {
        $result = [];

        if (!empty($_SESSION['sms_time']) && ($_SESSION['sms_time'] + self::SMS_DELAY) > time()) {
            $result['error'] = 'sms_time';
            $result['time_left'] = $_SESSION['sms_time'] + self::SMS_DELAY - time();
        } else {

            $code = mt_rand(1000, 9999);
            $_SESSION[self::SMS_SESSION_KEY] = $code;

            if (!empty($this->is_developer) || !empty($this->is_admin)) {
                $result['mode'] = 'developer';
                $result['developer_code'] = $code;
            }

            if (($result['mode'] ?? null) !== 'developer') {
                $sms_text = 'Ваш код для подписания услуг с ООО "Алфавит":' . $code;
                $msg = iconv('utf-8', 'cp1251', $sms_text);
                $user_phone = $user->phone_mobile;
                $send_result = $this->notify->send_sms($user_phone, $msg, 'Boostra.ru', 1);
                if (!is_numeric($send_result)) {
                    $this->logging(
                        __METHOD__,
                        "",
                        ['phone' => $user_phone, "msg" => $msg],
                        $send_result,
                        'user_credit_doctor_send.txt'
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
                $result['time_left'] = ($_SESSION['sms_time'] + self::SMS_DELAY) - time();
            }

            $result['success'] = true;
        }

        return $result;
    }

    /**
     * Добавляет информацию о покупке тарифного плана
     * @param array $data
     * @return mixed
     */
    public function addCDPayment(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __cd_payments SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Обновляет информацию о покупке тарифного плана
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateCDPayment(int $id, array $data)
    {
        $query = $this->db->placehold("UPDATE __cd_payments SET ?% WHERE id = ?", $data, $id);
        return $this->db->query($query);
    }

    /**
     * Получает информацию о покупке тарифного плана по payment_id полученному от YooKassa
     * @param string $payment_id
     * @return false|int
     */
    public function getCDPaymentByPaymentId(string $payment_id)
    {
        $query = $this->db->placehold("SELECT * FROM __cd_payments WHERE payment_id = ?", $payment_id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Получает информацию о заказе по id
     * @param string $id
     * @return false|int
     */
    public function getCDPaymentById(string $id)
    {
        $query = $this->db->placehold("SELECT * FROM __cd_payments WHERE id = ?", $id);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Добавляет связку для автоплатежей
     * @param $data
     * @return mixed
     */
    public function addCDSavePayment($data)
    {
        $query = $this->db->placehold("INSERT INTO __cd_save_payments SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Получим список платежей которые ещё не полностью оплачены
     * @return array|false
     */
    public function getEmptyPayments()
    {
        $query = $this->db->placehold("SELECT * FROM s_cd_payments WHERE DATEDIFF(NOW(), date_added) > 30 AND save_payment_method = 1 AND filled < full_amount AND status = ?", \YooKassa\Model\PaymentStatus::SUCCEEDED);
        $this->db->query($query);
        return $this->db->results();
    }

    /**
     * Получает последний платеж по user_id
     * @param int $user_id
     * @return false|int
     */
    public function getLastPaymentByUserId(int $user_id)
    {
        $query = $this->db->placehold("SELECT * FROM s_cd_payments WHERE user_id = ? AND save_payment_method = 1 ORDER BY id DESC LIMIT 1", $user_id);
        $this->db->query($query);
        return $this->db->result();
    }
}
