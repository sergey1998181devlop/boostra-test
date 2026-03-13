<?php
error_reporting(-1);
ini_set('display_errors', 'On');


chdir(dirname(__FILE__).'/../');
require_once 'api/Simpla.php';
require_once 'api/Helpers.php';

/**
 * IssuanceCron
 *
 * Скрипт выдает кредиты, и списывает страховку
 *
 * @author Ruslan Kopyl
 * @copyright 2021
 * @version $Id$
 * @access public
 */
class IssuanceCron extends Simpla
{
    private $il_enabled;

    private const LOG_FILE = 'b2p_issuance.txt';

    /** @var int Максимальное время ожидания привязки карты на сектор кросс-ордера (в секундах) */
    private const MAX_DURATION_TO_WAIT_ADD_CARD = 600;

    public function __construct()
    {
        parent::__construct();

        $this->il_enabled = $this->settings->il_enabled;

        $this->check_wait_for_card_orders();
        $this->check_wait_orders();
        $this->update_cool_loans();

        $i = $this->settings->issuance_count_orders;
        do {
            $orders = $this->run();
            $i--;
        } while ($i > 0 && !empty($orders));

        // В конце обрабатываем "зависшие" выдачи
        // (где деньги отправлены, но контракт не обновился из-за обрыва связи)
        $this->checkIssuedLoanWithoutContractUpdate();
    }

    private function update_cool_loans()
    {
        $cooling_period_hours = $this->settings->cooling_period_hours ?? 4;

        $orders = $this->orders->get_orders(['status' => $this->orders::ORDER_STATUS_COOL, 'limit' => 200, 'sort' => 'order_id_asc']);
        foreach ($orders as $order)
        {
            if ($this->orders->getHoursAfterConfirm($order->confirm_date) >= $cooling_period_hours)
            {
                $this->orders->update_order($order->order_id, array('status' => $this->orders::ORDER_STATUS_SIGNED));
                $cool_date = $this->order_data->get($order->order_id, $this->order_data::HOURS_IN_COOLING);
                $hours_in_cooling_period = intdiv(time() - strtotime($cool_date->updated), 3600);
                $this->order_data->set($order->order_id, $this->order_data::HOURS_IN_COOLING, $hours_in_cooling_period);
            }
        }
    }

    /**
     * Переводим заявки из статуса $this->orders::STATUS_WAIT_CARD в $this->orders::STATUS_SIGNED по
     * прошествии установленного времени
     */
    private function check_wait_for_card_orders()
    {
        $ordersInWaitCardStatus = $this->orders->get_orders([
            'status' => $this->orders::STATUS_WAIT_CARD,
            'limit' => 100
        ]);

        if (empty($ordersInWaitCardStatus)) {
            $this->logging(__METHOD__, '', 'Нет заявок в статусе STATUS_WAIT_CARD', '', self::LOG_FILE);
            return;
        }

        foreach ($ordersInWaitCardStatus as $order) {
            $contract = $this->contracts->get_contract_by_params(['order_id' => $order->order_id]);

            if (empty($contract)) {
                continue;
            }

            $diffSeconds = time() - (int)strtotime($contract->create_date);

            $this->logging(__METHOD__, '', 'Прошло ' . $diffSeconds . ' секунд', ['order' => $order], self::LOG_FILE);

            if ($diffSeconds > self::MAX_DURATION_TO_WAIT_ADD_CARD) {
                $this->orders->update_order($order->order_id, [
                    'status' => $this->orders::ORDER_STATUS_SIGNED
                ]);

                $this->logging(__METHOD__, '', 'Обновлен статусе заявки на ORDER_STATUS_SIGNED', ['order' => $order], self::LOG_FILE);
            } else {
                $this->logging(__METHOD__, '', 'Время привязки еще не истекло. Статус не обновлен', ['order' => $order], self::LOG_FILE);
            }
        }
    }

