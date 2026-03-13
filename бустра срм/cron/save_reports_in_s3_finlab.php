<?php

ini_set('display_errors', 'on');
error_reporting(-1);

chdir('..');
define('ROOT', dirname(__DIR__));
date_default_timezone_set('Europe/Moscow');

require 'api/Simpla.php';

/**
 * Класс для сохранения ССП и КИ отчетов из локального хранения в s3 хранилище
 */
class SaveReportsInS3Finlab extends Simpla
{
    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 55;

    /** @var string Лог файл */
    private const LOG_FILE = 'save_reports_in_s3_finlab.txt';

    public function run()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', 'Начало работы крон', '', self::LOG_FILE);

        $reportTypes = [$this->axi::CH_REPORT, $this->axi::SSP_REPORT];

        shuffle($reportTypes);

        $reportType = current($reportTypes);
        [$directory, $s3_directory] = $this->getDirectory($reportType);
        $filesName = $this->getFilesName($directory);

        if (empty($filesName) || count($filesName) <= 2) {
            $reportType = next($reportTypes);
            [$directory, $s3_directory] = $this->getDirectory($reportType);
            $filesName = $this->getFilesName($directory);
        }

        if (empty($filesName) || count($filesName) <= 2) {
            $this->logging(__METHOD__, '', '', 'Файлов больше нет. Можно отключать крон',
                self::LOG_FILE);
            print_r('Файлов больше нет. Можно отключать крон');
            die();
        }

        $i = 0;
        foreach ($filesName as $fileName) {
            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '',
                    'Достигнута максимальная продолжительность работы крон. Время ' .
                    (new DateTimeImmutable())->format('Y-m-d H:i:s'), self::LOG_FILE);
                break;
            }

            if ($fileName === '.' || $fileName === '..') {
                continue;
            }

            $orderId = preg_replace('/\D/', '', $fileName);

            if (empty($orderId)) {
                $this->logging(__METHOD__, '', '', 'Id заявки ' . $fileName . ' не найден', self::LOG_FILE);
                continue;
            }

            $order = $this->orders->get_order((int)$orderId);

            if (empty($order)) {
                $this->logging(__METHOD__, '', '', 'Заявка ' . $orderId . ' не найдена', self::LOG_FILE);
                continue;
            }

            $file_local_path = $directory . $fileName;
            $s3_name = $s3_directory . $this->getReportDate($file_local_path) . '/' . $fileName;

            try {
                $this->s3_api_client->putFileContent($file_local_path, $s3_name);
            } catch (Throwable $e) {
                $error = [
                    'Ошибка: ' . $e->getMessage(),
                    'Файл: ' . $e->getFile(),
                    'Строка: ' . $e->getLine(),
                    'Подробности: ' . $e->getTraceAsString()
                ];
                $this->logging(__METHOD__, '', ['file_local_path' => $file_local_path], $error, self::LOG_FILE);
                continue;
            }

            $data = [
                'user_id' => $order->user_id,
                'order_id' => $orderId,
                'type' => $reportType,
                'file_name' => $fileName,
                's3_name' => $s3_name,
                'date_create' => date('Y-m-d H:i:s')
            ];

            $file_id = $this->credit_history->insertRow($data);

            if (!$file_id) {
                $this->logging(__METHOD__, 'Не удалось сохранить файл в s_credit_histories', ['order_id' => $orderId], $data, self::LOG_FILE);
                continue;
            }

            if (file_exists($file_local_path)) {
                unlink($file_local_path);
            }

            $this->logging(__METHOD__, '', 'Файл успешно сохранен в s3',
                ['orderId' => $orderId, 'file_id' => $file_id, 's3_name' => $s3_name], self::LOG_FILE);

            $i++;

            if ($i >= 100) {
                break;
            }
        }

        $message = [
            'Сохранено файлов: ' => $i,
            'Осталось сохранить файлов: ' => count($filesName) - $i - 2,
            'Папка' => $directory
        ];

        $this->logging(__METHOD__, '', 'Завершение работы скрипта', $message, self::LOG_FILE);
    }

    private function getFilesName(string $directory): array
    {
        $filesName = scandir($directory);

        if (!empty($filesName) && count($filesName) > 2) {
            return $filesName;
        }

        if ($filesName === false) {
            $this->logging(__METHOD__, '', '', 'Ошибка при получении файлов из папки ' . $directory, self::LOG_FILE);
            print_r('Ошибка при получении файлов' . $directory);
            die();
        }

        if (empty($filesName) || count($filesName) <= 2) {
            $this->logging(__METHOD__, '', '', 'Файлов больше нет в папке ' . $directory,
                self::LOG_FILE);
            print_r('Файлов больше нет в папке ' . $directory);
        }

        return [];
    }

    private function getDirectory($reportType)
    {
        if ($reportType === $this->axi::SSP_REPORT) {
            $directory = ROOT . '/files/finlab/CCP/';
            $s3_directory = $this->config->s3['amp_report_url_finlab'];
        } else if ($reportType === $this->axi::CH_REPORT) {
            $directory = ROOT . '/files/finlab/credit_history/';
            $s3_directory = $this->config->s3['report_url_finlab'];
        } else {
            $this->logging(__METHOD__, '', '', 'Некорректный тип', self::LOG_FILE);
            print_r('Некорректный тип');
            die();
        }

        return [$directory, $s3_directory];
    }

    private function getReportDate(string $file_local_path): string
    {
        $fileContent = file_get_contents($file_local_path);

        if (strpos($file_local_path, 'CCP')) {
            preg_match('/ПоСостояниюНа="(.*?)"/', $fileContent, $matches);
            $reportDate = $matches[1];

            if (!empty($reportDate)) {
                return (date('Ymd', strtotime($reportDate)));
            }
        } else if (strpos($file_local_path, 'credit_history')) {
            preg_match('/<reportIssueDateTime>(.*?)<\/reportIssueDateTime>/', $fileContent, $matches);
            $reportDate = $matches[1];

            if (!empty($reportDate)) {
                return (date('Ymd', strtotime($reportDate)));
            }
        }

        return date('Ymd');
    }
}

$saveReportsInS3 = new SaveReportsInS3Finlab();
$saveReportsInS3->run();