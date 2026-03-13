<?php

use Traits\BKIHelperTrait;
use Traits\HasScoringsForAxiTrait;

require_once __DIR__ . '/../scorings/BoostraPTI.php';
require_once __DIR__ . '/Traits/HasScoringsForAxiTrait.php';
require_once __DIR__ . '/Traits/BKIHelperTrait.php';

class Axilink extends Simpla
{
    use HasScoringsForAxiTrait;
    use BKIHelperTrait;

    /**
     * Ключ объекта суммы с ПДН в ответе скоринга
     */
    public const KEY_AMOUNT_WITH_PDN = 'sum';

    /**
     * @var string DECISION
     */
    private const DECISION = 'GetApplicationDecision';

    /**
     * @var string RESPONSE
     */
    private const RESPONSE = 'GetApplication';

    /**
     * Robot browser data
     * @var string BROWSER
     */
    private const BROWSER = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36';

    private const DSS_FICO_4_10 = 'FICO_4_10';

    /**
     * @var ?resource $ch
     */
    private $ch = null;

    /**
     * @var ?string $error
     */
    private $error = null;

    /**
     * @var ?string $xml
     */
    private $xml = null;

    /**
     * @var array $data
     */
    protected $data;

    /**
     * CALL type of AXI [SCORISTA, START, FSSP, FNS]
     * @var string $command
     */
    protected $command = 'START';

    /**
     * minimum time for valida passport date - 01.01.1997
     * @var int PASSPORT_VALID_TIME
     */
    private const PASSPORT_VALID_TIME = 852076800;

    /**
     * card number of user
     * @var string $cardNumber
     */
    private $cardNumber = '';

    /**
     * @var string $createResponse
     */
    private $createResponse = '';

    /**
     * @var string $infoResponse
     */
    private $infoResponse = '';

    /**
     * Адрес для запросов
     * @var string $service_url
     */
    private $service_url;

    /** @var object|null Конфиг Axi из БД */
    private $axiConfig = null;

    /**
     * Устанавливает url для отправки запросов в зависимости от организации
     * @param object $order
     * @return void
     */
    private function set_service_url($order)
    {
        $this->axiConfig = $this->organizations_data->getAxiConfig((int) $order->organization_id, false);

        if ($this->axiConfig) {
            $this->service_url = 'http://' . $this->axiConfig->service_ip . ':8080/' . $this->axiConfig->version . '/rpc/';
        }
    }

