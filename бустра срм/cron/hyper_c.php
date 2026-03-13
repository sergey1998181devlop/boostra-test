<?php

date_default_timezone_set('Europe/Moscow');

ini_set('memory_limit', '1024M');

define('ROOT', dirname(__DIR__));

require_once __DIR__ . '/../api/Simpla.php';

/**
 * Крон получает результаты скоринга hyper-c из таблицы s_hyper_c и сохраняет их в s_scorings.
 * Если результат скоринга $this->hyper_c::APPROVED_DECISION, то авто-одобряем заявку с суммой одобрения = s_hyper_c.approve_amount.
 * Если результат скоринга $this->hyper_c::REJECTED_DECISION, то заявка авто-отказывается
 */
class HyperCCron extends Simpla
{
    /** @var int Лимит для одобряемой суммы */
    private const AMOUNT_LIMIT = 30_000;

    /** @var int Время ожидания скоринга (в минутах) */
    private const HYPER_C_SCORING_TIME_LIMIT = 3;

    /** @var int Максимальное кол-во скорингов для обработки одним запуском крона */
    private const MAX_REPORT_SCORINGS_TO_PROCESS = 50;

    /** @var int Максимальное время выполнения скрипта (в секундах) */
    private const MAX_EXECUTION_TIME = 55;

    private const LOG_FILE = 'hyper_c.txt';

