<?php

namespace App\Service;

use App\Enums\MindboxConstants;
use DateTime;
use DateTimeZone;
use Exception;
use Generator;
use api\helpers\TimeZoneHelper;

class CsvGenerator
{
    private $config;

    public function __construct($config = null)
    {
        $this->config = $config;
    }


    /**
     * Стриминг CSV в браузер для скачивания
     * @param string|Generator $csvContent
     * @param string $filename
     * @return void
     */
    public function streamToDownload($csvContent, string $filename): void
    {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        header('X-Accel-Buffering: no');

        echo "\xEF\xBB\xBF"; // BOM for UTF-8

        if (is_string($csvContent)) {
            echo $csvContent;
        } else {
            $counter = 0;
            foreach ($csvContent as $line) {
                echo $line;
                if (++$counter % 10000 == 0) {
                    flush();
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                }
            }
        }

        exit;
    }


    /**
     * Заголовки CSV для пользователей
     * @return string
     */
    public function getUsersHeaders(): string
    {
        $headers = [
            'ExternalIdentityBoostraClientID',
            'MobilePhone',
            'Email',
            'LastName',
            'FirstName',
            'MiddleName',
            'BirthDate',
            'Sex',
            'Area',
            'CustomFieldBoostraCreated',
            'CustomFieldphoneconfirmed',
            'CustomFieldregregion',
            'CustomFieldbankrupt',
            'CustomFieldMoratoriumDate',
            'CustomFieldpartnerName',
            'CustomFieldBoostraUtmSource',
            'CustomFieldBoostraUtmMedium',
            'CustomFieldBoostraUtmCampaign',
            'CustomFieldBoostraUtmContent',
            'CustomFieldBoostraUtmTerm',
            'CustomFieldBoostraCardConfirmed',
            //'CustomFieldBoostraCardAddedDate',
            'CustomFieldBoostraAcceptDataAdded',
            'CustomFieldBoostraAcceptDataAddedDate',
            'CustomFieldBoostraSoglasienaPDn',
            'CustomFieldBoostraPersonalDataAddedDate',
            'IsSubscribedBySms',
            'IsSubscribedByWebPush',
            'IsSubscribedByMobilePush',
            'IsSubscribedByEmail'
        ];
        return implode(';', $headers);
    }


    /**
     * Форматирование строки пользователя
     * @param object $row
     * @return string
     * @throws Exception
     */
    public function formatUserRow(object $row): string
    {
        $isSubscribed = !$row->block_sms_created_at ? '1' : '0';

        $userTimezone = 'Europe/Moscow';
        $area = '';
        $regregionForExport = $row->Regregion ?? '';

        if (!empty($row->Regregion)) {
            $parsedRegion = TimeZoneHelper::parseRegion($row->Regregion);
            $area = $parsedRegion[1] ?? '';
            $userTimezone = $parsedRegion[1] ?? 'Europe/Moscow';
        } else {
            $factualRegionCode = $row->factual_region_code ?? null;
            if ($factualRegionCode !== null && $factualRegionCode !== '') {
                $userTimezone = TimeZoneHelper::getTimezoneByRegionCode($factualRegionCode);
                $area = $userTimezone;
                $regregionForExport = $row->factual_region_name ?? '';
            }
        }

        // Даты в UTC: created — серверное время (Moscow), даты согласий — по таймзоне региона пользователя
        $created = '';
        if ($row->created) {
            $created = $this->formatDateToUTC($row->created, 'Europe/Moscow') ?? '';
        }
        $acceptDataAddedDate = !empty($row->accept_data_added_date)
            ? ($this->formatDateToUTC($row->accept_data_added_date, $userTimezone) ?? '')
            : '';
        $personalDataAddedDate = !empty($row->personal_data_added_date)
            ? ($this->formatDateToUTC($row->personal_data_added_date, $userTimezone) ?? '')
            : '';

        // Порядок и набор полей соответствуют getUsersHeaders() и editCustomer в mindBox.php
        $formatted_row = [
            $this->escapeCSV($row->id),                                    // ExternalIdentityBoostraClientID
            $this->escapeCSV($row->phone_mobile ?? ''),                     // MobilePhone
            $this->escapeCSV($row->email ?? ''),                            // Email
            $this->escapeCSV($row->lastname ?? ''),                         // LastName
            $this->escapeCSV($row->firstname ?? ''),                        // FirstName
            $this->escapeCSV($row->patronymic ?? ''),                       // MiddleName
            $this->escapeCSV($row->birth ?? ''),                            // BirthDate
            $this->escapeCSV($row->gender ?? ''),                            // Sex
            $this->escapeCSV($area),                                        // Area (timeZone)
            $this->escapeCSV($created),                                     // CustomFieldBoostraCreated
            '1',                                                            // CustomFieldphoneconfirmed
            $this->escapeCSV($regregionForExport),                       // CustomFieldregregion
            $this->escapeCSV($row->bankrupt ?? ''),                         // CustomFieldbankrupt
            $this->escapeCSV($row->maratorium_date ?? ''),                 // CustomFieldMoratoriumDate
            $this->escapeCSV($row->partner_name ?? ''),                     // CustomFieldpartnerName
            $this->escapeCSV($row->utm_source ?? ''),                       // CustomFieldBoostraUtmSource
            $this->escapeCSV($row->utm_medium ?? ''),                       // CustomFieldBoostraUtmMedium
            $this->escapeCSV($row->utm_campaign ?? ''),                     // CustomFieldBoostraUtmCampaign
            $this->escapeCSV($row->utm_content ?? ''),                       // CustomFieldBoostraUtmContent
            $this->escapeCSV($row->utm_term ?? ''),                         // CustomFieldBoostraUtmTerm
            $this->escapeCSV($row->card_added ?? ''),                        // CustomFieldBoostraCardConfirmed
            //$this->escapeCSV($row->card_added_date ?? ''),                  // CustomFieldBoostraCardAddedDate
            $this->escapeCSV($row->accept_data_added ?? ''),                // CustomFieldBoostraAcceptDataAdded
            $this->escapeCSV($acceptDataAddedDate),                          // CustomFieldBoostraAcceptDataAddedDate
            $this->escapeCSV($row->personal_data_added ?? ''),               // CustomFieldBoostraSoglasienaPDn
            $this->escapeCSV($personalDataAddedDate),                       // CustomFieldBoostraPersonalDataAddedDate
            $isSubscribed,                                                  // IsSubscribedBySms
            $isSubscribed,                                                  // IsSubscribedByWebPush
            $isSubscribed,                                                  // IsSubscribedByMobilePush
            $isSubscribed                                                   // IsSubscribedByEmail
        ];

        return implode(';', $formatted_row);
    }


