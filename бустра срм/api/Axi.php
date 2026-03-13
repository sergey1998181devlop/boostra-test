<?php

require_once 'Simpla.php';

/**
 * Получение отчетов из акси
 */
class Axi extends Simpla
{
    /**
     * @var int Максимальное кол-во дней актуальности ССП и КИ отчетов. По факту отчеты актуальны в течение 5 рабочих дней,
     * но для упрощения берем 6 календарных дней (дата запроса отчета считается 1-ым днем)
     */
    public const REPORTS_RELEVANCE_MAX_DAYS = 6;

    /** @var string ССП отчет */
    public const SSP_REPORT = 'SSP_NBKI';

    /** @var string КИ отчет */
    public const CH_REPORT = 'NBKI';

    /** @var int Время ожидания скоринга (в минутах). Не уменьшать, так как отчеты изначально получаем во время скоринга акси, поэтому необходимо ждать завершения акси */
    public const AXI_SCORING_TIME_LIMIT = 3;

    /** @var array Статусы, когда скоринг акси еще выполняется */
    public const AXI_SCORING_PROGRESS_STATUSES = [
        Scorings::STATUS_NEW,
        Scorings::STATUS_PROCESS,
        Scorings::STATUS_WAIT
    ];

    private const LOG_FILE = 'axi.txt';

    /**
     * Получение из БД записи о результате акси для данной заявке
     *
     * @param $orderId
     * @return stdClass|bool
     */
    public function getAppData($orderId)
    {
        $query = $this->db->placehold("SELECT * FROM s_axilink WHERE order_id = ? ORDER BY id DESC LIMIT 1", $orderId);
        $this->db->query($query);
        return $this->db->result();
    }

    /**
     * Запрашивает и сохраняет новый отчет из акси
     *
     * @param stdclass $order
     * @param stdClass $appData
     * @param string $reportType
     * @return bool
     */
    public function inquireNewReportFromAxi(stdclass $order, stdClass $appData, string $reportType): bool
    {
        $applicationId = $this->getNewApplicationId($order, $appData, $reportType);

        if ($applicationId === null) {
            $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
                'Не получен applicationId для ' . $this->getReportTypeRus($reportType) . ' отчета: ' . $applicationId, 'axi.txt');
            return false;
        }

        $this->logging(__METHOD__, '', ['order_id' => $order->order_id],
            'Получен новый applicationId для ' . $this->getReportTypeRus($reportType) . ' отчета: ' . $applicationId, 'axi.txt');

        // По $applicationId запрашиваем из акси новый ССП или КИ отчет и сохраняем его
        $this->dbrainAxi->saveChData($applicationId, $order->order_id, [$this->dbrainAxi::STATUS_RESPONSE, $this->dbrainAxi::STATUS_REQUEST], [$reportType]);

