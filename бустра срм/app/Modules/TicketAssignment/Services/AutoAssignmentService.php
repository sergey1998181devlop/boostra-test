<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\TicketAssignment\Repositories\ManagerRepository;
use App\Modules\TicketAssignment\Repositories\TicketAssignmentRepository;
use App\Repositories\UserRepository;
use App\Modules\TicketAssignment\Dto\AssignmentDto;
use App\Modules\TicketAssignment\Enums\CompetencyLevel;
use App\Modules\TicketAssignment\Enums\TicketType;
use App\Modules\TicketAssignment\Contracts\AutoAssignmentServiceInterface;
use App\Modules\TicketAssignment\Contracts\ManagerFinderServiceInterface;
use App\Modules\TicketAssignment\Contracts\SLAEscalationServiceInterface;

/**
 * Сервис автоматического назначения тикетов
 */
class AutoAssignmentService implements AutoAssignmentServiceInterface
{
    /** @var ManagerRepository */
    private $managerRepository;

    /** @var CompetencyService */
    private $competencyService;

    /** @var CoefficientCalculatorService */
    private $coefficientCalculator;

    /** @var TicketAssignmentRepository */
    private $assignmentRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var ManagerFinderService */
    private $managerFinderService;

    /** @var SLAEscalationService */
    private $slaEscalationService;

    /** @var \Simpla */
    private $db;

    /** @var array */
    private $assignedInThisRun = [];

    /** @var int ID системного пользователя для логирования */
    private const MANAGER_SYSTEM_ID = 50;

    public function __construct(
        ManagerRepository $managerRepository,
        CompetencyService $competencyService,
        CoefficientCalculatorService $coefficientCalculator,
        TicketAssignmentRepository $assignmentRepository,
        UserRepository $userRepository,
        ManagerFinderServiceInterface $managerFinderService,
        SLAEscalationServiceInterface $slaEscalationService,
        \Simpla $db
    ) {
        $this->managerRepository = $managerRepository;
        $this->competencyService = $competencyService;
        $this->coefficientCalculator = $coefficientCalculator;
        $this->assignmentRepository = $assignmentRepository;
        $this->userRepository = $userRepository;
        $this->managerFinderService = $managerFinderService;
        $this->slaEscalationService = $slaEscalationService;
        $this->db = $db;
    }

    /**
     * Назначает все неназначенные тикеты на подходящих менеджеров
     * Включает проверку SLA и эскалацию
     *
     * @return array Результат операции с количеством назначенных и ошибок
     */
    public function assignUnassignedTickets(): array
    {
        $this->assignedInThisRun = [];
        $result = [
            'assigned' => 0,
            'failed' => 0,
            'escalated' => 0,
            'errors' => []
        ];

        // Шаг 1: Проверяем SLA и эскалируем нарушенные тикеты
        try {
            $slaResult = $this->slaEscalationService->checkAndEscalateViolations();
            $result['escalated'] = $slaResult['escalated'];
        } catch (\Exception $e) {
            $result['errors'][] = "Ошибка при проверке SLA: " . $e->getMessage();
        }

        // Шаг 2: Назначаем обычные тикеты (включая эскалированные)
        try {
            $tickets = $this->assignmentRepository->getUnassignedTickets();
            if (empty($tickets)) {
                return $result;
            }

            foreach ($tickets as $ticket) {
                try {
                    // Для эскалированных тикетов используем специальную логику
                    $isEscalated = $this->slaEscalationService->isEscalated($ticket);
                    
                    if ($isEscalated) {
                        $managerId = $this->managerFinderService->findEscalationManager($ticket);
                    } else {
                        $managerId = $this->managerFinderService->findAvailableManager($ticket);
                    }

                    $assignResult = $this->assignTicketToManager($ticket, $managerId);
                    if ($assignResult['success']) {
                        $result['assigned']++;
                    } else {
                        $result['failed']++;
                        $result['errors'][] = "Тикет #{$ticket->id}: {$assignResult['message']}";
                    }
                } catch (\RuntimeException $e) {
                    $result['failed']++;
                    $result['errors'][] = "Тикет #{$ticket->id}: {$e->getMessage()}";
                } catch (\Exception $e) {
                    $result['failed']++;
                    $result['errors'][] = "Тикет #{$ticket->id}: " . $e->getMessage();
                }
            }
        } catch (\Exception $e) {
            $result['errors'][] = "Ошибка при получении неназначенных тикетов: " . $e->getMessage();
        }

        return $result;
    }

    /**
     * Назначить тикет на менеджера
     */
    public function assignTicketToManager(object $ticket, int $managerId): array
    {
        try {
            $type = TicketType::getBySubject($ticket->subject_id, $ticket->subject_parent_id);
            $overdueDays = $this->managerFinderService->calculateOverdueDays($ticket);
            // Если не удалось рассчитать просрочку, используем 0 (SOFT уровень)
            $overdueDays = $overdueDays ?? 0;
            $requiredLevel = CompetencyLevel::getByOverdueDays($overdueDays);
            $coefficient = $this->coefficientCalculator->calculateTotalCoefficient(
                $overdueDays, 
                $ticket->priority_id ?? 1
            );

            $assignment = new AssignmentDto(
                $ticket->id,
                $managerId,
                $type,
                $overdueDays,
                $requiredLevel,
                $coefficient
            );

            $this->assignTicket($assignment);
            return ['success' => true];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Назначает тикет на менеджера
     */
    private function assignTicket(AssignmentDto $assignment): void
    {
        // Обновляем тикет
        $this->managerRepository->assignTicket(
            $assignment->getTicketId(),
            $assignment->getManagerId()
        );

        // Сохраняем информацию о назначении
        $this->assignmentRepository->save($assignment);

        // Устанавливаем SLA дедлайн для новых тикетов
        $this->slaEscalationService->setSLADeadline($assignment->getTicketId(), 1);

        // Логируем назначение
        $this->assignmentRepository->logTicketHistory(
            $assignment->getTicketId(),
            'manager_id',
            '',
            $assignment->getManagerId(),
            self::MANAGER_SYSTEM_ID,
            sprintf(
                'Автоназначение: просрочка %d дн., уровень %s, коэффициент %.2f',
                $assignment->getOverdueDays() ?? 0,
                $assignment->getComplexityLevel(),
                $assignment->getCoefficient()
            )
        );

        // Добавляем в список назначенных в этом запуске
        $this->assignedInThisRun[] = $assignment->getManagerId();
    }
}