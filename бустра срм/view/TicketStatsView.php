<?php

use api\tickets\TicketDetailStats;
use api\tickets\TicketStatusStats;
use api\tickets\TicketSubjectStats;
use api\tickets\TicketTimeMetrics;
use App\Modules\TicketAssignment\Services\TicketAssignmentStatsService;
use App\Modules\TicketAssignment\Services\ComplaintsByManagerService;
use App\Modules\TicketAssignment\Services\ComplaintsByResponsibleService;

require_once 'View.php';
require_once 'Exports/TicketStatsExport.php';
require_once 'Exports/ComplaintsExport.php';

class TicketStatsView extends View
{
    private TicketDetailStats $ticketDetailStats;
    private TicketSubjectStats $ticketSubjectStats;
    private TicketTimeMetrics $ticketTimeMetrics;
    private TicketStatusStats $ticketStatusStats;
    private TicketAssignmentStatsService $ticketAssignmentStats;
    private ComplaintsByManagerService $complaintsByManagerService;
    private ComplaintsByResponsibleService $complaintsByResponsibleService;

    public function __construct()
    {
        parent::__construct();
        $this->ticketDetailStats = new TicketDetailStats($this->db, $this->tickets);
        $this->ticketSubjectStats = new TicketSubjectStats($this->db, $this->tickets);
        $this->ticketTimeMetrics = new TicketTimeMetrics($this->db, $this->tickets);
        $this->ticketStatusStats = new TicketStatusStats($this->db, $this->tickets);
        $this->ticketAssignmentStats = new TicketAssignmentStatsService();
        $this->complaintsByManagerService = new ComplaintsByManagerService($this->db);
        $this->complaintsByResponsibleService = new ComplaintsByResponsibleService($this->db);
    }

    public function fetch()
    {
        $action = $this->request->method('post')
            ? $this->request->post('action', 'string')
            : $this->request->get('action', 'string');

        switch ($action) {
            case 'detailed':
                $this->ticketDetailStats->getStatistics();
                break;
            case 'topics':
                $this->ticketSubjectStats->getStatistics();
                break;
            case 'statuses':
                $this->ticketTimeMetrics->getStatistics();
                break;
            case 'assignment_stats':
                $this->getAssignmentStatistics();
                break;
            case 'complaints_by_manager':
                $this->getComplaintsByManager();
                break;
            case 'download_complaints_by_manager_excel':
                $this->downloadComplaintsByManagerExcel();
                break;
            case 'complaints_by_responsible':
                $this->getComplaintsByResponsible();
                break;
            case 'download_complaints_by_responsible_excel':
                $this->downloadComplaintsByResponsibleExcel();
                break;
            case 'get_daily_stats':
                $this->getDailyStatistics();
                break;
            default:
                return $this->showStatistics();
        }
    }

    /**
     * Страница статистики
     *
     * @return string
     */
    private function showStatistics(): string
    {
        $managerId = $this->request->get('manager_id', 'integer');
        $priorityId = $this->request->get('priority_id', 'integer') ?: null;
        $dateFrom = $this->request->get('date_from', 'string') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->get('date_to', 'string') ?: date('Y-m-d');

        $channels = $this->tickets->getChannels();
        $managers = $this->managers->get_managers();

        $subjects = $this->tickets->getMainAndChildSubjects();
        $mainSubjects = $subjects['main'] ?? [];
        $childSubjects = $subjects['child'] ?? [];

        $this->design->assign_array([
            'mainSubjects' => $mainSubjects,
            'childSubjects' => $childSubjects,
            'channels' => $channels,
            'manager_id' => $managerId,
            'managers' => $managers,
            'statuses' => $this->tickets->getStatuses(),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,

            'detailedStats' => $this->ticketDetailStats->getStatistics(),
            'ticketTimeMetrics' => $this->ticketTimeMetrics->getStatistics($priorityId),
            'subjectStatistics' => $this->ticketSubjectStats->getStatistics($mainSubjects, $childSubjects),
            'statusStatistics' => $this->ticketStatusStats->getStatistics(),

            'priorities' => $this->tickets->getPriorities(),
            'selected_priority_id' => $priorityId,

            'report_uri' => strtok($_SERVER['REQUEST_URI'], '?'),
        ]);

        return $this->design->fetch('contact_center/ticket_statistic.tpl');
    }
    
