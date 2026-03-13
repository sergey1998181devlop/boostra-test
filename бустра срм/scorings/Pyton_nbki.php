<?php

require_once __DIR__ . '/PytonReportsAbstract.php';

if (!defined('ROOT')) {
    define('ROOT', dirname(__DIR__));
}

class Pyton_nbki extends PytonReportsAbstract
{
    private const LOG_FILE = 'pyton_nbki.log';

    private const CH_SIGNED = 'historysign';

    private const REPORT_URL = 'api/v1/history/sign/';

    private const LOGS_URL = 'report-search-by-person/';

    private const LOCAL_REPORT_PATH = ROOT . '/files/credit_history/';

    private const REPORT_TYPE = 'NBKI';

    /**
     * @throws Exception
     */
    protected function buildReportRequestData(object $order, object $organizationData): array
    {
        $passportSerial = $this->preparePassportFullNumber($order->passport_serial);
        $subdivisionCode = $this->prepareSubdivisionCode($order->subdivision_code);

        $data = [
            'user' => [
                'passport' => [
                    'series' => substr($passportSerial, 0, 4),
                    'number' => substr($passportSerial, 4),
                    'issued_date' => date('Y-m-d', strtotime($order->passport_date)),
                    'issued_by' => $order->passport_issued,
                    'division_code' => $subdivisionCode,
                ],
                'person' => [
                    'last_name' => $order->lastname,
                    'first_name' => $order->firstname,
                    'middle_name' => $order->patronymic ?: '-', 'birthday' => date('Y-m-d', strtotime($order->birth)),
                    'place_of_birth' => $order->Regcity,
                    'gender' => $this->getGenderCode($order->gender),
                ],
            ],
            'requisites' => [
                'member_code' => $organizationData->member_code,
                'taxpayer_number' => $organizationData->taxpayer_number ,
                'company_name' => $organizationData->company_name,
                'password' => $organizationData->password,
                'registration_number' => $organizationData->registration_number,
                'user_id' => $organizationData->user_id,
            ],
            'extra_parameters' => [
                'mapped_format' => $organizationData->mapped_format,
            ],
        ];

        if (!empty($order->inn)) {
            $data['user']['registration_numbers']['taxpayer_number'] = $order->inn;
        }

        return $data;
    }

    protected function getReportPathS3(): string
    {
        return $this->config->s3['report_url'];
    }

    protected function getReportUrl(): string
    {
        return self::REPORT_URL;
    }

    protected function getLogsUrl(): string
    {
        return self::LOGS_URL;
    }

    protected function getReportType(): string
    {
        return self::REPORT_TYPE;
    }

    protected function getReportPathDisc(): string
    {
        return self::LOCAL_REPORT_PATH;
    }

    protected function getRequestType(): string
    {
        return self::CH_SIGNED;
    }

    protected function getLogFile(): string
    {
        return self::LOG_FILE;
    }
}

