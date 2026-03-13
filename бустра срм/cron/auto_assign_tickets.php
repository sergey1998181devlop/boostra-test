<?php

ini_set('error_reporting', -1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', 0);

chdir(dirname(__FILE__));

date_default_timezone_set('Europe/Moscow');

define('APP_ROOT', dirname(__FILE__) . '/..');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/api/Simpla.php';

use App\Core\Application\Container\Container;
use App\Modules\TicketAssignment\Providers\TicketAssignmentServiceProvider;
use App\Modules\TicketAssignment\Services\AutoAssignmentService;
use App\Modules\TicketAssignment\Contracts\AutoAssignmentServiceInterface;


class AutoAssignTickets extends Simpla
{
    /** @var AutoAssignmentServiceInterface */
    private $autoAssignmentService;

    public function __construct()
    {
        parent::__construct();
        $this->autoAssignmentService = $this->createAutoAssignmentService();
    }

    private function createAutoAssignmentService(): AutoAssignmentServiceInterface
    {
        try {
            $container = new Container();
            $provider = new TicketAssignmentServiceProvider($container);
            $provider->register();

            return $container->make(AutoAssignmentService::class);
        } catch (\Exception $e) {
            echo "Ошибка инициализации сервисов: " . $e->getMessage() . "\n";
            $this->logging('error', '', '', 'Ошибка инициализации сервисов автоназначения тикетов: ' . $e->getMessage(), 'ticket_settings.txt');
            exit(1);
        }
    }

    public function run(): void
    {
        try {
            echo "Запуск автоматического назначения тикетов...\n";

            $result = $this->autoAssignmentService->assignUnassignedTickets();

            echo "Завершено. Назначено: {$result['assigned']}, Эскалировано: {$result['escalated']}, Ошибок: {$result['failed']}\n";

            if (!empty($result['errors'])) {
                foreach ($result['errors'] as $error) {
                    echo "Ошибка: {$error}\n";
                }
            }
        } catch (\Exception $e) {
            echo "Ошибка при выполнении автоназначения: " . $e->getMessage() . "\n";
            $this->logging('error', '', '', 'Ошибка автоназначения тикетов: ' . $e->getMessage(), 'ticket_settings.txt');
        }
    }
}

$autoAssign = new AutoAssignTickets();
$autoAssign->run();