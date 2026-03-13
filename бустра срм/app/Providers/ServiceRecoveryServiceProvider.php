<?php

namespace App\Providers;

use App\Core\Application\Container\ServiceProvider;
use App\Modules\AdditionalServiceRecovery\Application\Service\ExclusionManagementService;
use App\Modules\AdditionalServiceRecovery\Application\Service\RecoveryCoordinator;
use App\Modules\AdditionalServiceRecovery\Application\Service\RecoveryReporter;
use App\Modules\AdditionalServiceRecovery\Application\Service\RevenueAnalyzer;
use App\Modules\AdditionalServiceRecovery\Application\Service\RuleManagementService;
use App\Modules\AdditionalServiceRecovery\Domain\Service\ServiceEnabler;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\ChangelogAdapter;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\OrderDataAdapter;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\Soap1cAdapter;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\CandidateRepository;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\ExclusionRepository;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\ProcessLogRepository;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\RevenueTrackingRepository;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Repository\RuleRepository;
use App\Modules\Manager\Repository\ManagerRepository;
use App\Modules\Notifications\Repository\NotificationRepository;
use App\Modules\Notifications\Service\NotificationService;
use Changelogs;
use Database;
use OrderData;
use Soap1c;

/**
 * Сервис-провайдер для модуля восстановления дополнительных услуг.
 */
class ServiceRecoveryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->container->bind(\Database::class, function () {
            return new Database();
        });
        
        // --- Адаптеры для Legacy ---
        $this->container->bind(OrderDataAdapter::class, function () {
            return new OrderDataAdapter(new OrderData());
        });
        $this->container->bind(ChangelogAdapter::class, function () {
            return new ChangelogAdapter(new Changelogs());
        });
        $this->container->bind(Soap1cAdapter::class, function () {
            return new Soap1cAdapter(new Soap1c());
        });

        // --- Репозитории ---
        $this->container->bind(RuleRepository::class, function () {
            return new RuleRepository(
                $this->container->make(\Database::class)
            );
        });
        $this->container->bind(ManagerRepository::class, function () {
            return new ManagerRepository(
                $this->container->make(\Database::class)
            );
        });
        $this->container->bind(ProcessLogRepository::class, function () {
            return new ProcessLogRepository($this->container->make(\Database::class));
        });
        $this->container->bind(ExclusionRepository::class, function () {
            return new ExclusionRepository($this->container->make(\Database::class));
        });
        $this->container->bind(NotificationRepository::class, function () {
            return new NotificationRepository($this->container->make(\Database::class));
        });
        $this->container->bind(RevenueTrackingRepository::class, function () {
            return new RevenueTrackingRepository($this->container->make(\Database::class));
        });

        $this->container->bind(CandidateRepository::class, function () {
            return new CandidateRepository(
                $this->container->make(\Database::class),
                $this->container->make(ManagerRepository::class),
            );
        });

        // --- Сервисы ---
        $this->container->bind(NotificationService::class, function () {
            return new NotificationService(
                $this->container->make(NotificationRepository::class)
            );
        });
        $this->container->bind(RuleManagementService::class, function () {
            return new RuleManagementService(
                $this->container->make(RuleRepository::class)
            );
        });
        $this->container->bind(ServiceEnabler::class, function () {
            return new ServiceEnabler(
                $this->container->make(OrderDataAdapter::class),
                $this->container->make(ChangelogAdapter::class),
                $this->container->make(NotificationService::class),
                $this->container->make(RevenueTrackingRepository::class)
            );
        });
        $this->container->bind(RecoveryCoordinator::class, function () {
            return new RecoveryCoordinator(
                $this->container->make(RuleRepository::class),
                $this->container->make(CandidateRepository::class),
                $this->container->make(ServiceEnabler::class),
                $this->container->make(ProcessLogRepository::class)
            );
        });
        $this->container->bind(RevenueAnalyzer::class, function () {
            return new RevenueAnalyzer($this->container->make(\Database::class));
        });
        $this->container->bind(RecoveryReporter::class, function () {
            return new RecoveryReporter($this->container->make(\Database::class));
        });
        $this->container->bind(ExclusionManagementService::class, function () {
            return new ExclusionManagementService($this->container->make(ExclusionRepository::class));
        });
    }
}