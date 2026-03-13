<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'View.php';

/**
 * Генерация отчёта по ссылкам отказникам
 * Class ReportPartnerHrefView
 */
class ReportPartnerHrefView extends View
{
    public function fetch()
    {

        $filter_data = [
            'filter_unique' => $this->request->get('filter_unique') ?? '',
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
        $filter_data['filter_date_end'] = new \DateTime($filter_date_end . ' 00:00:01');

        $partner_hrefs = $this->PartnerHref->getAll();

        $result = [];
        $totals = [
            'total_clicks' => 0,
            'total_views' => 0,
            'cv' => 0,
        ];

        foreach ($partner_hrefs as $partner_href) {
            $interval_period = '1 ' . $filter_data['filter_group_by'];

            $formatter = new \IntlDateFormatter('ru_RU', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);

            if ($filter_data['filter_group_by'] === 'month') {
                $formatter->setPattern('y г. MMM');
            } else {
                $formatter->setPattern('y.MM.dd');
            }

            $interval = DateInterval::createFromDateString($interval_period);
            $period = new DatePeriod($filter_data['filter_date_start'], $interval, $filter_data['filter_date_end']);

            foreach ($period as $dt) {

                if ($filter_data['filter_group_by'] === 'month') {
                    $date = $dt->format("Y-m");
                } else {
                    $date = $dt->format("Y-m-d");
                }

                $filter = [
                    'date' => $date,
                    'type_action' => 'click',
                    'href_id' => $partner_href->id,
                    'filter_group_by' => $filter_data['filter_group_by'],
                    'filter_unique' => $filter_data['filter_unique'],
                ];

                $total_clicks = $this->PartnerHref->getReportTotals($filter);

                $filter['type_action'] = 'view';
                $total_views = $this->PartnerHref->getReportTotals($filter);

                if ($total_views > 0 || $total_clicks > 0) {
                    $result[$formatter->format($dt)][] = [
                        'href' => $partner_href,
                        'total_clicks' => $total_clicks,
                        'total_views' => $total_views,
                        'cv' => $total_views > 0 ? round($total_clicks * 100 / $total_views, 2) : 0,
                    ];

                    $totals['total_clicks'] += $total_clicks;
                    $totals['total_views'] += $total_views;
                }
            }
        }

        $totals['cv'] = $totals['total_views'] > 0 ? round($totals['total_clicks'] * 100 / $totals['total_views'], 2) : 0;

        $this->design->assign('result', $result);
        $this->design->assign('totals', $totals);
        $this->design->assign('ajax_url', '/report_partner_href');

        return $this->design->fetch('partner_href_report.tpl');
    }
}