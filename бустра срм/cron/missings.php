<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

date_default_timezone_set('Europe/Moscow');

chdir(dirname(__FILE__) . '/../');
require_once 'api/Simpla.php';

use Carbon\Carbon;
use Cmixin\BusinessDay;

// Включаем поддержку бизнес-дней и праздников
BusinessDay::enable(Carbon::class);
Carbon::setHolidaysRegion('ru-national');

class MissingsCron extends Simpla
{
    private $batchSize = 50; // Обрабатываем по 50 записей за раз
    private $maxExecutionTime = 50; // Максимальное время выполнения в секундах
    private $startTime;

    public function run()
    {
        $this->startTime = time();
        $lastProcessedId = PHP_INT_MAX; // Начинаем с максимального ID
        $totalProcessed = 0;
        $totalCalls = 0;

        while (true) {
            // Проверяем время выполнения
            if (time() - $this->startTime >= $this->maxExecutionTime) {
                echo json_encode([
                        'status' => 'timeout',
                        'message' => 'Execution time limit reached',
                        'total_processed' => $totalProcessed,
                        'total_calls' => $totalCalls,
                        'last_processed_id' => $lastProcessedId
                    ]) . "\n";
                break;
            }

            $users = $this->fetchNextBatch($lastProcessedId);

            if (empty($users)) {
                echo json_encode([
                        'status' => 'completed',
                        'message' => 'All users processed',
                        'total_processed' => $totalProcessed,
                        'total_calls' => $totalCalls
                    ]) . "\n";
                break;
            }

            foreach ($users as $user) {
                $totalProcessed++;

                // Проверка временных ограничений (только если это не первый звонок)
                if (!$user->is_first_call) {
                    $timeCheck = $this->checkTimeRestrictions($user->timezone_id, $user->timezone_offset);

                    if (!$timeCheck['can_call']) {
                        echo json_encode([
                                'success' => false,
                                'phone' => $user->phone_mobile,
                                'error' => $timeCheck['error']
                            ]) . "\n";
                        continue;
                    }
                }

                // Создаем запись о звонке
                $this->db->query("
                    INSERT INTO s_vox_robot_calls 
                    (user_id, client_phone, vox_call_id, status, is_redirected_manager, type, created_at, updated_at) 
                    VALUES (?, ?, 0, 0, false, 'missing', NOW(), NOW())",
                    $user->id,
                    $user->phone_mobile
                );

                // Отправляем звонок
                $resultVoxImplant = $this->voximplant->sendVoximplantCall(
                    (string)$user->phone_mobile,
                    $this->config->incomplete_scenario_id,
                    $this->config->incomplete_outcoming_phone_id
                );

                $this->design->assign('link', $this->config->email_link);
                $base64_user_id = base64_encode($user->id);
                $hmac_hash = hash_hmac('sha256', $base64_user_id, $this->config->email_secret_key);
                $encrypted_user_id = $base64_user_id . '.' . $hmac_hash;
                $this->design->assign('encrypted_user_id', $encrypted_user_id);
                $email_body = $this->design->fetch('unsub_email.tpl');

                $result_email = false;
                if ($user->call_count == 0 && $this->user_data->read($user->id, 'email_is_unsubscribed') == null) {
                    $result_email = $this->emails->sendEmail("До получения денег на карту осталось совсем чуть-чуть!", $email_body, $user->email);
                }

                $email_result = [
                    'success' => $result_email,
                    'email' => $user->email,
                ];

                $totalCalls++;
                echo json_encode(['result_voximplant' => $resultVoxImplant, 'result_email' => $email_result]) . "\n";
            }

            // Обновляем cursor на ID последнего пользователя в батче
            $lastProcessedId = end($users)->id;
        }
    }

