<?php

require_once 'Simpla.php';
require_once 'Orders.php';

class CreditRating extends Simpla
{
    const SMS_SESSION_KEY = 'credit_rating_sms';
    const PAYMENT_SALT = 'ail6Eis3';
    const SMS_DELAY = 30;

    public function send_credit_rating_sms($user)
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
                $sms_text = 'Ваш код для подписания Заявления на "Кредитный рейтинг": ' . $code;
                $msg = iconv('utf-8', 'cp1251', $sms_text);
                $user_phone = $user->phone_mobile;
                $send_result = $this->notify->send_sms($user_phone, $msg, 'Boostra.ru', 1);
                if (!is_numeric($send_result)) {
                    $this->logging(
                        __METHOD__,
                        "",
                        ['phone' => $user_phone, "msg" => $msg],
                        $send_result,
                        'credit_rating_sms.txt'
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

    public function download_individual_contract_pdf($user)
    {
        $contract_params = $this->documents->get_document_params();
        $tpl_name = $contract_params[Documents::CREDIT_RATING_APPLICATION][Documents::KEY_TEMPLATE];

        foreach ($this->get_contract_document_params($user) as $param => $value)
        {
            $this->design->assign($param, $value);
        }

        $tpl = $this->design->fetch('pdf/' . $tpl_name);

        $this->pdf->create(
            $tpl,
            $contract_params[Documents::CREDIT_RATING_APPLICATION][Documents::KEY_NAME],
            $contract_params[Documents::CREDIT_RATING_APPLICATION][Documents::KEY_TEMPLATE]
        );
    }


    public static function get_payment_hash($sms)
    {
        return md5($sms . CreditRating::PAYMENT_SALT);
    }

    public function handle_credit_rating_paid($user, $transaction_id, $transaction_code_sms = false)
    {
        $created_rating_id = $this->created_credit_rating($user->id, $transaction_id);
        if (!empty($created_rating_id))
        {
            return $created_rating_id;
        }

        $scorista_score = $this->scorings->get_last_scorista_for_user($user->id, true);

        $sms_code = $transaction_code_sms ?: $_SESSION[self::SMS_SESSION_KEY];

        $params = [
            'asp' => $sms_code,
            'transaction_id' => $transaction_id,
        ];

        if (isset($scorista_score->scorista_ball)) {
            $params['score'] = $scorista_score->scorista_ball;
        }

        $rating_document_id = $this->documents->create_document([
            'user_id' => $user->id,
            'type' => Documents::SCORE_CREDIT_RATING,
            'content' => '',
            'params' => $params,
        ]);

        $this->save_contract_document($user, $sms_code);

        $this->send_receipt($user, (int)$transaction_id);

        return $rating_document_id;
    }

    public function get_credit_rating_pdf_path($rating_document_id)
    {
        $rating = $this->documents->get_document($rating_document_id);
        $rating_file = $this->get_rating_file_number($rating->params['score'] ?? null);

        return $this->config->root_dir
            . 'design' . DIRECTORY_SEPARATOR
            . $this->settings->theme . DIRECTORY_SEPARATOR
            . 'html' . DIRECTORY_SEPARATOR
            . 'credit_rating' . DIRECTORY_SEPARATOR
            . 'results' . DIRECTORY_SEPARATOR
            . "$rating_file.html";
    }

    private function save_contract_document($user, $sms_code)
    {
        $this->documents->create_document([
            'user_id' => $user->id,
            'type' => Documents::CREDIT_RATING_APPLICATION,
            'params' => $this->get_contract_document_params($user, $sms_code),
        ]);
    }

    private function get_contract_document_params($user, $sms_code = '')
    {
        $short_name = $user->lastname . ' ' . mb_substr($user->firstname, 0, 1) . '. ' . mb_substr($user->patronymic, 0, 1) . '.';
        $registration_address = "{$user->Regindex} {$user->Regregion}, {$user->Regcity}, ".
            "{$user->Regstreet} ул, д. {$user->Reghousing}, кв. {$user->Regroom}";

        return [
            'short_name' => $short_name,
            'birth_date' => $user->birth,
            'passport_serial' => $user->passport_serial,
            'passport_issued' => $user->passport_issued,
            'passport_code' => $user->subdivision_code,
            'passport_date' => $user->passport_date,
            'registration_address' => $registration_address,
            'sms_code' => $sms_code,
        ];
    }

    private function send_receipt($user, int $transaction_id) {
        $service = new stdClass();
        $service->Sum = 39900;
        $service->Service = 'Услуга "Кредитный Рейтинг"';

        $input_data = new stdClass();
        $input_data->UID = $user->uid;
        $input_data->Services = [$service];
        $input_data->FIO = "{$user->lastname} {$user->firstname} {$user->patronymic}";
        $input_data->Zaim = $transaction_id;

        $passport_serial = str_replace(' ', '', $user->passport_serial);
        $serial = substr($passport_serial, 0, 4);
        $number = substr($passport_serial, 4, 6);
        $input_data->PassportSer = $serial;
        $input_data->PassportNum = $number;

        if ($this->is_developer)
        {
            error_log(__FILE__.':'.__LINE__.': '.var_export($input_data, true));
            return;
        }

        $this->cloudkassir->send_receipt($input_data);
    }

    public function get_rating_file_number($score)
    {
        if (is_null($score)) {
            $rating_file = 'null';
        } elseif ($score < 250) {
            $rating_file = 125;
        } elseif ($score < 301) {
            $rating_file = 275;
        } elseif ($score < 401) {
            $rating_file = 350;
        } elseif ($score < 451) {
            $rating_file = 450;
        } elseif ($score < 501) {
            $rating_file = 489;
        } elseif ($score < 601) {
            $rating_file = 600;
        } elseif ($score < 701) {
            $rating_file = 675;
        } else {
            $rating_file = 725;
        }
        return $rating_file;
    }

    private function created_credit_rating($user_id, $transaction_id)
    {
        $user_ratings = $this->documents->get_documents([
            'user_id' => $user_id,
            'type' => [Documents::SCORE_CREDIT_RATING],
        ]);

        foreach ($user_ratings as $user_rating)
        {
            if (($user_rating->params['transaction_id'] ?? null) == $transaction_id)
            {
                return $user_rating->id;
            }
        }

        return null;
    }
}
