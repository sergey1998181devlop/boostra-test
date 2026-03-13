<?php

require_once('Simpla.php');

class Installments extends Simpla
{
    /**
     * Интервал в днях после выдачи займа когда становятся доступны заявление на ПДП|ЧДП
     */
    const ACCEPT_INTERVAL = 14;    
    
    const MIN_APPROVE_BALL = 500;
    
    public $base_period = 84;
    
    /**
     * Installments::get_loan_type()
     * Возвращает тип займа в зависимости от срока займа
     * 
     * @param int $period
     * @return string
     */
    public function get_loan_type($period)
    {
        if ($period > 30) {
            return 'IL';
        } else {
            return 'PDL';
        }
    }
    
    /**
     * Installments::get_installments_enabled()
     * Возвращает активны ли инстоллменты
     * 
     * @return bool
     */
    public function get_installments_enabled($user_id = null)
    {
        $installment_test_users = array_map('trim', explode(',', $this->settings->installment_test_users));
        return $this->settings->installments_enabled || in_array($user_id, $installment_test_users);
    }

    public function update_empty_installment($update)
    {
        if (isset($update['approve_amount'])) {
            $update['approve_amount'] = min($update['approve_amount'], $this->orders::PDL_MAX_AMOUNT);
        }
        if (isset($update['amount'])) {
            $update['amount'] = min($update['amount'], $this->orders::PDL_MAX_AMOUNT);
        }

        $update['period'] = $this->orders::PDL_MAX_PERIOD;
        $update['max_amount'] = 0;
        $update['min_period'] = 0;
        $update['max_period'] = 0;
        $update['loan_type'] = $this->orders::LOAN_TYPE_PDL;

        return $update;
    }

    private function check_status($order)
    {
        return empty($order->status)
            || $order->status == $this->orders::ORDER_STATUS_CRM_NEW
            || $order->status == $this->orders::ORDER_STATUS_CRM_APPROVED;
    }

    /**
     * Installments::check_installment()
        - только ПК
        - балл скористы 700 или более
        - суммы одобренного инстоллмента от 32000 до 39000
     * 
     * @param int $scorista_id
     * @return array
     */
    public function check_installment($scorista_id)
    {
        $update = [];

        $scorista = $this->scorings->get_scoring($scorista_id);

        // 1. Если нет скористы
        if (empty($scorista)) {
            return $update;
        }

        // 2. Если инстолменты отключены
        if (!$this->get_installments_enabled($scorista->user_id)) {
            return $update;
        }

        $order = $this->orders->get_order((int)$scorista->order_id);

        // 3. Если заявка не найдена
        if (empty($order)) {
            return $update;
        }

        // 4. Если неподходящий статус заявки
        if (!$this->check_status($order)) {
            return $update;
        }

        // 5. Если кросс-ордер
        if ($order->utm_source === $this->orders::UTM_SOURCE_CROSS_ORDER) {
            return $update;
        }

        // 6. Если заявка - ВКЛ
        $isRcl = (bool)$this->order_data->read((int)$order->order_id, $this->order_data::RCL_LOAN);
        if ($isRcl) {
            return $update;
        }

        $scorista->body = $this->scorings->get_body_by_type($scorista);

        // 7. Скориста не одобрила инстолмент
        if (!$this->check_approve($scorista, $order->have_close_credits)) {
            return $update;
        }

        $max_amount = min($this->orders::IL_MAX_AMOUNT, $scorista->body->additional->result2->additional->decisionSum);
        $decisionPeriod = $scorista->body->additional->result2->additional->decisionPeriod ?? 0;
        $max_period = max($this->orders::IL_MIN_PERIOD, $decisionPeriod);
        $update = [
            'amount' => $max_amount,
            'period' => $max_period,
            'max_amount' => $max_amount,
            'min_period' => $this->orders::IL_MIN_PERIOD,
            'max_period' => $max_period,
            'loan_type' => $this->orders::LOAN_TYPE_IL,
            'autoretry' => 0,
        ];
        $log_status = 'APPROVE';

        $this->logging('check_installment', $log_status, ['order_id'=>$scorista->order_id], $update, 'installment_3.txt');
        $this->save_changelog($order, $update);

        return $update;
    }
    
    private function check_approve($scorista, $pk) 
    {
        $min_amount = $pk ? $this->orders::IL_MIN_AMOUNT : $this->orders::IL_MIN_AMOUNT_NK;
        return isset($scorista->body->additional->result2)
            && $scorista->body->additional->summary->score >= self::MIN_APPROVE_BALL
            && $scorista->body->additional->result2->additional->decisionType == 'IL ON'
            && $scorista->body->additional->result2->additional->decisionSum >= $min_amount;
    }
    
