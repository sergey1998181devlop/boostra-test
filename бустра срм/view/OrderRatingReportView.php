<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'View.php';
require_once dirname(__DIR__) . '/api/YaMetric.php';

/**
 * Генерация отчёта по кредитному рейтингу
 * Class OrderRatingReportView
 */
class OrderRatingReportView extends View
{
    private $yandex_metric;

    public function __construct()
    {
        parent::__construct();
        $this->yandex_metric = new api\YaMetric\YaMetric();
    }

    public function fetch()
    {
        $metric_data = [];

        $filter_data = [
            'filter_client' => $this->request->get('filter_client') ?? '',
            'filter_offer' => $this->request->get('filter_offer') ?? '',
            'filter_group_by' => $this->request->get('filter_group_by') ?: 'day',
        ];

        $filter_date_start = 'now';
        $filter_date_end = 'now';

        $filter_date_range = $this->request->get('date_range') ?? '';

        if (!empty($filter_date_range)) {
            $filter_date_array = array_map('trim', explode('-', $filter_date_range));
            $filter_date_start = str_replace('.', '-', $filter_date_array[0]);
            $filter_date_end = str_replace('.', '-', $filter_date_array[1]);
        }

        $filter_data['filter_date_start'] = new \DateTime($filter_date_start);
        $filter_data['filter_date_end'] = new \DateTime($filter_date_end);
        $filter_data['filter_url_exists'] = 'https://boostra.ru/user/';

        $generate_totals = function (array $array): array {
            $keys = array_keys(current($array));
            $totals_array = array_combine($keys, array_fill(0, count($keys), 0));

            foreach ($array as $array_item) {
                foreach ($keys as $key) {
                    $totals_array[$key] += array_sum(
                        array_filter($array_item, fn($array_key) => $array_key === $key, ARRAY_FILTER_USE_KEY)
                    );
                }
            }
            return $totals_array;
        };

        $filter_data['referer'] = $this->config->front_url.'/user';
        $filter_data['is_completed'] = true;

        $metric_data['items'] = $this->getMetric($filter_data);
        $metric_data['totals'] = !empty($metric_data['items']) ? $generate_totals($metric_data['items']) : [];

        $this->design->assign('metric', $metric_data);
        $this->design->assign('ajax_url', '/order_rating_report');

        return $this->design->fetch('rating_report.tpl');
    }

    /**
     * Получает метрику
     * @param array $filter_data
     * @return array
     */
    public function getMetric(array $filter_data = []): array
    {
        $metric_db = $this->yandex_metric->getOffersFromDB($filter_data);
        $metric_yandex = $this->yandex_metric->getStatistic($this->yandex_metric::GOALS_CREDIT_RATING, $filter_data);

        return $this->yandex_metric->mergeData($metric_db, $metric_yandex, $filter_data);
    }
}