    public function run()
    {
        $executionStartTime = microtime(true);

        $this->logging(__METHOD__, '', '', 'Начало работы крон: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE);

        $hyperCScorings = $this->scorings->get_scorings([
            'status' => $this->scorings::STATUS_NEW,
            'type' => $this->scorings::TYPE_HYPER_C,
            'limit' => self::MAX_REPORT_SCORINGS_TO_PROCESS
        ]);

        if (empty($hyperCScorings)) {
            $this->logging(__METHOD__, '', '', 'Не найдено новых заявок', self::LOG_FILE);
            return;
        }

        $ordersIdForHyperC = array_column($hyperCScorings, 'order_id');

        if (empty($ordersIdForHyperC)) {
            $this->logging(__METHOD__, '', '', 'Не найдены ID заявок для hyper_c', self::LOG_FILE);
            return;
        }

        $hyperCResults = $this->hyper_c->get([
            'order_id' => $ordersIdForHyperC
        ]);

        if (empty($hyperCResults)) {
            $this->logging(__METHOD__, '', '', 'По новым заявкам нет результатов в hyper-c', self::LOG_FILE);
            $hyperCResults = [];
        } else {
            $hyperCResults = array_column($hyperCResults, null, 'order_id');
        }

        $hyperCScoringType = $this->scorings->get_type($this->scorings::TYPE_HYPER_C);

        if (empty($hyperCScoringType)) {
            $this->logging(__METHOD__, '', '', 'Не найден тип скоринга hyper-c', self::LOG_FILE);
            return;
        }

        foreach ($hyperCScorings as $hyperCScoring) {
            if (microtime(true) - $executionStartTime > self::MAX_EXECUTION_TIME) {
                $this->logging(__METHOD__, '', '', 'Достигнута максимальная продолжительность работы крон', self::LOG_FILE);
                break;
            }

            $hyperCResult = $hyperCResults[$hyperCScoring->order_id];

            if (empty($hyperCResult) || $hyperCResult->success === null) {
                $minutesAfterCreatingHyperCScoring = (int)((time() - (int)strtotime($hyperCScoring->created)) / 60);

                if ($minutesAfterCreatingHyperCScoring >= self::HYPER_C_SCORING_TIME_LIMIT) {
                    $this->scorings->update_scoring((int)$hyperCScoring->id, [
                        'status' => $this->scorings::STATUS_ERROR,
                        'string_result' => 'Истекло время ожидания',
                        'start_date' => date('Y-m-d H:i:s'),
                        'end_date' => date('Y-m-d H:i:s'),
                    ]);

                    $this->logging(__METHOD__, '', 'Истекло время ожидания скоринга', ['hyper_c_scoring' => $hyperCScoring, 'hyper_c_result' => $hyperCResult], self::LOG_FILE);
                } else {
                    $this->logging(__METHOD__, '', 'Пока нет решения из hyper_c. Ожидаем', ['hyper_c_scoring' => $hyperCScoring, 'hyper_c_result' => $hyperCResult], self::LOG_FILE);
                }

                continue;
            }

            $this->updateHyperCScoring($hyperCScoring, $hyperCResult, $hyperCScoringType);

            try {
                if (!empty($hyperCResult->success) && !empty($hyperCScoringType->active)) {
                    $this->processOrder((int)$hyperCScoring->order_id, $hyperCResult, $hyperCScoringType);
                }
            } catch (Throwable $e) {
                $error = [
                    'Ошибка: ' . $e->getMessage(),
                    'Файл: ' . $e->getFile(),
                    'Строка: ' . $e->getLine(),
                    'Подробности: ' . $e->getTraceAsString()
                ];
                $this->logging(__METHOD__, '', 'Возникла фатальная ошибка', ['hyper_c_scoring' => $hyperCScoring, 'error' => $error], self::LOG_FILE);
            }
        }

        $this->logging(__METHOD__, '', '', 'Крон завершен: ' .
            date('Y-m-d H:i:s'), self::LOG_FILE);
    }

    private function updateHyperCScoring(stdClass $hyperCScoring, stdClass $hyperCResult, stdClass $hyperCScoringType): void
    {
        if (empty($hyperCScoringType->active)) {
            $status = $this->scorings::STATUS_ERROR;
            $success = null;
            $decision = null;
            $string_result = 'Скоринг Hyper-C отключен в настройках';
        } elseif (empty($hyperCResult->success)) {
            $status = $this->scorings::STATUS_ERROR;
            $success = null;
            $decision = null;
            $string_result = 'Ошибка при выполнении скоринга';
        } elseif ($hyperCResult->decision === $this->hyper_c::APPROVED_DECISION) {
            $status = $this->scorings::STATUS_COMPLETED;
            $success = 1;
            $decision = $this->hyper_c::APPROVED_DECISION;
            $string_result = 'Заявка одобрена. Рекомендуемая сумма: ' . $hyperCResult->approve_amount . ' руб.';
        } elseif ($hyperCResult->decision === $this->hyper_c::REJECTED_DECISION) {
            $status = $this->scorings::STATUS_COMPLETED;
            $success = 0;
            $decision = $this->hyper_c::REJECTED_DECISION;
            $string_result = 'Заявка отклонена';
        } else {
            $status = $this->scorings::STATUS_ERROR;
            $success = null;
            $decision = null;
            $string_result = 'Ошибка, получен некорректный ответ';
        }

        $this->scorings->update_scoring((int)$hyperCScoring->id, [
            'status' => $status,
            'success' => $success,
            'scorista_status' => $decision,
            'string_result' => $string_result,
            'start_date' => $hyperCResult->start_date,
            'end_date' => $hyperCResult->end_date,
            'body' => $hyperCResult->result
        ]);
    }

    private function processOrder(int $orderId , stdClass $hyperCResult, stdClass $hyperCScoringType): void
    {
        $order = $this->orders->get_order($orderId);

        if (empty($order)) {
            $this->logging(__METHOD__, '', 'Заявка не найдена', ['order_id' => (int)$order->order_id, 'hyper_c_result' => $hyperCResult], self::LOG_FILE);
            return;
        }

        if (!empty($order->have_close_credits)) {
            $this->logging(__METHOD__, '', 'Не НК', ['order_id' => (int)$order->order_id, 'hyper_c_result' => $hyperCResult], self::LOG_FILE);
            return;
        }

        // Заявка уже одобрена или по ней есть отказ
        if ((int)$order->status !== $this->orders::ORDER_STATUS_CRM_NEW) {
            $this->logging(__METHOD__, '', 'Заявка не новая', ['order_id' => (int)$order->order_id, 'hyper_c_result' => $hyperCResult], self::LOG_FILE);
            return;
        }

        if (empty($hyperCScoringType->params['utm_sources']) || !is_array($hyperCScoringType->params['utm_sources'])) {
            $this->logging(__METHOD__, '', '', 'Не указаны utm_sources у типа скоринга hyper-c', self::LOG_FILE);
            return;
        }

        $orderUtmSource = $order->utm_source ?: 'Boostra';

        // Если utm_source у заявки согласно настройкам подлежит добавлению скорингу hyper-c
        if (!in_array($orderUtmSource, $hyperCScoringType->params['utm_sources'])) {
            $this->logging(__METHOD__, '', 'Utm_source заявки не подлежит скорингу hyper-c', ['order_id' => (int)$order->order_id, 'hyper_c_result' => $hyperCResult], self::LOG_FILE);
            return;
        }

        // Сумма не должна выходить за лимит
        $approveAmount = min($hyperCResult->approve_amount, self::AMOUNT_LIMIT);

        $this->order_data->set((int)$order->order_id, $this->order_data::HYPER_C_APPROVE_AMOUNT, $approveAmount);

        if (!empty((int)$hyperCResult->success) && !empty($approveAmount)) {
//            if ($order->first_loan == 1) {
//                $this->orders->update_order((int)$order->order_id, [
//                    'amount' => $approveAmount,
//                    'approve_amount' => $approveAmount,
//                ]);
//            }

//            $this->orders->approveOrder((int)$order->order_id);
            $this->logging(__METHOD__, '', 'Одобрение заявки (пока отключено)', ['hyper_c_result' => $hyperCResult, 'order_id' => (int)$order->order_id], self::LOG_FILE);
        } else {
//            if ($order->first_loan == 1) {
//                $this->orders->rejectOrder($order, $this->reasons::REASON_HYPER_C);
//            }
            $this->logging(__METHOD__, '', 'Отказ по заявке (пока отключено)', ['hyper_c_result' => $hyperCResult, 'order_id' => (int)$order->order_id], self::LOG_FILE);
        }
    }
}

$hyperCCron = new HyperCCron();
$hyperCCron->run();