    private function check_wait_orders()
    {
        $repay_max_count = $this->settings->repay_max_count;
        $repay_timeout = $this->settings->repay_timeout * 60;
        
        $params = [
            'status' => $this->orders::ORDER_STATUS_CRM_WAIT, 
            'limit' => 200, 
            'b2p' => 1
        ];
        if (empty($this->il_enabled)) {
            $params['loan_type'] = $this->orders::LOAN_TYPE_PDL;
        }
        if ($orders = $this->orders->get_orders($params)) {
            foreach ($orders as $order) {
                $order->p2pcredits = $this->best2pay->get_p2pcredits(['order_id'=>$order->order_id]);
                if (empty($order->p2pcredits)) {
                    $this->orders->update_order($order->order_id, [
                        'status' => $this->orders::ORDER_STATUS_SIGNED
                    ]);
                } elseif (count($order->p2pcredits) >= $repay_max_count) {
                    $this->orders->update_order($order->order_id, [
                        'status' => $this->orders::ORDER_STATUS_CRM_NOT_ISSUED, 
                        'pay_result'=>'превышено количество попыток выдачи: '.count($order->p2pcredits)
                    ]);
                } else {
                    $last_p2pcredit = reset($order->p2pcredits);
                    if ((strtotime($last_p2pcredit->complete_date) + $repay_timeout) < time()) {
                        $this->orders->update_order($order->order_id, [
                            'status' => $this->orders::ORDER_STATUS_SIGNED
                        ]);
                    }
                }
            }
        }

    }

