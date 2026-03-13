<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Traits\BKIHelperTrait;

require_once __DIR__ . '/Traits/BKIHelperTrait.php';

abstract class PytonReportsAbstract extends Simpla
{
    use BKIHelperTrait;

    private const ORGANIZATION_DATA_KEY = 'adapter_nbki';

    private static ?Client $client = null;

    private string $host = 'http://185.182.111.110:9010/';

    public function run_scoring($scoring_id): void
    {
        $scoring = $this->scorings->get_scoring($scoring_id);

        if (!empty($scoring)) {
            $order = $this->orders->get_order((int)$scoring->order_id);
            if (!empty($order)) {
                $isCreditReportsDisabled = $this->order_data->read($order->order_id ?? $order->id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS);
                if (!empty($isCreditReportsDisabled)) {
                    $update = ['status' => $this->scorings::STATUS_ERROR, 'string_result' => 'Проверка отключена по этой заявке (не нужна КИ)'];
                }
                else {
                    $update = $this->process($order);
                }
            } else {
                $update = ['status' => $this->scorings::STATUS_ERROR, 'string_result' => 'не найдена заявка'];
            }
        } else {
            $update = ['status' => $this->scorings::STATUS_ERROR, 'string_result' => 'Скоринг не найден'];
        }

        $update['end_date'] = date('Y-m-d H:i:s');
        $this->scorings->update_scoring($scoring_id, $update);
    }

    public function process(object $order): array
    {
        try {
            $organizationData = $this->getOrganizationData($order->organization_id);
            $report = $this->getReport($order, $organizationData);

            $update = [
                'status' => $this->scorings::STATUS_COMPLETED,
                'success' => 1,
                'string_result' => 'Отчет запрошен',
                'body' => $report,
            ];

            $this->saveReport($report, $order);
            $update['string_result'] = 'Отчет сохранен.';

            $this->saveReportRequest($order, $organizationData->member_code);
            $update['string_result'] .= ' Запрос сохранен.';
        } catch (Throwable $e) {
            $update['status'] = $this->scorings::STATUS_ERROR;
            $update['string_result'] = 'Возникла ошибка!';

            $this->log(__METHOD__, '', ['order_id' => $order->order_id], ['err' => $e]);
        }

        return $update;
    }

    public function saveReportRequest(object $order, string $memberCode): void
    {
        $data = [
            'first_name' => $order->firstname,
            'last_name' => $order->lastname,
            'middle_name' => $this->preparePatronymic($order->patronymic),
            'request_type' => $this->getRequestType(),
            'member_code' => $memberCode,
            'limit' => 1
        ];

        $json = json_encode($data);
        $res = $this->sendRequest($this->getLogsUrl(), $json);

        $result = json_decode($res);

        if (empty($result) || !is_array($result) || empty($result[0])) {
            throw new RuntimeException('Requests not valid - ' . $res);
        }

        $latestData = $result[0];

        $s3Name = $this->ssp_nbki_request_log->saveInS3($latestData->nbki_request, $order->order_id, $this->getReportType());

        $this->ssp_nbki_request_log->saveNewLog([
            'request_type' => $this->getReportType(),
            'data' => $latestData->nbki_request,
            'created_at' => date('Y-m-d H:i:s'),
            'order_id' => $order->order_id,
            's3_name' => $s3Name,
        ]);
    }

    public function isValidXML($content): bool
    {
        libxml_clear_errors();
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        $errors = libxml_get_errors();

        if ((empty($xml) || !empty($errors)) && stripos($content, 'encoding="windows-1251"') !== false) {
            libxml_clear_errors();
            $content = str_ireplace('encoding="windows-1251"', 'encoding="UTF-8"', $content);
            $xml = simplexml_load_string($content);
            $errors = libxml_get_errors();

            if (empty($xml) || !empty($errors)) {
                libxml_clear_errors();
                $content = iconv("windows-1251", "UTF-8", $content);
                $xml = simplexml_load_string($content);
                $errors = libxml_get_errors();
            }
        }

        libxml_clear_errors();

        return $xml !== false && empty($errors);
    }

    /**
     * @throws Exception
     */
    protected function getReport(object $order, object $organization): string
    {
        $data = $this->buildReportRequestData($order, $organization);

        $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        $report = $this->sendRequest($this->getReportUrl(), $json);

        if (empty($report) || !$this->isValidXML($report)) {
            $this->log(__METHOD__, '', ['order' => $order->order_id], [
                'error' => 'В ответе не валидный xml',
                'report' => $report
            ]);

            throw new RuntimeException('В ответе не валидный xml');
        }

        return $report;
    }

