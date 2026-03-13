<?php

use App\Core\Application\Application;
use App\Modules\AdditionalServiceRecovery\Application\DTO\RecoveryFilterRequest;
use App\Modules\AdditionalServiceRecovery\Application\Service\RecoveryReporter;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\RuleRepository;

require_once 'View.php';

class ServiceRecoveryReportView extends View
{
    private RecoveryReporter $reporter;
    private RuleRepository $ruleRepository;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $app = Application::getInstance();
        $this->reporter = $app->make(RecoveryReporter::class);
        $this->ruleRepository = $app->make(RuleRepository::class);
    }

    /**
     * @throws Exception
     */
    public function fetch(): string
    {
        $filters = $this->createFiltersFromRequest();

        $report = $this->reporter->getReport($filters);
        $this->design->assign('report', $report);

        $allRules = $this->ruleRepository->findAll();
        $this->design->assign('all_rules', $allRules);

        $this->design->assign('filters', $filters);

        return $this->design->fetch('service_recovery/report.tpl');
    }

    /**
     * @throws Exception
     */
    private function createFiltersFromRequest(): RecoveryFilterRequest
    {
        $dateFrom = $this->request->get('date_from', 'string');
        $dateTo = $this->request->get('date_to', 'string');
        $ruleIds = (array)$this->request->get('rule_ids');

        $dateFromObj = $dateFrom ? new DateTime($dateFrom) : (new DateTime())->modify('-1 month');
        $dateToObj = $dateTo ? new DateTime($dateTo) : new DateTime();

        return new RecoveryFilterRequest($dateFromObj, $dateToObj, array_filter($ruleIds, 'is_numeric'));
    }
}
