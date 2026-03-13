<?php
// cron/migrate_fromtech_records.php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 0);

chdir(dirname(__FILE__));

date_default_timezone_set('Europe/Moscow');

define('APP_ROOT', dirname(__FILE__) . '/..');

require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/api/Simpla.php';

use App\Core\Application\Application;
use App\Core\Application\Container\Container;
use App\Providers\ClientServiceProvider;
use App\Service\NeuroRecordingService;
use App\Service\AIBotNotificationService;

class MigrateFromtechRecordsCron extends Simpla
{
    private const MIN_AGE_SECONDS = 120;
    private const LOCK_FILE = '/tmp/migrate_fromtech_records.lock';
    private const LOG_FILE = 'migrate_fromtech.txt';
    private const NEURO_NET_HOST = 'cms-v3.neuro.net';
    /** @var NeuroRecordingService */
    private $neuroService;
    /** @var AIBotNotificationService */
    private $notifier;

    public function __construct()
    {
        parent::__construct();
        $this->neuroService = $this->createNeuroService();
        $app = Application::getInstance();
        $this->notifier = $app->make(AIBotNotificationService::class);
    }

    private function createNeuroService(): NeuroRecordingService
    {
        try {
            $container = new Container();
            $provider = new ClientServiceProvider($container);
            $provider->register();

            return $container->make(NeuroRecordingService::class);
        } catch (\Exception $e) {
            echo "Ошибка инициализации NeuroRecordingService: " . $e->getMessage() . "\n";
            $this->logging('error', '', '', 'Ошибка инициализации NeuroRecordingService: ' . $e->getMessage(), 'migrate_fromtech.txt');
            exit(1);
        }
    }

    public function run(): void
    {
		$lock = fopen(self::LOCK_FILE, 'c');
		if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
			return;
		}

		try {
			$now = time();
			$minAgeSec = self::MIN_AGE_SECONDS;

			$comments = $this->comments->get_comments([
				'block' => 'fromtechIncomingCall'
			]);

			if (!$comments) {
				return;
			}

			$migrated = 0;
			$errors = 0;
			$skipped = 0;

            foreach ($comments as $comment) {
				if (strpos($comment->text, self::NEURO_NET_HOST) === false) {
					$skipped++;
					continue;
				}

				$createdTs = 0;
				if (isset($comment->date)) {
					$createdTs = is_numeric($comment->date) ? (int)$comment->date : strtotime((string)$comment->date);
				}
				if ($createdTs && ($now - $createdTs) < $minAgeSec) {
					$skipped++;
					continue;
				}

				$callData = json_decode($comment->text, true);
				if (empty($callData['call_record']) || empty($callData['call_log'])) {
					$skipped++;
					continue;
				}

				$callUuid = $this->neuroService->extractCallUuid($callData['call_record']);
				$agentUuid = $this->neuroService->extractAgentUuid($callData['call_log']);
				if (!$callUuid || !$agentUuid) {
					$skipped++;
					continue;
				}

				try {
					$newUrl = $this->neuroService->fetchAndStore($callUuid, $agentUuid);
					if ($newUrl) {
						$callData['call_record'] = $newUrl;
						$newText = json_encode($callData, JSON_UNESCAPED_UNICODE);
						$this->comments->update_comment($comment->id, ['text' => $newText]);
						$migrated++;
						$this->logging('info', '', '', "Fromtech migrated ID={$comment->id} {$newUrl}", self::LOG_FILE);

                        if (!empty($callData['switch_to_operator'])) {
                            try {
                                $userData = [
                                    'phone_mobile' => (string)($callData['msisdn'] ?? '')
                                ];
                                if (!empty($comment->user_id)) {
                                    $user = $this->users->get_user((int)$comment->user_id);
                                    if (!empty($user)) {
                                        $userData = [
                                            'id' => (int)$user->id,
                                            'firstname' => (string)$user->firstname,
                                            'lastname' => (string)$user->lastname,
                                            'patronymic' => (string)$user->patronymic,
                                            'phone_mobile' => (string)$user->phone_mobile,
                                        ];
                                    }
                                }

                                $this->notifier->sendTransferNotification($userData, $callData);
                            } catch (\Throwable $e) {
                                $this->logging('error', '', '', "Notify error ID={$comment->id}: " . $e->getMessage(), self::LOG_FILE);
                            }
                        }
					} else {
						$skipped++;
					}
				} catch (\Throwable $e) {
					$errors++;
					$this->logging('error', '', '', "Fromtech migrate exception ID={$comment->id}: " . $e->getMessage(), self::LOG_FILE);
				}
			}

			$this->logging('info', '', '', "Fromtech migrate done. ok={$migrated}, skipped={$skipped}, errors={$errors}", self::LOG_FILE);
		} finally {
			if (isset($lock) && is_resource($lock)) {
				flock($lock, LOCK_UN);
				fclose($lock);
			}
		}
    }
}

$cron = new MigrateFromtechRecordsCron();
$cron->run();