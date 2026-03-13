<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\TicketAssignment\Repositories\ManagerRepository;
use App\Modules\TicketAssignment\Enums\CompetencyLevel;
use App\Modules\TicketAssignment\Enums\TicketType;
use App\Modules\TicketAssignment\Enums\SLAEscalationLevel;
use App\Modules\TicketAssignment\Contracts\ManagerFinderServiceInterface;
use App\Repositories\UserRepository;
use App\Modules\Clients\Domain\Service\OverdueCalculator;

/**
 * Сервис для поиска подходящих менеджеров
 */
class ManagerFinderService implements ManagerFinderServiceInterface
{
    /** @var ManagerRepository */
    private $managerRepository;

    /** @var CompetencyService */
    private $competencyService;

    /** @var UserRepository */
    private $userRepository;

    /** @var OverdueCalculator */
    private $overdueCalculator;

    /** @var \Simpla */
    private $db;

    /** @var array Кэш для менеджеров эскалации */
    private $escalationManagersCache = [];

    public function __construct(
        ManagerRepository $managerRepository,
        CompetencyService $competencyService,
        UserRepository $userRepository,
        OverdueCalculator $overdueCalculator,
        \Simpla $db
    ) {
        $this->managerRepository = $managerRepository;
        $this->competencyService = $competencyService;
        $this->userRepository = $userRepository;
        $this->overdueCalculator = $overdueCalculator;
        $this->db = $db;
    }

    /**
     * Найти подходящего менеджера для тикета
     */
    public function findAvailableManager(object $ticket): int
    {
        $type = TicketType::getBySubject($ticket->subject_id, $ticket->subject_parent_id);
        if (!$type) {
            throw new \RuntimeException('Неизвестный тип тикета');
        }

        $overdueDays = $this->calculateOverdueDays($ticket);
        // Если не удалось рассчитать просрочку, используем 0 (SOFT уровень)
        $overdueDays = $overdueDays ?? 0;
        $requiredLevel = CompetencyLevel::getByOverdueDays($overdueDays);

        $authorizedManagers = $this->managerRepository->getAuthorizedManagers($type);
        $autoAssignManagers = $this->managerRepository->getAutoAssignManagers();
        $availableManagers = array_intersect($authorizedManagers, $autoAssignManagers);

        if (empty($availableManagers)) {
            throw new \RuntimeException('Нет доступных менеджеров для автоназначения');
        }

        if (empty($ticket->order_id)) {
            $manager = $this->managerRepository->findLeastLoadedManager($availableManagers);
            if (!$manager) {
                throw new \RuntimeException('Нет свободных менеджеров онлайн');
            }
            return (int)$manager->id;
        }

        $competentManagers = $this->competencyService->getManagersByLevel($type, $requiredLevel);
        $availableCompetentManagers = array_intersect($competentManagers, $availableManagers);

        if (empty($availableCompetentManagers)) {
            throw new \RuntimeException("Нет менеджеров с требуемым уровнем компетенции ({$requiredLevel})");
        }

        $manager = $this->managerRepository->findLeastLoadedManager($availableCompetentManagers);
        if (!$manager) {
            throw new \RuntimeException('Нет свободных менеджеров онлайн');
        }

        return (int)$manager->id;
    }

    /**
     * Найти менеджера для эскалированного тикета
     */
    public function findEscalationManager(object $ticket): int
    {
        $ticketData = json_decode($ticket->data, true) ?: [];
        $escalationLevel = $ticketData['escalation_level'] ?? 1;
        
        $escalationManagers = $this->getEscalationManagers($escalationLevel);
        if (empty($escalationManagers)) {
            throw new \RuntimeException('Нет доступных менеджеров для эскалации');
        }

        $selectedManager = $this->selectEscalationManager($escalationManagers, $ticket->subject_id);
        if (!$selectedManager) {
            throw new \RuntimeException('Не удалось найти подходящего менеджера для эскалации');
        }

        return $selectedManager;
    }

    /**
     * Получить менеджеров для эскалации из компетенций с кэшированием
     */
    private function getEscalationManagers(int $level): array
    {
        $cacheKey = "level_{$level}";
        
        if (!isset($this->escalationManagersCache[$cacheKey])) {
            // Получаем менеджеров для обоих типов тикетов
            $collectionManagers = $this->competencyService->getSLAEscalationManagers('collection', $level);
            $additionalServicesManagers = $this->competencyService->getSLAEscalationManagers('additional_services', $level);
            
            $this->escalationManagersCache[$cacheKey] = array_merge($collectionManagers, $additionalServicesManagers);
        }
        
        return $this->escalationManagersCache[$cacheKey];
    }

    /**
     * Выбрать менеджера для эскалации
     */
    private function selectEscalationManager(array $managerIds, int $subjectId): ?int
    {
        if (empty($managerIds)) {
            return null;
        }

        $type = TicketType::getBySubject($subjectId);
        if (!$type) {
            return null;
        }

        // Получаем менеджеров для конкретного типа тикета
        $escalationManagers = $this->competencyService->getSLAEscalationManagers($type, SLAEscalationLevel::LEVEL_2);
        $availableManagers = array_intersect($managerIds, $escalationManagers);

        if (empty($availableManagers)) {
            return null;
        }

        // Проверяем нагрузку и выбираем менеджера с минимальной нагрузкой
        return $this->managerRepository->findLeastLoadedEscalationManager($availableManagers);
    }

    /**
     * Рассчитать дни просрочки для тикета
     */
    public function calculateOverdueDays(object $ticket): ?int
    {
        if (empty($ticket->client_id)) {
            return null;
        }

        $client = $this->userRepository->getById($ticket->client_id);
        if (!$client || empty($client->loan_history)) {
            return null;
        }

        $loanHistory = json_decode($client->loan_history, true);
        if (!is_array($loanHistory)) {
            return null;
        }

        $activeLoan = null;
        foreach ($loanHistory as $loan) {
            if (empty($loan['close_date']) || $loan['close_date'] === '') {
                $activeLoan = $loan;
                break;
            }
        }

        if (!$activeLoan) {
            return null;
        }

        return $this->overdueCalculator->calculatePlanCloseOverdueDays($activeLoan);
    }

    /**
     * Очистить кэш менеджеров эскалации
     */
    public function clearEscalationManagersCache(): void
    {
        $this->escalationManagersCache = [];
    }
}
