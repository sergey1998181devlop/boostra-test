<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\Shared\Repositories\SettingsRepository;
use App\Modules\TicketAssignment\Repositories\TicketAssignmentRepository;
use App\Modules\TicketAssignment\Enums\SLAEscalationLevel;
use App\Modules\TicketAssignment\Enums\TicketType;
use App\Modules\TicketAssignment\Contracts\SLAEscalationServiceInterface;
use App\Modules\Notifications\Service\NotificationService;
use Carbon\Carbon;

/**
 * Сервис для работы с SLA эскалацией
 */
class SLAEscalationService implements SLAEscalationServiceInterface
{
    /** @var TicketAssignmentRepository */
    private $assignmentRepository;

    /** @var CompetencyService */
    private $competencyService;

    /** @var NotificationService */
    private $notificationService;

    /** @var SettingsRepository */
    private $settingsRepository;

    /** @var \Simpla */
    private $db;

    /** @var \Simpla */
    private $simpla;

    /** @var int ID системного пользователя для логирования */
    private const MANAGER_SYSTEM_ID = 50;

    /** @var int Максимальный уровень эскалации */
    private const MAX_ESCALATION_LEVEL = SLAEscalationLevel::LEVEL_2;

    /** @var array Время SLA в часах для разных уровней по умолчанию */
    private const DEFAULT_SLA_TIMEOUTS = [
        1 => 4,
        2 => 8
    ];

    public function __construct(
        TicketAssignmentRepository $assignmentRepository,
        CompetencyService $competencyService,
        NotificationService $notificationService,
        SettingsRepository $settingsRepository,
        \Simpla $db,
        \Simpla $simpla
    ) {
        $this->assignmentRepository = $assignmentRepository;
        $this->competencyService = $competencyService;
        $this->notificationService = $notificationService;
        $this->settingsRepository = $settingsRepository;
        $this->db = $db;
        $this->simpla = $simpla;
    }

    public function checkAndEscalateViolations(): array
    {
        $result = [
            'checked' => 0,
            'escalated' => 0,
            'errors' => []
        ];

        $violatedTickets = $this->assignmentRepository->getSLAViolatedTickets();
        
        foreach ($violatedTickets as $ticket) {
            $result['checked']++;
            
            try {
                $escalationResult = $this->escalateTicket($ticket);
                if ($escalationResult['success']) {
                    $result['escalated']++;
                } else {
                    $result['errors'][] = "Тикет #{$ticket->id}: {$escalationResult['message']}";
                }
            } catch (\Exception $e) {
                $result['errors'][] = "Тикет #{$ticket->id}: " . $e->getMessage();
            }
        }

        return $result;
    }

    public function escalateTicket(object $ticket): array
    {
        $ticketData = json_decode($ticket->data, true) ?: [];
        $currentLevel = $ticketData['escalation_level'] ?? 1;
        $newLevel = $currentLevel + 1;
        
        if ($newLevel > self::MAX_ESCALATION_LEVEL) {
            return [
                'success' => false,
                'message' => 'Достигнут максимальный уровень эскалации'
            ];
        }

        $this->updateSLAData($ticket->id, $newLevel);
        $this->logEscalation($ticket, $newLevel);

        try {
            $this->sendEscalationNotifications($ticket, $newLevel);
        } catch (\Throwable $e) {
            $this->simpla->logging('error', '', '', 'SLA notify failed for ticket #' . $ticket->id . ': ' . $e->getMessage(), 'sla_errors.txt');
        }

        return ['success' => true];
    }

    public function getSLATimeout(int $level): int
    {
        $defaultTimeout = self::DEFAULT_SLA_TIMEOUTS[$level] ?? 4;
        $jsonSettings = $this->settingsRepository->get('sla_settings', '{}');
        $settings = json_decode($jsonSettings, true) ?: [];
        $timeoutKey = "timeout_level_{$level}";
        return (int)($settings[$timeoutKey] ?? $defaultTimeout);
    }

    public function setSLADeadline(int $ticketId, int $level = 1): void
    {
        $timeoutHours = $this->getSLATimeout($level);
        $deadline = Carbon::now()->addHours($timeoutHours);
        
        $this->db->query("
            UPDATE s_mytickets 
            SET data = JSON_SET(COALESCE(data, '{}'), 
                '$.sla_deadline', ?,
                '$.escalation_level', ?,
                '$.sla_timeout_hours', ?)
            WHERE id = ?
        ", $deadline->format('Y-m-d H:i:s'), $level, $timeoutHours, $ticketId);
    }

    private function updateSLAData(int $ticketId, int $level): void
    {
        $timeoutHours = $this->getSLATimeout($level);
        $newDeadline = Carbon::now()->addHours($timeoutHours);
        
        $this->db->query("
            UPDATE s_mytickets 
            SET data = JSON_SET(COALESCE(data, '{}'), 
                '$.escalation_level', ?,
                '$.escalated_at', NOW(),
                '$.sla_deadline', ?,
                '$.sla_timeout_hours', ?)
            WHERE id = ?
        ", $level, $newDeadline->format('Y-m-d H:i:s'), $timeoutHours, $ticketId);
    }

    private function logEscalation(object $ticket, int $level): void
    {
        $this->db->query("
            INSERT INTO s_tickets_history 
            SET ticket_id = ?, field_name = ?, old_value = ?, new_value = ?, 
                changed_by = ?, changed_at = NOW(), comment = ?
        ", $ticket->id, 'escalation', '', $level, self::MANAGER_SYSTEM_ID, 
           "Автоматическая эскалация уровня {$level}");
    }

    private function sendEscalationNotifications(object $ticket, int $level): void
    {
        $linkToTicket = "/tickets/{$ticket->id}";
        $levelNames = [
            SLAEscalationLevel::LEVEL_1 => 'старшему специалисту',
            SLAEscalationLevel::LEVEL_2 => 'руководителю'
        ];

        $levelName = $levelNames[$level] ?? "уровню {$level}";
        $type = TicketType::getBySubject($ticket->subject_id, $ticket->subject_parent_id);
        if (!$type) {
            return;
        }

        $recipientIds = $this->competencyService->getSLAEscalationManagers($type, $level);
        if (empty($recipientIds)) {
            return;
        }

        $subject = 'Эскалация тикета';
        $message = "Тикет #{$ticket->id} эскалирован на {$levelName}. {$linkToTicket}";

        $this->notificationService->sendNotificationToMultipleManagers(
            $recipientIds,
            $subject,
            $message,
            self::MANAGER_SYSTEM_ID
        );
    }

    public function isEscalated(object $ticket): bool
    {
        $ticketData = json_decode($ticket->data ?? '{}', true) ?: [];
        return isset($ticketData['escalation_level']) && (int)$ticketData['escalation_level'] > 1;
    }
}