    /**
     * Выгрузка отчета в Excel
     *
     * @return void
     */
    public function downloadExcel(): void
    {
        ['main' => $mainSubjects, 'child' => $childSubjects] = $this->tickets->getMainAndChildSubjects();
        $statistics = $this->ticketDetailStats->getStatistics();

        $data = [
            'parentData' => $statistics['parentData'],
            'childData' => $statistics['childData'],
            'channels' => $this->tickets->getChannels(),
            'statuses' => $this->tickets->getStatuses(),
            'mainSubjects' => $mainSubjects,
            'childSubjects' => $childSubjects
        ];

        $exporter = new TicketStatsExport($data);
        $writer = $exporter->export();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="statistics_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    /**
     * Выгрузка отчета жалоб по менеджерам в Excel
     *
     * @return void
     */
    public function downloadComplaintsByManagerExcel(): void
    {
        $dateFrom = $this->request->get('date_from', 'string') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->get('date_to', 'string') ?: date('Y-m-d');
        $type = $this->request->get('type', 'string') ?: 'collection';

        $data = $this->complaintsByManagerService->getComplaintsByManager($dateFrom, $dateTo, $type);

        $filenamePrefix = $type === 'additional_services'
            ? 'complaints_additional_by_manager'
            : 'complaints_collection_by_manager';

        $exporter = new ComplaintsExport(
            $data,
            'Жалобы по менеджерам (Взыскание)',
            'Менеджер',
            $filenamePrefix
        );
        $exporter->download();
    }

    /**
     * Получить статистику автоназначения
     */
    private function getAssignmentStatistics(): void
    {
        $dateFrom = $this->request->get('date_from', 'string') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->get('date_to', 'string') ?: date('Y-m-d');

        $stats = [
            'distribution' => $this->ticketAssignmentStats->getDistributionStats($dateFrom, $dateTo),
            'managers' => $this->ticketAssignmentStats->getManagerStats($dateFrom, $dateTo),
            'efficiency' => $this->ticketAssignmentStats->getEfficiencyStats($dateFrom, $dateTo)
        ];

        $this->response->json_output([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Получить статистику жалоб по менеджерам
     */
    private function getComplaintsByManager(): void
    {
        $dateFrom = $this->request->get('date_from', 'string') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->get('date_to', 'string') ?: date('Y-m-d');
        $type = $this->request->get('type', 'string') ?: 'collection';

        try {
            $stats = $this->complaintsByManagerService->getComplaintsByManager($dateFrom, $dateTo, $type);

            $this->response->json_output([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Ошибка при получении статистики: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Получить статистику жалоб по ответственным лицам
     */
    private function getComplaintsByResponsible(): void
    {
        $dateFrom = $this->request->get('date_from', 'string') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->get('date_to', 'string') ?: date('Y-m-d');
        $type = $this->request->get('type', 'string') ?: 'collection';

        try {
            $stats = $this->complaintsByResponsibleService->getComplaintsByResponsible($dateFrom, $dateTo, $type);

            $this->response->json_output([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Ошибка при получении статистики: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Выгрузка отчета жалоб по ответственным в Excel
     *
     * @return void
     */
    public function downloadComplaintsByResponsibleExcel(): void
    {
        $dateFrom = $this->request->get('date_from', 'string') ?: date('Y-m-d', strtotime('-30 days'));
        $dateTo = $this->request->get('date_to', 'string') ?: date('Y-m-d');
        $type = $this->request->get('type', 'string') ?: 'collection';

        $data = $this->complaintsByResponsibleService->getComplaintsByResponsible($dateFrom, $dateTo, $type);

        $filenamePrefix = $type === 'additional_services'
            ? 'complaints_additional_by_responsible'
            : 'complaints_collection_by_responsible';

        $exporter = new ComplaintsExport(
            $data,
            'Жалобы по ответственным (Взыскание)',
            'Ответственный по займу',
            $filenamePrefix
        );
        $exporter->download();
    }

    /**
     * Получение статистики по дням для месяца
     * @return void
     */
    private function getDailyStatistics(): void
    {
        $month = $this->request->get('month', 'string');

        try {
            $dailyStats = $this->ticketDetailStats->getDailyStatsForMonth($month);

            $this->json_output([
                'success' => true,
                'data' => $dailyStats
            ]);
        } catch (Exception $e) {
            $this->response->json_output([
                'success' => false,
                'message' => 'Ошибка при получении статистики: ' . $e->getMessage()
            ]);
        }

    }
}