<?php

declare(strict_types=1);

use App\Service\OrganizationService;
use App\Service\VoximplantCampaignService;
use App\Service\VoximplantApiClient;
use App\Service\VoximplantLogger;
use DateTime;
use Throwable;

error_reporting(0);
ini_set('display_errors', 'Off');

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: must-revalidate');
header('Pragma: no-cache');
header('Expires: -1');

session_start();

require_once __DIR__ . '/../api/Simpla.php';

$simpla = new Simpla();

final class SendToVoxMkk
{
    private const ERROR_NO_MANAGERS = 'Не указаны менеджеры';
    private const ERROR_DEFAULT = 'Не удалось отправить номера';

    /**
     * @var Simpla
     */
    private $simpla;

    /**
     * @var array<string, mixed>
     */
    private $request;

    /**
     * @var VoximplantCampaignService
     */
    private $campaignService;

    /**
     * @var VoximplantLogger
     */
    private $logger;

    /**
     * @var OrganizationService
     */
    private $organizationService;

    /**
     * @param array<string, mixed> $request
     */
    public function __construct(Simpla $simpla, array $request)
    {
        $this->simpla = $simpla;
        $this->request = $request;
        $this->organizationService = new OrganizationService();
        $this->logger = new VoximplantLogger();
        $apiClient = new VoximplantApiClient($this->organizationService, $this->logger);
        $this->campaignService = new VoximplantCampaignService($apiClient, $this->logger, $this->organizationService);
    }

    /**
     * @return array{success: bool, error: string, count: int}
     */
    public function getResponse(): array
    {
        $startTime = microtime(true);
        $method = 'sendToVoxMkk';

        try {
            $managers = $this->extractManagerIds();

            if ($managers === []) {
                $this->logger->logError('voximplant_send_mkk', $method, 
                    new \Exception(self::ERROR_NO_MANAGERS), []);
                return $this->buildError(self::ERROR_NO_MANAGERS);
            }

            $organizationId = $this->extractOrganizationId();
            $date = $this->extractDate();

            $context = [
                'managers_count' => count($managers),
                'organization_id' => $organizationId,
                'date' => $date,
            ];

            $this->logger->logRequest('voximplant_send_mkk', $method, [
                'managers_count' => count($managers),
                'date' => $date,
            ], $context);

            $totalCount = 0;
            $errors = [];

            foreach ($managers as $managerId) {
                $result = $this->processManager($managerId, $date, $organizationId);

                if ($result['success']) {
                    $totalCount += $result['count'];
                    continue;
                }

                $errors[] = $result['error'];
            }

            $duration = microtime(true) - $startTime;

            if ($totalCount > 0) {
                $this->logger->logSuccess('voximplant_send_mkk', $method, [
                    'total_count' => $totalCount,
                    'managers_count' => count($managers),
                ], $duration, $context);
                return $this->buildSuccess($totalCount);
            }

            if (! empty($errors)) {
                $errorMessage = implode('; ', $errors);
                $this->logger->logError('voximplant_send_mkk', $method, 
                    new \Exception($errorMessage), $context);
                return $this->buildError($errorMessage);
            }

            $this->logger->logError('voximplant_send_mkk', $method, 
                new \Exception(self::ERROR_DEFAULT), $context);
            return $this->buildError(self::ERROR_DEFAULT);
        } catch (Throwable $throwable) {
            $this->logger->logError('voximplant_send_mkk', $method, $throwable, []);
            return $this->buildError('Ошибка: ' . $throwable->getMessage());
        }
    }

    /**
     * @return array{success: bool, error: string, count: int}
     */
    private function processManager(int $managerId, string $date, int $organizationId): array
    {
        $manager = $this->simpla->managers->get_manager($managerId);

        if (! $manager) {
            return $this->buildError("Менеджер с ID {$managerId} не найден");
        }

        $users = $this->simpla->users->get_users_ccprolongations([
            'manager_id' => $managerId,
            'date' => $date,
            'organization_id' => $organizationId,
        ]);

        if (empty($users)) {
            return $this->buildSuccess(0);
        }

        $preparedUsers = $this->prepareUsers($users);

        // Используем VoximplantCampaignService вместо прямого вызова Voximplant
        $result = $this->campaignService->sendForOrganization(
            $managerId,
            $preparedUsers,
            $organizationId,
            $manager->role
        );

        if (! empty($result['success'])) {
            return $this->buildSuccess(count($preparedUsers));
        }

        $error = $result['error'] ?? self::ERROR_DEFAULT;

        return $this->buildError("Ошибка для менеджера {$managerId}: {$error}");
    }

    /**
     * @param array<int, mixed> $users
     * @return array<int, mixed>
     */
    private function prepareUsers(array $users): array
    {
        foreach ($users as $user) {
            if (($user->loan_type ?? null) !== 'IL') {
                continue;
            }

            $overdueDebtOd = (float) ($user->overdue_debt_od_IL ?? 0);
            $overdueDebtPercent = (float) ($user->overdue_debt_percent_IL ?? 0);
            $nextPaymentOd = (float) ($user->next_payment_od ?? 0);
            $nextPaymentPercent = (float) ($user->next_payment_percent ?? 0);

            $user->prolongation_amount = 0;
            $user->zaim_summ = $overdueDebtOd + $overdueDebtPercent + $nextPaymentOd + $nextPaymentPercent;
        }

        usort($users, static function ($current, $next) {
            $pattern = '/^(\+|-)\d{2}:\d{2}$/';
            $currentTimezone = $current->UTC ?? '+00:00';
            $nextTimezone = $next->UTC ?? '+00:00';

            if (
                ! preg_match($pattern, $currentTimezone)
                || ! preg_match($pattern, $nextTimezone)
            ) {
                return 0;
            }

            if ($currentTimezone === '+12:00' && $nextTimezone !== '+12:00') {
                return -1;
            }

            if ($currentTimezone !== '+12:00' && $nextTimezone === '+12:00') {
                return 1;
            }

            return strcmp($nextTimezone, $currentTimezone);
        });

        return $users;
    }

    /**
     * @return array<int, int>
     */
    private function extractManagerIds(): array
    {
        if (! isset($this->request['managers']) || ! is_array($this->request['managers'])) {
            return [];
        }

        $managers = array_map('intval', $this->request['managers']);
        $managers = array_filter($managers, static function ($managerId) {
            return $managerId > 0;
        });

        return array_values(array_unique($managers));
    }

    private function extractOrganizationId(): int
    {
        $rawOrganizationId = $this->request['organization_id'] ?? null;
        if ($rawOrganizationId === null || $rawOrganizationId === '') {
            return $this->organizationService->getDefaultId();
        }

        return $this->organizationService->resolveOrganizationId((int) $rawOrganizationId);
    }

    private function extractDate(): string
    {
        $date = (string) ($this->request['date'] ?? '');

        if ($date === '') {
            return date('Y-m-d');
        }

        $dateTime = DateTime::createFromFormat('Y-m-d', $date);

        return $dateTime ? $dateTime->format('Y-m-d') : date('Y-m-d');
    }

    /**
     * @return array{success: bool, error: string, count: int}
     */
    private function buildError(string $message): array
    {
        return [
            'success' => false,
            'error' => $message,
            'count' => 0,
        ];
    }

    /**
     * @return array{success: bool, error: string, count: int}
     */
    private function buildSuccess(int $count): array
    {
        return [
            'success' => true,
            'error' => '',
            'count' => $count,
        ];
    }
}

$response = (new SendToVoxMkk($simpla, $_POST))->getResponse();

$simpla->response->json_output($response);
