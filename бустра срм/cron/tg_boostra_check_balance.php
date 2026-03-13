<?php

require_once dirname(__FILE__).'/../api/Telegram.php';
require_once dirname(__FILE__).'/../api/Simpla.php';

class TgBot extends Simpla
{
    private $tg;

    private $last_day_reports = 8;
    private $current_day_reports = [14, 20];

    public function __construct()
    {
        parent::__construct();

        $this->tg = new Telegram(Telegram::BOOSTRA_1C_MONEY_BALANCE['token'], Telegram::BOOSTRA_1C_MONEY_BALANCE['chat_id']);

        $this->run();
    }

    private function run()
    {
        $this->send_balance();
        $this->send_report();
    }

    private function send_balance()
    {
        $msg = "Остаток на балансе\n \n";

        $msg .= $this->get_balance('RZS_SBP_ISSUANCE_LOAN', 'РЗС');
        $msg .= $this->get_balance('LORD_SBP_ISSUANCE_LOAN', 'ЛОРД');
        $msg .= $this->get_balance('FRIDA_PAY_CREDIT', 'Фрида');
        $msg .= $this->get_balance('MOREDENEG_SBP_ISSUANCE_LOAN', 'Море Денег');
        $msg .= $this->get_balance('BLAY_PAY_CREDIT', 'Блай');
        $msg .= $this->get_balance('FORINT_PAY_CREDIT', 'Форинт');
        $msg .= $this->get_balance('KVIKMANI_PAY_CREDIT', 'КвикМани');
        $msg .= $this->get_balance('FASTFINANCE_PAY_CREDIT', 'ФастФинанс');
        $msg .= $this->get_balance('RUBL_PAY_CREDIT', 'Рубль.Ру');

        $this->send($msg);
    }

    private function get_balance($sector_name, $org_name)
    {
        $response_b2p = $this->best2pay->getBalance($sector_name);
        $xml = simplexml_load_string($response_b2p);

        return $this->get_balance_msg($org_name, $xml->amount);
    }

    private function get_balance_sbp($sector_name, $org_name)
    {
        $response_b2p = $this->best2pay->getBalanceSbp($sector_name);
        $xml = simplexml_load_string($response_b2p);

        return $this->get_balance_msg($org_name, $xml->account->amount);
    }

    private function get_balance_msg($org_name, $amount)
    {
        return $org_name.": ".(isset($amount) ? $this->format_amount($amount/100) : "нет данных")." \n";
    }

    private function send_report()
    {
        $report_datetime = new DateTime();
        $current_hour = $report_datetime->format('H');
        if ($current_hour == $this->last_day_reports) {
            $report_datetime->sub(DateInterval::createFromDateString('1 day'));
            $msg = "Отчет за ".$report_datetime->format('d.m.Y')."\n";
            $msg .= $this->create_report($report_datetime->format('Y-m-d 00:00:00'), $report_datetime->format('Y-m-d 23:59:59'));
        } elseif (in_array($current_hour, $this->current_day_reports)) {
            $msg = "Отчет на ".$report_datetime->format('d.m.Y H:i')."\n";
            $msg .= $this->create_report($report_datetime->format('Y-m-d 00:00:00'), $report_datetime->format('Y-m-d H:i:s'));
        }

        if (!empty($msg)) {
            $this->send($msg);
        }
    }

    private function create_report_item($org_id, $org_name, $from, $to)
    {
        $msg = "\n$org_name \n";
        $msg .= $this->get_issuance($org_id, $from, $to);
        $msg .= $this->get_payments($org_id, $from, $to);
        $msg .= $this->get_issuance_services($org_id, $from, $to);
        $msg .= $this->get_payment_services($org_id, $from, $to);

        return $msg;
    }


