<?php

error_reporting(E_ERROR);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 3300);

chdir(dirname(__FILE__) . '/../');
require_once 'api/Simpla.php';

/**
 * Автоматическое отключение принудительного показа информации (сумма займа) в ЛК
 * после истечения настроенного срока (по умолчанию 14 дней).
 *
 * Настройка срока: site_settings -> show_order_information_days (дефолт 14)
 * Управление: client.tpl -> кнопка "Показать информацию (сумма займа) в ЛК"
 *
 * Срок отсчитывается от поля updated в s_user_data (обновляется автоматически через REPLACE INTO).
 */
class DisableExpiredOrderInfoCron extends Simpla
{
    private const DEFAULT_DAYS = 14;

    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    public function run(): void
    {
        $days = (int)$this->settings->show_order_information_days;
        if ($days <= 0) {
            $days = self::DEFAULT_DAYS;
        }

        $expiredUsers = $this->getExpiredUsers($days);

        foreach ($expiredUsers as $userId) {
            $this->disableOrderInfo($userId);
        }

        $cleaned = $this->deleteDisabledRecords();

        echo "Отключён принудительный показ для " . count($expiredUsers) . " пользователей, удалено мусорных записей: {$cleaned}.\n";
    }

    /**
     * Возвращает список user_id, у которых show_order_information=1
     * и поле updated в s_user_data старше $days дней.
     *
     * @param int $days
     * @return int[]
     */
    private function getExpiredUsers(int $days): array
    {
        $query = $this->db->placehold(
            "SELECT user_id
             FROM s_user_data
             WHERE `key` = 'show_order_information'
               AND value = '1'
               AND updated <= DATE_SUB(NOW(), INTERVAL ? DAY)",
            $days
        );
        $this->db->query($query);
        $rows = $this->db->results();

        if (empty($rows)) {
            return [];
        }

        return array_column($rows, 'user_id');
    }

    /**
     * Удаляет записи show_order_information с value='0' — они не несут смысла в БД.
     *
     * @return int количество удалённых записей
     */
    private function deleteDisabledRecords(): int
    {
        $this->db->query(
            "DELETE FROM s_user_data WHERE `key` = 'show_order_information' AND value = '0'"
        );
        return $this->db->affected_rows();
    }

    /**
     * Отключает принудительный показ для пользователя.
     *
     * @param int $userId
     */
    private function disableOrderInfo(int $userId): void
    {
        $this->user_data->set($userId, 'show_order_information');

        $this->changelogs->add_changelog([
            'manager_id' => $this->managers::MANAGER_SYSTEM_ID,
            'created'    => date('Y-m-d H:i:s'),
            'type'       => 'showOrderInformation',
            'old_values' => 1,
            'new_values' => 0,
            'user_id'    => $userId,
        ]);
    }
}

new DisableExpiredOrderInfoCron();