    /**
     * Получает следующую порцию пользователей для обработки
     * Сортировка от новых к старым (DESC)
     *
     * @param int $lastProcessedId ID последнего обработанного пользователя
     * @return array
     */
    private function fetchNextBatch($lastProcessedId)
    {
        $sql = $this->db->placehold("
            SELECT 
                u.id,
                u.phone_mobile,
                u.email,
                u.timezone_id,
                tz.time as timezone_offset,
                COALESCE(call_stats.call_count, 0) as call_count,
                COALESCE(call_stats.active_calls_count, 0) as active_calls_count,
                COALESCE(call_stats.has_success_call, 0) as has_success_call,
                call_stats.last_call_updated_at,
                CASE WHEN COALESCE(call_stats.call_count, 0) = 0 THEN 1 ELSE 0 END as is_first_call
            FROM __users u
            LEFT JOIN s_time_zones tz ON u.timezone_id = tz.time_zone_id
            LEFT JOIN (
                SELECT 
                    user_id,
                    client_phone,
                    COUNT(CASE WHEN status != 0 THEN 1 END) as call_count,
                    COUNT(CASE WHEN status = 0 AND created_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE) THEN 1 END) as active_calls_count,
                    MAX(CASE WHEN status = 2 THEN 1 ELSE 0 END) as has_success_call,
                    MAX(updated_at) as last_call_updated_at
                FROM s_vox_robot_calls
                WHERE type = 'missing'
                GROUP BY user_id, client_phone
            ) call_stats ON u.id = call_stats.user_id AND u.phone_mobile = call_stats.client_phone
            WHERE 
                u.id < ?
                -- Условия неполных данных
                AND (
                    u.personal_data_added = 0 
                    OR u.address_data_added = 0 
                    OR u.accept_data_added = 0 
                    OR u.card_added = 0 
                    OR u.files_added = 0 
                    OR u.additional_data_added = 0
                )
                -- Условия по времени добавления данных
                AND (
                    (NOW() > u.created + INTERVAL 300 SECOND  AND u.personal_data_added = 0)
                    OR (NOW() > u.personal_data_added_date + INTERVAL 300 SECOND AND u.address_data_added = 0)
                    OR (NOW() > u.address_data_added_date + INTERVAL 300 SECOND AND u.accept_data_added = 0)
                    OR (NOW() > u.accept_data_added_date + INTERVAL 300 SECOND AND u.files_added = 0)
                    OR (NOW() > u.files_added_date + INTERVAL 300 SECOND AND u.card_added = 0)
                    OR (NOW() > u.card_added_date + INTERVAL 300 SECOND AND u.additional_data_added = 0)
                )
                -- Исключения
                AND NOT EXISTS (
                    SELECT 1 FROM s_orders so 
                    WHERE so.user_id = u.id
                    AND so.status = 3
                )
                AND NOT EXISTS (
                    SELECT 1 FROM s_user_data sud 
                    WHERE sud.user_id = u.id
                    AND sud.key = 'is_rejected_nk'
                    AND sud.value = '1'
                )
                AND u.created > NOW() - INTERVAL 2 MONTH
                AND u.phone_mobile IS NOT NULL
                AND u.phone_mobile != ''
                -- Условия для звонков (call conditions)
                AND COALESCE(call_stats.active_calls_count, 0) = 0
                AND COALESCE(call_stats.call_count, 0) < 3
                AND COALESCE(call_stats.has_success_call, 0) = 0
                AND (
                    call_stats.last_call_updated_at IS NULL
                    OR TIMESTAMPDIFF(SECOND, call_stats.last_call_updated_at, NOW()) >= 7200
                )
            ORDER BY u.id DESC
            LIMIT ?
        ", $lastProcessedId, $this->batchSize);

        $this->db->query($sql);
        return $this->db->results();
    }

    /**
     * Проверяет временные ограничения для звонков используя Carbon
     *
     * @param int|null $timezone_id ID часового пояса пользователя
     * @param int|null $timezone_offset Смещение часового пояса
     * @return array
     */
    private function checkTimeRestrictions($timezone_id, $timezone_offset)
    {
        $now = Carbon::now('Europe/Moscow');

        if (empty($timezone_id) || $timezone_offset === null) {
            // Проверяем по МСК
            $hour = (int) $now->format('H');
            $isWorkday = !$now->isWeekend() && !$now->isHoliday();

            if ($isWorkday) {
                // Будни: с 8:00 до 22:00 по МСК
                if ($hour < 8 || $hour >= 22) {
                    return [
                        'can_call' => false,
                        'error' => "Calls allowed only 8:00-22:00 MSK on weekdays (current: {$now->format('H:i')})"
                    ];
                }
            } else {
                // Праздники/выходные: с 9:00 до 20:00 по МСК
                if ($hour < 9 || $hour >= 20) {
                    return [
                        'can_call' => false,
                        'error' => "Calls allowed only 9:00-20:00 MSK on holidays/weekends (current: {$now->format('H:i')})"
                    ];
                }
            }
        } else {
            // Проверяем по местному времени пользователя
            $msk_offset = 3; // МСК = UTC+3
            $user_utc_offset = 3 + $timezone_offset;
            $time_diff = $user_utc_offset - $msk_offset;

            // Создаем время в часовом поясе пользователя
            $localTime = $now->copy()->addHours($time_diff);
            $hour = (int) $localTime->format('H');
            $isWorkday = !$localTime->isWeekend() && !$localTime->isHoliday();

            if ($isWorkday) {
                // Будни: с 8:00 до 22:00
                if ($hour < 8 || $hour >= 22) {
                    return [
                        'can_call' => false,
                        'error' => "Calls allowed only 8:00-22:00 local time on weekdays (current local: {$localTime->format('H:i')})"
                    ];
                }
            } else {
                // Праздники/выходные: с 9:00 до 20:00
                if ($hour < 9 || $hour >= 20) {
                    return [
                        'can_call' => false,
                        'error' => "Calls allowed only 9:00-20:00 local time on holidays/weekends (current local: {$localTime->format('H:i')})"
                    ];
                }
            }
        }

        return [
            'can_call' => true
        ];
    }
}

$cron = new MissingsCron();
$cron->run();