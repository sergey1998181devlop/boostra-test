<?php

require_once 'Simpla.php';

class Nbki_report extends Simpla
{
    private $username = 'YQ01SS000000';
    private $authorization_code = 'rwpGK6WW';
    private $api_url = 'http://185.182.111.110:9009/api/';    
    
    public function get_order_for_nbki($id)
    {
        $this->db->query("
            SELECT 
                o.order_uid,
                o.amount,
                o.period,
                o.percent,
                o.confirm_date,
                p.complete_date AS p2pcredit_date
            FROM s_orders as o
            LEFT JOIN b2p_p2pcredits as p
            ON p.order_id = o.id AND p.status = 'APPROVED'
            WHERE o.id = ?
        ", (int)$id);
        return $this->db->result();
    }

    public function get_user_for_nbki($id)
    {
        $this->db->query("
            SELECT 
                lastname,
                firstname,
                patronymic,
                birth,
                birth_place,
                passport_serial,
                passport_date,
                passport_issued,
                subdivision_code,
                inn                
            FROM s_users
            WHERE id = ?
        ", (int)$id);
        return $this->db->result();
    }

    public function reset_fail_pay_items()
    {
        $this->db->query("
            UPDATE b2p_payments
            SET nbki_ready = 0
            WHERE nbki_ready = 3
        ");
    }
    
    public function get_report_items($type = [])
    {
        if (empty($type)) {
            $type_filter = $this->db->placehold('AND type IN ("PAY", "P2P")');
        } else {
            $type_filter = $this->db->placehold('AND type IN (?@)', (array)$type);
        }
        
        $this->db->query("
            SELECT * 
            FROM __nbki_items
            WHERE report_id = 0
            AND operation_date < ?
            $type_filter
            ORDER BY operation_date ASC
        ", date('Y-m-d'));
        return $this->db->results();
    } 
        
    public function add_item($item)
    {
		$query = $this->db->placehold("
            INSERT INTO __nbki_items SET ?%
        ", (array)$item);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }

    public function update_item($id, $item)
    {
		$query = $this->db->placehold("
            UPDATE __nbki_items SET ?% WHERE id = ?
        ", (array)$item, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_item($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __nbki_items WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
    
    public function send_items($items)
    {
        $wrapper = $this->wrap($items);
        $resp = $this->send($wrapper, 'v2/report/');
        return $resp;
    }

    private function wrap($items)
    {
        $wrapper = new StdClass();
        $wrapper->MANY_EVENTS = [];

        $HEADER = new StdClass();
        $HEADER->username = $this->username;
        $HEADER->password = $this->authorization_code;
        $HEADER->creation_date = date('d.m.Y');

        $wrapper->HEADER = $HEADER;
        
        foreach ($items as $item) {
            $wrapper->MANY_EVENTS[] = $this->prepare_item($item);
        }
        
        return $wrapper;
    }
    
    private function prepare_item($item)
    {
        $data = [];
        
        $data['GROUPHEADER'] = $this->block_GROUPHEADER($item);
        $data['C1_NAME'] = $this->block_C1_NAME($item);
        $data['C2_PREVNAME'] = $this->block_C2_PREVNAME($item);
        $data['C3_BIRTH'] = $this->block_C3_BIRTH($item);
        $data['C4_ID'] = $this->block_C4_ID($item);
        $data['C4_ID'] = $this->block_C4_ID($item);
        $data['C5_PREVID'] = $this->block_C5_PREVID($item);
        $data['C6_REGNUM'] = $this->block_C6_REGNUM($item);
        $data['C17_UID'] = $this->block_C17_UID($item);
        $data['C18_TRADE'] = $this->block_C18_TRADE($item);
        $data['C19_ACCOUNTAMT'] = $this->block_C19_ACCOUNTAMT($item);
        $data['C21_PAYMTCONDITION'] = $this->block_C21_PAYMTCONDITION($item);
        $data['C22_OVERALLVAL'] = $this->block_C22_OVERALLVAL($item);
        $data['C24_FUNDDATE'] = $this->block_C24_FUNDDATE($item);
        $data['C25_ARREAR'] = $this->block_C25_ARREAR($item);
        $data['C26_DUEARREAR'] = $this->block_C26_DUEARREAR($item);
        $data['C27_PASTDUEARREAR'] = $this->block_C27_PASTDUEARREAR($item);
        $data['C28_PAYMT'] = $this->block_C28_PAYMT($item);
        $data['C29_MONTHAVERPAYMT'] = $this->block_C29_MONTHAVERPAYMT($item);
        $data['C38_OBLIGTERMINATION'] = $this->block_C38_OBLIGTERMINATION($item);
        $data['C54_OBLIGACCOUNT'] = $this->block_C54_OBLIGACCOUNT($item);
        $data['C56_OBLIGPARTTAKE'] = $this->block_C56_OBLIGPARTTAKE($item);
        
        
        return array_filter($data);
    }
    
    private function block_GROUPHEADER($item)
    {
        $GROUPHEADER = new StdClass();
        $GROUPHEADER->operation_code = "B";
        $GROUPHEADER->event_date = date('d.m.Y', strtotime($item->operation_date));
        
        if ($item->type == 'P2P') {
            // выдача
            $GROUPHEADER->event_number = "2.2";
        } elseif ($item->type == 'PAY') {
            if (!empty($item->onec_data->Закрыт)) {
                // закрытие
                $GROUPHEADER->event_number = "2.5";
            } else {
                // оплата
                $GROUPHEADER->event_number = "2.3";
            }
        }
        
        return $GROUPHEADER;        
    }
    
    private function block_C1_NAME($item)
    {
        $C1_NAME = [
            'surname' => $this->clearing($item->user->lastname),
            'name' => $this->clearing($item->user->firstname),
        ];
        if (!empty($item->user->patronymic)) {
            $C1_NAME['patronymic'] = $this->clearing($item->user->patronymic);
        }

        return $C1_NAME;        
    }
    
    private function block_C2_PREVNAME($item)
    {
        $C2_PREVNAME = [
            'is_prev_name' => '0',
        ];
        return $C2_PREVNAME;
    }
    
    private function block_C3_BIRTH($item)
    {
        $C3_BIRTH = [
            'birth_date' => $item->user->birth,
            'country_code' => '643',
            'birth_place' => $this->clearing($item->user->birth_place),
        ];
        return $C3_BIRTH;
    }    
    
    private function block_C4_ID($item)
    {
        $passport_serial = str_replace([' ','-'], '', $item->user->passport_serial);
        $passport_series = substr($passport_serial, 0, 4);
        $passport_number = substr($passport_serial, 4, 6);

        $C4_ID = [
            'country_code' => '643',
            'document_code' => '21',
            'series_number' => $passport_series,
            'document_number' => $passport_number,
            'issue_date' => date('d.m.Y', strtotime($item->user->passport_date)),
            'issued_by_division' => $this->clearing($item->user->passport_issued),
            'division_code' => $item->user->subdivision_code,
        ];
        return $C4_ID;
        
    }

    private function block_C5_PREVID($item)
    {
        $C5_PREVID = [
            'is_prev_document' => '0',
        ];
        return $C5_PREVID;
    }
    
    private function block_C6_REGNUM($item)
    {
        $C6_REGNUM = [
            'taxpayer_code' => '1',
            'taxpayer_number' => empty($item->user->inn) ? '000000000000' : $item->user->inn,
            'is_special_tax' => 0,
        ];
        return $C6_REGNUM;        
    }
    
    private function block_C17_UID($item)
    {
        $C17_UID = [
            'uuid' => $item->order->order_uid
        ];
        return $C17_UID;
    }
    
    private function block_C18_TRADE($item)
    {        
        if ($item->type == 'P2P') {
            $inssuance_date = date('d.m.Y', strtotime($item->operation_date));
            $return_date = date('d.m.Y', strtotime($item->operation_date) + $item->order->period * 86400);
        } else {
            $inssuance_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));
            $return_date = $item->onec_data->СледующаяПлановаяДата;
        }
        
        $C18_TRADE = [
            'owner_indicator_code' => '1',
            'trade_type_code' => '1',
            'load_kind_code' => '99',
            'account_type_code' => '14',
            'is_consumer_loan' => '1',
            'has_card' => '1',
            'is_novation' => '0',
            'is_money_source' => '1',
            'is_money_borrower' => '1',
            'lender_type_code' => 2,
            'opened_date' => $inssuance_date,
            'close_date' => $return_date,
        ];
        return $C18_TRADE;
    }

    private function block_C19_ACCOUNTAMT($item)
    {
        $C19_ACCOUNTAMT = [
            'credit_limit' => $this->format_amount($item->order->amount),
            'currency_code' => 'RUB',
        ];
        return $C19_ACCOUNTAMT;
    }

    private function block_C21_PAYMTCONDITION($item)
    {
        if ($item->type == 'P2P') {
            $terms_amount_date = date('d.m.Y', strtotime($item->operation_date) + $item->order->period * 86400);
            $principal_terms_amount = $this->format_amount($item->order->amount);
            $interest_terms_amount = $this->format_amount($item->order->amount * $item->order->percent / 100 * $item->order->period);
        } else {
            if (!empty($item->onec_data->Закрыт)) {
                // Если по показателю 21.1, а также по показателю 21.3 указано значение «0,00», иные показатели блока 21 не заполняются.
                return [
                    'principal_terms_amount' => $this->format_amount(0),
                    'interest_terms_amount' => $this->format_amount(0),
                ];
            } else {
                // TODO: 
                $principal_terms_amount = $this->format_amount($item->onec_data->ОстатокОД);
                $interest_terms_amount = $this->format_amount($item->onec_data->ОстатокОД * $item->order->percent / 100 * 14);                
            }
            $terms_amount_date = $item->onec_data->СледующаяПлановаяДата;
        }
                
        $C21_PAYMTCONDITION = [
            'principal_terms_amount' => $principal_terms_amount, // Сумма ближайшего следующего платежа по основному долгу.
            'principal_terms_amount_date' => $terms_amount_date, // Дата ближайшего следующего платежа по основному долгу
            'interest_terms_amount' => $interest_terms_amount, // Сумма ближайшего следующего платежа по процентам
            'interest_terms_amount_date' => $terms_amount_date, // Дата ближайшего следующего платежа по процентам
            'terms_frequency_code' => '3',
            'interest_payment_due_date' => $terms_amount_date, // Дата окончания срока уплаты процентов
        ];
        return $C21_PAYMTCONDITION;
    }

    private function block_C22_OVERALLVAL($item)
    {
        $total_credit_amount_interest = $this->format_amount($item->order->percent * 365);
        $total_credit_amount_monetary = $this->format_amount(($item->order->amount * $item->order->percent / 100 * $item->order->period));
        
        if ($item->type == 'P2P') {
            $total_credit_amount_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));            
        } else {
            $total_credit_amount_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));
        }
        
        $C22_OVERALLVAL = [
            'total_credit_amount_interest' => $total_credit_amount_interest, // Полная стоимость кредита (займа) в процентах годовых.
            'total_credit_amount_monetary' => $total_credit_amount_monetary, // Полная стоимость кредита (займа) в денежном выражении.
            'total_credit_amount_date' => $total_credit_amount_date, // Дата расчета полной стоимости кредита (займа)
        ];
        return $C22_OVERALLVAL;
    }

    private function block_C24_FUNDDATE($item)
    {
        if ($item->type == 'P2P') {
            $funding_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));            
        } else {
            $funding_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));
        }
        
        $C24_FUNDDATE = [
            'funding_date' => $funding_date,
        ];
        return $C24_FUNDDATE;
    }

    private function block_C25_ARREAR($item)
    {
        if ($item->type == 'P2P') {
            $amount_outstanding = $this->format_amount($item->order->amount);
            $principal_amount_outstanding = $this->format_amount($item->order->amount);
            $interest_amount_outstanding = $this->format_amount(0);
            $calculation_date = date('d.m.Y', strtotime($item->operation_date));
        } else {
            if (!empty($item->onec_data->Закрыт)) {
                return [
                    'has_arrear' => 0,
                ];
            }

            $amount_outstanding = $this->format_amount($item->onec_data->ОстатокОД + $item->onec_data->ОстатокПроцентов);
            $principal_amount_outstanding = $this->format_amount($item->onec_data->ОстатокОД);
            $interest_amount_outstanding = $this->format_amount($item->onec_data->ОстатокПроцентов);
            $calculation_date = date('d.m.Y', strtotime($item->operation_date));
        }
        
        $C25_ARREAR = [
            'has_arrear' => '1',
            'start_amount_outstanding' => $this->format_amount($item->order->amount),
            'is_last_payment_due' => '1',
            'amount_outstanding' => $amount_outstanding,
            'principal_amount_outstanding' => $principal_amount_outstanding,
            'interest_amount_outstanding' => $interest_amount_outstanding,
            'other_amount_outstanding' => $this->format_amount('0'),
            'calculation_date' => $calculation_date,
        ];
        return $C25_ARREAR;        
    }
    
    private function block_C26_DUEARREAR($item)
    {
        if ($item->type == 'P2P') {
            $start_date = date('d.m.Y', strtotime($item->operation_date));
            $amount_outstanding = $this->format_amount($item->order->amount);
            $principal_amount_outstanding = $this->format_amount($item->order->amount);
            $interest_amount_outstanding = $this->format_amount(0);
            $calculation_date = date('d.m.Y', strtotime($item->operation_date));
        } else {

            if (!empty($item->onec_data->Закрыт)) {
                return [
                    'amount_outstanding' => $this->format_amount(0),
                ];
            }

            $start_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));
            $amount_outstanding = $this->format_amount($item->onec_data->ОстатокОД + $item->onec_data->ОстатокПроцентов);
            $principal_amount_outstanding = $this->format_amount($item->onec_data->ОстатокОД);
            $interest_amount_outstanding = $this->format_amount($item->onec_data->ОстатокПроцентов);
            $calculation_date = date('d.m.Y', strtotime($item->operation_date));
        }

        $C26_DUEARREAR = [
            'start_date' => $start_date,
            'amount_outstanding' => $amount_outstanding,
            'principal_amount_outstanding' => $principal_amount_outstanding,
            'interest_amount_outstanding' => $interest_amount_outstanding,
            'other_amount_outstanding' => $this->format_amount(0),
            'calculation_date' => $calculation_date,
        ];
        if ($item->type == 'PAY') {
            $C26_DUEARREAR['is_last_payment_due'] = 1;
        }
        return $C26_DUEARREAR;        
    }
    
    private function block_C27_PASTDUEARREAR($item)
    {
        $calculation_date = date('d.m.Y', strtotime($item->operation_date));

        $C27_PASTDUEARREAR = [
            'amount_outstanding' => $this->format_amount(0),
        ];
        if (empty($item->onec_data->Закрыт)) {
            $C27_PASTDUEARREAR['calculation_date'] = $calculation_date;
        }

        return $C27_PASTDUEARREAR;
    }
    
    private function block_C28_PAYMT($item)
    {
        if ($item->type == 'P2P') {
            return [
                'payment_amount' => $this->format_amount(0),
                'amount_keep_code' => '3',
                'terms_due_code' => '1',
                'days_past_due' => '0',
            ];
        } else {
            $payment_date = date('d.m.Y', strtotime($item->operation_date));
            
        }
        
        $C28_PAYMT = [
            'payment_date' => $payment_date,
            'payment_amount' => $this->format_amount(min($item->onec_data->ОплатаОД + $item->onec_data->ОплатаПроцентов, $item->onec_data->ВсегоОплатаОД + $item->onec_data->ВсегоОплатаПроцентов)),
            'principal_payment_amount' => $this->format_amount(min($item->onec_data->ОплатаОД, $item->onec_data->ВсегоОплатаОД)),
            'interest_payment_amount' => $this->format_amount(min($item->onec_data->ОплатаПроцентов, $item->onec_data->ВсегоОплатаПроцентов)),
            'other_payment_amount' => $this->format_amount(0),
            'total_amount' => $this->format_amount($item->onec_data->ВсегоОплатаОД + $item->onec_data->ВсегоОплатаПроцентов),
            'principal_total_amount' => $this->format_amount($item->onec_data->ВсегоОплатаОД),
            'interest_total_amount' => $this->format_amount($item->onec_data->ВсегоОплатаПроцентов),
            'other_total_amount' => $this->format_amount(0),
            'amount_keep_code' => 1, //TODO: сделать расчет просроченный платеж или нет
            'terms_due_code' => 2, //TODO: сделать расчет просроченный платеж или нет
            'days_past_due' => 0, //TODO: сделать расчет просроченный платеж или нет
        ];
        return $C28_PAYMT;        
    }
    
    public function block_C29_MONTHAVERPAYMT($item)
    {
        if ($item->type == 'P2P') {
            $calculation_date = date('d.m.Y', strtotime($item->operation_date));
        } else {
            if (!empty($item->onec_data->Закрыт)) {
                return [
                    'average_payment_amount' => 0,
                    'calculation_date' => date('d.m.Y', strtotime($item->operation_date)),
                ];
            }

            $calculation_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));
        }

        $C29_MONTHAVERPAYMT = [
            'average_payment_amount' => round($item->order->amount * $item->order->percent / 100 * $item->order->period),
            'calculation_date' => $calculation_date,
        ];
        return $C29_MONTHAVERPAYMT;
    }
    
    public function block_C38_OBLIGTERMINATION($item)
    {
        if ($item->type == 'PAY' && !empty($item->onec_data->Закрыт)) {
            $C38_OBLIGTERMINATION = [
                'loan_indicator' => 1,
                'loan_indicator_date' => date('d.m.Y', strtotime($item->operation_date)),
            ];
            return $C38_OBLIGTERMINATION;
        }
    }
        
    public function block_C54_OBLIGACCOUNT($item)
    {
        $C54_OBLIGACCOUNT = [
            'has_obligation' => 1,
            'interest_rate' => $this->format_amount($item->order->percent * 365),
        ];
        return $C54_OBLIGACCOUNT;        
    }

    public function block_C56_OBLIGPARTTAKE($item)
    {
        if ($item->type == 'P2P') {
            $funding_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));
        } else {
            $funding_date = date('d.m.Y', strtotime($item->order->p2pcredit_date));
        }
        $loan_indicator = intval($item->type == 'PAY' && !empty($item->onec_data->Закрыт));
                
        $C56_OBLIGPARTTAKE = [
            'flag_indicator_code' => '1',
            'approved_loan_type_code' => '99',
            'agreement_number' => $item->order->order_uid,
            'funding_date' => $funding_date,
            'default_flag' => '0',
            'loan_indicator' => $loan_indicator,
        ];
        return $C56_OBLIGPARTTAKE;
    }

    private function format_amount($amount)
    {
        return str_replace('.', ',', sprintf("%01.2f", $amount));
    }
    
    private function send($data, $url = 'v2/report/')
    {
        $url = $this->api_url.$url;
        
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        
        $json_res = curl_exec($curl);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        $res = json_decode($json_res);

        $this->soap1c->logging(__METHOD__, $url, $data, $res, 'nbki_report.txt');
        return $res;
    }


	public function get_day_report($date)
	{
		$query = $this->db->placehold("
            SELECT COUNT(id) AS report_count
            FROM __nbki_reports
            WHERE DATE(created) = ?
        ", (string)$date);
        $this->db->query($query);
        $result = $this->db->result('report_count');
	
        return $result;
    }
    
	public function get_report($id)
	{
		$query = $this->db->placehold("
            SELECT * 
            FROM __nbki_reports
            WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
        $result = $this->db->result();
	
        return $result;
    }
    
	public function get_reports($filter = array())
	{
		$id_filter = '';
        $keyword_filter = '';
        $limit = 1000;
		$page = 1;
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
        
		if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
        
		if(isset($filter['limit']))
			$limit = max(1, intval($filter['limit']));

		if(isset($filter['page']))
			$page = max(1, intval($filter['page']));
            
        $sql_limit = $this->db->placehold(' LIMIT ?, ? ', ($page-1)*$limit, $limit);

        $query = $this->db->placehold("
            SELECT * 
            FROM __nbki_reports
            WHERE 1
                $id_filter
				$keyword_filter
            ORDER BY id DESC 
            $sql_limit
        ");
        $this->db->query($query);
        $results = $this->db->results();
        
        return $results;
	}
    
	public function count_reports($filter = array())
	{
        $id_filter = '';
        $keyword_filter = '';
        
        if (!empty($filter['id']))
            $id_filter = $this->db->placehold("AND id IN (?@)", array_map('intval', (array)$filter['id']));
		
        if(isset($filter['keyword']))
		{
			$keywords = explode(' ', $filter['keyword']);
			foreach($keywords as $keyword)
				$keyword_filter .= $this->db->placehold('AND (name LIKE "%'.$this->db->escape(trim($keyword)).'%" )');
		}
                
		$query = $this->db->placehold("
            SELECT COUNT(id) AS count
            FROM __nbki_reports
            WHERE 1
                $id_filter
                $keyword_filter
        ");
        $this->db->query($query);
        $count = $this->db->result('count');
	
        return $count;
    }
    
    public function add_report($nbki_report)
    {
		$query = $this->db->placehold("
            INSERT INTO __nbki_reports SET ?%
        ", (array)$nbki_report);
        $this->db->query($query);
        $id = $this->db->insert_id();
        
        return $id;
    }
    
    public function update_report($id, $nbki_report)
    {
		$query = $this->db->placehold("
            UPDATE __nbki_reports SET ?% WHERE id = ?
        ", (array)$nbki_report, (int)$id);
        $this->db->query($query);
        
        return $id;
    }
    
    public function delete_report($id)
    {
		$query = $this->db->placehold("
            DELETE FROM __nbki_reports WHERE id = ?
        ", (int)$id);
        $this->db->query($query);
    }
    
    public function clearing($string)
    {
        $replace = [
            '   ' => ' ',
            '  ' => ' ',
            ' -' => '-',
            '- ' => '-',
            "\t" => '',
            '   ' => '',
            '&quot;' => '',
            '"' => '',
            '..' => '.',
            '...' => '.',
            
        ];
        
        $string = str_replace(array_keys($replace), array_values($replace), $string);
        $string = trim($string);
        
        return $string;
    }

}