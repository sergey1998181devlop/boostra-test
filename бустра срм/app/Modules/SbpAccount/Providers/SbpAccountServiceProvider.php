<?php

namespace App\Modules\SbpAccount\Providers;

require_once __DIR__ . '/../../../../api/Database.php';

use App\Core\Application\Container\ServiceProvider;
use App\Handlers\ToggleSbpAutodebitHandler;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\ChangelogAdapter;
use App\Modules\RecurrentsCenter\Services\RecurrentCenterService;
use App\Modules\SbpAccount\Services\SbpAccountService;
use App\Modules\TicketAssignment\Adapters\SettingsAdapter;
use App\Modules\TicketAssignment\Contracts\SettingsInterface;
use App\Repositories\B2PSbpAccountRepository;
use App\Repositories\UserRepository;
use Database;

/**
 * Сервис-провайдер для модуля сбп счетов
 */
class SbpAccountServiceProvider extends ServiceProvider
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

        $this->container->bind(SbpAccountService::class, function () {
            return new SbpAccountService(
                new RecurrentCenterService(),
                new \SbpAccount(),
                new \Best2Pay(),
                new ChangelogAdapter(
                    new \Changelogs()
                )
            );
        });

        $this->container->bind(ToggleSbpAutodebitHandler::class, function () {
            return new ToggleSbpAutodebitHandler(
                $this->container->make(B2PSbpAccountRepository::class),
                $this->container->make(UserRepository::class),
                $this->container->make(SbpAccountService::class)
            );
        });

    }

    public function boot()
    {
        //
    }
}
