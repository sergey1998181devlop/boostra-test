<?php

require_once('Simpla.php');

class FinKartaAPI extends Simpla
{
    // Тестовое апи
    //private const API_URL = 'https://reqxml.f-karta.ru/prod/request_test.php';
    // Прод апи
    private const API_URL = 'https://reqxml.f-karta.ru/prod/request.php';

    /** @var false|string Путь к сертификату */
    private $certPath;

    /** @var false|string Ключ к сертификату */
    private $certKey;

    /** @var false|string Путь к Stribog для генерации хэша */
    private $stribogPath;

    public function __construct()
    {
        parent::__construct();

        $this->certPath = realpath(dirname(__FILE__).'/../cert/finkarta/user_353.crt');
        $this->certKey = realpath(dirname(__FILE__).'/../cert/finkarta/user_353.key.txt');

        $this->stribogPath = realpath(dirname(__FILE__).'/../programs/stribog');
    }

    /**
     * Отправка XML в ФинКарту.
     * @param string $xml
     * @return bool|string
     * @throws Exception
     */
    public function request(string $xml)
    {
        $c = curl_init();

        curl_setopt($c, CURLOPT_URL, self::API_URL);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($c, CURLOPT_ENCODING, "utf-8");
        curl_setopt($c, CURLOPT_AUTOREFERER, 1);
        curl_setopt($c, CURLOPT_TIMEOUT, 120);
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($c, CURLOPT_SSLCERT, $this->certPath);
        curl_setopt($c, CURLOPT_SSLKEY, $this->certKey);
        curl_setopt($c, CURLOPT_SSLCERTPASSWD, "");
        curl_setopt($c, CURLOPT_SSLKEYPASSWD, "");
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, ['xml' => $xml]);

        $response = curl_exec($c);

        if ($response === false) {
            $error = curl_error($c);
            $errno = curl_errno($c);
            $this->logging(__METHOD__, self::API_URL, $xml, $error, 'finkarta.txt');
            throw new Exception($error, $errno);
        }
        else {
            $this->logging(__METHOD__, self::API_URL, $xml, $response, 'finkarta.txt');
        }

