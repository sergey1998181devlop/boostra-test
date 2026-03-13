<?php
declare(strict_types=1);
ini_set('memory_limit', '1G');
require_once 'View.php';
if (file_exists(__DIR__ . '/../lib/autoloader.php')) {
    require_once __DIR__ . '/../lib/autoloader.php';
}

use App\Enums\MindboxConstants;
use App\Service\CsvGenerator;
use App\Service\MindboxApiClient;
use App\Service\MindboxExportService;
use App\Repositories\MindboxDbRepository;

/**
 * Класс для выгрузки клиентов и заказов в MindBox
 */
class MigrateToMBView extends View
{
    private string $startDateUsers;
    private string $startDateOrders;
    private MindboxExportService $exportService;

    public function __construct()
    {
        parent::__construct();

        if (!in_array('boss_cc', $this->manager->permissions)) {
            header('HTTP/1.1 403 Forbidden');
            exit ('Access denied');
        }

        $this->exportService = new MindboxExportService(
            new MindboxDbRepository($this->db),
            new MindboxApiClient($this->config),
            new CsvGenerator()
        );

        $this->startDateUsers = $this->validateDate('start_date', MindboxConstants::START_DATE_USERS);
        $this->startDateOrders = $this->validateDate('start_date_orders', MindboxConstants::START_DATE_ORDERS);

        $this->route();
    }

    /**
     * Whitelist методов, доступных для вызова через action параметр
     */
    private const ALLOWED_ACTIONS = [
        'downloadUsers',
        'downloadUsersCsv',
        'downloadOrders',
        'downloadOrdersCsv',
    ];

    /**
     * Маршрутизация запросов к соответствующим методам
     * @return void
     */
    private function route(): void
    {
        $action = $this->request->get('action');
        if ($action && in_array($action, self::ALLOWED_ACTIONS, true)) {
            $this->$action();
        } else {
            $this->fetch();
        }
    }

    /**
     * Валидация дат
     * @param string $paramName
     * @param string $default
     * @return string
     */
    private function validateDate(string $paramName, string $default): string
    {
        $date = $this->request->get($paramName);
        if (!$date) {
            return $default;
        }

        $escapedDate = $this->db->escape($date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $escapedDate)) {
            header("HTTP/1.1 400");
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'message' => 'Неверный формат даты']);
            exit;
        }

        return $escapedDate;
    }

    public function fetch()
    {
        $this->design->assign('start_date_users', $this->startDateUsers);
        $this->design->assign('start_date_orders', $this->startDateOrders);
        return $this->design->fetch('migrate_mb.tpl');
    }

    /**
     * Экспорт пользователей в Mindbox
     */
    public function downloadUsers()
    {
        ob_start();

        $this->logMindbox('downloadUsers: start', [
            'start_date_users' => $this->startDateUsers,
        ]);

        try {
            $result = $this->exportService->exportUsersToMindbox($this->startDateUsers);

            $this->logMindbox('downloadUsers: done', [
                'status' => $result['status'] ?? null,
                'total_clients' => $result['total_clients'] ?? null,
                'transaction_id' => $result['result']['transaction_id'] ?? null,
            ]);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
        } catch (Exception $e) {
            $this->logMindbox('downloadUsers: exception', [
                'error' => $e->getMessage(),
            ], 'error');

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ]);
        }

        ob_end_flush();
        exit;
    }

    /**
     * Логирование импорта Mindbox из админки
     * @param string $message
     * @param array $context
     * @param string $level
     */
    private function logMindbox(string $message, array $context = [], string $level = 'info'): void
    {
        $line = '[MigrateToMB] ' . $message;
        if (!empty($context)) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        error_log($line);
        if (function_exists('logger')) {
            try {
                logger('mindbox')->{$level}($message, $context);
            } catch (\Throwable $e) {
                // ignore if Application not bootstrapped
            }
        }
    }

    /**
     * Скачивание CSV пользователей
     */
    public function downloadUsersCsv()
    {
        $this->exportService->streamUsersCsvToDownload($this->startDateUsers);
    }

    /**
     * Экспорт заказов в Mindbox
     */
    public function downloadOrders()
    {
        ob_start();

        try {
            $result = $this->exportService->exportOrdersToMindbox($this->startDateOrders);

            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($result);
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'status' => 'error',
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ]);
        }

        ob_end_flush();
        exit;
    }

    /**
     * Скачивание CSV заказов
     */
    public function downloadOrdersCsv()
    {
        $this->exportService->streamOrdersCsvToDownload($this->startDateOrders);
    }
}