    private function save_changelog($order, $update)
    {
        $system_manager = $this->managers->get_manager($this->managers::MANAGER_SYSTEM_ID);
        $old = [
            'amount' => $order->amount,
            'period' => $order->period,
            'max_amount' => $order->max_amount,
            'min_period' => $order->min_period,
            'max_period' => $order->max_period,
            'loan_type' => $order->loan_type,
            'autoretry' => $order->autoretry,
        ];
        $this->changelogs->add_changelog([
            'manager_id' => $system_manager->id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'installment',
            'old_values' => serialize($old),
            'new_values' => serialize($update),
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
        ]);        
    }
    
    /**
     * DEPRECATED
     * Installments::check_enabled()
     * Проверяет доступен ли клиенту калькулятор для подачи заявки с инстоллмент займами
     * 
     * @param int $user_id
     * @return 
     */
    public function check_segment($order)
    {
        if ($user = $this->users->get_user((int)$order->user_id)) {
            if ($axiscore = $this->scorings->get_last_type_scoring($this->scorings::TYPE_AXILINK_2, $user->id)) {
                if (!empty($user->loan_history)) {
                    $loan_close_count = 0;
                    $loan_total_days = 0;
                    foreach ($user->loan_history as $history_item) {
                        if (!empty($history_item->close_date)) {
                            $loan_close_count++;
                            
                            $origin = date_create(date('Y-m-d', strtotime($history_item->date)));
                            $target = date_create(date('Y-m-d', strtotime($history_item->close_date)));
                            $interval = date_diff($origin, $target);
                            $loan_total_days += $interval->format('%a');
                        }
                    }
                    $loan_avg_days = intval($loan_total_days / $loan_close_count);
        
                    $max_overdue = $this->soap->MaxOverdueByClient($user->UID);
                    $segments = $this->get_segments();
                    foreach ($segments as $seg) {
                        if ($loan_close_count >= $seg->min_close_count 
                            && $loan_avg_days >= $seg->history_avg_days
                            && $max_overdue <= $seg->history_max_expired 
                        ) {
                            if (!empty($axiscore->success)
                                && $seg->min_axi_score < $axiscore->scorista_ball 
                                && $seg->max_axi_score > $axiscore->scorista_ball
                            ) {
                                return $this->return_true([
                                    'segment' => $seg,
                                    'order' => $order
                                ]);
                            }
                        } 
                    }            
                    return $this->return_false([
                        'order' => $order->order_id,
                        'msg' => 'params not complete',
                        '$loan_close_count' => $loan_close_count,
                        '$loan_avg_days' => $loan_avg_days,
                        '$max_overdue' => $max_overdue,
                        'axiscore_ball' => $axiscore->scorista_ball
                    ]);
                } else {
                    return $this->return_false(['order' => $order->order_id,'msg' => 'empty loan_history']);
                }
            } else {
                return $this->return_false(['order' => $order->order_id,'msg' => 'axilink not found']);
            }
        } else {
            return $this->return_false(['order' => $order->order_id,'msg' => 'user not found']);                
        }
        
        return $this->return_false(['order' => $order->order_id,]);
    }
    
    private function return_false($params)
    {
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($params);echo '</pre><hr />';
        $this->logging('return_false', '', [], $params, 'installment2.txt');

        return false;
    }
    
    private function return_true($params)
    {
        $this->logging('return_true', '', [], $params, 'installment2.txt');
        return $params['segment'];
    }
    
    /**
     * Installments::check_accept()
     * Проверяет нужно ли клиенту подписывать заявление на ПДП|ЧДП
     * 
     * @param string $loan_date
     * @return int
     */
    public function check_accept($issuance_date)
    {
        $loan_date = date_create(date('Y-m-d', strtotime($issuance_date)));
        $today_date = date_create(date('Y-m-d'));
        $interval = date_diff($loan_date, $today_date);
        
        return intval($interval->format('%a') > self::ACCEPT_INTERVAL);        
    }

	/**
	 * Installments::get_segment()
	 * Получает сегмент для инстоллмент займов по id
     * 
	 * @param integer $id
	 * @return object
	 */
	public function get_segment($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __installment_segments
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	/**
	 * Installments::get_segments()
	 * Получает список всех сегментов для инстоллмент займов
     * 
	 * @param void
	 * @return array
	 */
	public function get_segments()
	{
        $query = $this->db->placehold("
            SELECT * 
            FROM __installment_segments
            ORDER BY id ASC
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
}