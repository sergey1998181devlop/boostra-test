<?php
namespace App\Providers;

use App\Core\Application\Container\Container;
use App\Core\Application\Container\ServiceProvider;
use App\Core\Application\Session\Session;
use App\Repositories\DoctorConditionRepository;
use App\Repositories\DoctorReturnLogRepository;
use App\Repositories\OracleReturnLogRepository;
use App\Repositories\ContractRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ReturnRepository;
use App\Services\ReturnExtraService;
use App\Services\RiskGroupService;
use App\Services\SafetyFlowService;
use Database;
use OrderData;
use App\Services\ScoringService;
use UserData;
use Users;
use Settings;

class ExtraServiceServiceProvider extends ServiceProvider
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function register(): void
    {
        $this->container->bind(Session::class, function() {
            return Session::singleton();
        });

        // Регистрируем репозитории
        $this->container->bind(DoctorReturnLogRepository::class, function() {
            return new DoctorReturnLogRepository();
        });

        $this->container->bind(OracleReturnLogRepository::class, function() {
            return new OracleReturnLogRepository();
        });

        $this->container->bind(ContractRepository::class, function() {
            return new ContractRepository();
        });

        $this->container->bind(OrderRepository::class, function() {
            return new OrderRepository();
        });

        $this->container->bind(\Users::class, function() {
            return new \Users();
        });
        $this->container->bind(\Database::class, function() {
            return new \Database();
        });
        $this->container->bind(\OrderData::class, function() {
            return new \OrderData();
        });

        $this->container->bind(UserData::class, function () {
            return new UserData();
        });

        $this->container->bind(RiskGroupService::class, function() {
            $c = $this->container;
            return new RiskGroupService(
                $c->make(OrderRepository::class)
            );
        });

        $this->container->bind(ScoringService::class, function () {
            $c = $this->container;
            return new ScoringService(
                $c->make(OrderRepository::class)
            );
        });

        $this->container->bind(SafetyFlowService::class, function() {
            $c = $this->container;
            return new SafetyFlowService(
                $c->make(Users::class),
                $c->make(Database::class),
                $c->make(OrderData::class),
                $c->make(ContractRepository::class)
            );
        });

        $this->container->bind(DoctorConditionRepository::class, function () {
            return new DoctorConditionRepository();
        });

        $this->container->bind(Settings::class, function() {
            return new Settings();
        });
        
        $this->container->bind(ReturnRepository::class, function() {
            return new ReturnRepository();
        });

        $this->container->bind(ReturnExtraService::class, function() {
            $c = $this->container;
            return new ReturnExtraService(
                $c->make(Session::class),
                $c->make(Users::class),
                $c->make(UserData::class),
                $c->make(SafetyFlowService::class),
                $c->make(DoctorReturnLogRepository::class),
                $c->make(OracleReturnLogRepository::class),
                $c->make(DoctorConditionRepository::class),
                $c->make(RiskGroupService::class),
                $c->make(ScoringService::class),
                $c->make(Settings::class),
                $c->make(ReturnRepository::class)
            );
        });
    }

    public function boot(): void
    {
        // Здесь можно добавить инициализацию, если потребуется
    }
}