        return $response;
    }

    /**
     * Преобразование и хэширование строки с помощью Stribog.
     *
     * Все даты ЗАРАНЕЕ должны быть приведены к формату YYYY-MM-DD
     * @param $str
     * @return string
     * @throws Exception
     */
    public function hash($str)
    {
        // region Преобразование строки
        // Удаляем все символы, кроме а-я, А-Я, a-z, A-Z, 0-9
        $str = preg_replace('/[^a-zA-Zа-яА-Я0-9]/u', '', $str);
        // Приводим к верхнему регистру
        $str = mb_strtoupper($str, 'UTF-8');
        // Делаем транслитерацию
        $str = $this->transliterate($str);
        // endregion

        // region Передача строки в Stribog
        $command = escapeshellcmd($this->stribogPath . " -s \"$str\"");

        exec($command, $output, $code);
        if ($code !== 0) {
            $error = implode("\n", $output);
            throw new Exception("FinKarta: Stribog error\n$error");
        }

        return implode("\n", $output);
        // endregion
    }

    /**
     * Транслитерация (Замена русских символов на английские) строки в верхнем регистре
     * @param $str
     * @return string
     */
    private function transliterate($str)
    {
        return strtr($str, [
            'А' => 'A',   'Б' => 'B',   'В' => 'V',   'Г' => 'G',   'Д' => 'D',
            'Е' => 'E',   'Ё' => 'E',   'Ж' => 'ZH',  'З' => 'Z',   'И' => 'I',
            'Й' => 'Y',   'К' => 'K',   'Л' => 'L',   'М' => 'M',   'Н' => 'N',
            'О' => 'O',   'П' => 'P',   'Р' => 'R',   'С' => 'S',   'Т' => 'T',
            'У' => 'U',   'Ф' => 'F',   'Х' => 'KH',  'Ц' => 'TS',  'Ч' => 'CH',
            'Ш' => 'SH',  'Щ' => 'SHCH','Ъ' => '""',    'Ы' => 'Y',   'Ь' => "''",
            'Э' => 'E',   'Ю' => 'YU',  'Я' => 'YA',
        ]);
    }

    public function makeRequestXml($scoring)
    {
        $currentDateFull = date('Y-m-d\TH:i:s');
        $currentDateShort = date('Y-m-d');

        $user = $this->users->get_user($scoring->user_id);
        if (empty($user))
            return false;

        $hash_firstname_middlename_today = $this->hash($user->firstname . $user->patronymic . $currentDateShort);
        $hash_firstname = $this->hash($user->firstname);
        $hash_lastname = $this->hash($user->lastname);
        $hash_middlename = $this->hash($user->patronymic);

        $birthDateTime = DateTime::createFromFormat('d.m.Y', $user->birth);
        $hash_birthdate = $this->hash($birthDateTime->format('Y-m-d'));

        $passport = str_replace(array('-', ' '), '', $user->passport_serial);
        $passport_serial = substr($passport, 0, 4);
        $passport_number = substr($passport, 4, 6);

        $hash_docserial = $this->hash($passport_serial);
        $hash_docnumber = $this->hash($passport_number);
        $hash_docissuedate = $this->hash($user->passport_date);

        $hash_regstreetname = $this->hash($user->Regstreet);
        $hash_regbuilding = $this->hash($user->Regbuilding);
        $hash_faktstreetname = $this->hash($user->Faktstreet);
        $hash_faktbuilding = $this->hash($user->Faktbuilding);

        $workPhone = $this->users->clear_phone($user->work_phone);

        $cards = $this->best2pay->get_cards([
            'user_id' => $user->id
        ]);
        $cardsXML = '';
        $recordId = 1;
        foreach ($cards as $card) {
            if (!empty($card->deleted))
                continue;

            $expDate = DateTime::createFromFormat('m/Y', $card->expdate);
            $expDate->modify('last day of this month');
            $expDate = $expDate->format('Y-m-d');

            $cardsXML .= <<<EOD
<card record_id="$recordId" card_number="$card->pan" card_exp_date="$expDate" card_ref_id="$card->approval_code"/>
EOD;
            $recordId += 1;
        }

        $xml = <<<EOD
        <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
        <request date="$currentDateFull" id="$scoring->id" request_type="FKSCORES" out_format="xml">
           <check_hash first_name="$user->firstname" middle_name="$user->patronymic" today="$currentDateShort" hash="$hash_firstname_middlename_today"/>
           <person_data>
              <person reason_request="2" record_id="1" type="1" hash_last_name="$hash_lastname" hash_first_name="$hash_firstname" hash_middle_name="$hash_middlename" hash_birth_date="$hash_birthdate">
                 <person_docs>
                    <doc record_id="1" doc_type="1" hash_doc_serial="$hash_docserial" hash_doc_number="$hash_docnumber" hash_doc_issue_date="$hash_docissuedate" doc_issue_auth="$user->passport_issued"/>
                 </person_docs>
                 <person_addresses>
                    <address record_id="1" address_type="1" address_index="$user->Regindex" address_region="$user->Regregion" address_city_name="$user->Regcity" address_street_type="21" hash_address_street_name="$hash_regstreetname" hash_address_building_number="$hash_regbuilding"/>
                    <address record_id="2" address_type="2" address_index="$user->Faktindex" address_region="$user->Faktregion" address_city_name="$user->Faktcity" address_street_type="21" hash_address_street_name="$hash_faktstreetname" hash_address_building_number="$hash_faktbuilding"/>
                 </person_addresses>
                 <person_cards>
                    $cardsXML
                 </person_cards>
              </person>
           </person_data>
        </request>
        EOD;
        return $xml;
    }

    public function addScoring($user_id, $order_id)
    {
        if (!isset($this->scoring_type))
            $this->scoring_type = $this->scorings->get_type($this->scorings::TYPE_FINKARTA);

        if (empty($this->scoring_type->active))
            return false;

        // Проверка на уже добавленный скоринг
        $scoring = $this->scorings->get_last_type_scoring($this->scorings::TYPE_FINKARTA, $user_id);
        if (!empty($scoring) && $scoring->order_id == $order_id)
            return false;

        $order = $this->orders->get_order($order_id);
        if ($order->utm_source == 'cross_order' || $order->utm_source == 'crm_auto_approve') {
            return false;
        }

        $this->scorings->add_scoring([
            'user_id' => $user_id,
            'order_id' => $order_id,
            'status' => $this->scorings::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
            'type' => $this->scorings::TYPE_FINKARTA,
        ]);

        return true;
    }

    /** @var string[] Описание правил финкарты */
    const CHECKS_DESCRIPTION = [
        'basicstdcheck_5_8_3' => 'Принадлежность карты',
        'basicstdcheck_5_8_4' => 'Принадлежность номера телефона',
        'basicstdcheck_5_8_5' => 'Принадлежность устройства',
    ];

    /** @var string[] Описание результата проверок */
    const RESULT_DESCRIPTION = [
        '0' => 'Проверка проведена, негатив не обнаружен',
        '1' => 'Негативный результат проверки',
        '2' => 'Успешный результат проверки'
    ];

    /**
     * @param array|object $body
     * @return array
     */
    public function getFormattedBody($body)
    {
        if (empty($body))
            return [];
        $body = (array)$body;

        $formatted_body = [];
        foreach ($body as $check => $result) {
            if (empty(self::CHECKS_DESCRIPTION[$check]))
                continue;

            $key = self::CHECKS_DESCRIPTION[$check];
            $value = self::RESULT_DESCRIPTION[(string)$result] ?? $result;
            $success = $result != 1 && $result != 'Результат не найден';

            $formatted_body[$key] = [
                'result' => $value,
                'success' => $success
            ];
        }

        return $formatted_body;
    }
}