    private function run()
    {
        $params = [
            'status' => 8,
            'limit' => 1,
            'b2p' => 1,
            'sort' => 'confirm_date_asc',
        ];
        if ($orders = $this->orders->get_orders($params)) {

            foreach ($orders as $order)
            {
                if ($this->virtualCard->shouldWaitUntilVirtualCardReadyOrMoveToSBP($order)) {
                    continue; // Переводим заказ в ожидание (status = 16) или отдаем сбп
                }

                // Проверяем отсрочку выдачи для крупных займов
                if ($this->orders->shouldDelayIssuance($order)) {
                    $this->orders->update_order($order->order_id, ['status' => $this->orders::ORDER_STATUS_COOL]);
                    $this->order_data->set($order->order_id, $this->order_data::HOURS_IN_COOLING, 0);
                    continue; // Пропускаем этот заказ до истечения отсрочки
                }

                if (empty($this->il_enabled) && $order->loan_type == $this->orders::LOAN_TYPE_IL) {
                    $this->orders->update_order($order->order_id, [
                        'status' => $this->orders::ORDER_STATUS_CRM_WAIT
                    ]);
                    continue;
                }

                // Проверяем, есть ли открытые займы у клиента
                if ($this->orders->check_issued_order($order)) {
                    $this->orders->update_order($order->order_id, array('status' => $this->orders::ORDER_STATUS_CRM_NOT_ISSUED, 'pay_result'=>'У клиента найдены открытые займы'));
                    continue;
                }

                if (!$this->isOrderPdnWithinMpl($order)) {
                    continue;
                }

                if (!$this->checkPdnThreshold($order)) {
                    continue;
                }

                if ($order->utm_source == $this->orders::UTM_SOURCE_CROSS_ORDER && $this->has_cross_order_error($order)) {
                    // Переводим выдачу в ожидание, ждём пока будет готова выдача по основной заявке
                    $this->orders->update_order($order->order_id, ['status' => $this->orders::ORDER_STATUS_CRM_WAIT]);
                    // Для корректных повторных попыток выдачи
                    $p2pcredit = [
                        'order_id' => $order->order_id,
                        'user_id' => $order->user_id,
                        'date' => date('Y-m-d H:i:s'),
                        'complete_date' => date('Y-m-d H:i:s'),
                        'register_id' => '',
                        'operation_id' => '',
                        'body' => 'Ждём выдачу по ' . ($order->utm_medium ?? 'НЕТ ЗАЯВКИ'),
                        'response' => '',
                        'status' => 'WAIT',
                    ];
                    $this->best2pay->add_p2pcredit($p2pcredit);
                    continue;
                }

                $payoutDecision = $this->orders->readyForPayout($order);

                $ready      = $payoutDecision['ready'] ?? null;
                $reason     = $payoutDecision['reason'] ?? [];
                $reasonCode = $reason['code'] ?? null;
                $reasonMsg  = $reason['message'] ?? null;

                if (empty($reasonMsg)) {
                    if ($ready === false) {
                        $reasonMsg = 'Отказ по результатам скорингов';
                    } elseif ($ready === null) {
                        $reasonMsg = 'Ожидание завершения скорингов';
                    }
                }

                // 1) Окончательный отказ
                if ($ready === false) {
                    $this->orders->update_order($order->order_id, [
                        'status'     => $this->orders::ORDER_STATUS_CRM_NOT_ISSUED,
                        'pay_result' => $reasonMsg,
                    ]);

                    $this->logging(
                        __METHOD__,
                        '',
                        $reasonMsg,
                        ['order' => $order, 'reasonCode' => $reasonCode],
                        self::LOG_FILE
                    );

                    continue;
                }

                // 2) Ждём завершения скорингов
                if ($ready === null) {
                    $this->orders->update_order($order->order_id, [
                        'status' => $this->orders::ORDER_STATUS_CRM_WAIT,
                    ]);

                    $this->best2pay->add_p2pcredit([
                        'order_id'      => $order->order_id,
                        'user_id'       => $order->user_id,
                        'date'          => date('Y-m-d H:i:s'),
                        'complete_date' => date('Y-m-d H:i:s'),
                        'register_id'   => '',
                        'operation_id'  => '',
                        'body'          => $reasonMsg,
                        'response'      => '',
                        'status'        => 'WAIT',
                    ]);

                    $this->logging(
                        __METHOD__,
                        '',
                        $reasonMsg,
                        ['order' => $order, 'reasonCode' => $reasonCode],
                        self::LOG_FILE
                    );

                    continue;
                }

                $this->check_order_uid($order);

                if ($this->config->is_dev) {
                    $res = $this->best2pay->pay_contract($order->order_id);
                } else {
                    $remote_response = $this->best2pay->pay_contract_remote($order->order_id);
                    if (!($res = simplexml_load_string($remote_response))) {
                        $res = $remote_response;
                    }
                }

                $p2p_status = (string)$res->state;

                if ($this->virtualCard->shouldRetryRejectedVirtualCardOrderWithSBP($res, $order)) {
                    continue;
                }

                $this->issuance->issuanceByStatus($p2p_status, $order, $res);
            }
        }
        return $orders;
    }
    
    private function check_order_uid($order)
    {
        if (empty($order->order_uid)) {
            $this->orders->update_order($order->order_id, [
                'order_uid' => exec($this->config->root_dir . 'generic/uidgen')
            ]);
        }
    }

    /**
     * Проверяет наличие ошибок на выдачу cross_order заявки.
     *
     * @param stdClass $crossOrder
     * @return bool true, если есть ошибка
     */
    private function has_cross_order_error(stdClass $crossOrder): bool
    {
        $mainOrder = $this->orders->get_order($crossOrder->utm_medium);
        if (empty($mainOrder))
            return true; // Откладываем выдачу кросс-ордера

        // Основная заявка уже выдана
        if ($mainOrder->status == $this->orders::ORDER_STATUS_CRM_ISSUED)
            return false; // Выдаём кросс-ордер

        // Основная заявка в тех.отказе
        if ($mainOrder->status == $this->orders::ORDER_STATUS_CRM_REJECT &&
            $mainOrder->reason_id == 10)
            return false; // Выдаём кросс-ордер

        // Основная заявка ещё не выдана/выдача в процессе/выдача не удалась
        return true; // Откладываем выдачу кросс-ордера
    }

