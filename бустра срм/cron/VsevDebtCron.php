<?php

require_once __DIR__ . '/../api/Simpla.php';

class VsevDebtCron extends Simpla
{
    private $upload_dir = __DIR__ . '/../files/vsev_debt/';
    private $log_dir = __DIR__ . '/../logs/';
    private $state_save_interval = 5; // seconds

    public function __construct()
    {
        parent::__construct();
        $this->upload_dir = $this->config->root_dir . 'files/vsev_debt/';
        $this->log_dir = $this->config->root_dir . 'logs/';
    }

    public function run()
    {
        if (!is_dir($this->log_dir)) {
            mkdir($this->log_dir, 0777, true);
        }

        $tasks = $this->vsev_debt_task->get_tasks(['status' => 'pending']);
        $processing_tasks = $this->vsev_debt_task->get_tasks(['status' => 'processing', 'processing_older_than' => '1 hour']);

        $tasks = array_merge($tasks, $processing_tasks);

        foreach ($tasks as $task) {
            $this->vsev_debt_task->update_task($task->id, ['status' => 'processing', 'log' => '']);
            $log = '';
            $notFoundLog = '';

            try {
                $filepath = $this->upload_dir . $task->filename;
                if (!file_exists($filepath)) {
                    throw new Exception("Файл не найден: $filepath");
                }

                if (($handle = fopen($filepath, "r")) === FALSE) {
                    throw new Exception("Не удалось открыть файл: $filepath");
                }

                $foundUsers = 0;
                $updatedUsers = 0;
                $errors = 0;

                $currentRow = 0;
                if ($task->last_processed_row > 0) {
                    for ($i = 0; $i < $task->last_processed_row; $i++) {
                        fgetcsv($handle);
                        $currentRow++;
                    }
                }

                if ($currentRow == 0) {
                    fgetcsv($handle); // Skip header
                    $currentRow++;
                }

                $lastSaveTime = time();

                while (($data = fgetcsv($handle, 0, ";")) !== FALSE) {
                    $currentRow++;

                    if (count($data) < 3 || empty(trim($data[2]))) {
                        $log .= "[WARN] Неверные данные в строке $currentRow\n";
                        continue;
                    }

                    $contractNumber = trim($data[2]);
                    $this->processContract($contractNumber, $foundUsers, $updatedUsers, $errors, $log, $notFoundLog);

                    if (time() - $lastSaveTime > $this->state_save_interval) {
                        $this->vsev_debt_task->update_task($task->id, ['last_processed_row' => $currentRow, 'log' => $log]);
                        $lastSaveTime = time();
                    }
                }

                fclose($handle);

                $summary = "\n[SUMMARY] Обработано строк: " . ($currentRow - 1) . ", найдено пользователей: $foundUsers, успешно обновлено: $updatedUsers, ошибок: $errors";
                $log .= $summary;

                if (!empty($notFoundLog)) {
                    $log .= "\n\nНенайденные номера договоров:\n" . $notFoundLog;
                }

                $this->vsev_debt_task->update_task($task->id, ['status' => 'completed', 'log' => $log, 'last_processed_row' => $currentRow - 1]);

            } catch (Exception $e) {
                $log .= "\n[ERROR] Критическая ошибка: " . $e->getMessage();
                $this->vsev_debt_task->update_task($task->id, ['status' => 'error', 'log' => $log]);
            }

            file_put_contents($this->log_dir . 'vsev_debt_task_' . $task->id . '.log', $log);
        }
    }

    private function processContract(string $contractNumber, int &$foundUsers, int &$updatedUsers, int &$errors, string &$log, string &$notFoundLog): void
    {
        $contract = $this->contracts->get_contract_by_params(['number' => $contractNumber]);

        if ($contract) {
            $user = $this->users->get_user((int)$contract->user_id);
            if ($user) {
                $foundUsers++;
                try {
                    $this->user_data->set($user->id, 'vsev_debt_notification_disabled', 1);
                    $updatedUsers++;
                    $log .= "[UPDATE] Пользователь ID={$user->id}, договор №{$contractNumber} — настройка активирована\n";
                } catch (Exception $e) {
                    $errors++;
                    $log .= "[ERROR] Ошибка при обновлении user_id={$user->id}: " . $e->getMessage() . "\n";
                }
            } else {
                $log .= "[WARN] Не найден пользователь для контракта №{$contractNumber}\n";
                $notFoundLog .= "$contractNumber\n";
            }
        } else {
            $log .= "[WARN] Не найден контракт №{$contractNumber}\n";
            $notFoundLog .= "$contractNumber\n";
        }
    }
}


$cron = new VsevDebtCron();
$cron->run();