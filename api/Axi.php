<?php

require_once 'Simpla.php';

/**
 * Получение отчетов из акси
 */
class Axi extends Simpla
{
    private const AXI_URL = 'http://158.160.12.6:8080/axilink-1.0/rpc/';
    private const CREATE_APPLICATION = 'CreateApplication';
    private const GET_APPLICATION = 'GetApplication';

    private const PRODUCT_CATEGORY_REFINANCE = 'boostra2_REFIN';
    private const PRODUCT_CODE_REFINANCE = 'ps_boostra2_REFIN';

    private const PRODUCT_CATEGORY_SELF_DEC = 'boostra2_SSP';
    private const PRODUCT_CODE_SELF_DEC = 'ps_boostra2_SSP';

    private const PRODUCT_CATEGORY_IDX = 'boostra2_IDX';
    private const PRODUCT_CODE_IDX = 'ps_boostra2_IDX';

    private const LOG_FILE = 'self_dec.txt';

    private const LOG_FILE_REFINANCE = 'axi_refinance.txt';

    public const FINAL_DECISION_APPROVE = 'Approve';
    public const FINAL_DECISION_DECLINE = 'Decline';

    public const SSP_REPORT = 'SSP_NBKI';

    /** @var string КИ отчет */
    public const CH_REPORT = 'NBKI';

    /**
     * Есть ли у клиента самозапрет
     *
     * @param object $order
     * @param string $applicationId
     * @return bool|string
     */
    public function createSelfDecApplication(object $order, string $applicationId)
    {
        $date = date('Y-m-d\TH:i:s.vP');
        $endDate = date('Y-m-d\TH:i:s.vP', strtotime('+5days'));
        $phone_mobile = substr($order->phone_mobile, -10);

        $order->birth = date('Y-m-d', strtotime($order->birth));
        $order->subdivision_code = preg_replace('/(\d{3})(\d{3})/', '$1-$2', str_replace('-', '', $order->subdivision_code));
        $passSeria = str_replace([' ', '-'], '', $order->passport_serial);
        $passNumber = substr($passSeria, 4);
        $passSeria = substr($passSeria, 0, 4);
        $gender = ($order->gender == 'male') ? 1 : 2;
        $xml = '<Application DeliveryOptionCode="boostra2" ProcessingRequestType="DM">
            <CreditRequest ProductCategory="' . self::PRODUCT_CATEGORY_SELF_DEC . '" ProductCode="' . self::PRODUCT_CODE_SELF_DEC . '"></CreditRequest>
            <AXI>
                <application_e
                    dss_name="FICO_4_10_v2"
                    ApplicationDate="' . $date . '"
                    ApplicationId="' . $applicationId . '"
                    call_name="START"
                    pass_seria="' . $passSeria . '"
                    pass_number="' . $passNumber . '"
                    pass_date_issue="' . date('Y-m-d', strtotime($order->passport_date)) . '"
                    pass_issued="' . htmlspecialchars($order->passport_issued) . '"
                    pass_code="' . $order->subdivision_code . '"
                    pass_region_code=""
                    client_birthplace="' . Helpers::getSafeStringForXml($order->birth_place) . '"
                    client_birthdate="' . $order->birth . '"
                    client_middlename="' . $order->patronymic . '"
                    client_name="' . $order->firstname . '"
                    client_surname="' . $order->lastname . '"
                    person_INN="' . $order->inn . '"
                    gender="' . $gender . '"
                    consentDate="' . $date . '" 
                    consentEndDate="' . $endDate . '" 
                    consentFlag="Y" 
                    income_amount="' . intval($order->income_base) . '"
                    initial_limit="' . $order->amount . '"
                    initial_maturity="' . $order->period . '"
                    mob_phone_num="' . $phone_mobile . '"
                    >
                </application_e>
            </AXI></Application>';

        return $this->send($xml, self::CREATE_APPLICATION, 'application/xml');
    }