    private function create_report($from, $to)
    {
        //Выдача, руб - это сколько выдано, вместе с допами, то есть сумма выданных договоров
        //Сбор общий - сумма оплат с допами
        //Допы выдача - сумма допов при выдаче
        //Допы оплата - сумма допов при оплатах

        $org_list = [
            $this->organizations::RZS_ID => 'РЗС',
            $this->organizations::LORD_ID => 'Лорд',
            $this->organizations::FRIDA_ID => 'Фрида',
            $this->organizations::FASTFINANCE_ID => 'ФастФинанс',
        ];
        $msg = '';

        foreach ($org_list as $org_id => $org_name) {
            $msg .= $this->create_report_item($org_id, $org_name, $from, $to);
        }

        $msg .= "\nЛайк \n";
        $msg .= $this->get_divelopment($from, $to);

        return $msg;
    }

    private function get_divelopment($from, $to)
    {
        $data = $this->get_divelopment_data($from, $to);

        if (empty($data)) {
            $msg = "Нет данных \n";
        } elseif (!empty($data['error'])) {
            $msg = "Ошибка: ".$data['error'];
        } else {
            $msg = "Выдача: ".$this->format_amount($data['issuance'])." \n";

            $total_sum = (int)$data['payments'];
            $recurrents_data = $this->get_recurrents_data('like', $from, $to);
            $total_sum += intval($recurrents_data['data']['sum']);
            $recurrents_msg = $this->format_recurrents_msg($recurrents_data);

            $msg .= "Сбор общий: ".$this->format_amount($total_sum)." \n".$recurrents_msg;
            $msg .= "Допы выдача: ".$this->format_amount($data['issuance_services'])." \n";
            $msg .= "Допы оплата: ".$this->format_amount($data['payment_services'])." \n";
        }
        return $msg;
    }

