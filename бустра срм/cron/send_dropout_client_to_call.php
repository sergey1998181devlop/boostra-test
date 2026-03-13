<?php

use Carbon\Carbon;

ini_set('max_execution_time', '600');

require_once dirname(__DIR__) . '/api/Simpla.php';

/**
 * Класс для отправки отвальных клиентов в кампанию обзвона
 */
class DropoutClientCaller extends Simpla
{
    private const DEFAULT_UTC = '3';
    private const HOURS_START = 6;
    private const HOURS_END = 3;
    private const MAX_CLIENTS = 1000;

    /**
     * Инициализация процесса сбора и отправки отвальных клиентов
     *
     * @return void
     */
    public function init()
    {
        $campaignId = $this->config->vox_dropout_client_call_campaign_id;

        if (empty($campaignId)) {
            return;
        }

        $dropoutClients = $this->getDropoutClients();

        if (empty($dropoutClients)) {
            return;
        }

        $this->voximplant->appendToCampaign($campaignId, $dropoutClients);
    }

    /**
     * Получение списка отвальных клиентов из базы данных
     *
     * @return array Массив данных клиентов для отправки
     */
    private function getDropoutClients(): array
    {
        $startDay = Carbon::now()->subHours(self::HOURS_START)->toDateTimeString();
        $endDay = Carbon::now()->subHours(self::HOURS_END)->toDateTimeString();

        // Выбрать клиентов, которые не прошли все этапы регистрации
        $query = $this->buildDropoutClientsQuery();

        $this->db->query($query, $startDay, $endDay);
        $clients = $this->db->results();

        if (empty($clients)) {
            return [];
        }

        return $this->formatClientsData($clients);
    }

    /**
     * Формирует SQL запрос для получения отвальных клиентов
     *
     * @return string SQL запрос
     */
    private function buildDropoutClientsQuery(): string
    {
        return '
            SELECT 
                u.phone_mobile as phone,
                u.personal_data_added,
                u.address_data_added,
                u.accept_data_added,
                u.additional_data_added,
                u.card_added,
                u.files_added,
                tz.timezone
            FROM __users as u
            JOIN s_time_zones tz ON tz.time_zone_id = u.timezone_id
            WHERE u.created BETWEEN ? AND ?
            AND (
                u.personal_data_added = 0
                OR u.address_data_added = 0
                OR u.accept_data_added = 0
                OR u.card_added = 0
                OR u.files_added = 0
                OR u.additional_data_added = 0
            )
            AND NOT EXISTS (
                SELECT 1 FROM s_user_data sud 
                WHERE sud.user_id = u.id 
                AND sud.`key` = "is_rejected_nk"
                AND sud.`value` = 1
            )
            AND NOT EXISTS (
                SELECT 1 FROM s_order_data od
                WHERE od.user_id = u.id
                AND od.`key` = "is_sold_to_bonon"
            )
            LIMIT ' . self::MAX_CLIENTS . '
        ';
    }

    /**
     * Форматирует данные клиентов для отправки в API
     *
     * @param array $clients Данные клиентов из базы данных
     * @return array Отформатированные данные для API
     */
    private function formatClientsData(array $clients): array
    {
        return array_map(function($client) {
            $incompleteStages = [
                'personal_data' => 'personal_data_added',
                'address_data' => 'address_data_added',
                'accept_data' => 'accept_data_added',
                'card' => 'card_added',
                'photos' => 'files_added',
                'additional_data' => 'additional_data_added',
            ];
            $stage = 'completed';

            foreach ($incompleteStages as $stageName => $fieldName) {
                if (isset($client->$fieldName) && $client->$fieldName == 0) {
                    $stage = $stageName;
                    break;
                }
            }

            return [
                'phone' => $client->phone,
                'stage' => $stage,
                'UTC' => $this->extractTimezone($client->timezone),
            ];
        }, $clients);
    }

    /**
     * Извлекает часовой пояс из строки формата +/-HH:MM
     *
     * @param string|null $timezone Строка часового пояса
     * @return string Часовой пояс в формате для API
     */
    private function extractTimezone(?string $timezone): string
    {
        if (empty($timezone)) {
            return self::DEFAULT_UTC;
        }

        if (preg_match('/^([+-])(\d{1,2}):(\d{2})$/', $timezone, $matches)) {
            $sign = $matches[1];
            $hours = (int)$matches[2];

            return strval($sign === '-' ? -$hours : $hours);
        }

        return self::DEFAULT_UTC;
    }
}

$caller = new DropoutClientCaller();
$caller->init();