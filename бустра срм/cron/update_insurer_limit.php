<?php

require_once dirname(__FILE__).'/../api/Simpla.php';

/**
 * Класс для обновления порогов в лимитах
 * Настройки порогов выданных страховок
 * Class UpdateLimitInsurer
 */
class UpdateLimitInsurer extends Simpla
{
    public function run()
    {
        $settings = $this->settings->insurance_threshold_settings;

        // считаем кол-во выданных займов за прошлый день
        $filter_data = [
            'filter_client' => 'ALL',
            'filter_is_confirmed' => true,
            'filter_date_confirm' => [
                'filter_date_start' => (new DateTime())->modify('- 1 day')->format('Y-m-d'),
                'filter_date_end' => (new DateTime())->modify('- 1 day')->format('Y-m-d'),
            ],
        ];

        $percent_insurer_boostra = $this->settings->percent_insurer_boostra;
        $total_orders_yesterday = $this->orders->getTotalOrders($filter_data);

        $percent_90 = (int)($total_orders_yesterday * 0.9);
        $total_amount_percent = (int)((($percent_90 * $percent_insurer_boostra) / 100) * 3000);
        $settings['Boostra'] = $total_amount_percent;
        $this->settings->insurance_threshold_settings = $settings;
    }
}

(new UpdateLimitInsurer())->run();
