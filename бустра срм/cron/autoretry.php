<?php
error_reporting(-1);

session_start();

ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once dirname(__FILE__).'/../api/Simpla.php';

class AutoretryCron extends Simpla
{
    private const LOG_FILE = 'autoretry.txt';

    public function __construct()
    {
    	parent::__construct();

        //$this->run_autoreject();

        $this->run();
    }

    /**
     * AutoretryCron::run_autoreject()
     * Отказывает заявкам без страховки НК
     * @return void
     */
    public function run_autoreject()
    {
        $minus3 = date('Y-m-d H:i:s', time() - 180);

    	$this->db->query("
            SELECT 
                o.id AS order_id, 
                u.id AS user_id,
                o.1c_id AS id_1c,
                o.is_user_credit_doctor,
                o.*, 
                u.*,
                u.date_skip_cr_visit < CURRENT_TIMESTAMP as accept_reject_orders
            FROM __orders AS o
            LEFT JOIN __users AS u
            ON u.id = o.user_id
            WHERE o.have_close_credits = 0
            AND u.service_insurance = 0
            AND o.status != 3
            AND u.additional_data_added = 1
            AND fake_order_error > 1
            AND o.date < ?
            AND o.date > '2022-01-26'
            LIMIT 100
        ", $minus3);

        $results = $this->db->results();

        $system_manager = $this->managers->get_manager(50);
        $reason = $this->reasons->get_reason(30);

        foreach ($results as $order)
        {
            $has_pay_credit_rating = $this->scorings->hasPayCreditRating((int) $order->user_id);
            $last_scorista_scoring = $this->scorings->get_last_scorista_for_user((int) $order->user_id, true);

            if ((($has_pay_credit_rating && empty($last_scorista_scoring->scorista_id)) || empty($order->skip_credit_rating)) && !empty($order->accept_reject_orders)) {
                continue;
            }

            $update = array(
                'status' => 3,
                'manager_id' => $system_manager->id,
                'reject_date' => date('Y-m-d H:i:s'),
                'reason_id' => $reason->id,
            );
            $this->orders->update_order($order->order_id, $update);

            $this->changelogs->add_changelog(array(
                'manager_id' => $system_manager->id,
                'created' => date('Y-m-d H:i:s'),
                'type' => 'status',
                'old_values' => serialize(array()),
                'new_values' => serialize($update),
                'order_id' => $order->order_id,
            ));
            $this->soap->update_status_1c($order->id_1c, $this->orders::ORDER_1C_STATUS_REJECTED, $system_manager->name_1c, 0, 1, $reason->admin_name);
            if (!empty($order->is_user_credit_doctor))
                $this->soap1c->send_credit_doctor($order->id_1c);

//            $this->soap->set_order_manager($order->id_1c, $system_manager->name_1c);

        }

        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($results);echo '</pre><hr />';
    }

    private function log_autoretry($order_id, $manager_id) {
        $this->changelogs->add_changelog(array(
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'autoretry',
            'old_values' => serialize(array()),
            'new_values' => serialize(array('autoretry' => 0)),
            'order_id' => $order_id,
        ));
    }

    public function run()
    {
        $query = $this->db->placehold("
            SELECT *, 1c_id AS id_1c
            FROM __orders
            WHERE autoretry = 1
            AND status = 1
            ORDER BY date DESC
            LIMIT 30
        ");
        $this->db->query($query);

        if ($orders = $this->db->results())
        {
            $system_manager = $this->managers->get_manager(50);
            foreach ($orders as $order)
            {
                $user = $this->users->get_user($order->user_id);
                if (!empty($user->inn))
                {
                    $last_scorista = $this->scorings->get_last_scorista_for_order($order->id);
                    if ($last_scorista->status == $this->scorings::STATUS_COMPLETED)
                    {
                        if ($last_scorista->scorista_status == 'Одобрено')
                        {
                            $last_scorista->body = json_decode($this->scorings->get_scoring_body($last_scorista->id));
                            $decisionSum_without_PTI = $last_scorista->body->additional->decisionSum_without_PTI ?? null;
                            $decisionSum = $last_scorista->body->additional->decisionSum ?? null;
                            $has_two_decisionSum = !empty($decisionSum_without_PTI) && !empty($decisionSum);

                            if ($has_two_decisionSum) {
                                $last_scorista_max_amount = max($decisionSum, $decisionSum_without_PTI);
                            } else {
                                $last_scorista_max_amount = $last_scorista->body->additional->decisionSum;
                            }

                            if (empty($last_scorista_max_amount))
                            {
                                if ($this->leadgidScorista->isEnabled()
                                    && $leadgid = $this->leadgidScorista->getByOrder($order)
                                    && !$this->leadgidScorista->hasStopFactor($last_scorista->body))
                                    $last_scorista_max_amount = $leadgid->amount;
                            }

                            $checkCanApproveAutoretry = $this->checkCanApproveAutoretry($order);
                            if ($checkCanApproveAutoretry === null) {
                                continue;
                            }

                            if ($last_scorista_max_amount > 0 && $checkCanApproveAutoretry)
                            {
                                /** @var string $is_increased_amount Если не null значит сумма в заявке была увеличена настройкой */
                                $is_increased_amount = $this->order_data->read($order->id, $this->approve_amount_settings::ORDER_DATA_APPROVE_AMOUNT_VERSION);

                                if (!empty($is_increased_amount)) {
                                    // Увеличиваем сумму одобрения скористы
                                    $last_scorista_max_amount += (int)$this->order_data->read($order->id, $this->approve_amount_settings::ORDER_DATA_APPROVE_AMOUNT_INCREASED);
                                }

                                /** @var string $is_increased_amount Минимальная сумма для автоповторов при высоком балле скористы */
                                $increased_order_amount_for_autoretry = (int)$this->settings->increased_order_amount_for_autoretry;

                                $amount_limit = empty($order->have_close_credits) ? 15000 : 30000;

                                // Сумма не должна выходить за лимит
                                if ($last_scorista_max_amount > $amount_limit) {
                                    $last_scorista_max_amount = $amount_limit;
                                    $increased_order_amount_for_autoretry = $amount_limit;
                                }

                                $update = [
                                    'loan_type' => 'PDL'
                                ];

                                if ((int)$last_scorista->scorista_ball >= (int)$this->settings->min_scorista_ball_for_autoretry) {
                                    $newAmount = max($increased_order_amount_for_autoretry, $last_scorista_max_amount);
                                    $order->amount = $order->approve_amount = $newAmount;
                                    $this->order_data->set((int)$order->id, $this->order_data::INCREASED_ORDER_AMOUNT_FOR_AUTORETRY, $newAmount);
                                } else {
                                    $order->amount = $order->approve_amount = $last_scorista_max_amount;
                                }

                                $update['amount'] = $order->amount;
                                $update['approve_amount'] = $order->approve_amount;
                                $update['period'] = min(16, $order->period);
                                $update['status'] = 2;
                                $update['manager_id'] = $system_manager->id;
                                $update['approve_date'] = date('Y-m-d H:i:s');

                                echo 'одобряем '.$order->amount;
                                echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($update);echo '</pre><hr />';
                                $this->orders->update_order($order->id, $update);

                                $this->changelogs->add_changelog(array(
                                    'manager_id' => $system_manager->id,
                                    'created' => date('Y-m-d H:i:s'),
                                    'type' => 'status',
                                    'old_values' => serialize(array()),
                                    'new_values' => serialize($update),
                                    'order_id' => $order->id,
                                ));
                                $this->soap->update_status_1c($order->id_1c, 'Одобрено', $system_manager->name_1c, $order->amount, $order->percent, '', 0, $order->period);

                                $this->cross_orders->create($order->id);

        //                        $this->soap->set_order_manager($order->id_1c, $system_manager->name_1c);

                                $user = $this->users->get_user($order->user_id);

                                $sms_approve_status = $this->settings->sms_approve_status;
                                if(!empty($sms_approve_status)) {
                                    $template = $this->sms->get_template($this->sms::AUTO_APPROVE_TEMPLATE_NOW, $user->site_id);
                                    $text_message = strtr($template->template, [
                                        '{{firstname}}' => $user->firstname,
                                        '{{amount}}' => $order->approve_amount ?: $order->amount,
                                    ]);

                                    $text = $text_message;
                                    $resp = $status = $this->smssender->send_sms($user->phone_mobile, $text, $user->site_id);
                                    $this->sms->add_message(
                                        [
                                            'user_id' => $user->id,
                                            'order_id' => $order->id,
                                            'phone' => $user->phone_mobile,
                                            'message' => $text_message,
                                            'created' => date('Y-m-d H:i:s'),
                                            'send_status' => $resp[1],
                                            'delivery_status' => '',
                                            'send_id' => $resp[0],
                                            'type' => $this->smssender::TYPE_AUTO_APPROVE_ORDER,
                                        ]
                                    );

                                    if($status){
                                        $this->db->query("INSERT INTO sms_log SET phone='".$user->phone_mobile."', status='".$status[1]."', dates='".date("Y-m-d H:i:s")."', sms_id='".$status[0]."'");
                                    }
                                }
                            }
                            else
                            {
                                $this->log_autoretry($order->id,$system_manager->id);
                                $this->orders->update_order($order->id, array('autoretry' => 0));
                            }
                        }
                        else
                        {
                            $this->log_autoretry($order->id,$system_manager->id);
                            $this->orders->update_order($order->id, array('autoretry' => 0));
                        }
                    }
                    else
                    {
                        if (strtotime($order->date) < (time() - 600))
                        {
                            $this->log_autoretry($order->id,$system_manager->id);
                            $this->orders->update_order($order->id, array('autoretry' => 0));
                        }
                    }
                }
                elseif (strtotime($order->date) < (time() - 600))
                {
                    $this->log_autoretry($order->id,$system_manager->id);
                    $this->orders->update_order($order->id, array('autoretry' => 0));
                }
            }

        }
    }

    /**
     * Проверяем, можно ли оставить autoretry = 1 и одобрить автоповтор или
     * нужно сбросить флаг autoretry и чтобы заявка ушла на верификатора
     *
     * Условия, чтобы оставить autoretry = 1 и одобрить автоповтор
     *
     * 1. Есть результат по смене организации
     * 2. Заявка в статусе Новая
     * 3. Проверяем отчеты, если нужно согласно настройкам
     *
     * @return bool|null null - пропустить заявку, true/false - результат проверки
     */
    private function checkCanApproveAutoretry(stdClass $order): ?bool
    {
        $orgSwitchResult = $this->order_data->read((int)$order->id, $this->order_data::ORDER_ORG_SWITCH_RESULT);

        // 1. Если результата по смене организации еще нет
        if (empty($orgSwitchResult)) {

            $diffSeconds = time() - strtotime($order->date);
            if ($diffSeconds > 300) {
                $this->logging(__METHOD__, '', 'Таймаут ожидания результата переключения организации истек, сбрасываем флаг autoretry', ['order_id' => $order->id], self::LOG_FILE);

                return false;
            }

            return null;
        }

        $order = $this->orders->get_order((int)$order->id);

        // 2. Если заявка не найдена или НЕ в статусе Новая (вероятно, уже отказали по заявке)
        if (empty($order) || (int)$order->status !== $this->orders::ORDER_STATUS_CRM_NEW) {
            $this->logging(__METHOD__, '', 'Заявка не в статусе новая, сбрасываем флаг autoretry', ['order' => $order], self::LOG_FILE);

            return false;
        }

        // 3. Проверяем отчеты, если нужно согласно настройкам
        if ($this->report->needCheckReports((int)$order->id)) {
            $reportScorings = $this->getReportScorings($order);
            $lastReportScoring = $reportScorings[0] ?? null;

            // Если скоринг проверки отчетов не был добавлен, то добавляем
            if (empty($lastReportScoring)) {
                $this->addReportScoring($order);
                $this->logging(__METHOD__, '', 'Скоринг проверки ССП и КИ отчетов не найден. Скоринг добавлен. Пропуск заявки', ['order_id' => $order->id], self::LOG_FILE);
                return null;
            }

            $isScoringTimeOut = $this->report->isScoringTimeOut($lastReportScoring);

            // Если прошло меньше 15 минут и скоринг еще не завершен, то пропускаем заявку
            if (
                !$isScoringTimeOut &&
                in_array((int)$lastReportScoring->status, [$this->scorings::STATUS_NEW, $this->scorings::STATUS_PROCESS, $this->scorings::STATUS_WAIT])
            ) {
                $this->logging(__METHOD__, '', 'Скоринг проверки ССП и КИ отчетов еше не завершен. Ожидание завершения скоринга. Пропуск заявки', ['order_id' => $order->id], self::LOG_FILE);
                return null;
            }

            return !empty($lastReportScoring->success);
        }

        return true;
    }

    private function getReportScorings(stdClass $order): array
    {
        $reportScorings = $this->scorings->get_scorings([
            'order_id' => $order->id,
            'type' => $this->scorings::TYPE_REPORT,
            'sort' => 'id_date_desc'
        ]);

        if (empty($reportScorings)) {
            return [];
        }

        return $reportScorings;
    }
    private function addReportScoring(stdClass $order)
    {
        $this->scorings->add_scoring([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'status' => $this->scorings::STATUS_NEW,
            'created' => date('Y-m-d H:i:s'),
            'type' => $this->scorings::TYPE_REPORT,
        ]);
    }
        
}

new AutoretryCron();