    /**
     * Заголовки CSV для заказов
     * @return string
     */
    public function getOrdersHeaders(): string
    {
        $headers = [
            'OrderLastUpdateDateTimeUtc',
            'OrderLineQuantity',
            'OrderLineStatus',
            'OrderIdsBoostraID',
            'OrderCreationDateTimeUtc',
            'OrderTotalPrice',
            'OrderCustomFieldOrderDetailamountofpayments',
            'OrderCustomFieldOrderDetailcontractsclosedate',
            'OrderCustomFieldOrderDetailcontractsissuancedate',
            'OrderCustomFieldOrderDetaildecisiondate',
            'OrderCustomFieldOrderDetailDop',
            'OrderCustomFieldOrderDetailfirstloan',
            'OrderCustomFieldOrderDetailinterest',
            'OrderCustomFieldOrderDetailNomerzayavki1s',
            'OrderCustomFieldOrderDetailOrderId',
            'OrderCustomFieldOrderDetailOrderNumber',
            'OrderCustomFieldOrderDetailordersutmcampaign',
            'OrderCustomFieldOrderDetailordersutmmedium',
            'OrderCustomFieldOrderDetailorderswebmasterID',
            'OrderCustomFieldOrderDetailpdnnbki',
            'OrderCustomFieldOrderDetailProductlines',
            'OrderCustomFieldOrderDetailprolongation',
            'OrderCustomFieldOrderDetailRequestedAmount',
            'OrderCustomFieldOrderDetailscoristaball',
            'OrderCustomFieldOrderDetailSOrdersAmount',
            'OrderCustomFieldOrderDetailSOrdersDate',
            'OrderCustomFieldOrderDetailSOrdersPeriod',
            'OrderCustomFieldOrderDetailSOrdersReasonId',
            'OrderCustomFieldOrderDetailSOrdersStatus',
            'OrderCustomFieldOrderDetailStatusCRM',
            'OrderCustomFieldOrderDetailSOrdersUtmSource',
            'OrderEmail',
            'OrderMobilePhone',
            'CustomerEmail',
            'CustomerMobilePhone',
            'CustomerIdsLikeZaim67ClientID',
            'OrderLineProductIdsLikeZaim67',
            'OrderLineBasePricePerItem',
            'OrderLinePriceOfLine',
            'OrderLineId',
            'OrderLineCustomFieldPurchaseDetailEnddate',
            'OrderLineCustomFieldPurchaseDetailIsAddon',
            'OrderLineCustomFieldPurchaseDetailLicenseNumber',
            'OrderLineCustomFieldPurchaseDetailReturnAmount',
            'OrderLineCustomFieldPurchaseDetailStartdate',
        ];
        return implode(';', $headers);
    }