        return true;
    }

    /**
     * Добавляет скоринг акси, если с момента последнего добавления акси прошло более 5 минут
     *
     * @param stdClass $order
     * @return bool
     */
    public function addAxiScoring(stdClass $order): bool
    {
        if ($this->needAddAxiScoring($order)) {
            $this->scorings->add_scoring([
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'status' => Scorings::STATUS_NEW,
                'created' => date('Y-m-d H:i:s'),
                'type' => $this->getAxiScoringType($order)
            ]);
            return true;
        }

        return false;
    }

    /**
     * Проверяет, нужно ли добавлять скоринг акси
     *
     * @param stdClass $order
     * @return bool
     */
    public function needAddAxiScoring(stdClass $order): bool
    {
        $scoringType = $this->getAxiScoringType($order);
        $lastScoring = $this->scorings->get_last_type_scoring($scoringType, $order->user_id);

        if (empty($lastScoring)) {
            return true;
        }

        $minutesAfterCreatingLastScoring = (int)((time() - (int)strtotime($lastScoring->created)) / 60);

        // Не добавляем скоринг, если скоринг еще выполняется ИЛИ предыдущий скоринг был добавлен недавно
        if (in_array((int)$lastScoring->status, self::AXI_SCORING_PROGRESS_STATUSES) || $minutesAfterCreatingLastScoring < self::AXI_SCORING_TIME_LIMIT) {
            return false;
        }

        return true;
    }

    /**
     * Получить нужный тип скоринга акси (аксилинк или аксиНБКИ) для заявки
     *
     * @param stdClass $order
     * @return int
     */
    public function getAxiScoringType(stdClass $order): int
    {
        return $order->utm_source === $this->orders::UTM_SOURCE_CRM_AUTO_APPROVE ?
            $this->scorings::TYPE_AXILINK :
            $this->scorings::TYPE_AXILINK_2;
    }

    /**
     * Обновляет ССП или КИ отчеты на стороне акси и возвращает новые applicationId по заявке
     *
     * @param stdClass $order
     * @param stdClass $appData
     * @param string $reportType
     * @return string|null
     */
    public function getNewApplicationId(stdClass $order, stdClass $appData, string $reportType): ?string
    {
        $xml = simplexml_load_string($appData->xml);

        $inn = (string)$xml->AXI->application_e->attributes()->person_INN;
        if (!empty($order->inn)) {
            $inn = $order->inn; // берем актуальный ИНН из таблицы s_users
        }

        $xmlFormatted = [
            "Application" => [
                "@DeliveryOptionCode" => (string)$xml->attributes()->DeliveryOptionCode,
                "@ProcessingRequestType" => (string)$xml->attributes()->ProcessingRequestType,
                "CreditRequest" => [
                    "@ProductCategory" => (string)$xml->CreditRequest->attributes()->ProductCategory,
                    "@ProductCode" => (string)$xml->CreditRequest->attributes()->ProductCode
                ],
                "AXI" => [
                    "application_e" => [
                        "@client_name" => (string)$xml->AXI->application_e->attributes()->client_name,
                        "@client_surname" => (string)$xml->AXI->application_e->attributes()->client_surname,
                        "@client_birthdate" => (string)$xml->AXI->application_e->attributes()->client_birthdate,
                        "@client_birthplace" => Helpers::getSafeStringForXml($xml->AXI->application_e->attributes()->client_birthplace),
                        "@mob_phone_num" => (string)$xml->AXI->application_e->attributes()->mob_phone_num,
                        "@ApplicationDate" => (string)$xml->AXI->application_e->attributes()->ApplicationDate,
                        "@pass_number" => (string)$xml->AXI->application_e->attributes()->pass_number,
                        "@pass_seria" => (string)$xml->AXI->application_e->attributes()->pass_seria,
                        "@pass_date_issue" => (string)$xml->AXI->application_e->attributes()->pass_date_issue,
                        "@pass_code" => (string)$xml->AXI->application_e->attributes()->pass_code,
                        "@pass_region_code" => (string)$xml->AXI->application_e->attributes()->pass_region_code,
                        "@income_amount" => (string)$xml->AXI->application_e->attributes()->income_amount,
                        "@initial_limit" => (string)$xml->AXI->application_e->attributes()->initial_limit,
                        "@initial_maturity" => (string)$xml->AXI->application_e->attributes()->initial_maturity,
                        "@consentDate" => (string)$xml->AXI->application_e->attributes()->consentDate,
                        "@consentEndDate" => (string)$xml->AXI->application_e->attributes()->consentEndDate,
                        "@consentFlag" => (string)$xml->AXI->application_e->attributes()->consentFlag,
                        "@person_INN" => $inn
                    ]
                ]

            ]
        ];

        $xmlText = ('<?xml version="1.0" encoding="UTF-8"?><request>
                <callName>' . $reportType . '</callName>
                <text><![CDATA[
<Application DeliveryOptionCode="' . $xmlFormatted['Application']['@DeliveryOptionCode'] . '" ProcessingRequestType="' . $xmlFormatted['Application']['@ProcessingRequestType'] . '">
 <CreditRequest ProductCategory="' . $xmlFormatted['Application']['CreditRequest']['@ProductCategory'] . '" ProductCode="' . $xmlFormatted['Application']['CreditRequest']['@ProductCode'] . '" />
            <AXI>
                <application_e client_birthdate="' . $xmlFormatted['Application']['AXI']['application_e']['@client_birthdate'] . '" client_name="' . $xmlFormatted['Application']['AXI']['application_e']['@client_name'] . '" client_surname="' . $xmlFormatted['Application']['AXI']['application_e']['@client_surname'] .
            '" mob_phone_num="' . $xmlFormatted['Application']['AXI']['application_e']['@mob_phone_num'] . '" client_birthplace="' . $xmlFormatted['Application']['AXI']['application_e']['@client_birthplace'] . '." ApplicationDate="' . $xmlFormatted['Application']['AXI']['application_e']['@ApplicationDate'] . '" pass_number="' . $xmlFormatted['Application']['AXI']['application_e']['@pass_number'] .
            '" pass_seria="' . $xmlFormatted['Application']['AXI']['application_e']['@pass_seria'] . '" pass_date_issue="' . $xmlFormatted['Application']['AXI']['application_e']['@pass_date_issue'] . '" pass_code="' . $xmlFormatted['Application']['AXI']['application_e']['@pass_code'] .
            '" income_amount="' . $xmlFormatted['Application']['AXI']['application_e']['@income_amount'] . '" initial_limit="' . $xmlFormatted['Application']['AXI']['application_e']['@initial_limit'] . '" initial_maturity="' . $xmlFormatted['Application']['AXI']['application_e']['@initial_maturity'] .
            '" consentDate="' . $xmlFormatted['Application']['AXI']['application_e']['@consentDate'] . '" consentEndDate="' . $xmlFormatted['Application']['AXI']['application_e']['@consentEndDate'] . '" consentFlag="' . $xmlFormatted['Application']['AXI']['application_e']['@consentFlag'] . '" person_INN="' . $xmlFormatted['Application']['AXI']['application_e']['@person_INN'] . '"/>
            </AXI>
        </Application>
    ]]></text>
    <cacheTTL>0</cacheTTL>
</request>');

        $headers = [
            'Content-Type: application/xml'
        ];

        $this->dbrainAxi->set_organization_params($order);
        $serviceUrl = $this->dbrainAxi->getServiceUrl() . 'rpc/v2/sync-application';

        $response = $this->makeCurlRequest($xmlText, $headers, $serviceUrl);
        $xmlResponse = simplexml_load_string($response);

        if (!empty($xmlResponse) && isset($xmlResponse->applicationId)) {
            return (string)$xmlResponse->applicationId;
        }

        // Если xml слишком большой (больше 30 тыс строк), то simplexml_load_string не может его прочитать и возвращает false
        // с warning "CData section too big found". Поэтому пытаемся получить applicationId с помощью регулярного выражения
        preg_match('/<applicationId>(.*)<\/applicationId>/', (string)$response, $matches);
        $applicationId = $matches[1];

        if (!empty($applicationId)) {
            return $applicationId;
        }

        return null;
    }

    /**
     * Выполнить CURL запрос
     *
     * @param string $data
     * @param array $headers
     * @param string $url
     * @return bool|string
     */
    private function makeCurlRequest(string $data, array $headers, string $url)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 150, // не уменьшать, так как у больших КИ отчетов запрос может идти долго
            CURLOPT_TIMEOUT => 150, // не уменьшать, так как у больших КИ отчетов запрос может идти долго
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $headers
        ));

        $response = curl_exec($curl);

        $this->logging(__METHOD__, $url, $data, '', 'axi.txt');

        $error = curl_error($curl);

        if (!empty($error)) {
            $this->logging(__METHOD__, $url, $data, ['curl_error' => $error], 'axi.txt');
        }

        curl_close($curl);
        return $response;
    }

    /**
     * Получить название типа отчета
     *
     * @param string $reportType
     * @return string
     */
    public function getReportTypeRus(string $reportType): string
    {
        if ($reportType === self::SSP_REPORT) {
            return 'ССП';
        }

        if ($reportType === self::CH_REPORT) {
            return 'КИ';
        }

        return '';
    }

    /** Проверяем были ли в данной МКК недавно запросы в акси с запросом ССП или КИ отчетов */
    public function checkHasRecentlyInquiredReports(int $orderId, int $organizationId): bool
    {
        $order = $this->orders->get_order($orderId);

        // Заявка почему-то не найдена
        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Заявка почему-то не найдены', ['order_id' => $orderId, 'order' => $order], self::LOG_FILE);
            return true;
        }

        $userOrders = $this->orders->get_orders([
            'user_id' => (int)$order->user_id,
            'organization_id' => $organizationId,
        ]);

        // Заявки почему-то не найдены
        if (empty($userOrders)) {
            $this->logging(__METHOD__, '', 'Заявки почему-то не найдены', ['order_id' => (int)$order->order_id, 'user_orders' => $userOrders], self::LOG_FILE);
            return true;
        }

        $userOrdersId = array_column($userOrders, 'order_id');

        // Id заявок почему-то не найдены
        if (empty($userOrdersId)) {
            $this->logging(__METHOD__, '', 'Id заявок почему-то не найдены', ['order_id' => (int)$order->order_id, 'user_orders' => $userOrders, 'user_orders_id' => $userOrdersId], self::LOG_FILE);
            return true;
        }

        // Текущая дата минус self::REPORTS_RELEVANCE_MAX_DAYS дней
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

    public function hasRecentlyInquiredReportsByAxilinkTable(stdClass $order, array $ordersId, string $dateStart): bool
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

        $this->logging(__METHOD__, '', 'Проверка запросов отчетов для возможности выдачи без КИ по таблице s_axilink', ['order_id' => (int)$order->order_id, 'axi_records' => $axiRecords], self::LOG_FILE);

        if (empty($axiRecords)) {
            return false;
        }

        foreach ($axiRecords as $axiRecord) {
            if (empty($axiRecord->xml)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $axiRecord->xml по таблице s_axilink, отказываем по заявке', ['order_id' => (int)$order->order_id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }

            $xml = simplexml_load_string($axiRecord->xml);

            if (empty($xml)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $xml по таблице s_axilink, отказываем по заявке', ['order_id' => (int)$order->order_id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }

            $applicationDate =  (string)$xml->AXI->application_e['ApplicationDate'];
            if (empty($applicationDate)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $applicationDate по таблице s_axilink, отказываем по заявке', ['order_id' => (int)$order->order_id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }

            try {
                $reportDate = new DateTimeImmutable($applicationDate);
            } catch (Throwable $error) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $reportDate по таблице s_axilink, отказываем по заявке', ['order_id' => (int)$order->order_id, 'axi_record' => $axiRecord, 'error' => $error], self::LOG_FILE);
                return true;
            }

            $reportDate = $reportDate->format('Y-m-d H:i:s');

            if ($reportDate > $dateStart) {
                $this->logging(__METHOD__, '', 'Не можем выдать заявку без отчета, т.к. есть запрос отчета по s_axilink, отказываем по заявке', ['order_id' => (int)$order->order_id, 'axi_record' => $axiRecord], self::LOG_FILE);
                return true;
            }
        }

        return false;
    }

    public function hasRecentlyInquiredReportsBySspNbkiRequestLogTable(stdClass $order, array $ordersId, string $dateStart): bool
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

        $this->logging(__METHOD__, '', 'Проверка запросов отчетов для возможности выдачи без КИ по таблице ssp_nbki_request_log', ['order_id' => (int)$order->order_id, 'ssp_nbki_records' => $sspNbkiRecords], self::LOG_FILE);

        if (empty($sspNbkiRecords)) {
            return false;
        }

        libxml_use_internal_errors(true);

        foreach ($sspNbkiRecords as $sspNbkiRecord) {
            if (!empty($sspNbkiRecord->data)) {
                $fileContent = $sspNbkiRecord->data;
            } else {
                if (empty($sspNbkiRecord->s3_name)) {
                    $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $sspNbkiRecord->data и $sspNbkiRecord->s3_name по таблице ssp_nbki_request_log, отказываем по заявке', ['order_id' => (int)$order->order_id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                    return true;
                }

                try {
                    $s3_file = $this->s3_api_client->getFileContent($sspNbkiRecord->s3_name);
                    $fileContent = $s3_file->getContents();
                } catch (Throwable $error) {
                    $this->logging(__METHOD__, '', 'Ошибка получения файла из S3', ['order_id' => (int)$order->order_id, 'ssp_nbki_record' => $sspNbkiRecord, 'error' => $error], self::LOG_FILE);
                    return true;
                }
            }

            $xml = simplexml_load_string($fileContent);

            if (empty($xml)) {
                $xml = preg_replace('/encoding="Windows-1251"/', 'encoding="UTF-8"', $fileContent);
                $xml = simplexml_load_string($xml);

                if (empty($xml)) {
                    $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $xml по таблице ssp_nbki_request_log, отказываем по заявке', ['order_id' => (int)$order->order_id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                    return true;
                }
            }

            if ($sspNbkiRecord->request_type === self::SSP_REPORT) {
                $applicationDate = (string)$xml['ДатаЗапроса'];
            } else {
                $applicationDate = (string)$xml->prequest->req->InquiryReq->ConsentReq->consentDate;
            }

            if (empty($applicationDate)) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $applicationDate по таблице ssp_nbki_request_log, отказываем по заявке', ['order_id' => (int)$order->order_id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                return true;
            }

            try {
                $reportDate = new DateTimeImmutable($applicationDate);
            } catch (Throwable $error) {
                $this->logging(__METHOD__, '', 'Не можем определить дату запроса отчета по $reportDate по таблице ssp_nbki_request_log, отказываем по заявке', ['order_id' => (int)$order->order_id, 'ssp_nbki_record' => $sspNbkiRecord, 'error' => $error], self::LOG_FILE);
                return true;
            }

            $reportDate = $reportDate->format('Y-m-d H:i:s');

            if ($reportDate > $dateStart) {
                $this->logging(__METHOD__, '', 'Не можем выдать заявку без отчета, т.к. есть запрос отчета по ssp_nbki_request_log, отказываем по заявке', ['order_id' => (int)$order->order_id, 'ssp_nbki_record' => $sspNbkiRecord], self::LOG_FILE);
                return true;
            }
        }

        return false;
    }
}
