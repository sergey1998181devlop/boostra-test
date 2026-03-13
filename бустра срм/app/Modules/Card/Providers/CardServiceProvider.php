<?php

namespace App\Modules\Card\Providers;

require_once __DIR__ . '/../../../../api/Database.php';

use App\Core\Application\Container\ServiceProvider;
use App\Handlers\ToggleCardAutodebitHandler;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\ChangelogAdapter;
use App\Modules\Card\Services\CardService;
use App\Modules\RecurrentsCenter\Services\RecurrentCenterService;
use App\Modules\TicketAssignment\Adapters\SettingsAdapter;
use App\Modules\TicketAssignment\Contracts\SettingsInterface;
use App\Repositories\B2PCardRepository;
use App\Repositories\UserRepository;
use Best2pay;
use Database;

/**
 * Сервис-провайдер для модуля карт
 */
class CardServiceProvider extends ServiceProvider
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

        $this->container->bind(CardService::class, function () {
            return new CardService(
                new RecurrentCenterService(),
                new Best2Pay(),
                new ChangelogAdapter(
                    new \Changelogs()
                )
            );
        });

        $this->container->bind(ToggleCardAutodebitHandler::class, function () {
            return new ToggleCardAutodebitHandler(
                $this->container->make(B2PCardRepository::class),
                $this->container->make(UserRepository::class),
                $this->container->make(CardService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}
