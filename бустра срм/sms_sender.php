<?
class Sender{
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
if(isset($_POST['sms']) && $_POST['sms'] == 'TRUE'){
	$SR = new Sender;
	print_r(json_encode($SR->send($_POST['phone'], $_POST['message'])));
}else{
	echo 'POST data is empty';
}
?>