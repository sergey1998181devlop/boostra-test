<?php

require_once dirname(__DIR__) . '/api/Simpla.php';

/**
 * Класс для включения дополнительных услуг
 * class CronEnableAdditionalServices
 */
class CronEnableAdditionalServices extends Simpla
{
    public function __construct()
    {
        $this->run();
    }

    public function run()
    {
        $startDate = $this->request->get('start_date', 'string');
        $endDate = $this->request->get('end_date', 'string');

        if (empty($startDate) || empty($endDate)) {
            echo "Укажите параметры 'start_date' и 'end_date' в формате 'YYYY-MM-DD'.\n";
            return;
        }

        try {
            $result = $this->orders->enableAdditionalServicesWithLogs($startDate, $endDate);
            if ($result) {
                echo "Дополнительные услуги успешно обработаны.\n";
            } else {
                echo "Никаких изменений не внесено.\n";
            }
        } catch (Exception $e) {
            echo "Ошибка при обработке дополнительных услуг: " . $e->getMessage() . "\n";
        }
    }
}

new CronEnableAdditionalServices();
