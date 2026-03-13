<?php

use App\Core\Application\Application;
use App\Modules\AdditionalServiceRecovery\Application\DTO\ExclusionRequest;
use App\Modules\AdditionalServiceRecovery\Application\Service\ExclusionManagementService;

require_once 'View.php';

class ServiceRecoveryExclusionView extends View
{
    private ExclusionManagementService $service;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        $app = Application::getInstance();
        $this->service = $app->make(ExclusionManagementService::class);
    }

    public function fetch(): string
    {
        if ($this->request->method('post')) {
            $action = $this->request->post('action', 'string');
            
            if ($action === 'add_exclusion') {
                $this->addExclusionAction();
            } elseif ($action === 'deactivate_exclusion') {
                $this->deactivateExclusionAction();
            }
        }

        $exclusions = $this->service->getActiveExclusions();
        $this->design->assign('exclusions', $exclusions);

        return $this->design->fetch('service_recovery/exclusions.tpl');
    }

    private function addExclusionAction(): void
    {
        try {
            $request = new ExclusionRequest(
                (int)$this->request->post('user_id', 'integer'),
                (int)$this->request->post('order_id', 'integer'),
                $this->request->post('service_key'),
                $this->request->post('reason', 'string'),
                $this->manager->id,
                $this->request->post('expires_at', 'string') ? new DateTime($this->request->post('expires_at', 'string')) : null
            );

            $this->service->addExclusion($request);

            $this->json_output(['success' => true, 'message' => 'Исключение добавлено']);
        } catch (Exception $e) {
            http_response_code(500);
            $this->json_output(['success' => false, 'error' => 'Произошла внутренняя ошибка сервера.']);
        }
    }

    private function deactivateExclusionAction(): void
    {
        $exclusionId = $this->request->post('exclusion_id', 'integer');
        if ($exclusionId) {
            $this->service->deactivateExclusion($exclusionId);
        }

        $this->json_output(['success' => true, 'message' => 'Исключение удалено']);
    }
}