    /**
     * Форматирование строки заказа
     * @param object $order
     * @param array $line
     * @param bool $hasAddonsForOrder
     * @return string
     */
    public function formatOrderLine(object $order, array $line, bool $hasAddonsForOrder): string
    {
        $amountPayments = MindboxConstants::shouldIncludePayments($order->{'1c_status'}) ? (int)$order->amount_payments : 0;

        $orderTimezone = 'Europe/Moscow';
        if (!empty($order->Regregion)) {
            $parsed = TimeZoneHelper::parseRegion($order->Regregion);
            $orderTimezone = $parsed[1] ?? 'Europe/Moscow';
        } else {
            $factualCode = $order->factual_region_code ?? null;
            if ($factualCode !== null && $factualCode !== '') {
                $orderTimezone = TimeZoneHelper::getTimezoneByRegionCode($factualCode);
            }
        }

        $formatted_row = [
            $this->escapeCSV($this->formatDateToUTC($order->modified, $orderTimezone)),
            '1',
            $this->escapeCSV($line['status']),
            $this->escapeCSV($order->id),
            $this->escapeCSV($this->formatDateToUTC($order->date, $orderTimezone)),
            $this->escapeCSV($order->body_sum),
            $this->escapeCSV($amountPayments),
            $this->escapeCSV($this->formatDateToUTC($order->close_date, $orderTimezone)),
            $this->escapeCSV($this->formatDateToUTC($order->issuance_date, $orderTimezone)),
            $this->escapeCSV($this->formatDateToUTC($order->confirm_date, $orderTimezone)),
            $this->escapeCSV((bool)$order->addition_services),
            $this->escapeCSV((bool)$order->first_loan),
            $this->escapeCSV($order->percent),
            $this->escapeCSV($order->{'1c_id'}),
            $this->escapeCSV($order->id),
            $this->escapeCSV($order->contract),
            $this->escapeCSV($order->utm_campaign),
            $this->escapeCSV($order->utm_medium),
            $this->escapeCSV($order->webmaster_id),
            $this->escapeCSV($order->pdn_nkbi),
            $this->escapeCSV($hasAddonsForOrder),
            $this->escapeCSV($order->prolongation_count ?? 0),
            $this->escapeCSV($order->req_amount),
            $this->escapeCSV($order->scorista_ball),
            $this->escapeCSV($order->approve_amount),
            $this->escapeCSV($this->formatDateToUTC($order->date, $orderTimezone)),
            $this->escapeCSV($order->period),
            $this->escapeCSV($order->reason_id),
            $this->escapeCSV($order->{'1c_status'}),
            $this->escapeCSV($order->status),
            $this->escapeCSV($order->utm_source),
            $this->escapeCSV($order->email),
            $this->escapeCSV($order->phone_mobile),
            $this->escapeCSV($order->email),
            $this->escapeCSV($order->phone_mobile),
            $this->escapeCSV($order->user_id),
            $this->escapeCSV($line['product_id']),
            $this->escapeCSV($line['price']),
            $this->escapeCSV($line['price']),
            $this->escapeCSV($line['line_id']),
            $this->escapeCSV($this->formatDateToUTC($line['enddate'], $orderTimezone)),
            $this->escapeCSV($line['product_id'] !== 'L1'),
            $this->escapeCSV($line['license_key']),
            $this->escapeCSV($line['return_amount']),
            $this->escapeCSV($this->formatDateToUTC($line['startdate'], $orderTimezone))
        ];
        return implode(';', $formatted_row);
    }


    /**
     * Форматирование даты в UTC
     * @param string|null $date
     * @param string $sourceTimezone Таймзона, в которой хранится дата (по умолчанию Europe/Moscow)
     * @return string|null
     */
    private function formatDateToUTC(?string $date, string $sourceTimezone = 'Europe/Moscow'): ?string
    {
        if (!$date) {
            return null;
        }
        try {
            $dt = new DateTime($date, new DateTimeZone($sourceTimezone));
            $dt->setTimezone(new DateTimeZone('UTC'));
            $formatted = $dt->format('Y-m-d H:i:s');
            return $formatted . '.000';
        } catch (Exception $e) {
            return null;
        }
    }


    /**
     * Экранирование значений для CSV
     * @param $value
     * @return string
     */
    private function escapeCSV($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $value = (string)$value;

        if (strpos($value, ';') !== false ||
            strpos($value, '"') !== false ||
            strpos($value, "\n") !== false ||
            strpos($value, "\r") !== false) {
            // Экранируем кавычки удвоением
            $value = str_replace('"', '""', $value);
            return '"' . $value . '"';
        }

        return $value;
    }
}