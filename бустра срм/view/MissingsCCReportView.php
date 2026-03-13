<?php

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once dirname(__DIR__) . '/api/Helpers.php';

require_once 'View.php';

/**
 * Class MissingsCCReportView
 * Класс для работы с отчётом - эффективность сотрудников КЦ по отвалам
 */
class MissingsCCReportView extends View
{
    /**
     * Id менеджеров КЦ для выборки
     */
    public const MANAGER_IDS = [45, 125];

    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    /**
     * @throws Exception
     */
    public function fetch()
    {

        if ($this->request->get('ajax')) {
            $results = $this->getResults();
            $this->design->assign('results', $results);
        }

        return $this->design->fetch('missings_cc_report_view.tpl');
    }

    /**
     * Выбор фильтров
     * @return array
     */
    private function getFilterData(): array
    {
        return [
            'filter_date_order' => Helpers::getDataRange($this),
            'filter_manager_ids' => self::MANAGER_IDS,
            'filter_duration' => $this->request->get('filter_duration', 'integer'),
        ];
    }

    /**
     * Генерация данных
     * @return array
     * @throws Exception
     */
    private function getResults(): array
    {
        $filter_data = $this->getFilterData();
        return $this->managers->getMissingsOrdersCC($filter_data);
    }
}
