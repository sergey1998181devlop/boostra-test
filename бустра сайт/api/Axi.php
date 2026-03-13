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

    private const LOG_FILE = 'axi.txt';

    private const LOG_FILE_REFINANCE = 'axi_refinance.txt';

    public const FINAL_DECISION_APPROVE = 'Approve';
    public const FINAL_DECISION_DECLINE = 'Decline';

    public const SSP_REPORT = 'SSP_NBKI';

    /** @var string КИ отчет */
    public const CH_REPORT = 'NBKI';

    /** Ключ для результата переключения организации в ответе акси */
    private const ORDER_ORG_SWITCH_RESULT_KEY = 'order_org_switch_result';

    /** Ключ для ID родительской заявки при переключении организации */
    private const ORDER_ORG_SWITCH_PARENT_ORDER_ID_KEY = 'order_org_switch_parent_order_id';

    /** Результат переключения на организацию с КИ */
    private const ORDER_ORG_SWITCH_RESULT_WITH_KI = 'SUCCESS_WITH_ORGANIZATION_SWITCH_2';

    /** Результат переключения на организацию без КИ */
    private const ORDER_ORG_SWITCH_RESULT_WITHOUT_KI = 'SUCCESS_WITH_ORGANIZATION_SWITCH_3';

    /** @var int Максимальное кол-во дней актуальности ССП и КИ отчетов. По факту отчеты актуальны в течение 5 рабочих дней, но для упрощения берем 7 календарных дней */
    public const REPORTS_RELEVANCE_MAX_DAYS = 7;

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

    /**
     * Получить последний NBKI запрос (TYPE_AXILINK_2) по order_id
     *
     * @param int $orderId
     * @return object|null
     */
    public function getLastNbkiRequestByOrderId(int $orderId): ?object
    {
        $scoring = $this->scorings->getLastScoring([
            'order_id' => $orderId,
            'type' => $this->scorings::TYPE_AXILINK_2,
            'status' => $this->scorings::STATUS_COMPLETED,
        ]);

        return $scoring ?: null;
    }

    /**
     * Получить последний NBKI запрос по user_id
     *
     * @param int $userId
     * @return object|null
     */
    public function getLastNbkiRequestByUserId(int $userId): ?object
    {
        if ($userId <= 0) {
            return null;
        }

        $query = $this->db->placehold(
            "SELECT l.created_at FROM ssp_nbki_request_log l
             JOIN __orders o ON o.id = l.order_id
             WHERE o.user_id = ? AND l.request_type = 'NBKI'
             ORDER BY l.id DESC LIMIT 1",
            $userId
        );
        $this->db->query($query);
        return $this->db->result() ?: null;
    }

    /**
     * Получить значение allow_simplified_flow из ответа акси для заявки
     * Читает из таблицы s_axilink XML-атрибут application_e[@allow_simplified_flow]
     *
     * @param int $orderId
     * @return bool|null null если не найден скоринг или не содержит нужных данных
     */
    public function getAxilinkAllowSimplifiedFlow(int $orderId): ?bool
    {
        if ($orderId <= 0) {
            return null;
        }

        $query = $this->db->placehold(
            "SELECT xml FROM s_axilink WHERE order_id = ? ORDER BY id DESC LIMIT 1",
            $orderId
        );
        $this->db->query($query);
        $xml = $this->db->result('xml');

        if (empty($xml)) {
            return null;
        }

        $previous = libxml_use_internal_errors(true);
        $application = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($application === false) {
            return null;
        }

        $nodes = $application->xpath('//application_e');
        if (empty($nodes)) {
            return null;
        }

        $value = strtolower(trim((string) $nodes[0]['allow_simplified_flow']));
        if ($value === '') {
            return null;
        }

        if ($value === 'true') {
            return true;
        }
        if ($value === 'false') {
            return false;
        }

        return null;
    }

    /**
     * Проверить, требует ли заявка запрос в КИ
     *
     * @param int $orderId
     * @return bool
     */
    public function isOrderWithKiRequest(int $orderId): bool
    {
        if ($orderId <= 0) {
            return false;
        }

        $axilinkFlag = $this->getAxilinkAllowSimplifiedFlow($orderId);
        if ($axilinkFlag === true) {
            return false;
        }

        $result = $this->getOrderOrgSwitchResult($orderId);
        if ($result === self::ORDER_ORG_SWITCH_RESULT_WITH_KI) {
            return true;
        }

        $parentResult = $this->getParentOrderOrgSwitchResult($orderId);
        return $parentResult === self::ORDER_ORG_SWITCH_RESULT_WITH_KI;
    }

    /**
     * Проверить, идёт ли заявка по упрощённому флоу (без КИ)
     *
     * @param int $orderId
     * @return bool
     */
    public function isSimplifiedFlowOrder(int $orderId): bool
    {
        if ($orderId <= 0) {
            return false;
        }

        $axilinkFlag = $this->getAxilinkAllowSimplifiedFlow($orderId);
        if ($axilinkFlag === true) {
            return true;
        }

        $result = $this->getOrderOrgSwitchResult($orderId);
        if ($result === self::ORDER_ORG_SWITCH_RESULT_WITHOUT_KI) {
            return true;
        }

        $parentResult = $this->getParentOrderOrgSwitchResult($orderId);
        return $parentResult === self::ORDER_ORG_SWITCH_RESULT_WITHOUT_KI;
    }

    /**
     * Получить результат переключения организации для заявки из order_data
     *
     * @param int $orderId
     * @return string|null
     */
    public function getOrderOrgSwitchResult(int $orderId): ?string
    {
        return $this->order_data->read($orderId, self::ORDER_ORG_SWITCH_RESULT_KEY);
    }

    /**
     * Получить результат переключения организации для родительской заявки из order_data
     *
     * @param int $orderId
     * @return string|null
     */
    public function getParentOrderOrgSwitchResult(int $orderId): ?string
    {
        $parentOrderId = $this->order_data->read($orderId, self::ORDER_ORG_SWITCH_PARENT_ORDER_ID_KEY);
        if (empty($parentOrderId) || !is_numeric($parentOrderId)) {
            return null;
        }

        return $this->getOrderOrgSwitchResult((int) $parentOrderId);
    }

    /**
     * Синхронизация согласия БКИ из ответа Axi
     *
     * 4-ступенчатая логика:
     * 1. Если consent уже true — ничего не делаем
     * 2. Simplified flow → consent=false
     * 3. КИ через РЗС → consent=true
     * 4. Fallback: NBKI лог → consent=true, завершённый скоринг без КИ → consent=false
     *
     * @param int $userId
     * @return bool true если согласие было установлено или уже существовало
     */
    public function syncBkiConsent(int $userId): bool
    {
        try {
            $existingConsent = $this->user_data->read($userId, UserData::BKI_CONSENT);
            $current = json_decode((string) $existingConsent, true);
            if (is_array($current) && !empty($current['consent'])) {
                return true;
            }

            // Обратная совместимость: старое значение "1" (не JSON) — считаем consent=true
            if (!empty($existingConsent) && !is_array($current)) {
                return true;
            }

            $lastOrder = $this->orders->get_last_order($userId);
            if (empty($lastOrder)) {
                return false;
            }

            $orderId = (int) $lastOrder->id;

            // Сценарий: simplified flow → consent=false
            if ($this->isSimplifiedFlowOrder($orderId)) {
                $this->user_data->set($userId, UserData::BKI_CONSENT, json_encode([
                    'consent' => false,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'source' => 'axi_sync',
                    'order_id' => $orderId,
                ], JSON_UNESCAPED_UNICODE));
                return true;
            }

            // Сценарий: КИ через РЗС → consent=true
            if ($this->isOrderWithKiRequest($orderId)) {
                $this->user_data->set($userId, UserData::BKI_CONSENT, json_encode([
                    'consent' => true,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'source' => 'axi_sync',
                    'order_id' => $orderId,
                ], JSON_UNESCAPED_UNICODE));
                return true;
            }

            // Fallback: проверка лога NBKI запросов
            $log = $this->getLastNbkiRequestByUserId($userId);
            if (!empty($log)) {
                $this->user_data->set($userId, UserData::BKI_CONSENT, json_encode([
                    'consent' => true,
                    'timestamp' => $log->created_at ?? date('Y-m-d H:i:s'),
                    'source' => 'axi_sync',
                    'order_id' => $orderId,
                ], JSON_UNESCAPED_UNICODE));
                return true;
            }

            // Fallback: проверка завершённого скоринга без КИ
            $scoring = $this->scorings->getLastScoring([
                'type' => $this->scorings::TYPE_AXILINK_2,
                'user_id' => $userId,
            ]);
            if (!empty($scoring) && (int) $scoring->status === $this->scorings::STATUS_COMPLETED) {
                $this->user_data->set($userId, UserData::BKI_CONSENT, json_encode([
                    'consent' => false,
                    'timestamp' => $scoring->completed ?? date('Y-m-d H:i:s'),
                    'source' => 'axi_sync',
                    'order_id' => $orderId,
                ], JSON_UNESCAPED_UNICODE));
                return true;
            }

            return false;
        } catch (\Throwable $e) {
            $this->open_search_logger->create(
                'Error syncing BKI consent',
                ['user_id' => $userId, 'error' => $e->getMessage()],
                'bki_consent_sync',
                OpenSearchLogger::LOG_LEVEL_ERROR
            );
            return false;
        }
    }

    /** Проверяем были ли в данной МКК недавно запросы в акси с запросом ССП или КИ отчетов */
    public function checkHasRecentlyInquiredReports(int $orderId, int $organizationId): bool
    {
        $order = $this->orders->get_order($orderId);

        // Заявка почему-то не найдена
        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Заявка почему-то не найдены', ['order_id' => $orderId, 'order' => $order], self::LOG_FILE);
            return false;
        }

        $userOrders = $this->orders->get_orders([
            'user_id' => (int)$order->user_id,
            'organization_id' => $organizationId,
        ]);

        // Заявки почему-то не найдены
        if (empty($userOrders)) {
            $this->logging(__METHOD__, '', 'Заявки почему-то не найдены', ['order_id' => (int)$order->id, 'user_orders' => $userOrders], self::LOG_FILE);
            return false;
        }

        $userOrdersId = array_column($userOrders, 'id');

        // Id заявок почему-то не найдены
        if (empty($userOrdersId)) {
            $this->logging(__METHOD__, '', 'Id заявок почему-то не найдены', ['order_id' => (int)$order->id, 'user_orders' => $userOrders, 'user_orders_id' => $userOrdersId], self::LOG_FILE);
            return false;
        }

        // Текущая дата минус 7 дней
        $dateStart = date('Y-m-d 00:00:00', strtotime('-' . self::REPORTS_RELEVANCE_MAX_DAYS . ' days'));

        // 1. Проверяем, есть ли запросы ССП и КИ отчетов по таблице s_axilink
        if ($this->hasRecentlyInquiredReportsByAxilinkTable($order, $userOrdersId, $dateStart)) {
            return true;
        }

        // 2. Проверяем, есть ли запросы ССП и КИ отчетов по таблице ssp_nbki_request_log
        if ($this->hasRecentlyInquiredReportsBySspNbkiRequestLogTable($order, $userOrdersId, $dateStart)) {
            return true;
        }

        return false;
    }

    private function hasRecentlyInquiredReportsByAxilinkTable(stdClass $order, array $ordersId, string $dateStart): bool
    {
        $query = $this->db->placehold(
            '
            SELECT *
            FROM s_axilink
            WHERE order_id IN (?@)
              AND xml NOT LIKE \'%allow_simplified_flow="true"%\'
              AND created_date > ?
            ORDER BY id DESC;', $ordersId, $dateStart
        );

        $this->db->query($query);
        $axiRecords = $this->db->results();

        $this->logging(__METHOD__, '', 'Проверка запросов отчетов для возможности выдачи без КИ по таблице s_axilink', ['order_id' => (int)$order->id, 'axi_records' => $axiRecords], self::LOG_FILE);

        if (empty($axiRecords)) {
            return false;
        }

        foreach ($axiRecords as $axiRecord) {
            if (empty($axiRecord->xml)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $axiRecord->xml по таблице s_axilink', ['order_id' => (int)$order->id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }

            $xml = simplexml_load_string($axiRecord->xml);

            if (empty($xml)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $xml по таблице s_axilink', ['order_id' => (int)$order->id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }

            $applicationDate = (string)$xml->AXI->application_e['ApplicationDate'];
            if (empty($applicationDate)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $applicationDate по таблице s_axilink', ['order_id' => (int)$order->id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }

            try {
                $reportDate = new DateTimeImmutable($applicationDate);
            } catch (Exception $e) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $reportDate по таблице s_axilink', ['order_id' => (int)$order->id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }

            $reportDate = $reportDate->format('Y-m-d H:i:s');

            if ($reportDate > $dateStart) {
                $this->logging(__METHOD__, '', 'Не можем выдать заявку без отчета, т.к. есть запрос отчета по s_axilink', ['order_id' => (int)$order->id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }
        }

        return false;
    }

    private function hasRecentlyInquiredReportsBySspNbkiRequestLogTable(stdClass $order, array $ordersId, string $dateStart): bool
    {
        $query = $this->db->placehold(
            '
            SELECT *
            FROM ssp_nbki_request_log
            WHERE order_id IN (?@)
              AND created_at > ?
            ORDER BY id DESC;', $ordersId, $dateStart
        );

        $this->db->query($query);
        $sspNbkiRecords = $this->db->results() ?: [];

        $this->logging(__METHOD__, '', 'Проверка запросов отчетов для возможности выдачи без КИ по таблице ssp_nbki_request_log', ['order_id' => (int)$order->id, 'ssp_nbki_records' => $sspNbkiRecords], self::LOG_FILE);

        if (empty($sspNbkiRecords)) {
            return false;
        }

        libxml_use_internal_errors(true);

        foreach ($sspNbkiRecords as $sspNbkiRecord) {
            if (!empty($sspNbkiRecord->data)) {
                $fileContent = $sspNbkiRecord->data;
            } else {
                if (empty($sspNbkiRecord->s3_name)) {
                    $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $sspNbkiRecord->data и $sspNbkiRecord->s3_name по таблице ssp_nbki_request_log', ['order_id' => (int)$order->id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                    return true;
                }

                try {
                    $s3_file = $this->s3_api_client->getFileContent($sspNbkiRecord->s3_name);
                    $fileContent = $s3_file->getContents();
                } catch (Exception $e) {
                    $error = [
                        'Ошибка: ' . $e->getMessage(),
                        'Файл: ' . $e->getFile(),
                        'Строка: ' . $e->getLine(),
                        'Подробности: ' . $e->getTraceAsString()
                    ];

                    $this->logging(__METHOD__, '', 'Ошибка получения файла из S3', ['order_id' => (int)$order->id, 'ssp_nbki_record' => $sspNbkiRecord, 'error' => $error], self::LOG_FILE);
                    return true;
                }
            }

            $xml = simplexml_load_string($fileContent);

            if (empty($xml)) {
                $xml = preg_replace('/encoding="Windows-1251"/', 'encoding="UTF-8"', $fileContent);
                $xml = simplexml_load_string($xml);

                if (empty($xml)) {
                    $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $xml по таблице ssp_nbki_request_log', ['order_id' => (int)$order->id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                    return true;
                }
            }

            if ($sspNbkiRecord->request_type === $this->axi::SSP_REPORT) {
                $applicationDate = (string)$xml['ДатаЗапроса'];
            } else {
                $applicationDate = (string)$xml->prequest->req->InquiryReq->ConsentReq->consentDate;
            }

            if (empty($applicationDate)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $applicationDate по таблице ssp_nbki_request_log', ['order_id' => (int)$order->id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                return true;
            }

            try {
                $reportDate = new DateTimeImmutable($applicationDate);
            } catch (Exception $e) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $reportDate по таблице ssp_nbki_request_log', ['order_id' => (int)$order->id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                return true;
            }

            $reportDate = $reportDate->format('Y-m-d H:i:s');

            if ($reportDate > $dateStart) {
                $this->logging(__METHOD__, '', 'Не можем выдать заявку без отчета, т.к. есть запрос отчета по ssp_nbki_request_log', ['order_id' => (int)$order->id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                return true;
            }
        }

        return false;
    }
}
