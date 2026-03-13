<?php

use Traits\SoglasieBKITrait;

require_once __DIR__ . '/Traits/SoglasieBKITrait.php';
require_once __DIR__ . '/PytonReportsAbstract.php';

if (!defined('ROOT'))
    define('ROOT', dirname(__DIR__));

class Pyton_smp extends PytonReportsAbstract
{
    use SoglasieBKITrait;

    const AMP_SIGNED = 'amp_signed';

    const REPORT_URL = 'api/v2/history/amp/v2_0/';

    private const REPORT_TYPE = 'SSP_NBKI';

    private const LOCAL_REPORT_PATH = ROOT . '/files/CCP/';

    private const LOGS_URL = 'report-search-by-person/';

    private const LOG_FILE = 'pyton_smp.log';

    /**
     * @throws Exception
     */
    protected function buildReportRequestData(object $order, object $organizationData): array
    {
        $patronymic = $this->preparePatronymic($order->patronymic);
        $passportSerial = $this->preparePassportFullNumber($order->passport_serial);
        $inn = $order->inn;

        if (empty($inn)) {
            $inn = $this->getInnFromOtherOrders($order->user_id, $this->organizations::RZS_ID);

            if (!empty($inn)) {
                $this->users->update_user($order->user_id, ['inn' => $inn]);
            }
        }

        return [
            'inquiry' => [
                'id' => uniqid() . $order->order_id,
                'amount' => $order->amount,
                'request_type' => '2'
            ],
            'user' => [
                'passport' => [
                    'series' => substr($passportSerial, 0, 4),
                    'number' => substr($passportSerial, 4),
                    'issue_date' => date('Y-m-d', strtotime($order->passport_date))
                ],
                'person' => [
                    'last_name' => $order->lastname,
                    'first_name' => $order->firstname,
                    'middle_name' => $patronymic,
                    'date_of_birth' => date('Y-m-d', strtotime($order->birth)),
                    'taxpayer_number' => $inn
                ],
            ],
            'consent' => [
                'issue_date' => date('Y-m-d', strtotime($order->date)),
                'validity_code' => '1',
                'purpose_code' => ['2'],
                'hash_code' => $this->getHashCodeSoglasieBKI($order->user_id),
            ],
            'requisites' => [
                'member_code' => $organizationData->member_code,
                'taxpayer_number' => $organizationData->taxpayer_number,
                'registration_number' => $organizationData->registration_number,
                'full_name' => $organizationData->full_name,
                'short_name' => $organizationData->short_name,
                'password' => $organizationData->password,
                'user_id' => $organizationData->user_id,
            ],
            'extra_parameters' => [
                'mapped_format' => $organizationData->mapped_format,
            ],
        ];
    }

    protected function getReportPathS3(): string
    {
        return $this->config->s3['amp_report_url'];
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
        return self::AMP_SIGNED;
    }

    protected function getLogFile(): string
    {
        return self::LOG_FILE;
    }
}

