<?php

declare(strict_types=1);

require_once 'View.php';
require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/../api/services/MissingService.php';

/**
 * Класс для вывода отчёта по отвалам менеджера
 */
class ReportMissingsManagerView extends View
{

    private MissingService $missingService;

    public function __construct()
    {
        parent::__construct();

        $this->missingService = new MissingService($this->db);
        $action = $this->request->get( 'action' );
        
        if($action && method_exists( self::class, $action )) {
            $this->{$action}();
        }
    }

    public function fetch(): string
    {
        $managerId = $this->request->get('manager_id', 'integer');
        $defaultFrom = date('Y-m-01 00:00:00'); // start of current month
        $defaultTo   = date('Y-m-d H:i:s');     // now
        $dateFrom = $this->normalizeDate($this->request->get('date_from'), $defaultFrom);
        $dateTo   = $this->normalizeDate($this->request->get('date_to'), $defaultTo);

        $manager = $this->managers->get_manager($managerId);
        $this->design->assign('manager_data', $manager);
        if (!empty($manager)) {
            $statistic = $this->missingService->getManagerStatisticsById((int)$managerId,$dateFrom, $dateTo);
            $this->design->assign('statistic', $statistic);

            $managerData = $this->missingService->getManagerIssueDetails((int)$managerId,$dateFrom, $dateTo);
            $this->design->assign('manager_details', $managerData);
        }

        $this->design->assign_array([
            'date_from' => date('Y-m-d', strtotime($dateFrom)),
            'date_to' => date('Y-m-d', strtotime($dateTo)),
            'manager_id' => $managerId,
            'report_uri' => strtok($_SERVER['REQUEST_URI'], '?'),
        ]);

        // Возвращаем шаблон
        return $this->design->fetch('report_missings_manager.tpl');
    }

    /**
     * Get normalized date in 'Y-m-d H:i:s' format or default.
     *
     * @param string|null $input
     * @param string      $default
     * @return string
     */
    private function normalizeDate(?string $input, string $default): string
    {
        return !empty($input) ? date('Y-m-d H:i:s', strtotime($input)) : $default;
    }
}