    private function get_divelopment_data($from, $to)
    {
        $url = 'https://crm.likezaim67.ru/services/TgReport.php';
        $link = $url.'?'.http_build_query([
                'password' => 'BSTR123987',
                'from' => $from,
                'to' => $to
            ]);

        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    private function get_issuance($organization_id, $from, $to)
    {
        $this->db->query("
            SELECT SUM(c.amount) AS total_sum 
            FROM s_contracts as c
            LEFT JOIN s_orders AS o
            ON o.id = c.order_id
            WHERE o.organization_id = ?
            AND o.status = 10
            AND c.issuance_date >= ?
            AND c.issuance_date <= ?
        ", $organization_id, $from, $to);
        $total_sum = $this->db->result('total_sum');

        return "Выдача: ".$this->format_amount($total_sum)." \n";
    }

    private function get_payments($organization_id, $from, $to)
    {
        $this->db->query("
            SELECT (SUM(p.amount) - SUM(p.insure) - SUM(m.amount) - SUM(t.amount)) AS total_sum
            FROM b2p_payments AS p
            LEFT JOIN s_multipolis AS m 
            ON m.payment_id = p.id AND m.status = 'SUCCESS'
            LEFT JOIN s_tv_medical_payments AS t 
            ON t.payment_id = p.id AND t.status = 'SUCCESS'
            WHERE p.reason_code = 1
            AND p.organization_id = ?
            AND p.operation_date >= ?
            AND p.operation_date <= ?
        ", $organization_id, $from, $to);
        $total_sum = $this->db->result('total_sum');

        $recurrents_msg = '';
        if ($organization_id == 6) {
            $recurrents_data = $this->get_recurrents_data('boostra', $from, $to);
            $total_sum += intval($recurrents_data['data']['sum']);
            $recurrents_msg = $this->format_recurrents_msg($recurrents_data);
        }

        $msg = "Сбор общий: ".$this->format_amount($total_sum)." \n".$recurrents_msg;

        return $msg;
    }

    private function get_issuance_services($organization_id, $from, $to)
    {
        $this->db->query("
            SELECT SUM(cd.amount) AS total_sum
            FROM s_credit_doctor_to_user AS cd
            LEFT JOIN s_orders AS o ON o.id = cd.order_id
            WHERE cd.status = 'SUCCESS'
            AND cd.is_penalty = 0
            AND o.organization_id = ?
            AND cd.date_added >= ?
            AND cd.date_added <= ?
        ", $organization_id, $from, $to);
        $cd_sum = $this->db->result('total_sum');

        $this->db->query("
            SELECT SUM(s.amount) AS total_sum
            FROM s_star_oracle AS s
            LEFT JOIN s_orders AS o ON o.id = s.order_id
            WHERE s.status = 'SUCCESS'
            AND o.organization_id = ?
            AND s.action_type = 'issuance'
            AND s.date_added >= ?
            AND s.date_added <= ?
        ", $organization_id, $from, $to);
        $so_sum += $this->db->result('total_sum');

        $total_sum = $cd_sum + $so_sum;

        return "Допы выдача: ".$this->format_amount($total_sum)." \n";
    }

    private function get_payment_services($organization_id, $from, $to)
    {
        $this->db->query("
            SELECT SUM(cd.amount) AS penalty_sum
            FROM s_credit_doctor_to_user AS cd
            LEFT JOIN s_orders AS o ON cd.order_id = o.id
            WHERE cd.status = 'SUCCESS'
            AND cd.is_penalty = 1
            AND o.organization_id = ?
            AND cd.date_added >= ?
            AND cd.date_added <= ?;
        ", $organization_id, $from, $to);
        $penalty_sum = $this->db->result('penalty_sum');

        $this->db->query("
            SELECT SUM(m.amount) AS mult_sum
            FROM s_multipolis AS m
            LEFT JOIN s_orders AS o ON m.order_id = o.id
            WHERE m.status = 'SUCCESS'
            AND o.organization_id = ?
            AND m.date_added >= ?
            AND m.date_added <= ?
        ", $organization_id, $from, $to);
        $mult_sum = $this->db->result('mult_sum');

        $this->db->query("
            SELECT SUM(t.amount) AS med_sum
            FROM s_tv_medical_payments AS t
            LEFT JOIN s_orders AS o ON t.order_id = o.id
            WHERE t.status = 'SUCCESS'
            AND o.organization_id = ?
            AND t.date_added >= ?
            AND t.date_added <= ?
        ", $organization_id, $from, $to);
        $med_sum = $this->db->result('med_sum');

        $this->db->query("
            SELECT SUM(s.amount) AS total_sum
            FROM s_star_oracle AS s
            LEFT JOIN s_orders AS o ON o.id = s.order_id
            WHERE s.status = 'SUCCESS'
            AND o.organization_id = ?
            AND (s.action_type = 'full_payment' OR s.action_type = 'partial_payment')
            AND s.date_added >= ?
            AND s.date_added <= ?
        ", $organization_id, $from, $to);
        $so_sum += $this->db->result('total_sum');

        $total_sum = $penalty_sum + $mult_sum + $med_sum + $so_sum;

        return "Допы оплата : ".$this->format_amount($total_sum)." \n";
    }

    private function get_recurrents_data($site, $from, $to)
    {
        switch ($site):

            case 'boostra':
                $url = 'https://mkkcollection.ru/api/recurrent/get_boostra_data';
                break;

            case 'like':
                $url = 'https://mkkcollection.ru/api/recurrent/get_like_data';
                break;

            default:
                throw new Exception('undefined site '.$site);

        endswitch;

        $link = $url.'?'.http_build_query([
                'password' => 'T9kj%27(cIDyd4OMAJVo6q1Z.~',
                'from' => $from,
                'to' => $to
            ]);

        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }

    private function format_recurrents_msg($recurrents_data)
    {
        if (empty($recurrents_data)) {
            $recurrents_msg = "Реккуренты: Нет данных \n";
        } elseif (!empty($recurrents_data['error'])) {
            $recurrents_msg = "Ошибка: ".$recurrents_data['error']." \n";
        } else {
            $recurrents_msg = "Реккуренты: ".$this->format_amount(intval($recurrents_data['data']['sum']))." \n";
        }
        return $recurrents_msg;
    }

    private function format_amount($amount)
    {
        return number_format($amount, 0, ',', ' ')." руб.";
    }

    private function send($msg)
    {
        echo '<pre>'.$msg.'</pre>';
        $this->tg->sendMessage($msg);
    }
}
new TgBot();