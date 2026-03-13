<?php
error_reporting(-1);
ini_set('display_errors', 'On');

chdir(dirname(__FILE__).'/../');
require_once 'api/Simpla.php';
require_once 'api/Helpers.php';

ini_set('max_execution_time', '55');
ini_set('memory_limit', '2048M');

/**
 * Крон для проверки вхождения ПДН заявки в МПЛ перед выдачей
 */
class CheckPdnBeforeIssuance extends Simpla
{
    private const MAX_PDN_CALCULATION_ATTEMPTS = 3;

    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 55;
    private const LOG_FILE = 'check_pdn_before_issuance.txt';

    public function run()
    {
        $this->logging(__METHOD__, '', 'Начало работы крона', '', self::LOG_FILE);

        $orders = $this->orders->get_orders([
            'status' => $this->orders::STATUS_WAIT_PDN_CALCULATION,
            'sort' => 'confirm_date_asc',
        ]);

        if (empty($orders)) {
            $this->logging(__METHOD__, '', 'Нет заявок для расчета ПДН перед выдачей', ['orders' => $orders], self::LOG_FILE);
            return;
        }

        $startTime = time();

        foreach ($orders as $order) {
            if (time() - $startTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '',
                    'Достигнута максимальная продолжительность работы крон', '',self::LOG_FILE);
                break;
            }

            try {
                $this->checkFinalPdnBeforeIssuance($order);
            } catch (Throwable $error) {
                $this->logging(__METHOD__, '', 'Ошибка при расчете финального ПДН перед выдачей', ['order_id' => (int)$order->order_id, 'error' => $error], self::LOG_FILE);
            }
        }

