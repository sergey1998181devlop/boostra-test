<?php

namespace api\handlers;

use App\Core\Application\Application;
use App\Enums\LogAction;
use App\Service\ChangeLogs\ActionLoggerService;
use Carbon\Carbon;
use Simpla;

error_reporting(0);
ini_set('display_errors', 'Off');
ini_set('memory_limit', '256M');

require_once dirname(__DIR__) . '/Simpla.php';

class ChangeProlongationHandler extends Simpla
{
    const MAX_OVERDUE_DAYS = 30;
    const MAX_PROLONGATION_COUNT = 5;

    private $actionLogger;

    public function __construct()
    {
        parent::__construct();
        
        $app = Application::getInstance();
        $this->actionLogger = $app->make(ActionLoggerService::class);
    }

    public function handle($orderId, $managerId, $value): array
    {
        if (empty($orderId) || empty($managerId) || !isset($value)) {
            return ['success' => false, 'message' => 'Недостаточно параметров'];
        }

        $orderId = (int)$orderId;
        $managerId = (int)$managerId;

        $order = $this->orders->get_order($orderId);

        $currentProlongationState = $this->order_data->get($orderId, 'prolongation');
        $currentProlongationValue = $currentProlongationState ? $currentProlongationState->value : 0;

        if ($value && $currentProlongationValue) {
            return ['success' => true, 'message' => 'Пролонгация уже включена'];
        }

        if (!$value && !$currentProlongationValue) {
            return ['success' => true, 'message' => 'Пролонгация уже выключена'];
        }

        $timeoutMinutes = $this->getProlongationDisableTimeoutMinutes();

        //Если включена проверка пролонгации
        if (!$value && $timeoutMinutes > 0) {
            $lastActivation = $this->getLastProlongationActivation($orderId);
            if (!empty($lastActivation) && !empty($lastActivation->created)) {
                $activationTime = Carbon::parse($lastActivation->created);
                $diffMinutes = $activationTime->diffInMinutes(Carbon::now());

                if ($diffMinutes < $timeoutMinutes) {
                    $remaining = $timeoutMinutes - $diffMinutes;
                    return [
                        'success' => false,
                        'message' => 'Нельзя отключить пролонгацию в течение часа после включения. Осталось ' . $remaining . ' мин.'
                    ];
                }
            }
        }

        $user = $this->users->get_user($order->user_id);
        if ($value) {
            foreach ($user->loan_history as $loan_history) {
                if (strpos($loan_history->number, (string)$orderId) !== false) {
                    $paymentDate = Carbon::parse($loan_history->plan_close_date);
                    $daysOverdue = $paymentDate->isFuture() ? 0 : $paymentDate->diffInDays(Carbon::now());

                    if ($daysOverdue > static::MAX_OVERDUE_DAYS) {
                        return [
                            'success' => false,
                            'message' => 'Просрочка по платежу превышает ' . static::MAX_OVERDUE_DAYS . ' дней'];
                    }

                    $newPaymentDate = $paymentDate->addDays(30);
                    if ($newPaymentDate->isPast()) {
                        return ['success' => false, 'message' => 'Дата платежа + 30 дней превышает текущую дату.'];
                    }
                }
            }
        }

        $allowed = false;
        $prohibited = true;
        if ($value) {
            $allowed = true;
            $prohibited = false;
        }

        $response = $this->soap->setPermissionsProlongation(
            $order->order_uid,
            $allowed,
            $prohibited,
            $value ? $timeoutMinutes : 0
        );

        $isResultSuccess = $response && !empty($response['return']) && $response['return'] == 'ОК';

        if ($value && $isResultSuccess) {
            $userBalances = $this->soap->get_user_balances_array_1c($order->user_uid, $user->site_id);
            if (count($userBalances) == 1 && isset($userBalances[0]['НомерЗайма']) && $userBalances[0]['НомерЗайма'] == "Нет открытых договоров") {
                return ['success' => false, 'message' => 'Невозможно включить пролонгацию. Нет открытых договоров'];
            }

            foreach ($userBalances as $userBalance) {
                if (strpos($userBalance['НомерЗайма'], (string)$orderId) !== false && empty($userBalance['СуммаДляПролонгации'])) {
                    return ['success' => false, 'message' => 'Невозможно включить пролонгацию. СуммаДляПролонгации = 0'];
                }

                $prolongationCount = $userBalance['prolongation_count'];
                if ($prolongationCount >= self::MAX_PROLONGATION_COUNT) {
                    return ['success' => false, 'message' => 'Превышают лимит. Пролонгация невозможна.'];
                }
            }
        }

        if ($isResultSuccess) {
            $this->order_data->set($orderId, 'prolongation', $value ? 1 : 0);

            $this->actionLogger->log(
                $value ? new LogAction(LogAction::SWITCH_ON_PROLONGATION) : new LogAction(LogAction::SWITCH_OFF_PROLONGATION),
                $order->user_id,
                [
                    'manager_id' => $managerId,
                    'order_id' => $orderId,
                    'new_values' => $value,
                    'old_values' => $currentProlongationValue
                ]);

            return ['success' => true, 'message' => 'Успешно ' . ($value ? 'включена' : 'выключена') . ' пролонгация'];
        }

        return ['success' => false, 'message' => 'Внутренняя ошибка'];
    }

    /**
     * Возвращает последнюю запись о включении пролонгации по заявке.
     * Используется для проверки таймаута отключения.
     *
     * @param int $orderId
     * @return object|null
     */
    private function getLastProlongationActivation(int $orderId): ?object
    {
        $logs = $this->changelogs->get_changelogs([
            'order_id' => $orderId,
            'type' => ['switch_on_prolongation'],
            'limit' => 1,
            'sort' => 'date_desc'
        ]);
        
        return !empty($logs) ? $logs[0] : null;
    }

    /**
     * Возвращает таймаут отключения пролонгации в минутах из настроек.
     * Если не задано или 0, возвращает 0 (таймаут отсутствует).
     *
     * @return int
     */
    private function getProlongationDisableTimeoutMinutes(): int
    {
        $value = $this->settings->prolongation_disable_timeout_minutes;
        return $value !== null ? (int)$value : 0;
    }
}