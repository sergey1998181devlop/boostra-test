<?php

use App\Core\Application\Application;
use App\Modules\AdditionalServiceRecovery\Application\DTO\RuleRequest;
use App\Modules\AdditionalServiceRecovery\Application\Service\RecoveryCoordinator;
use App\Modules\AdditionalServiceRecovery\Application\Service\RuleManagementService;
use App\Modules\AdditionalServiceRecovery\Domain\Model\RecoveryRule;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\ProcessLogRepository;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceStage;

require_once 'View.php';

class ServiceRecoveryRuleView extends View
{
    private RuleManagementService $service;
    private RecoveryCoordinator $recoveryCoordinator;
    private ProcessLogRepository $processLogRepository;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->service = $app->make(RuleManagementService::class);
        $this->recoveryCoordinator = $app->make(RecoveryCoordinator::class);
        $this->processLogRepository = $app->make(ProcessLogRepository::class);
    }

    /**
     * Основной метод, который вызывается для обработки запроса.
     * Выполняет роль роутера.
     * @throws Exception
     */
    public function fetch(): string
    {
        if ($this->request->method('post')) {
            $action = $this->request->post('action', 'string');

            switch ($action) {
                case 'save_rule':
                    $this->saveAction();
                    break;

                case 'toggle_rule':
                    $this->toggleAction();
                    break;
                    
                case 'get_rule_runs':
                    $this->getRuleRunsAction();
                    break;
                    
                case 'run_rule':
                    $this->runRuleAction();
                    break;
            }
        }
        
        return $this->renderRuleList();
    }

    /**
     * Отображает страницу со списком правил.
     * @throws Exception
     */
    private function renderRuleList(): string
    {
        $rulesObjects = $this->service->getRules();
        $rules = array_map(fn(RecoveryRule $rule) => $rule->toArray(), $rulesObjects);
        
        $this->design->assign_array([
            'rules' => $rules,
            'all_service_keys' => AdditionalServiceKey::keyLabelList(),
            'all_stages' => AdditionalServiceStage::keyLabelList(),
            'services_by_stage_json' => json_encode(AdditionalServiceKey::getServicesByStage(), JSON_UNESCAPED_UNICODE)
        ]);

        return $this->design->fetch('service_recovery/rules.tpl');
    }

    /**
     * Обрабатывает создание или обновление правила.
     */
    private function saveAction()
    {
        try {
            $ruleData = $this->request->post();
            $ruleId = $this->request->post('id', 'integer');

            if (is_string($ruleData)) {
                parse_str($ruleData, $ruleData);
            } else {
                $ruleData = (array)$ruleData;
            }

            $managerId = $this->manager->id ?? 0;

            $ruleRequestDTO = new RuleRequest($ruleData);

            if ($ruleId) {
                $resultRule = $this->service->updateRule($ruleId, $ruleRequestDTO, $managerId);
            } else {
                $resultRule = $this->service->createRule($ruleRequestDTO, $managerId);
            }

            $this->json_output(['success' => true, 'rule' => $resultRule->toArray()]);

        } catch (InvalidArgumentException $e) {
            http_response_code($e->getCode());
            $this->json_output(['success' => false, 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            $this->json_output(['success' => false, 'error' => 'Произошла внутренняя ошибка сервера.']);
        }
    }

    /**
     * Переключает активность правила.
     */
    private function toggleAction()
    {
        try {
            $ruleId = $this->request->post('id', 'integer');
            if (empty($ruleId)) {
                throw new InvalidArgumentException('Не указан ID правила для переключения.');
            }

            $rule = $this->service->getRule($ruleId);
            if ($rule === null) {
                throw new RuntimeException('Правило не найдено.');
            }

            $ruleData = $rule->toArray();
            $ruleData['is_active'] = !$ruleData['is_active'];

            $ruleRequestDTO = new RuleRequest($ruleData);

            $managerId = $this->manager->id ?? 0;
            $this->service->updateRule($ruleId, $ruleRequestDTO, $managerId);

            $this->json_output(['success' => true]);

        } catch (InvalidArgumentException $e) {
            http_response_code($e->getCode());
            $this->json_output(['success' => false, 'error' => $e->getMessage()]);
        } catch (Exception $e) {
            http_response_code(500);
            $this->json_output(['success' => false, 'error' => 'Произошла внутренняя ошибка сервера.']);
        }
    }

    private function getRuleRunsAction()
    {
        $ruleId = $this->request->post('id', 'integer');
        if (empty($ruleId)) {
            $this->json_output(['success' => false, 'error' => 'Rule ID not provided']);
            return;
        }

        $runs = $this->processLogRepository->findRunsForRule($ruleId);

        $this->json_output(['success' => true, 'runs' => $runs]);
    }

    /**
     * Запускает выполнение одного правила.
     */
    private function runRuleAction()
    {
        try {
            $ruleId = $this->request->post('id', 'integer');
            if (empty($ruleId)) {
                throw new InvalidArgumentException('Не указан ID правила для запуска.');
            }
            $managerId = $this->manager->id ?? 0;

            $result = $this->recoveryCoordinator->runSingleRule($ruleId, $managerId);

            $this->json_output(['success' => true, 'result' => $result]);
        } catch (Exception $e) {
            http_response_code(500);
            $this->json_output(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}