        $this->logging(__METHOD__, '', 'Завершение работы крона', '', self::LOG_FILE);
    }

    private function checkFinalPdnBeforeIssuance(stdClass $order): bool
    {
        // 1. Проверка кол-ва попыток расчета ПДН
        if ($this->hasExceededPdnCalculationAttempts($order)) {
            return false;
        }

        // 2. Если заявка - ВКЛ
        $isRcl = (bool)$this->order_data->read((int)$order->order_id, $this->order_data::RCL_LOAN);
        if ($isRcl) {
            $this->logging(__METHOD__, '', 'Заявка - ВКЛ, не считаем ПДН', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            $this->allowIssuance($order, 5, 0);
            return true;
        }

        $this->logging(__METHOD__, '', 'Начат расчет ПДН', ['order_id' => (int)$order->order_id], self::LOG_FILE);

        // Считаем ПДН до выдачи
        $pdnCalculationResult = $this->order_org_switch->calculateFinalPdnBeforeIssuance($order);

        $this->logging(__METHOD__, '', 'Завершен расчет ПДН', ['order_id' => (int)$order->order_id, 'pdn_calculation_result' => $pdnCalculationResult], self::LOG_FILE);

        // 3. Если расчет ПДН некорректный, то пропускаем заявку,
        // чтобы в следующий запуск крона заново попробовало рассчитаться
        if (empty($pdnCalculationResult) || !isset($pdnCalculationResult->pti_percent)) {
            $this->logging(__METHOD__, '', 'Некорректный расчет ПДН, оставляем заявку в статусе "Ожидание расчета ПДН"', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return false;
        }

        $axiWithoutCreditReports = $this->order_data->read((int)$order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS);

        // 4. Если нет флага $this->order_data::AXI_WITHOUT_CREDIT_REPORTS (т.е. можно запросить отчеты):
        if (empty($axiWithoutCreditReports)) {
            return $this->canIssuanceWithReports($order, $pdnCalculationResult);
        }

        // 5. Если есть флаг $this->order_data::AXI_WITHOUT_CREDIT_REPORTS (нельзя запрашивать отчеты):
        return $this->canIssuanceWithoutReports($order, $pdnCalculationResult);
    }

    private function hasExceededPdnCalculationAttempts(stdClass $order): bool
    {
        $pdnCalculationAttempts = $this->order_data->read((int)$order->order_id, $this->order_data::PDN_CALCULATION_ATTEMPTS) ?? 0;
        $pdnCalculationAttempts++;

        // Если превышено максимальное кол-во попыток расчета ПДН, то отказываем по заявке
        if ($pdnCalculationAttempts > self::MAX_PDN_CALCULATION_ATTEMPTS) {
            $this->rejectOrderDueToExceededPdnCalculation($order);
            return true;
        }

        $this->order_data->set((int)$order->order_id, $this->order_data::PDN_CALCULATION_ATTEMPTS, $pdnCalculationAttempts);
        return false;
    }

    private function saveOrderOrgSwitchResult(int $orderId, int $riverNumber, int $scenarioNumber)
    {
        $this->order_data->set($orderId, $this->order_data::ORDER_ORG_SWITCH_RESULT, 'SUCCESS_WITH_ORGANIZATION_SWITCH_' . $riverNumber . '_' . $scenarioNumber);
    }

    private function rejectOrderDueToNotInMpl(stdClass $order, bool $withReports)
    {
        $withReports = $withReports ? 'с КИ' : 'без КИ';
        $this->logging(__METHOD__, '', "Не вошли в МПЛ $withReports, отказываем по заявке", ['order_id' => (int)$order->order_id], self::LOG_FILE);
        $this->orders->rejectOrder($order, $this->reasons::REASON_NOT_IN_MPL);
    }

    private function rejectOrderDueToRecentlyInquiredReport(stdClass $order, int $riverNumber, int $scenarioNumber, ?int $orderOrgSwitchParentOrderId = null)
    {
        $this->logging(__METHOD__, '', 'Отказываем по заявке, т.к. был недавний запрос отчета', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        $this->orders->rejectOrder($order, $this->reasons::REASON_RECENTLY_INQUIRED_REPORT);

        if (!empty($orderOrgSwitchParentOrderId)) {
            $this->saveOrderOrgSwitchResult($orderOrgSwitchParentOrderId, $riverNumber, $scenarioNumber);
        } else {
            $this->saveOrderOrgSwitchResult((int)$order->order_id, $riverNumber, $scenarioNumber);
        }
    }

    private function rejectOrderDueToExceededPdnCalculation(stdClass $order)
    {
        $this->logging(__METHOD__, '', 'Превышено максимальное кол-во попыток расчета ПДН, отказываем по заявке', ['order_id' => (int)$order->order_id], self::LOG_FILE);
        $this->orders->rejectOrder($order, $this->reasons::REASON_EXCEEDED_MAX_PDN_CALCULATION_ATTEMPTS);
    }

    private function allowIssuance(stdClass $order, int $riverNumber, int $scenarioNumber, ?int $orderOrgSwitchParentOrderId = null)
    {
        $this->logging(__METHOD__, '', "Вошли в МПЛ по ручейку $riverNumber сценарию $scenarioNumber, разрешаем выдачу", ['order_id' => (int)$order->order_id], self::LOG_FILE);

        $this->orders->update_order($order->order_id, [
            'status' => $this->orders::ORDER_STATUS_SIGNED
        ]);

        if (!empty($orderOrgSwitchParentOrderId)) {
            $this->saveOrderOrgSwitchResult($orderOrgSwitchParentOrderId, $riverNumber, $scenarioNumber);
        } else {
            $this->saveOrderOrgSwitchResult((int)$order->order_id, $riverNumber, $scenarioNumber);
        }
    }

    private function canIssuanceWithReports(stdClass $order, stdClass $pdnCalculationResult): bool
    {
        $orderOrgSwitchParentOrderId = (int)$this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);

        // Сценарий 1 и 2: Выдача с КИ: Проверяем вхождение в МПЛ, входим -> выдаем, нет -> отказываем
        if (!empty($pdnCalculationResult->is_within_mpl)) {
            if (empty($orderOrgSwitchParentOrderId)) {
                $this->allowIssuance($order, 4, 1);
            } else {
                $this->allowIssuance($order, 4, 2, $orderOrgSwitchParentOrderId);
            }
            return true;
        }

        $this->rejectOrderDueToNotInMpl($order, true);
        return false;
    }

    private function canIssuanceWithoutReports(stdClass $order, stdClass $pdnCalculationResult): bool
    {
        $orderOrgSwitchParentOrderId = (int)$this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);

        // Сценарий 3: Если был запрос КИ в течение последних REPORTS_RELEVANCE_MAX_DAYS дней, то не можем выдавать без КИ и поэтому отказываем по заявке
        if ($this->axi->checkHasRecentlyInquiredReports((int)$order->order_id, (int)$order->organization_id)) {
            $this->rejectOrderDueToRecentlyInquiredReport($order, 4, 3, $orderOrgSwitchParentOrderId);
            return false;
        }

        // Сценарий 4: Выдача без КИ: Проверяем вхождение в МПЛ, входим -> выдаем, нет -> отказываем
        if (!empty($pdnCalculationResult->is_within_mpl)) {
            $this->allowIssuance($order, 4, 4, $orderOrgSwitchParentOrderId);
            return true;
        }

        $this->rejectOrderDueToNotInMpl($order, false);
        return false;
    }
}

$checkPdnBeforeIssuance = new CheckPdnBeforeIssuance();
$checkPdnBeforeIssuance->run();
