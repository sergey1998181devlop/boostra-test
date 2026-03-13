<?php

require_once 'View.php';

use App\Core\Application\Application;
use App\Service\CbrLinkClickReportService;

class CbrLinkClicksReportView extends View
{
    private $service;

    public function __construct()
    {
        parent::__construct();
        $app = Application::getInstance();
        $this->service = $app->make(CbrLinkClickReportService::class);
    }

    public function fetch()
    {
        if (!in_array('settings', $this->manager->permissions))
            return $this->design->fetch('403.tpl');

        $currentPageNum = $this->request->get('page', 'integer');
        $currentPageNum = max(1, $currentPageNum);

        $itemsPerPage = 20;

        $dateFrom = $this->request->get('date_from');
        $dateTo = $this->request->get('date_to');
        $filterApplied = !empty($dateFrom) || !empty($dateTo);

        $dateFrom = $dateFrom ?: date('Y-m-01');
        $dateTo = $dateTo ?: date('Y-m-d');

        $totalItems = $this->service->count($dateFrom, $dateTo);
        $totalPagesNum = ceil($totalItems / $itemsPerPage);
        $totalPagesNum = max(1, $totalPagesNum);
        $currentPageNum = min($currentPageNum, $totalPagesNum);

        $clicks = $this->service->getReport($dateFrom, $dateTo, $currentPageNum, $itemsPerPage);

        $this->design->assign_array([
            'clicks' => $clicks,
            'filter_applied' => $filterApplied,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'current_page_num' => $currentPageNum,
            'total_pages_num' => $totalPagesNum,
        ]);

        return $this->design->fetch('cbr_link_clicks_report.tpl');
    }
}