<?php

namespace App\Providers;

require_once __DIR__ . '/../../api/Database.php';

use App\Contracts\FromtechIncomingCallServiceInterface;
use App\Contracts\IncomingCallCommentHandlerContract;
use App\Contracts\OutgoingCallCommentHandlerContract;
use App\Core\Application\Container\ServiceProvider;
use App\Core\Application\Session\Session;
use App\Handlers\IncomingCallCommentHandler;
use App\Handlers\UserDncCallsHandler;
use App\Handlers\OutgoingCallCommentHandler;
use App\Handlers\ToggleCardAutodebitHandler;
use App\Handlers\ToggleSbpAutodebitHandler;
use App\Http\Controllers\AutodebitController;
use App\Http\Controllers\BlacklistController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SmsController;
use App\Modules\AdditionalServiceRecovery\Infrastructure\Adapter\Soap1cAdapter;
use App\Modules\Clients\Application\Service\ClientInfoService;
use App\Modules\Clients\Application\Service\UserBalanceService;
use App\Modules\Clients\Domain\Repository\ClientRepositoryInterface;
use App\Modules\Clients\Domain\Repository\LoanRepositoryInterface;
use App\Modules\Clients\Infrastructure\Repository\ClientRepository;
use App\Modules\Clients\Infrastructure\Repository\LoanRepository;
use App\Modules\Shared\Repositories\SettingsRepository;
use App\Modules\Shared\Repositories\OrganizationRepository;
use App\Core\Cache\CacheInterface;
use App\Core\Cache\RedisCache;
use App\Modules\Clients\Infrastructure\Repository\UserBalanceRepository as ClientsUserBalanceRepository;
use App\Modules\Clients\Infrastructure\Client\OneCBalanceClient;
use App\Modules\Clients\Domain\Service\BalanceCalculator;
use App\Modules\Clients\Domain\Service\LoanHistoryMatcher;
use App\Modules\Clients\Domain\Service\OverdueCalculator;
use App\Repositories\AIBotCallsRepository;
use App\Repositories\BlockedAdvSmsRepository;
use App\Repositories\CbrLinkClickRepository;
use App\Repositories\ChangelogRepository;
use App\Repositories\CommentRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ExtraServicePurchaseRepository;
use App\Repositories\ExtraServicesInformRepository;
use App\Repositories\IncomingCallBlacklistRepository;
use App\Repositories\ManagerRepository;
use App\Repositories\B2PCardRepository;
use App\Repositories\B2PSbpAccountRepository;
use App\Repositories\OrdersRepository;
use App\Repositories\SmsMessagesRepository;
use App\Repositories\SmsTemplateRepository;
use App\Repositories\UserBalanceRepository;
use App\Repositories\UserDataRepository;
use App\Repositories\UserRepository;
use App\Repositories\UserDncRepository;
use App\Repositories\UserPhoneRepository;
use App\Repositories\ApplicationTokenRepository;
use App\Repositories\VoxSiteDncRepository;
use App\Service\AIBotCallsReportService;
use App\Service\AIBotNotificationService;
use App\Service\BlacklistService;
use App\Service\BotActionDetailsService;
use App\Service\ChangelogService;
use App\Service\CbrLinkClickReportService;
use App\Service\ClientAccountService;
use App\Service\CommentService;
use App\Service\FileStorageFactory;
use App\Service\ChangeLogs\ActionLoggerService;
use App\Service\ChangeLogs\ClientControllerLogService;
use App\Service\FromtechIncomingCallService;
use App\Service\LicenseSmsService;
use App\Service\NeuroRecordingService;
use App\Service\OrderStatusSyncService;
use App\Service\SmsService;
use App\Service\SystemNoticeSettingsService;
use App\Service\TelegramService;
use App\Service\TemplateMatcherService;
use App\Service\VoximplantService;
use App\Service\VoxSiteDncService;
use App\Service\CbRequestService;
use App\Http\Controllers\VoxSiteDncController;
use App\Http\Controllers\CbRequestController;
use App\Repositories\CbRequestRepository;
use Database;