    /**
     * Run scoring Axilink
     * @param string $scoringId
     * @return void
     */
    public function run_scoring(string $scoringId): void
    {
        if ($scoring = $this->scorings->get_scoring($scoringId)) {
            if ($order = $this->orders->get_order((int)$scoring->order_id)) {
                $timePassport = strtotime($order->passport_date);
                $birthYear = date('Y', strtotime($order->birth));
                $passportYear = date('Y', $timePassport);
                if ($timePassport < self::PASSPORT_VALID_TIME) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Старая дата выдачи паспорта!',
                    ];
                } elseif ($passportYear == $birthYear) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Не должны совпадать дата рождения с датой выдачи паспорта!',
                    ];
                } elseif (($passportYear - $birthYear) < 14) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Паспорт РФ выдается в возрасте 14 лет!',
                    ];
                } elseif ($timePassport > time()) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Дата выдачи паспорта не может быть в будущем!',
                    ];
                } elseif (!$this->organizations->isActiveOrganization((int)$order->organization_id)) {
                    $update = [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Организация отключена для выдачи',
                    ];
                } else {
                    if (!($this->cardNumber = $this->getCardNumber($order)) && $order->card_type !== $this->orders::CARD_TYPE_SBP) {
                        $update = array(
                            'status' => $this->scorings::STATUS_ERROR,
                            'string_result' => 'Номер карты получен пустым из 1С! Повторите позднее',
                        );
                    } else {
                        $this->set_service_url($order);

                        $id = $this->getLastAppId($order->order_id);

                        try {
                            $this->createResponse = $this->create($order, $id);
                            if ($this->createResponse && (is_numeric($this->createResponse) || $this->createResponse === 'APPLICATION_EXISTS')) {
                                $this->saveInDb($id, $order->order_id, $this->xml);
                                $update = array(
                                    'status' => $this->scorings::STATUS_WAIT,
                                    'string_result' => $id
                                );
                            } else {
                                $update = array(
                                    'status' => $this->scorings::STATUS_ERROR,
                                    'string_result' => ((string) $this->createResponse) . $this->error
                                );
                            }
                        } catch (Throwable $t) {
                            $this->logging(__METHOD__, '', $order->order_id, $t, 'axilink.log');

                            $update = array(
                                'status' => $this->scorings::STATUS_ERROR,
                                'string_result' => $t->getMessage()
                            );
                        }

                    }
                }
            } else {
                $update = array(
                    'status' => $this->scorings::STATUS_ERROR,
                    'string_result' => 'не найдена заявка'
                );
            }
            $this->scorings->update_scoring($scoringId, $update);
        }
    }

    /**
     * Get response for scoring
     * @param object $scoring
     * @return void
     */
    public function getInfo(object $scoring): void
    {
        $order = $this->orders->get_order($scoring->order_id);
        $this->set_service_url($order);

        if (($this->infoResponse = $this->getDecision($scoring->string_result)) && strpos($this->infoResponse, 'ERROR') === false) {
            if ($this->infoResponse != 'UNDEFINED') {
                $update = array(
                    'status' => $this->scorings::STATUS_COMPLETED,
                    'success' => 0
                );
                if ($this->infoResponse === 'APPROVE') {
                    $update['success'] = 1;
                    $update['string_result'] = 'заявка одобрена';
                } elseif ($this->infoResponse === 'DECLINE') {
                    $update['string_result'] = 'заявка отклонена';
                } else {
                    $update['status'] = $this->scorings::STATUS_ERROR;
                }
                $this->getFullData($scoring->string_result);

                if (!empty($this->data['final_limit'])) {
                    $this->order_data->set($scoring->order_id, 'amount_after_axi', $this->data['final_limit']);
                }

                $this->saveEquifaxData();
                $order = $this->orders->get_order($scoring->order_id);
                $user = $this->users->get_user($scoring->user_id);

                $calculatorPTI = new BoostraPTI($order);
                $calculatorPTI->setSource();
                $calculatorPTI->toggleDetails(true);
                $dataPTI = $calculatorPTI->getPTIData();

                // передаем в 1с agrid скористы
                $this->orders->update_order($scoring->order_id, ['scorista_ball' => $this->data['score'], 'pti_order' => $dataPTI['rosstat_pti'] ?? 0]);
                $this->soap->send_scorista_id($user->UID, $order->id_1c, $this->data['agrid'], $order->organization_id);

                if (!empty($this->data['comments'])) {
                    $this->comments->add_comment([
                        'manager_id' => 0,
                        'user_id' => $user->id,
                        'order_id' => $order->order_id,
                        'block' => 'axilink',
                        'text' => $this->data['comments'],
                        'created' => date('Y-m-d H:i:s'),
                    ]);
                    unset($this->data['comments']);
                }

                $update['body'] = json_encode($this->data, JSON_UNESCAPED_UNICODE);
                $update['scorista_ball'] = $this->data['score'];
                $update['scorista_status'] = $this->data['name'];
                $update['scorista_id'] = $this->data['agrid'];
                $update['end_date'] = date('Y-m-d H:i:s');

                if ($order->utm_source == $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE && empty($update['success'])) {
                    $order = $this->orders->get_order($scoring->order_id);
                    if ($this->leadgidScorista->isEnabled() && $leadgid = $this->leadgidScorista->getByOrder($order)) {
                        $update['success'] = 1;
                        $update['scorista_status'] = $this->scorings::SCORISTA_STATUS_RESULT_SUCCESS;
                        $update['string_result'] = 'заявка одобрена';
                        $this->orders->update_order($scoring->order_id, ['amount' => $leadgid->amount]);
                        $this->leadgidScorista->markOrder($scoring->order_id);
                    }
                }

                if (!empty($update['success'])) {
                    // добавляет проверку на купленный рейтинг и пустое значение в документе
                    $document_credit_rating = $this->documents->getLastDocumentCreditRating((int)$scoring->user_id);
                    if (!empty($document_credit_rating) && !isset($document_credit_rating->params->score)) {
                        $document_credit_rating->params['score'] = $this->data['score'];
                        $this->documents->update_document(
                            $document_credit_rating->id,
                            ['params' => $document_credit_rating->params]
                        );
                    }
                }
                if ($order->utm_source == $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE){
                    $this->dbrainAxi->saveChData($scoring->string_result,$scoring->order_id);
                    //                $this->pdnCalculation->run($scoring->order_id);
                }
                $this->scorings->update_scoring($scoring->id, $update);
            }
        }
    }

    /**
     * Set call mode
     * @param string $command
     * @return void
     */
    public function setCommand(string $command): void
    {
        $this->command = $command;
    }

    /**
     * Get createResponse data
     * @return string
     */
    public function getCreateResponse(): string
    {
        return $this->createResponse;
    }

    /**
     * Get infoResponse data
     * @return string
     */
    public function getInfoResponse(): string
    {
        return $this->infoResponse;
    }

    /**
     * Get Full data in response
     * @param string $appId
     * @return void
     */
    protected function getFullData(string $appId): void
    {
        $score = $pdn = $sum = $sumNoPti = $period = 0;
        $message = $name = $equiFax = $agrid = $comments = '';
        $final_limit = null;

        $this->data = json_decode(
            $this->send(['applicationId' => $appId], self::RESPONSE, 'multipart/form-data')
        );
        if ($this->data) {
            $period = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->
                additional_SCR->{'@decisionPeriod'} ?? ''
            );
            $pdn = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->
                additional_SCR->pti_RosStat_SCR->pti_RS_pti_SCR->{'@result'} ?? ''
            );
            $name = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->
                decision_SCR->{'@decisionName'} ?? ''
            );
            $sum = trim(
                $this->data->Application->AXI->application_e->calc->{'@limit_final'} ?? ''
            );
            $sumNoPti = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->
                additional_SCR->{'@decisionSum_without_PTI'} ?? ''
            );
            $score = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->
                creditHistory_SCR->{'@score'} ?? ''
            );
            $message = trim(
                $this->data->Application->AXI->application_e->policyRules->{'@stop_factors'} ?? ''
            );
            $equiFax = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->equifaxCH->_value ?? ''
            );
            $agrid = trim(
                $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->{'@agrid'} ?? ''
            );
            if (!empty($this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->additional_SCR->phonesBRS)) {
                $comments = $this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->additional_SCR->phonesBRS->description . PHP_EOL;
                foreach ($this->data->Application->AXI->application_e->SCORISTA->Response_SCR->data_SCR->additional_SCR->phonesBRS->result as $item) {
                    $item = (array)$item;
                    $comments .= $item['actuality-date'] . ' ' . $item['phone-number'] . PHP_EOL;
                }
            }

            $final_limit = call_user_func(function ($final_limit_raw) {
                if (empty($final_limit_raw)) {
                    return null;
                }

                if (is_string($final_limit_raw)) {
                    $final_limit_raw = trim($final_limit_raw);
                }

                if (!is_numeric($final_limit_raw)) {
                    return null;
                }

                return number_format($final_limit_raw, 2, '.', '');
            }, $this->data->Application->AXI->application_e->{'@final_limit'} ?? '');
        }
        $this->data = [
            'limit_period' => $period,
            'pdn' => $pdn,
            'sum' => $sum,
            'sum_no_pti' => $sumNoPti,
            'name' => $name,
            'message' => $message,
            'score' => $score,
            'appId' => $appId,
            'agrid' => $agrid,
            'equifax' => $equiFax,
            'comments' => $comments,
            'final_limit' => $final_limit,
        ];
    }

    /**
     * Save equifaxCH data
     * @return void
     */
    protected function saveEquifaxData(): void
    {
        if ($this->data && !empty($this->data['agrid']) && !empty($this->data['equifax'])) {
            $zip = new ZipArchive();
            $zip->open($this->config->root_dir . 'files/axilink_zipped/' . $this->data['agrid'] . '.zip', ZipArchive::CREATE);
            $zip->addFromString($this->data['agrid'] . '.xml', base64_decode($this->data['equifax'], true));
            $zip->setCompressionIndex(0, ZipArchive::CM_LZMA, 9);
            $zip->close();
            unset($this->data['equifax']);
        }
    }

    /**
     * @param object $order
     * @param string $id
     * @return bool|string
     * @throws Exception
     */
    protected function create(object $order, string $id)
    {
        $dss_name = $this->getDssName();
        $date = ($dss_name === self::DSS_FICO_4_10)
            ? date('Y-m-d\TH:i:s')     // FICO_4_10_V2...: без таймзоны
            : date('Y-m-d\TH:i:s.vP');     // FICO_4_10: с таймзоной

        $timeUtc3 = date('d.m.Y H:i:s');
        $sessionId = $order->juicescore_session_id; //md5(rand() . microtime());
        $endDate = date('Y-m-d\TH:i:s.vP', strtotime('+5days'));
        $order->birth = date('Y-m-d', strtotime($order->birth));

        $modifiedId = ($dss_name === self::DSS_FICO_4_10) ?  $order->id : $id;

        $regIndex = trim($order->Regindex);
        if (strlen($regIndex) !== 6 || $regIndex === 'undefi') {
            $regIndex = '000000';
        }

        $faktIndex = trim($order->Faktindex);
        if (strlen($faktIndex) !== 6 || $faktIndex === 'undefi') {
            $faktIndex = '000000';
        }

        $scorings = $this->scorings($order->user_id, $order->order_id);
        $internalCR = $this->internalCR($order->user_id);
        $passportFullNumber = $this->preparePassportFullNumber($order->passport_serial);
        $passNumber = $this->getPassportNumberFromPassportFullNumber($passportFullNumber);
        $passSeria = $this->getPassportSerialFromPassportFullNumber($passportFullNumber);
        $subdivisionCode = $this->prepareSubdivisionCode($order->subdivision_code);
        $passportIssued = $this->preparePassportIssued($order->passport_issued);
        $flat = $order->Faktroom ?: $order->Regroom;
        $house = $order->Fakthousing ?: $order->Reghousing;
        $repaymentDate = date('Y-m-d', strtotime("+{$order->period}day"));
        $repaymentAmount = $order->amount + ($order->period * $order->amount / 100);
        $gender = $this->getGenderCode($order->gender);
        $cardHolder = Helpers::translit($order->firstname . ' ' . $order->lastname);
        $useragent = $this->order_data->read($order->order_id, $this->order_data::USERAGENT) ?? '';

        /** @var array $additionalApplicationParams Опциональные параметры для акси в формате [параметр] => значение */
        $additionalApplicationParams = [];

        sleep(1);

        $axiWithoutCreditReports = !empty($this->order_data->read($order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS));
        $additionalApplicationParams['allow_simplified_flow'] = $axiWithoutCreditReports ? 'true' : 'false';

        $this->logging(__METHOD__, 'Получен флаг allow_simplified_flow для заявки',  '', ['order_id' => $order->order_id, 'axi_without_credit_reports' => $axiWithoutCreditReports, 'additional_application_params' => $additionalApplicationParams],  'axilink.log');

        $additionalApplicationParamsStr = '';
        foreach ($additionalApplicationParams as $key => $value) {
            $additionalApplicationParamsStr .= $key . '="' . $value . '"' . "\n";
        }


        $this->xml = '<Application DeliveryOptionCode="boostra2" ProcessingRequestType="DM">
            <CreditRequest ProductCategory="' . $this->getProductCategory($order) . '" ProductCode="' . $this->getProductCode($order) . '"></CreditRequest>
            <AXI>
                <application_e 
                    dss_name="' . $dss_name . '" 
                    ApplicationDate="' . $date . '" 
                    ApplicationId="' . $modifiedId . '" 
                    uuid="' . $order->order_uid . '"                     
                    call_name="' . $this->command . '" 
                    pass_seria="' . $passSeria . '" 
                    pass_number="' . $passNumber . '" 
                    pass_date_issue="' . date('Y-m-d', strtotime($order->passport_date)) . '" 
                    pass_issued="' . $passportIssued . '" 
                    pass_code="' . $subdivisionCode . '" 
                    snils="' . $order->Snils . '" 
                    email="' . $order->email . '"  
                    IP="' . $order->ip . '"
                    app_channel="1"
                    user_agent="' . $useragent . '"
                    initial_limit="' . $order->amount . '"
                    mob_phone_num="' . $order->phone_mobile . '"
                    home_phone="' . $order->identified_phone . '"
                    client_birthplace="' . Helpers::getSafeStringForXml($order->birth_place). '"
                    client_birthdate="' . $order->birth . '"
                    client_middlename="' . $order->patronymic . '"
                    client_name="' . $order->firstname . '"
                    client_surname="' . $order->lastname . '"
                    reg_address_index="' . $regIndex . '" 
                    reg_address_region="' . $this->clearQuotes( $this->clearGarbage($order->Regregion_shorttype, $order->Regregion) ) . '" 
                    reg_address_city="' . $this->clearQuotes( $this->clearGarbage($order->Regcity_shorttype, $order->Regcity) ) . '" 
                    reg_address_street="' . $this->clearQuotes( $this->clearGarbage($order->Regstreet_shorttype, $order->Regstreet) ) . '"
                    reg_address_house = "' . $this->clearQuotes($house) . '"
                    reg_address_flat = "' . $this->clearQuotes($flat) . '"
                    liv_address_index="' . $faktIndex . '" 
                    liv_address_region="' . $this->clearQuotes( $this->clearGarbage($order->Faktregion_shorttype, $order->Faktregion) ) . '" 
                    liv_address_city="' . $this->clearQuotes( $this->clearGarbage($order->Faktcity_shorttype, $order->Faktcity) ) . '" 
                    liv_address_street="' . $this->clearQuotes( $this->clearGarbage($order->Faktstreet_shorttype, $order->Faktstreet) ) . '"
                    liv_address_house="' . $this->clearQuotes($house) . '"
                    liv_address_flat="' . $this->clearQuotes($flat) . '"
                    person_INN="' . $order->inn . '"
                    gender="' . $gender . '" 
                    productType="' . $this->getProductType($order) . '" 
                    repaymentDate="' . $repaymentDate . '"  
                    repaymentAmount="' . $repaymentAmount . '" 
                    agreementSignatureMethod="2" 
                    applicationSourceType="1" 
                    staffMember="CRM" 
                    cellularState="2" 
                    cellularMethod="2" 
                    loanRepaymentMethod="1" 
                    ' . $this->getLoanInfo($order) . ' 
                    card_number="' . $this->cardNumber . '" 
                    card_holder_name="' . $cardHolder . '"
                    consentDate="' . $date . '" 
                    consentEndDate="' . $endDate . '" 
                    consentFlag="Y" 
                    webmasterBlocked="' . ((int) $this->webmasterBlocked($order)) . '"
                    scnd_24="' . $this->dbrainAxi->getMaxExpiredPeriod($order) . '"
                    loan_type_IL="' . $this->dbrainAxi->getOrderLoanType($order) . '"
                    ' . $additionalApplicationParamsStr . '
                    consentPurpose="1">
                    <loanReceivingMethod 
                        value="11"
                        cash="0"
                    ></loanReceivingMethod>
                    <juicy_scoring
                        session_id="' . $sessionId . '"
                        channel="' . $order->utm_source . '"
                        client_id="' . $order->user_id . '"
                        time_utc3="' . $timeUtc3 . '"
                        tenor="' . $order->period . '"
                    ></juicy_scoring>
                    <innerCH
                        TotalPayment="' . $order->amount . '"
                    ></innerCH>
                    <application_innerHistory
                        initial_rate="' . $order->percent . '"
                        initial_maturity="' . $order->period . '"
                    ></application_innerHistory>
                    <scorings>' . $scorings . '</scorings>
                    <internal_CR>' . $internalCR . '</internal_CR>
                </application_e>
            </AXI></Application>';

        $this->logging(__METHOD__, 'axilink',  $this->xml, '',  'axilink.log');

        return $this->send($this->xml, $this->axiConfig->create_action, 'application/xml');
    }

    /**
     * Clear not need garbage string
     * @param string $garbage
     * @param string $str
     * @return string
     */
    protected function clearGarbage(string $garbage, string $str): string
    {
        $replaced = ['*'];
        if (!empty($garbage)) {
            $replaced[] = ' ' . $garbage;
        }
        return trim(str_replace($replaced, '', $str)) ?: 'НЕТ';
    }

    /**
     * Clear quotes in string
     * @param string $garbage
     * @param string $str
     * @return string
     */
    protected function clearQuotes(string $str): string
    {
        return str_replace('"', '', $str);
    }

    /**
     * Get property product_category
     * @param stdClass $order Обрабатываемая заявка
     * @return string
     */
    protected function getProductCategory(stdClass $order): string
    {
        return $this->axiConfig->AxilinkProductCategory;
    }

    /**
     * Get property product_code
     * @param stdClass $order Обрабатываемая заявка
     * @return string
     */
    protected function getProductCode(stdClass $order): string
    {
        return $this->axiConfig->AxilinkProductCode;
    }

    /**
     * Get dss_name from config
     * @return string
     */
    protected function getDssName(): string
    {
        return $this->axiConfig->dss_name;
    }

    /**
     * Get product type
     * @param object $order
     * @return string
     */
    protected function getProductType(object $order): string
    {
        $str = 'Boostra';
        if (!empty($order->utm_source)) {
            $str = $order->utm_source;
            if (!empty($order->webmaster_id)) {
                $str .= '_' . $order->webmaster_id;
            }
        }
        return $str;
    }

    /**
     * Get loan Info for SPR
     * @param object $order
     * @return string
     */
    protected function getLoanInfo(object $order): string
    {
        if (empty($order->loan_history)) {
            $str = 'numberLoansRepaid = "0"' . "\n";
            $str .= 'countClosedLoans = "0"' . "\n";
            $str .= 'countLoans = "0"' . "\n";
            return $str;
        }

        $loansAmount =  count($order->loan_history);
        $closedLoansAmount = count(array_filter($order->loan_history, function ($loan) {
            return !empty($loan->close_date);
        }));

        $lastLoan = end($order->loan_history);

        $str = 'numberLoansRepaid = "' . $loansAmount . '"' . "\n";
        $str .= 'previousLoanDate = "' . date('Y-m-d', strtotime($lastLoan->date)) . '"' . "\n";
        $str .= 'previousLoanPlanRepaidDate = "' . date('Y-m-d', strtotime($lastLoan->{empty($lastLoan->plan_close_date) ? 'close_date' : 'plan_close_date'})) . '"' . "\n";
        $str .= 'previousLoanFactRepaidDate = "' . date('Y-m-d', strtotime($lastLoan->close_date)) . '"' . "\n";
        $str .= 'previousLoanAmount = "' . (int)$lastLoan->amount . '"' . "\n";
        $str .= 'previousLoanRepaymentAmount = "' . round($lastLoan->amount + $lastLoan->paid_percents) . '"' . "\n";
        $str .= 'previousLoanProlongationNumber = "' . (empty($lastLoan->prolongation_count) ? 0 : $lastLoan->prolongation_count) . '"' . "\n";
        $str .= 'countClosedLoans = "' . $closedLoansAmount . '"' . "\n";
        $str .= 'countLoans = "' . $loansAmount . '"' . "\n";

        return $str;
    }

    /**
     * Get Card number from b2p or tinkoff
     * @param object $order
     * @return string
     */
    private function getCardNumber(object $order): string
    {
        $cardNumber = '';
        $cards = $this->best2pay->get_cards(array('user_id' => $order->user_id));
        $arg = 'pan';
        if ($cards && !empty($cards[0])) {
            $cardNumber = $cards[0]->{$arg};
        }
        return $cardNumber;
    }

    /**
     * @param object $order
     * @return bool
     */
    private function webmasterBlocked(object $order): bool
    {
        $this->webmaster->setOrder($order);
        if ($blocked = $this->webmaster->isBlocked()) {
            try {
                $this->webmaster->runDeclineProcess();
            } catch (Exception $e) {
                $this->logging(__METHOD__, '', $order, $blocked, 'webmaster.txt');
            }
        }
        return $blocked;
    }

    /**
     * @param string $appID
     * @return bool|string
     */
    private function getDecision(string $appID)
    {
        return $this->send(['applicationId' => $appID], self::DECISION, 'multipart/form-data');
    }

    /**
     * @param array|string $data
     * @param string $method
     * @param string $type
     * @return bool|string
     */
    private function send($data, string $method, string $type)
    {
        if (is_null($this->ch)) {
            $this->ch = curl_init();
            curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        }
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array("Content-Type: {$type}"));
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($this->ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->ch, CURLOPT_URL, $this->service_url . $method);
        if (curl_errno($this->ch)) {
            $this->error = curl_error($this->ch);
            return false;
        }
        return curl_exec($this->ch);
    }

    /**
     * Get correct App id for axilink table
     * @param int $order_id
     * @return string
     */
    private function getLastAppId(int $order_id): string
    {
        $query = $this->db->placehold("SELECT app_id FROM __axilink WHERE order_id = ? ORDER BY id DESC, created_date DESC LIMIT 1", $order_id);
        $this->db->query($query);
        $id = $this->db->result('app_id');
        return (!$id ? '0' : (substr($id, 0, strpos($id, (string) $order_id)) + 1)) . $order_id;
    }

    /**
     * @param string $app_id
     * @param int $order_id
     * @param string $xml
     * @return void
     */
    private function saveInDb(string $app_id, int $order_id, string $xml): void
    {
        $query = $this->db->placehold("INSERT INTO __axilink SET ?%", compact('app_id', 'order_id', 'xml'));
        $this->db->query($query);
    }
}