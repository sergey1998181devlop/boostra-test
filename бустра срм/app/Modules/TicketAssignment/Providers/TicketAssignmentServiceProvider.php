<?php

namespace App\Modules\TicketAssignment\Providers;

require_once __DIR__ . '/../../../../api/Database.php';

use App\Core\Application\Container\ServiceProvider;
use App\Core\Database\SimplaDatabase;
use App\Modules\Shared\Repositories\SettingsRepository;
use App\Modules\TicketAssignment\Adapters\SettingsAdapter;
use App\Modules\TicketAssignment\Contracts\SettingsInterface;
use App\Modules\TicketAssignment\Repositories\ManagerRepository;
use App\Modules\TicketAssignment\Repositories\ManagerCompetencyRepository;
use App\Modules\TicketAssignment\Repositories\TicketAssignmentRepository;
use App\Modules\TicketAssignment\Repositories\ComplaintsByManagerRepository;
use App\Modules\TicketAssignment\Repositories\ComplaintsByResponsibleRepository;
use App\Modules\TicketAssignment\Services\AutoAssignmentService;
use App\Modules\TicketAssignment\Services\CompetencyService;
use App\Modules\TicketAssignment\Services\CoefficientCalculatorService;
use App\Modules\TicketAssignment\Services\ManagerFinderService;
use App\Modules\TicketAssignment\Services\SLAEscalationService;
use App\Modules\TicketAssignment\Contracts\ManagerFinderServiceInterface;
use App\Modules\TicketAssignment\Contracts\SLAEscalationServiceInterface;
use App\Repositories\UserRepository;
use App\Modules\Clients\Domain\Service\OverdueCalculator;
use App\Modules\Clients\Domain\Service\BalanceCalculator;
use App\Modules\Clients\Domain\Service\LoanHistoryMatcher;
use App\Modules\Notifications\Service\NotificationService;
use App\Modules\Notifications\Repository\NotificationRepository;
use Database;

/**
 * Сервис-провайдер для модуля автоназначения тикетов
 */
class TicketAssignmentServiceProvider extends ServiceProvider
{
    /**
     * Регистрирует все зависимости модуля
     */
    public function register(): void
    {
        $database = new Database();

        // Database - базовый сервис для SimplaDatabase
        $this->container->bind(\Database::class, function () use ($database) {
            return $database;
        });

        // Регистрируем адаптер настроек
        $this->container->bind(SettingsInterface::class, function () {
            return new SettingsAdapter(new \Simpla());
        });

        // Регистрируем репозиторий настроек
        $this->container->bind(SettingsRepository::class, function () {
            return new SettingsRepository(
                SimplaDatabase::getInstance()->db()
            );
        });

        // Регистрируем репозитории
        $this->container->bind(ManagerRepository::class, function () {
            return new ManagerRepository(
                $this->container->make(SettingsInterface::class)
            );
        });

        $this->container->bind(ManagerCompetencyRepository::class, function () {
            return new ManagerCompetencyRepository(
                SimplaDatabase::getInstance()->db()
            );
        });

        $this->container->bind(TicketAssignmentRepository::class, function () {
            return new TicketAssignmentRepository(
                SimplaDatabase::getInstance()->db()
            );
        });

        $this->container->bind(ComplaintsByManagerRepository::class, function () {
            return new ComplaintsByManagerRepository(
                SimplaDatabase::getInstance()->db()
            );
        });

        $this->container->bind(ComplaintsByResponsibleRepository::class, function () {
            return new ComplaintsByResponsibleRepository(
                SimplaDatabase::getInstance()->db()
            );
        });

        // Регистрируем сервисы
        $this->container->bind(CompetencyService::class, function () {
            return new CompetencyService(
                $this->container->make(ManagerCompetencyRepository::class)
            );
        });

        $this->container->bind(CoefficientCalculatorService::class, function () {
            return new CoefficientCalculatorService();
        });

        // Регистрируем сервисы из модуля Clients
        $this->container->bind(LoanHistoryMatcher::class, function () {
            return new LoanHistoryMatcher();
        });

        $this->container->bind(BalanceCalculator::class, function () {
            return new BalanceCalculator(
                $this->container->make(LoanHistoryMatcher::class)
            );
        });

        $this->container->bind(OverdueCalculator::class, function () {
            return new OverdueCalculator(
                $this->container->make(BalanceCalculator::class)
            );
        });

        // Регистрируем сервисы уведомлений
        $this->container->bind(NotificationRepository::class, function () {
            return new NotificationRepository(
                SimplaDatabase::getInstance()->db()
            );
        });

        $this->container->bind(NotificationService::class, function () {
            return new NotificationService(
                $this->container->make(NotificationRepository::class)
            );
        });

        $this->container->bind(ManagerFinderServiceInterface::class, function () {
            return new ManagerFinderService(
                $this->container->make(ManagerRepository::class),
                $this->container->make(CompetencyService::class),
                $this->container->make(UserRepository::class),
                $this->container->make(OverdueCalculator::class),
                SimplaDatabase::getInstance()->db()
            );
        });

        $this->container->bind(SLAEscalationServiceInterface::class, function () {
            return new SLAEscalationService(
                $this->container->make(TicketAssignmentRepository::class),
                $this->container->make(CompetencyService::class),
                $this->container->make(NotificationService::class),
                $this->container->make(SettingsRepository::class),
                SimplaDatabase::getInstance()->db(),
                new \Simpla()
            );
        });

        $this->container->bind(AutoAssignmentService::class, function () {
            return new AutoAssignmentService(
                $this->container->make(ManagerRepository::class),
                $this->container->make(CompetencyService::class),
                $this->container->make(CoefficientCalculatorService::class),
                $this->container->make(TicketAssignmentRepository::class),
                $this->container->make(UserRepository::class),
                $this->container->make(ManagerFinderServiceInterface::class),
                $this->container->make(SLAEscalationServiceInterface::class),
                SimplaDatabase::getInstance()->db()
            );
        });

        $this->container->bind(UserRepository::class, function () use ($database) {
            return new UserRepository($database);
        });
    }

    public function boot()
    {
        //
    }
}
