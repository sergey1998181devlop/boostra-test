<?

require_once dirname(__FILE__).'/../api/Simpla.php';
include('addons/sms.php');

class Prolongsms extends Simpla{
	
	public function get_prolog_users($tomorrow){
		$query = "
		SELECT 
			s_users.phone_mobile, 
            s_user_balance.payment_date, 
            s_user_balance.prolongation_amount,
            s_user_balance.zaim_number,
            s_user_balance.user_id
		FROM 
			`s_users`, `s_user_balance`
		WHERE
			s_users.id = s_user_balance.user_id
		AND
			payment_date like '%".$tomorrow."%'
		";

		$this->db->query($query);
		return $this->db->results();
	}
	
	public function enter_point(){
		$date     = date("Y-m-d");
		$datetmp  = str_replace('-', '/', $date);
		$tomorrow = date('Y-m-d',strtotime($datetmp . "+1 days"));
		
		$users    = $this->get_prolog_users($tomorrow);
		if($users){
			foreach($users as $user){
				$phone   = $user->phone_mobile;
				$prolong = $user->prolongation_amount;
				
                $limit = $this->soap->limit_sms($user->zaim_number);
                if ($limit == 1)
                {
                    $msg = 'Внесите минимальный платеж '.$prolong.'р '.$tomorrow.' boostra.ru. 88003333073, МКК ООО Бустра';
                    $status  = $this->send($phone, $msg);
    				if($status){
    					$query   = $this->db->query("INSERT INTO prolong_sms_log SET phone='".$phone."', status='".$status[1]."', dates='".date("Y-m-d H:i:s")."', sms_id='".$status[0]."'");
    					$this->db->query($query);

                        $this->sms->add_message(array(
                            'user_id' => $user->user_id,
                            'order_id' => 0,
                            'phone' => $user->phone_mobile,
                            'message' => $msg,
                            'created' => date('Y-m-d H:i:s'),
                            'send_status' => $status[1],
                            'delivery_status' => '',
                            'send_id' => $status[0],
                        ));
                        
                        $this->changelogs->add_changelog(array(
                            'manager_id' => 50,//System
                            'created' => date('Y-m-d H:i:s'),
                            'type' => 'send_sms',
                            'old_values' => '',
                            'new_values' => $msg,
                            'user_id' => $user->user_id,
                        ));
                        
                        $this->soap->send_number_of_sms($balance->zaim_number, $user->phone_mobile, $template->template);

    				}
                    
                }

			}
			
		}
	}
	
	public function send($phone, $message, $originator='Boostra.ru', $rus=1, $udh=''){
		$smstraffic_login    = "mkkbustra";
		$smstraffic_password = "Lxe9i89Gh";
		$max_parts           = 1;
		$host                = "api.smstraffic.ru";
		$failover_host       = "api2.smstraffic.ru";
		$path                = "/multi.php";
		$params              = "login=".urlencode($smstraffic_login) . "&password=".urlencode($smstraffic_password) . "&want_sms_ids=1&phones=$phone&message=".urlencode($message) . "&max_parts=$max_parts&rus=$rus&originator=".urlencode($originator);
		if ($udh){
			$params         .= "&udh=".urlencode($udh);
		}
		$response            = $this->httpPost($host, $path, $params);
		if($response == null){
			$response = $this->httpPost($failover_host, $path, $params);
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
	
	public function httpPost($host, $path, $params){
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
	
}

$PS = new Prolongsms;
$PS->enter_point();