<?php

require_once dirname(__DIR__) . '/api/Helpers.php';

require_once 'View.php';

/**
 * Class CreditRatingPaysReportView
 * Класс для формирования отчёта по КР
 */
class CreditRatingPaysReportView extends View
{
    /**
     * @throws Exception
     */
    public function fetch()
    {
        if (!empty($this->request->get('ajax', 'integer'))) {
            $results = $this->getResults();
            $this->design->assign('results', $results);

            $totals = $this->getTotals($results);
            $this->design->assign('totals', $totals);
        }
        return $this->design->fetch('credit_rating_pays_report_view.tpl');
    }

    /**
     * Получает данные
     * @return array
     */
    private function getResults(): array
    {
        $filter_date = Helpers::getDataRange($this);
        $filter_data = [
            'filter_created_date' => $filter_date,
        ];

        return $this->credit_rating->getPayments($filter_data);
    }

    /**
     * Получаем результирующие данные
     * @param array $results
     * @return float|int
     */
    private function getTotals(array $results)
    {
        return array_sum(array_column($results, 'amount'));
    }
}