    /**
     * @throws Exception
     */
    private function saveReport($report, $order): void
    {
        $report = $this->prepareReport($report);
        $filename = $this->buildFileName($order->order_id);

        try {
            $s3Name = $this->saveReportInS3($report, $filename);

            $fileId = $this->credit_history->insertRow([
                'user_id' => $order->user_id,
                'order_id' => $order->order_id,
                'type' => $this->getReportType(),
                'file_name' => $filename,
                's3_name' => $s3Name,
                'date_create' => date('Y-m-d H:i:s'),
            ]);

            if (empty($fileId)) {
                throw new RuntimeException('Не удалось сохранить файл в s_credit_histories');
            }
        } catch (Throwable $e) {
            $this->log(__METHOD__, '', ['order_id' => $order->order_id], $e->getMessage());
            $this->saveReportInDisc($report, $filename);
        }
    }

    /**
     * @throws Exception
     */
    private function saveReportInS3(string $content, string $filename): string
    {
        $s3_name = $this->getReportPathS3() . date('Ymd') . '/' . $filename;
        $this->s3_api_client->putFileBody($content, $s3_name);

        return $s3_name;
    }

    /**
     * @throws Exception
     */
    private function saveReportInDisc(string $content, string $filename): string
    {
        $directory = $this->getReportPathDisc();

        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }

        $savePath = $directory . $filename;
        $fp = fopen($savePath, "wb");

        $writeResult = fwrite($fp, $content);
        fclose($fp);

        if ($writeResult === false) {
            throw new Exception('Не удалось записать отчет на диск');
        }

        return $savePath;
    }

    private function prepareReport(string $report): string
    {
        return iconv("UTF-8", "windows-1251", $report);
    }

    private function buildFileName(string $orderId): string
    {
        return $orderId . '.xml';
    }

    private function sendRequest(string $api, string $json): ?string
    {
        $client = $this->getHttpClient();

        try {
            $response = $client->post($api, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => $json,
            ]);

            $this->log(__METHOD__, $api, $json, [
                'status' => 'Success',
                'code' => $response->getStatusCode()
            ]);

            return $response->getBody()->getContents();

        } catch (GuzzleException $e) {
            $errorMsg = $e->getMessage();
            $errorCode = $e->getCode();

            $this->log(__METHOD__, $api, $json, [
                'status' => 'Error',
                'code' => $errorCode,
                'msg' => $errorMsg
            ]);

            throw new RuntimeException($errorMsg, $errorCode);
        }
    }

    private function getHttpClient(): Client
    {
        if (self::$client === null) {
            self::$client = new Client([
                'base_uri' => $this->host,
                'timeout' => 150,
                'connect_timeout' => 150,
            ]);
        }

        return self::$client;
    }

    protected function getOrganizationData(int $organizationId): object
    {
        $organizationData = $this->organizations_data->get_data($organizationId, self::ORGANIZATION_DATA_KEY);

        if (empty($organizationData)) {
            throw new RuntimeException('Organization data not found with id ' . $organizationId);
        }

        if (!is_object($organizationData)) {
            throw new RuntimeException('Data not valid for organization_id ' . $organizationId);
        }

        return $organizationData;
    }

    protected function getInnFromOtherOrders(int $clientId, int $organizationId): ?string
    {
        $callName = 'EXIT';

        $orders = $this->orders->get_orders([
            'user_id' => $clientId,
            'limit' => 1,
            'sort' => ['date_desc'],
            'organization_id' => $organizationId
        ]);

        if (empty($orders) || empty($orders[0])) {
            return null;
        }

        $order = $orders[0];
        $appId = $this->dbrainAxi->getLastAppId($order->order_id);
        if (empty($appId)) {
            return null;
        }

        $history = $this->dbrainAxi->getHistory($appId, $order, [$this->dbrainAxi::AXICREDIT_RESPONSE], $callName);
        if (empty($history) || empty($history->content)) {
            return null;
        }

        foreach ($history->content as $content) {
            $inn = $this->getInnFromContent($content, $callName);

            if ($inn !== null) {
                return $inn;
            }
        }

        return null;
    }

    protected function log(string $method, string $url, $request, $response): void
    {
        $this->logging($method, $this->host . $url, $request, $response, $this->getLogFile());
    }

    private function getInnFromContent($content, $callName): ?string
    {
        if (!empty($content) && !empty($content->call->name)) {
            if ($content->call->name === $callName && !empty($content->status)) {
                if ($content->status === $this->dbrainAxi::AXICREDIT_RESPONSE) {
                    $xml = simplexml_load_string($content->xml);

                    if ($xml !== false) {
                        return $xml->AXI->application_e[0]['person_INN'];
                    }
                }
            }
        }

        return null;
    }

    abstract protected function getReportPathS3(): string;

    abstract protected function buildReportRequestData(object $order, object $organizationData): array;

    abstract protected function getReportUrl(): string;

    abstract protected function getLogsUrl(): string;

    abstract protected function getReportType(): string;

    abstract protected function getReportPathDisc(): string;

    abstract protected function getRequestType(): string;

    abstract protected function getLogFile(): string;
}