    public function getRefinance(object $order, string $applicationId)
    {
        $date = date('Y-m-d\TH:i:s.vP');
        $endDate = date('Y-m-d\TH:i:s.vP', strtotime('+5days'));
        $phone_mobile = substr($order->phone_mobile, -10);

        $order->birth = date('Y-m-d', strtotime($order->birth));
        // Форматирует код подразделения паспорта в формат XXX-XXX
        $order->subdivision_code = preg_replace('/(\d{3})(\d{3})/', '$1-$2', str_replace('-', '', $order->subdivision_code));
        $passSeria = str_replace([' ', '-'], '', $order->passport_serial);
        $passNumber = substr($passSeria, 4);
        $passSeria = substr($passSeria, 0, 4);
        $gender = ($order->gender == 'male') ? 1 : 2;
        $xml = '<Application DeliveryOptionCode="boostra2" ProcessingRequestType="DM">
            <CreditRequest ProductCategory="' . self::PRODUCT_CATEGORY_REFINANCE . '" ProductCode="' . self::PRODUCT_CODE_REFINANCE . '"></CreditRequest>
            <AXI>
                <application_e
                    dss_name="FICO_4_10_v2"
                    ApplicationDate="' . $date . '"
                    ApplicationId="' . $applicationId . '"
                    call_name="START"
                    pass_seria="' . $passSeria . '"
                    pass_number="' . $passNumber . '"
                    pass_date_issue="' . date('Y-m-d', strtotime($order->passport_date)) . '"
                    pass_issued="' . htmlspecialchars($order->passport_issued) . '"
                    pass_code="' . $order->subdivision_code . '"
                    pass_region_code=""
                    client_birthplace="' . Helpers::getSafeStringForXml($order->birth_place) . '"
                    client_birthdate="' . $order->birth . '"
                    client_middlename="' . $order->patronymic . '"
                    client_name="' . $order->firstname . '"
                    client_surname="' . $order->lastname . '"
                    person_INN="' . $order->inn . '"
                    gender="' . $gender . '"
                    consentDate="' . $date . '" 
                    consentEndDate="' . $endDate . '" 
                    consentFlag="Y" 
                    income_amount="' . intval($order->income_base) . '"
                    initial_limit="' . $order->amount . '"
                    initial_maturity="' . $order->period . '"
                    mob_phone_num="' . $phone_mobile . '"
                    >
                </application_e>
            </AXI></Application>';

        $response = $this->send($xml, self::CREATE_APPLICATION, 'application/xml');

        $this->logging(__METHOD__, 'getRefinance', ['request' => $xml], ['response' => $response], self::LOG_FILE_REFINANCE);

        return $response;
    }

    /**
     * Получить информацию о самозапрете по ранее созданному запросу
     *
     * @param string $applicationId
     * @return bool|string
     */
    public function getApplication(string $applicationId)
    {
        $data = [
            'applicationId' => $applicationId
        ];

        return $this->send($data, self::GET_APPLICATION, 'multipart/form-data');
    }

    /**
     * @param array|string $data
     * @param string $method
     * @param string $type
     * @return bool|string
     */
    private function send($data, string $method, string $type)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => self::AXI_URL . $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "Content-Type: {$type}", "charset:'UTF-8"
            ]
        ));

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            $this->logging(__METHOD__, '', 'Ошибка при получении результата из акси', ['data' => $data, 'error' => $error], self::LOG_FILE);
            return false;
        }

        return $response;
    }

    /**
     * Сверка номера телефона с ФИО и датой рождения по IDX
     *
     * @param stdClass $user Пользователь, с короткого получаем ФИО, дату рождения и другие паспортные данные
     * @param string $phone Номер телефона, который сверяем
     * @param string $applicationId
     * @return bool|string
     */
    public function createIdxApplication(stdClass $user, string $phone, string $applicationId)
    {
        $date = date('Y-m-d\TH:i:s.vP');
        $passSeria = str_replace([' ', '-'], '', $user->passport_serial);
        $passNumber = substr($passSeria, 4);
        $passSeria = substr($passSeria, 0, 4);
        $endDate = date('Y-m-d\TH:i:s.vP', strtotime('+5days'));
        $gender = ($user->gender == 'male') ? 1 : 2;

        $xml = '<Application DeliveryOptionCode="boostra2" ProcessingRequestType="DM">
              <CreditRequest ProductCategory="' . self::PRODUCT_CATEGORY_IDX . '" ProductCode="' . self::PRODUCT_CODE_IDX . '"></CreditRequest>
            <AXI>
                <application_e
                    dss_name="FICO_4_10_v2"
                    ApplicationDate="' . $date . '"
                    ApplicationId="' . $applicationId . '"
                    call_name="START"
                    pass_seria="' . $passSeria . '"
                    pass_number="' . $passNumber . '"
                    pass_date_issue="' . date('Y-m-d', strtotime($user->passport_date)) . '"
                    pass_issued="' . htmlspecialchars($user->passport_issued) . '"
                    pass_code="' . preg_replace('/(\d{3})(\d{3})/', '$1-$2', str_replace('-', '', $user->subdivision_code)) . '"
                    pass_region_code=""
                    client_birthplace="' . Helpers::getSafeStringForXml($user->birth_place) . '"
                    client_birthdate="' . date('Y-m-d', strtotime($user->birth)) . '"
                    client_middlename="' . $user->patronymic . '"
                    client_name="' . $user->firstname . '"
                    client_surname="' . $user->lastname . '"
                    person_INN="' . $user->inn . '"
                    gender="' . $gender . '"
                    consentDate="' . $date . '" 
                    consentEndDate="' . $endDate . '" 
                    consentFlag="Y" 
                    mob_phone_num="' . $phone . '"
                    >
                </application_e>
            </AXI></Application>';

        return $this->send($xml, self::CREATE_APPLICATION, 'application/xml');
    }


    /**
     * Получить нужный тип скоринга акси (аксилинк или аксиНБКИ) для заявки
     *
     * @param stdClass $order
     * @return int
     */
    public function getAxiScoringType(stdClass $order): int
    {
        return $order->utm_source === $this->orders::UTM_RESOURCE_AUTO_APPROVE ?
            $this->scorings::TYPE_AXILINK :
            $this->scorings::TYPE_AXILINK_2;
    }
}
