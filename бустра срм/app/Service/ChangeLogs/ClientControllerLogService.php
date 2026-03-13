<?php

namespace App\Service\ChangeLogs;

use App\Enums\LogAction;

class ClientControllerLogService
{
    const systemManagerId = 50;

    private ActionLoggerService $actionLogger;

    public function __construct(ActionLoggerService $actionLogger)
    {
        $this->actionLogger = $actionLogger;
    }

    public function blockAccount(int $userId, int $managerId = self::systemManagerId): void
    {
        $this->addLog(new LogAction(LogAction::BLOCK_ACCOUNT), $userId, $managerId);
    }

    private function addLog(LogAction $logAction, int $userId, int $managerId): void
    {
        $this->actionLogger->log(
            $logAction,
            $userId,
            [
                'manager_id' => $managerId,
                'user_id' => $userId,
                'order_id' => null,
                'created' => date('Y-m-d H:i:s'),
            ]
        );
    }

    public function unblockAccount(int $userId, int $managerId = self::systemManagerId): void
    {
        $this->addLog(new LogAction(LogAction::UNBLOCK_ACCOUNT), $userId, $managerId);
    }
}
