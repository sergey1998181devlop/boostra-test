<?php

class Scorista extends Simpla
{
    /**
     * @var object|stdClass
     */
    private object $data;

    private $user_id;
    private $order_id;
    private $audit_id;
    private $type;
    
    private $username;
    private $token;

    public const CONNECT_TIMEOUT = 5;

    /**
     * Флаг, при котором мы проставляем шаг фото
     */
    const FLAG_STEP_FILES = 'scorista_step_files';

    /**
     * Флаг, при котором мы проставляем шаг работа
     */
    const FLAG_STEP_ADDITIONAL_DATA = 'scorista_step_additional_data';
    
    public function __construct()
    {
    	parent::__construct();
        
        $this->username = $this->settings->apikeys['scorista']['username'];
        $this->token = $this->settings->apikeys['scorista']['token'];
        $this->data = new stdClass;
    }
    
    private function set_keys($organization_id)
    {
        $this->username = $this->settings->apikeys['scorista2'][$organization_id]['username'];
        $this->token = $this->settings->apikeys['scorista2'][$organization_id]['token'];
    }

    public function run_scoring($scoring_id, bool $credit_up = false)
    {
        $update = [];

        if ($scoring = $this->scorings->get_scoring($scoring_id)) {
            if ($order = $this->orders->get_order((int)$scoring->order_id)) {
                if (empty($order->lastname) || empty($order->firstname) || empty($order->patronymic)
                    || empty($order->passport_serial) || empty($order->passport_date) || empty($order->birth)) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'в заявке не достаточно данных для проведения скоринга',
                        'end_date' => date('Y-m-d H:i:s'),
                    ];
                }
                elseif (!$this->organizations->isActiveOrganization((int)$order->organization_id)) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Организация отключена для выдачи',
                        'end_date' => date('Y-m-d H:i:s'),
                    ];
                } else {
                    $task = $this->create($order->order_id, $credit_up);
                    if (!empty($task->requestid)) {
                        $this->scorings->update_scoring($scoring_id, [
                            'scorista_id' => $task->requestid,
                            'status' => $this->scorings::STATUS_IMPORT,
                            'start_date' => date('Y-m-d H:i:s'),
                        ]);
                        return false;
                    } elseif ($task->status == 'ERROR') {
                        $update = [
                            'status' => $this->scorings::STATUS_ERROR,
                            'body' => json_encode($task->error->details),
                            'success' => 0,
                            'scorista_status' => '',
                            'scorista_ball' => 0,
                            'string_result' => $task->error->message
                        ];
                    } else {
                        $update = [
                            'status' => $this->scorings::STATUS_ERROR,
                            'body' => json_encode($task),
                            'success' => 0,
                            'scorista_status' => '',
                            'scorista_ball' => 0,
                            'string_result' => 'Ошибка при запросе'
                        ];
                    }
                }
            } else {
                $update = [
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                ];
            }
            
            if (!empty($update)) {
                $this->scorings->update_scoring($scoring_id, $update);
            }
            
            return $update;
        }
    }
    
    public function create($order_id, bool $credit_up = false)
    {
        if ($order = $this->orders->get_order((int)$order_id))
        {
            $this->set_keys($order->organization_id);

            $user = $this->users->get_user((int)$order->user_id);
            
            if (empty($user))
            {
                return (object)['error' => 'undefined_user'];
            }
            
            $data = & $this->data;
            $data->form = new StdClass();
            
            $persona = new StdClass();

            $patronymic = (string)$user->patronymic;
            if (empty(trim($patronymic)) || $patronymic == '-') {
                $patronymic = 'НЕТ';
            }

            /** Обшая информация */
            $personalInfo = new StdClass();
            $personalInfo->lastName = (string)$user->lastname;
            $personalInfo->firstName = (string)$user->firstname;
            $personalInfo->patronimic = $patronymic;
            $personalInfo->gender = $user->gender == 'male' ? 1 : 2;
            $personalInfo->birthDate = (string)$user->birth;
            $personalInfo->placeOfBirth = (string)$user->birth_place;
            $passport_serial = str_replace(array(' ', '-'), '', $user->passport_serial);
            $personalInfo->passportSN = (string)(substr($passport_serial, 0, 4).' '.substr($passport_serial, 4, 6));
            $personalInfo->issueDate = (string)$user->passport_date;
            $personalInfo->subCode = (string)$user->subdivision_code;
            $personalInfo->issueAuthority = (string)$user->passport_issued;
            
            $persona->personalInfo = $personalInfo;
            
            /** Адрес регистрации */
            $addressRegistration = new StdClass();
            $addressRegistration->postIndex = '000000';
            $addressRegistration->region = (string)$user->Regregion;
            $addressRegistration->city = (string)$user->Regcity;
            $addressRegistration->street = (string)$user->Regstreet;
            $addressRegistration->house = (string)$user->Reghousing;
            if ($user->Regbuilding)
                $addressRegistration->building = (string)$user->Regbuilding;
            if ($user->Regroom)
                $addressRegistration->flat = (string)$user->Regroom;
            
            $persona->addressRegistration = $addressRegistration;
            
            /** Фактический адрес проживания */
            $addressResidential = new StdClass();
            $addressResidential->postIndex = '000000';
            $addressResidential->region = (string)$user->Faktregion;
            $addressResidential->city = (string)$user->Faktcity;
            $addressResidential->street = (string)$user->Faktstreet;
            $addressResidential->house = (string)$user->Fakthousing;
            if ($user->Faktbuilding)
                $addressResidential->building = (string)$user->Faktbuilding;
            if ($user->Faktroom)
                $addressResidential->flat = (string)$user->Faktroom;
            
            $persona->addressResidential = $addressResidential;
            
            /** Контакная информация */
            $contactInfo = new StdClass();
            $contactInfo->cellular = (string)$user->phone_mobile;
            $contactInfo->cellularState = 2; // Статус подтверждения мобильного телефона (2. Проходил проверку и был подтверждён)
            $contactInfo->cellularMethod = 2; // Способ подтверждения (2. По СМС-коду)
            $contactInfo->phone = empty($user->landline_phone) ? 'НЕТ' : $user->landline_phone;
            $contactInfo->phoneState = 1; // Статус подтверждения домашнего телефона (1. Не проходил проверку)
            $contactInfo->phoneMethod = 4; // Способ подтверждения (4. нет)
            $contactInfo->email = empty($user->email) ? 'НЕТ' : $user->email;
            $contactInfo->emailState = 1; // Статус подтверждения личного Email (1. Не проходил проверку)
            $contactInfo->emailMethod = 4; // Способ подтверждения личного Email (4. нет)
            
            $persona->contactInfo = $contactInfo;

            $employment = new StdClass();
            $employment->jobCategory = 10; // Нет в анкете
            
            $persona->employment = $employment;
            
            $data->form->persona = $persona;

            $info = new StdClass();
            
            $loan = new StdClass();
            $loan->uid = $order->order_uid;
            $loan->loanID = $order->order_id;
            $loan->staffMember = 'CRM';
            $loan->loanPeriod = $order->period;
            $loan->loanSum = $order->amount;
            $loan->dayRate = $this->orders::BASE_PERCENTS; // Процентная ставка в день
            $loan->loanCurrency = 'RUB';
            $loan->fullRepaymentAmount = $order->amount + ($order->period * $order->amount * $this->orders::BASE_PERCENTS/ 100); // Сумма к возврату на плановую дату погашения
            $loan->applicationSourceType = 1; // Канал привлечения заявки (1. Интернет)
            $loan->agreementSignatureMethod = 2; // Способ подписания договора (2. На сайте заказчика онлайн)
            $loan->loanReceivingMethod = 11; // Способ получения займа (11. Другое)
            $loan->loanRepaymentMethod = 1; // Предполагаемый способ возврата займа (1. Перевод с банковской карты)

            if (!empty($order->utm_source))
            {
                $loan_productType = $order->utm_source;
                if (!empty($order->webmaster_id))
                    $loan_productType .= '_'.$order->webmaster_id;
                $loan->productType = $loan_productType;                
            }
            
            $info->loan = $loan;
            
            $repaymentSchedule = new StdClass();
            $repaymentSchedule->repaymentDate = date('d.m.Y', time() + 86400 * $order->period);
            $repaymentSchedule->repaymentAmount = $order->amount + ($order->period * $order->amount / 100);
            
            $info->repaymentSchedule = $repaymentSchedule;
            
            $borrowingHistory = new StdClass();
            if (count($user->loan_history) > 0)
            {
                $last_loan_item = end($user->loan_history);

                if (!empty($last_loan_item->close_date))
                {
                    $borrowingHistory->numberLoansRepaid = count($user->loan_history); // Количество ранее взятых и погашенных займов
    
                    $borrowingHistory->previousLoanDate = date('d.m.Y', strtotime($last_loan_item->date)); //Дата взятия предыдущего займа
                    $borrowingHistory->previousLoanPlanRepaidDate = empty($last_loan_item->plan_close_date) ? date('d.m.Y', strtotime($last_loan_item->close_date)) : date('d.m.Y', strtotime($last_loan_item->plan_close_date)); // Дата планового погашения предыдущего займа
                    $borrowingHistory->previousLoanFactRepaidDate = date('d.m.Y', strtotime($last_loan_item->close_date)); // Дата фактического погашения предыдущего займа
                    $borrowingHistory->previousLoanAmount = (int)$last_loan_item->amount; // Сумма предыдущего займа
                    $borrowingHistory->previousLoanRepaymentAmount = round($last_loan_item->amount + $last_loan_item->paid_percents);//Возвращенная сумма предыдущего займа
                    $borrowingHistory->previousLoanProlongationNumber = empty($last_loan_item->prolongation_count) ? 0 : $last_loan_item->prolongation_count; //Количество продлений предыдущего займа
                }
                else
                {
                    $borrowingHistory->numberLoansRepaid = 0;
                }
            }
            else
            {
                $borrowingHistory->numberLoansRepaid = 0; // Количество ранее взятых и погашенных займов
            }

            if ($credit_up) {
                $borrowingHistory->creditUP = 1;
            }
            
            $info->borrowingHistory = $borrowingHistory;
            
            $data->form->info = $info;
            
            $loanReceivingMethod = new StdClass();
            
            $cash = new StdClass();
            $cash->cash = 0;
            
            $loanReceivingMethod->cash = $cash;
            
            $data->form->loanReceivingMethod = $loanReceivingMethod;

            $data->form->juicy = $this->getJuicyScoreBody((int)$order->user_id) ?? '';

            $send_result = $this->send($data);
            
            $this->logging('scorista', $this->username, $data->form->info, $send_result, 'scorista.txt');
            
            return $send_result;
        }
        else
        {
            return (object)['error' => 'undefined_order'];
        }
    }

    /**
     * Get Data
     * @return object
     */
    public function getData(): object
    {
        return $this->data;
    }
    
    public function get_result($request_id, $organization_id = null)
    {
        $data = new StdClass();
        $data->requestID = $request_id;
        
        if (!empty($organization_id)) {
            $this->set_keys($organization_id);
        }

        return $this->send($data);
    }
    
    public function send($data)
    {
        $url = 'https://api.scorista.ru/mixed/json';
        
        $nonce = sha1(uniqid(true));
        $password = sha1($nonce.$this->token);
        
        $headers = [
            'Content-Type: application/json',
            'username: '.$this->username,
            'nonce: '.$nonce,
            'password: '.$password,
        ];

        $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::CONNECT_TIMEOUT);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        
        curl_close($ch);

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($error,$info, $result);echo '</pre><hr />';
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($data_string, $headers);echo '</pre><hr />';
        $this->logging('send', $this->username, $headers, $data, 'send_scorista.txt');


        return json_decode($result);
    }

    /**
     * Получение ответа скоринга Juicescore в виде base64 строки.
     * @param $user_id
     * @return string|null null, если скоринг не провёлся или не успешный
     */
    public function getJuicyScoreBody($user_id)
    {
        $scoring = $this->scorings->getLastScoring([
            'type' => $this->scorings::TYPE_AXILINK_2,
            'user_id' => $user_id,
            'status' => $this->scorings::STATUS_COMPLETED,
        ]);

        if (empty($scoring))
            return null;

        $body = $this->scorings->get_body_by_type($scoring);
        if (empty($body))
            return null;

        $body = (array)$body;
        if (empty($body['juicy']))
            return null;

        return base64_encode($body['juicy']);
    }

    /**
     * Сохранение кредитной истории в отдельный .zip архив.
     * @param string $scoristaId
     * @param $equifaxCH
     * @return void
     */
    public function saveCreditHistory(string $scoristaId, $equifaxCH)
    {
        $zip = new ZipArchive();
        $zip->open($this->config->root_dir . 'files/equifax_zipped/' . $scoristaId . '.zip', ZipArchive::CREATE);
        $zip->addFromString($scoristaId . '.xml', base64_decode($equifaxCH, true));
        $zip->setCompressionIndex(0, ZipArchive::CM_LZMA, 9);
        $zip->close();
    }
}