    private function isOrderPdnWithinMpl(stdClass $order): bool
    {
        // 1. Если дев, то не считаем ПДН
        if ($this->helpers->isDev()) {
            return true;
        }

        // 2. Если тестовый пользователь, то не считаем ПДН
        if ($this->user_data->isTestUser((int)$order->user_id)) {
            return true;
        }

        $user = $this->users->get_user($order->user_id);
        if (empty($user)) {
            $this->logging(__METHOD__, '', 'Пользователь не найден, отказываем по заявке', ['order_id' => (int)$order->order_id, 'user' => $user], self::LOG_FILE);
            $this->rejectOrderDueToNotInMpl($order);
            return false;
        }

        $this->settings->setSiteId($user->site_id);
        $settings = $this->settings->organization_switch;

        // 3. Если ручеек выключен, то не считаем ПДН
        if (empty($settings['enabled'])) {
            $this->logging(__METHOD__, '', 'Ручеек отключен, разрешаем выдачу', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return true;
        }

        // 4. Не проверяем ПДН для кросс-ордеров
        if ($this->orders->isCrossOrder($order)) {
            $this->logging(__METHOD__, '', 'Не проверяем ПДН для кросс-ордеров, разрешаем выдачу', ['order_id' => $order->order_id], self::LOG_FILE);
            return true;
        }

        $orderOrgSwitchParentOrderId = $this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);
        $orderOrgSwitchResult = $this->order_data->read((int)$order->order_id, $this->order_data::ORDER_ORG_SWITCH_RESULT);
        $axiWithoutCreditReports = $this->order_data->read((int)$order->order_id, $this->order_data::AXI_WITHOUT_CREDIT_REPORTS);

        $this->logging(__METHOD__, '', 'Взята заявка для проверки вхождения ПДН в МПЛ', ['order_id' => (int)$order->order_id, 'order_org_switch_parent_order_id' => $orderOrgSwitchParentOrderId, 'order_org_switch_result' => $orderOrgSwitchResult, 'axi_without_credit_reports' => $axiWithoutCreditReports], self::LOG_FILE);

        // 5. Если нет меток ручейка
        if (empty($orderOrgSwitchParentOrderId) && empty($orderOrgSwitchResult) && empty($axiWithoutCreditReports)) {
            $this->logging(__METHOD__, '', 'Заявка прошла мимо ручейка, отказываем по заявке', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            $this->rejectOrderDueToNotInMpl($order);
            return false;
        }

        // 6. Если заявка - ВКЛ, то не считаем ПДН перед выдачей и лишь проверяем сумму выдачи
        $isRcl = (bool)$this->order_data->read((int)$order->order_id, $this->order_data::RCL_LOAN);

        if (!empty($isRcl)) {
            $this->logging(__METHOD__, '', 'Заявка - ВКЛ, не считаем ПДН', ['order_id' => (int)$order->order_id], self::LOG_FILE);

            $contract = $this->contracts->get_contract((int)$order->contract_id);
            $rclMaxAmount = $this->order_data->read((int)$order->order_id, $this->order_data::RCL_MAX_AMOUNT);

            if ((int)$contract->amount > (int)$rclMaxAmount) {

                $this->logging(__METHOD__, '', 'Сумма займа превышает максимальный лимит ВКЛ, переводим заявку в "Не удалось выдать" (не отказываем по заявке)', ['order_id' => (int)$order->order_id, 'contract_amount' => $contract->amount, 'rcl_max_amount' => $rclMaxAmount,], self::LOG_FILE);

                $this->orders->update_order($order->order_id, [
                    'status' => $this->orders::ORDER_STATUS_CRM_NOT_ISSUED,
                    'pay_result' => 'Сумма займа превышает максимальный лимит ВКЛ'
                ]);
                return false;
            }

            $this->logging(__METHOD__, '', 'Сумма займа не превышает максимальный лимит ВКЛ, разрешаем выдачу', ['order_id' => (int)$order->order_id, 'contract_amount' => $contract->amount, 'rcl_max_amount' => $rclMaxAmount,], self::LOG_FILE);

            return true;
        }

        $finalPdnCalculation = $this->pdnCalculation->getPdnRow($order->order_id, true);

        // 7. Если нет финального расчет ПДН перед выдачей, то переводим заявку в статус "Ожидание расчета ПДН"
        if (empty($finalPdnCalculation)) {
            $this->orders->update_order($order->order_id, [
                'status' => $this->orders::STATUS_WAIT_PDN_CALCULATION
            ]);

            $this->logging(__METHOD__, '', 'Переводим заявку в статус "Ожидание расчета ПДН"', ['order_id' => (int)$order->order_id], self::LOG_FILE);
            return false;
        }

        $finalPdnCalculationResult = json_decode($finalPdnCalculation->result);

        // Если вошли в МПЛ, то разрешаем выдачу
        if (!empty($finalPdnCalculationResult->is_within_mpl)) {
            return true;
        }

        // Если не вошли в МПЛ, то отказываем по заявке
        $this->logging(__METHOD__, '', 'Не вошли в МПЛ, отказываем по заявке', ['order_id' => (int)$order->order_id, 'final_pdn_calculation_result' => $finalPdnCalculation], self::LOG_FILE);
        $this->rejectOrderDueToNotInMpl($order);

        return false;

    }

    private function rejectOrderDueToNotInMpl(stdClass $order)
    {
        $this->orders->rejectOrder($order, $this->reasons::REASON_NOT_IN_MPL);
    }

    /**
     * Проверяет порог ПДН перед выдачей для организаций
     */
    private function checkPdnThreshold(stdClass $order): bool
    {
        if (!$this->scorings->isPdnEnabledForOrder($order)) {
            return true;
        }

        $site_id = $this->organizations->get_site_organization($order->organization_id);
        if (!empty($site_id)) {
            $this->settings->setSiteId($site_id);
        }

        $disablePdnCheck = (bool)$this->settings->disable_pdn_check;

        $pdnCalculationResult = $this->order_org_switch->calculatePdnBeforeIssuance($order);

        if (empty($pdnCalculationResult) || !isset($pdnCalculationResult->pti_percent)) {
            $this->orders->update_order($order->order_id, [
                'status' => $this->orders::ORDER_STATUS_CRM_WAIT
            ]);
            $this->logging(__METHOD__, '', 'ПДН не рассчитан, ожидание расчёта',
                ['order_id' => $order->order_id], self::LOG_FILE);
            return false;
        }

        $pdn = $pdnCalculationResult->pti_percent;

        $pdnScoringType = $this->scorings->get_type($this->scorings::TYPE_PDN);
        $maxPdnThreshold = (float)($pdnScoringType->params['max_pdn_threshold'] ?? 80);

        if ((float)$pdn > $maxPdnThreshold && !$disablePdnCheck) {
            $this->orders->update_order($order->order_id, [
                'status' => $this->orders::ORDER_STATUS_CRM_NOT_ISSUED,
                'pay_result' => 'Высокий ПДН: ' . $pdn . '%',
                'reason_id' => $this->reasons::REASON_HIGH_PDN
            ]);

            $this->logging(__METHOD__, '', 'Отказ по высокому ПДН',
                ['order_id' => $order->order_id, 'pdn' => $pdn, 'threshold' => $maxPdnThreshold], self::LOG_FILE);
            return false;
        }

        return true;
    }

    /**
     * Находит выданные займы (успешный pay_contract_remote),
     * у которых не обновился контракт из-за обрыва связи,
     * и повторно выполняет issuanceByStatus
     */
    public function checkIssuedLoanWithoutContractUpdate(): void
    {
        // Ищем записи в b2p_p2pcredits со статусом APPROVED,
        // у которых s_contract.issuance_date IS NULL
        // Это означает, что деньги отправлены, но issuanceByStatus не выполнился
        $query = $this->db->placehold(
            '
            SELECT
                p2p.id as p2p_id,
                p2p.order_id,
                p2p.user_id,
                p2p.status,
                p2p.response,
                p2p.register_id,
                p2p.operation_id,
                p2p.complete_date,
                o.id as order_id
            FROM b2p_p2pcredits p2p
            LEFT JOIN s_contracts c ON c.order_id = p2p.order_id
            LEFT JOIN s_orders o ON o.id = p2p.order_id
            WHERE p2p.status = ?
              AND c.issuance_date IS NULL
              AND p2p.complete_date >= ?
            ORDER BY p2p.id DESC
            LIMIT 10;',
            'APPROVED',
            date('Y-m-01 00:00:00')
        );

        $this->db->query($query);
        $issuedLoansWithoutContractUpdate = $this->db->results() ?: [];

        $this->logging(
            __METHOD__,
            '',
            'Найдено зависших выдач: ' . count($issuedLoansWithoutContractUpdate),
            ['count' => count($issuedLoansWithoutContractUpdate)],
            self::LOG_FILE
        );

        foreach ($issuedLoansWithoutContractUpdate as $p2pRecord) {
            try {
                // Получаем полную информацию о заявке
                $order = $this->orders->get_order($p2pRecord->order_id);

                if (empty($order)) {
                    $this->logging(
                        __METHOD__,
                        '',
                        'Заявка не найдена для зависшей выдачи',
                        ['p2p_id' => $p2pRecord->p2p_id, 'order_id' => $p2pRecord->order_id],
                        self::LOG_FILE
                    );
                    continue;
                }

                // Восстанавливаем XML ответ из сериализованного response
                $response = @unserialize($p2pRecord->response);

                if ($response === false || empty($response)) {
                    $this->logging(
                        __METHOD__,
                        '',
                        'Не удалось восстановить response для зависшей выдачи',
                        ['p2p_id' => $p2pRecord->p2p_id, 'order_id' => $p2pRecord->order_id],
                        self::LOG_FILE
                    );
                    continue;
                }

                // Пробуем распарсить XML
                $res = simplexml_load_string($response);

                if ($res === false) {
                    $this->logging(
                        __METHOD__,
                        '',
                        'Не удалось распарсить XML для зависшей выдачи',
                        ['p2p_id' => $p2pRecord->p2p_id, 'order_id' => $p2pRecord->order_id],
                        self::LOG_FILE
                    );
                    continue;
                }

                $this->logging(
                    __METHOD__,
                    '',
                    'Повторная обработка зависшей выдачи через issuanceByStatus',
                    [
                        'p2p_id' => $p2pRecord->p2p_id,
                        'order_id' => $p2pRecord->order_id,
                        'status' => 'APPROVED'
                    ],
                    self::LOG_FILE
                );

                // Повторно выполняем issuanceByStatus для APPROVED статуса
                $this->issuance->issuanceByStatus('APPROVED', $order, $res);

                // Устанавливаем sent=0, чтобы запись переотправилась
                $this->best2pay->update_p2pcredit($p2pRecord->p2p_id, ['sent' => 0]);

                $this->logging(
                    __METHOD__,
                    '',
                    'Зависшая выдача успешно обработана, sent=0 установлен',
                    ['p2p_id' => $p2pRecord->p2p_id, 'order_id' => $p2pRecord->order_id],
                    self::LOG_FILE
                );
            } catch (Exception $e) {
                $this->logging(
                    __METHOD__,
                    '',
                    'Ошибка при обработке зависшей выдачи: ' . $e->getMessage(),
                    [
                        'p2p_id' => $p2pRecord->p2p_id ?? null,
                        'order_id' => $p2pRecord->order_id ?? null,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ],
                    self::LOG_FILE
                );
            }
        }
    }
}

$lockfile = sys_get_temp_dir() . '/' . md5(__FILE__) . '.lock';
$pid = file_exists($lockfile) ? trim(file_get_contents($lockfile)) : null;

if (is_null($pid) || posix_getsid($pid) === false) {
    file_put_contents($lockfile, getmypid());
    echo 'run '.date("H:i:s").PHP_EOL;
$cron = new IssuanceCron();
    unlink($lockfile);
} else {
    exit('is already running '.date("H:i:s").PHP_EOL);
}