class ClientServiceProvider extends ServiceProvider
{
    public function register()
    {
        $database = new Database();
        
        // Database - базовый сервис для SimplaDatabase
        $this->container->bind(\Database::class, function () use ($database) {
            return $database;
        });

        // Telegram сервисы
        $this->container->bind(TelegramService::class, function () {
            return new TelegramService();
        });

        $this->container->bind(AIBotNotificationService::class, function () {
            return new AIBotNotificationService(
                $this->container->make(TelegramService::class)
            );
        });

        $this->container->bind(AIBotCallsRepository::class, function () use ($database) {
            return new AIBotCallsRepository($database);
        });

        $this->container->bind(AIBotCallsReportService::class, function () use ($database) {
            return new AIBotCallsReportService(
                $this->container->make(AIBotCallsRepository::class)
            );
        });

        $this->container->bind(IncomingCallBlacklistRepository::class, function () use ($database) {
            return new IncomingCallBlacklistRepository($database);
        });

        $this->container->bind(SmsMessagesRepository::class, function () use ($database) {
            return new SmsMessagesRepository($database);
        });

        $this->container->bind(CommentRepository::class, function () use ($database) {
            return new CommentRepository($database);
        });

        $this->container->bind(UserRepository::class, function () use ($database) {
            return new UserRepository($database);
        });

        $this->container->bind(BlockedAdvSmsRepository::class, function () use ($database) {
            return new BlockedAdvSmsRepository($database);
        });

        $this->container->bind(OrdersRepository::class, function () use ($database) {
            return new OrdersRepository($database);
        });

        $this->container->bind(B2PCardRepository::class, function () use ($database) {
            return new B2PCardRepository($database);
        });

        $this->container->bind(B2PSbpAccountRepository::class, function () use ($database) {
            return new B2PSbpAccountRepository($database);
        });

        $this->container->bind(UserDataRepository::class, function () use ($database) {
            return new UserDataRepository($database);
        });

        $this->container->bind(SmsTemplateRepository::class, function () use ($database) {
            return new SmsTemplateRepository($database);
        });

        $this->container->bind(CbrLinkClickRepository::class, function () {
            return new CbrLinkClickRepository();
        });
        $this->container->bind(CbrLinkClickReportService::class, function () {
            return new CbrLinkClickReportService(
                $this->container->make(CbrLinkClickRepository::class),
                $this->container->make(UserRepository::class)
            );
        });

        $this->container->bind(ClientAccountService::class, function () {
            return new ClientAccountService(
                $this->container->make(UserRepository::class),
                $this->container->make(OrdersRepository::class),
                $this->container->make(BlockedAdvSmsRepository::class)
            );
        });

        $this->container->bind(SmsService::class, function () {
            return new SmsService(
                $this->container->make(SmsTemplateRepository::class),
                $this->container->make(SmsMessagesRepository::class),
                $this->container->make(UserDataRepository::class),
                $this->container->make(UserRepository::class),
                $this->container->make(ChangelogRepository::class)
            );
        });

        $this->container->bind(LicenseSmsService::class, function () {
            return new LicenseSmsService(
                $this->container->make(SmsService::class),
                $this->container->make(UserRepository::class),
                $this->container->make(UserBalanceRepository::class),
                $this->container->make(DocumentRepository::class),
                $this->container->make(CommentRepository::class),
                $this->container->make(ExtraServicesInformRepository::class),
                $this->container->make(ExtraServicePurchaseRepository::class)
            );
        });

        $this->container->bind(Session::class, function () {
            return Session::getInstance();
        });

        $this->container->bind(ChangelogRepository::class, function () use ($database) {
            return new ChangelogRepository($database);
        });

        $this->container->bind(ChangelogService::class, function () {
            return new ChangelogService();
        });

        $this->container->bind(ManagerRepository::class, function () use ($database) {
            return new ManagerRepository($database);
        });

        $this->container->bind(VoxSiteDncRepository::class, function () use ($database) {
            return new VoxSiteDncRepository($database);
        });

        $this->container->bind(ApplicationTokenRepository::class, function () use ($database) {
            return new ApplicationTokenRepository($database);
        });

        $this->container->bind(UserDncRepository::class, function () use ($database) {
            return new UserDncRepository($database);
        });

        $this->container->bind(UserPhoneRepository::class, function () use ($database) {
            return new UserPhoneRepository($database);
        });

        $this->container->bind(VoxSiteDncService::class, function () {
            return new VoxSiteDncService(
                $this->container->make(VoxSiteDncRepository::class)
            );
        });

        $this->container->bind(VoxSiteDncController::class, function () {
            return new VoxSiteDncController(
                $this->container->make(VoxSiteDncService::class)
            );
        });

        $this->container->bind(CbRequestRepository::class, function () {
            return new CbRequestRepository();
        });

        $this->container->bind(CbRequestService::class, function () {
            return new CbRequestService(
                $this->container->make(CbRequestRepository::class)
            );
        });

        $this->container->bind(CbRequestController::class, function () {
            return new CbRequestController(
                $this->container->make(CbRequestService::class)
            );
        });

        $this->container->bind(UserDncCallsHandler::class, function () {
            return new UserDncCallsHandler(
                $this->container->make(OrdersRepository::class),
                $this->container->make(UserRepository::class),
                $this->container->make(VoxSiteDncRepository::class),
                $this->container->make(UserDncRepository::class),
                $this->container->make(UserPhoneRepository::class),
                $this->container->make(ChangelogService::class)
            );
        });

        $this->container->bind(CommentService::class, function () {
            return new CommentService();
        });

        $this->container->bind(VoximplantService::class, function () {
            return new VoximplantService();
        });

        $this->container->bind(SystemNoticeSettingsService::class, function () {
            return new SystemNoticeSettingsService();
        });

        $this->container->bind(ActionLoggerService::class, function () {
            return new ActionLoggerService(
                $this->container->make(ChangelogRepository::class),
                $this->container->make(CommentRepository::class),
                $this->container->make(UserRepository::class),
                $this->container->make(ManagerRepository::class),
                $this->container->make(CommentService::class)
            );
        });

        $this->container->bind(ClientControllerLogService::class, function () {
            return new ClientControllerLogService(
                $this->container->make(ActionLoggerService::class)
            );
        });

        $this->container->bind(TemplateMatcherService::class, function () {
            return new TemplateMatcherService(
                $this->container->make(SmsTemplateRepository::class)
            );
        });

        $this->container->bind(BotActionDetailsService::class, function () {
            return new BotActionDetailsService(
                $this->container->make(SmsMessagesRepository::class),
                $this->container->make(ChangelogRepository::class),
                $this->container->make(TemplateMatcherService::class)
            );
        });

        $this->container->bind(IncomingCallCommentHandlerContract::class, function () {
            return new IncomingCallCommentHandler(
                $this->container->make(UserRepository::class),
                $this->container->make(ManagerRepository::class),
                $this->container->make(CommentRepository::class),
                $this->container->make(CommentService::class),
                $this->container->make(VoximplantService::class)
            );
        });

        $this->container->bind(IncomingCallCommentHandler::class, function () {
            return $this->container->make(IncomingCallCommentHandlerContract::class);
        });

        $this->container->bind(OutgoingCallCommentHandlerContract::class, function () {
            return new OutgoingCallCommentHandler();
        });

        $this->container->bind(OutgoingCallCommentHandler::class, function () {
            return $this->container->make(OutgoingCallCommentHandlerContract::class);
        });

        $this->container->bind(BlacklistService::class, function () {
            return new BlacklistService(
                $this->container->make(IncomingCallBlacklistRepository::class),
                $this->container->make(CommentRepository::class),
                $this->container->make(UserRepository::class),
                $this->container->make(ChangelogRepository::class),
                $this->container->make(Session::class)
            );
        });

        $this->container->bind(BlacklistController::class, function () {
            return new BlacklistController(
                $this->container->make(BlacklistService::class)
            );
        });

        // Cache Interface
        $this->container->bind(CacheInterface::class, function () {
            return new RedisCache();
        });

        // Репозиторий настроек
        $this->container->bind(SettingsRepository::class, function () use ($database) {
            return new SettingsRepository($database);
        });
        
        // Репозиторий организаций
        $this->container->bind(OrganizationRepository::class, function () use ($database) {
            return new OrganizationRepository($database);
        });

        // Репозиторий балансов
        $this->container->bind(ClientsUserBalanceRepository::class, function () use ($database) {
            return new ClientsUserBalanceRepository($database);
        });

        // 1C Клиент для балансов
        $this->container->bind(OneCBalanceClient::class, function () {
            return new OneCBalanceClient(
                $this->container->make(SettingsRepository::class)
            );
        });

        // Сервис балансов с кэшированием
        $this->container->bind(UserBalanceService::class, function () {
            return new UserBalanceService(
                $this->container->make(CacheInterface::class),
                $this->container->make(OneCBalanceClient::class),
                $this->container->make(ClientsUserBalanceRepository::class),
                $this->container->make(OrganizationRepository::class)
            );
        });

        $this->container->bind(SmsController::class, function () {
            return new SmsController(
                $this->container->make(SmsService::class)
            );
        });

        // Репозитории
        $this->container->bind(ClientRepositoryInterface::class, function () use ($database) {
            return new ClientRepository($database);
        });

        $this->container->bind(LoanRepositoryInterface::class, function () use ($database) {
            return new LoanRepository($database);
        });

        // Доменные сервисы
        $this->container->bind(LoanHistoryMatcher::class, function () {
            return new LoanHistoryMatcher();
        });

        $this->container->bind(BalanceCalculator::class, function () {
            return new BalanceCalculator(new LoanHistoryMatcher());
        });

        $this->container->bind(OverdueCalculator::class, function () {
            return new OverdueCalculator(
                new BalanceCalculator(new LoanHistoryMatcher())
            );
        });

        // Сервисы приложения
        $this->container->bind(ClientInfoService::class, function () use ($database) {
            return new ClientInfoService(
                new ClientRepository($database),
                new LoanRepository($database),
                new BalanceCalculator(new LoanHistoryMatcher()),
                new OverdueCalculator(
                    new BalanceCalculator(new LoanHistoryMatcher())
                ),
                $this->container->make(Soap1cAdapter::class),
                $this->container->make(UserBalanceService::class)
            );
        });

        $this->container->bind(FileStorageFactory::class, function () {
            return new FileStorageFactory();
        });

        $this->container->bind(NeuroRecordingService::class, function () {
            return new NeuroRecordingService(
                $this->container->make(FileStorageFactory::class)
            );
        });

        $this->container->bind(FromtechIncomingCallServiceInterface::class, function () {
            return new FromtechIncomingCallService(
                $this->container->make(UserRepository::class),
                $this->container->make(IncomingCallCommentHandler::class)
            );
        });

        $this->container->bind(OrderStatusSyncService::class, function () use ($database) {
            return new OrderStatusSyncService(
                new OrdersRepository($database)
            );
        });

        $this->container->bind(OrderController::class, function () {
            return new OrderController(
                $this->container->make(OrderStatusSyncService::class),
                $this->container->make(UserDncCallsHandler::class)
            );
        });

        $this->container->bind(ClientController::class, function () {
            return new ClientController(
                $this->container->make(IncomingCallCommentHandler::class),
                $this->container->make(OutgoingCallCommentHandler::class),
                $this->container->make(ClientControllerLogService::class)
            );
        });

        $this->container->bind(AutodebitController::class, function () {
            return new AutodebitController(
                $this->container->make(OrdersRepository::class),
                $this->container->make(ToggleCardAutodebitHandler::class),
                $this->container->make(ToggleSbpAutodebitHandler::class),
                $this->container->make(ActionLoggerService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}
