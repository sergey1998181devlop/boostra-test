<?php
/**
 * Extension for Simpla
 * @author spy45242
 */
require_once 'Simpla.php';
include_once('addons/sms_new.php');

class Smssender extends Simpla{

    /**
     * Автоматическая рассылка для одобренных заявок
     */
    public const TYPE_APPROVE_ORDER = 'approve_retry';

    /**
     * Создание автоодобрения или одобрение заявки
     */
    public const TYPE_AUTO_APPROVE_ORDER = 'auto_approve';

    /** @var string АСП-код авто-одобренной заявки */
    public const TYPE_ASP = 'asp';

    public const TYPE_MARATORIUM = 'moratorium';

    /**
     * СМС клиентам с мотивацией на закрытие
     */
    public const TYPE_MOTIVATION_CLOSED_ORDER = 'motivation_order';

    public const TYPE_LIKEZAIM = 'likezaim';

    /**
     * Шаблон смс после одобрения скористы для нового клиента, который не закончил регистрацию
     */
    public const TYPE_AFTER_APPROVE_SCORISTA = 'new_client_after_scorista';

    /**
     * Созданная кроном финлаб заявка. Создалась не сразу
     */
    public const TYPE_RESTORE_FINLAB = 'restore_finlab';

    public function send_sms($phone, $message, $originator, $rus=0)
    {
        return send_sms_new($this ,$phone, $message, $originator, 0);
    }


	public function send_sms_new($phone, $message, $originator, $rus=1, $udh=''){
        $organization_from   = $this->config->SMSC_PROVIDER[$originator];
		$smstraffic_login    = $this->settings->apikeys['smstraffic']['login'];
		$smstraffic_password = $this->settings->apikeys['smstraffic']['password'];
		$max_parts           = 3;
		$host                = "api.smstraffic.ru";
		$failover_host       = "api2.smstraffic.ru";
		$path                = "/multi.php";
		$params              = "login=".urlencode($smstraffic_login) . "&password=".urlencode($smstraffic_password) . "&want_sms_ids=1&phones=$phone&message=".urlencode($message) . "&max_parts=$max_parts&rus=$rus&originator=".urlencode($organization_from);
		if ($udh){
			$params         .= "&udh=".urlencode($udh);
		}
		$response            = $this->httpPost_sms($host, $path, $params);
		if($response == null){
			$response = $this->httpPost_sms($failover_host, $path, $params);
			if ($response == null)
				return array(0, "failed to send sms");
		}
		if(strpos($response, '<result>OK</result>')){
			if (preg_match('|<sms_id>(\d+)</sms_id>|s', $response, $regs)){
				$sms_id = $regs[1];
				return array($sms_id, 'OK');
			}else{
				return array(-1, 'failed to find sms_id');
			}
		}elseif(preg_match('|<description>(.+?)</description>|s', $response, $regs)){
			$error = $regs[1];
			return array(0, $error);
		}else{
			return array(0, 'failed to send sms '.$response);
		}
	}
	
	public function httpPost_sms($host, $path, $params){
		$params_len=strlen($params);
		$fp = @fsockopen($host, 80);
		if (!$fp)
			return null;
		fputs($fp, "POST $path HTTP/1.0\nHost: $host\nContent-Type: application/x-www-form-urlencoded\nUser-Agent: sms.php class 1.0 (fsockopen)\nContent-Length: $params_len\nConnection: Close\n\n$params\n");
		$response = fread($fp, 8000);
		fclose($fp);
		if (preg_match('|^HTTP/1\.[01] (\d\d\d)|', $response, $regs))
			$http_result_code=$regs[1];
		return ($http_result_code==200) ? $response : null;
	}

    /**
     * Отправка смс об Одобрении займа
     * @param $user
     * @param $order_id
     * @param $approve_amount
     * @return void
     */
    public function sendApprovedSms($user, $order_id, $approve_amount)
    {
        $sms_approve_status = $this->settings->sms_approve_status;
        if(!empty($sms_approve_status)) {
            $template = $this->sms->get_template($this->sms::AUTO_APPROVE_TEMPLATE_NOW, $user->site_id);
            $text_message = strtr($template->template, [
                '{{firstname}}' => $user->firstname,
                '{{amount}}' => $approve_amount,
            ]);

            $text = $text_message;
            $resp = $this->send_sms($user->phone_mobile, $text, $user->site_id);
            $this->sms->add_message(
                [
                    'user_id' => $user->id,
                    'order_id' => $order_id,
                    'phone' => $user->phone_mobile,
                    'message' => $text_message,
                    'created' => date('Y-m-d H:i:s'),
                    'send_status' => $resp[1],
                    'delivery_status' => '',
                    'send_id' => $resp[0],
                    'type' => self::TYPE_AUTO_APPROVE_ORDER,
                ]
            );
        }
    }
}