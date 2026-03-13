<?php

namespace App\Modules\AdditionalServiceRecovery\Domain\Service;

use App\Modules\AdditionalServiceRecovery\Domain\Model\ServiceCandidate;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\ChangelogAdapter;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\OrderDataAdapter;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\RevenueTrackingRepository;
use App\Modules\Notifications\Service\NotificationService;
use App\Modules\Shared\AdditionalServices\Enum\AdditionalServiceKey;

/**
 * Сервис включения услуг.
 * Отвечает за весь бизнес-процесс восстановления услуги.
 */
class ServiceEnabler
{
    private OrderDataAdapter $orderDataAdapter;
    private ChangelogAdapter $changelogAdapter;
    private NotificationService $notificationService;
    private RevenueTrackingRepository $revenueRepository;

    public function __construct(
        OrderDataAdapter $orderDataAdapter,
        ChangelogAdapter $changelogAdapter,
        NotificationService $notificationService,
        RevenueTrackingRepository $revenueRepository
    ) {
        $this->orderDataAdapter = $orderDataAdapter;
        $this->changelogAdapter = $changelogAdapter;
        $this->notificationService = $notificationService;
        $this->revenueRepository = $revenueRepository;
    }

    /**
     * Включает дополнительную услугу для кандидата и инициирует отслеживание дохода.
     *
     * @param ServiceCandidate $candidate Кандидат на восстановление
     * @param int $initiatorManagerId ID менеджера, запустившего процесс (для логов)
     * @param int $processLogId ID записи в логе процессов (для связи с доходом)
     * @param int $ruleId ID сработавшего правила (для связи с доходом)
     * @return bool
     */
    public function reenableService(ServiceCandidate $candidate, int $initiatorManagerId, int $processLogId, int $ruleId): bool
    {
        $this->orderDataAdapter->setServiceStatus(
            $candidate->getOrderId(),
            $candidate->getServiceKey(),
            '0'
        );

        $this->changelogAdapter->logServiceReEnabled(
            $candidate->getOrderId(),
            $candidate->getUserId(),
            $candidate->getServiceKey(),
            $initiatorManagerId
        );

        $serviceName = AdditionalServiceKey::from($candidate->getServiceKey())->getLabel();

        /*$this->notificationService->sendNotification([
            'to_user' => $candidate->getManagerId(),
            'subject' => 'Автоматическое восстановление доп. услуги',
            'message' => "По заявке №{$candidate->getOrderId()} была автоматически восстановлена услуга '{$serviceName}'.",
            'from_user' => $initiatorManagerId
        ]);*/

        $this->revenueRepository->createTrackingRecord($candidate, $processLogId, $ruleId);

        return true;
    }
}
