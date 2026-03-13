<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

chdir(dirname(__FILE__));

date_default_timezone_set('Europe/Moscow');

define('APP_ROOT', dirname(__FILE__) . '/..');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/api/Simpla.php';

use App\Core\Application\Container\Container;
use App\Providers\ClientServiceProvider;
use App\Repositories\IncomingCallBlacklistRepository;

class DeactivateBlacklistCron extends Simpla
{
    /** @var IncomingCallBlacklistRepository */
    private $blacklistRepository;

    public function __construct()
    {
        parent::__construct();
        $this->blacklistRepository = $this->createBlacklistRepository();
    }

    private function createBlacklistRepository(): IncomingCallBlacklistRepository
    {
        try {
            $container = new Container();
            $provider = new ClientServiceProvider($container);
            $provider->register();

            return $container->make(IncomingCallBlacklistRepository::class);
        } catch (\Exception $e) {
            echo "Ошибка инициализации сервисов: " . $e->getMessage() . "\n";
            $this->logging('error', '', '', 'Ошибка инициализации сервисов деактивации блэклиста: ' . $e->getMessage(), 'incoming_call_blacklist.txt');
            exit(1);
        }
    }

    public function run(): void
    {
        try {
            echo "Запуск деактивации истекших записей блэклиста...\n";

            $records = $this->blacklistRepository->getExpiredRecords();

            foreach ($records as $record) {
                $this->blacklistRepository->updateStatus($record->id, false);

                echo date('Y-m-d H:i:s') . " Deactivated record ID: {$record->id}, Phone: {$record->phone_number}, Created: {$record->created_at}\n";
            }

            echo date('Y-m-d H:i:s') . " Total deactivated: " . count($records) . "\n";
        } catch (\Exception $e) {
            echo "Ошибка при деактивации блэклиста: " . $e->getMessage() . "\n";
            $this->logging('error', '', '', 'Ошибка деактивации блэклиста: ' . $e->getMessage(), 'blacklist.txt');
        }
    }
}

$cron = new DeactivateBlacklistCron();
$cron->run();