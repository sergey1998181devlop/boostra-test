<?php

require_once 'View.php';

class VerificationView extends View{
	public function fetch(){
		$from      = $this->request->get('from') ? $this->request->get('from') : false;
		$to        = $this->request->get('to')  ? $this->request->get('to') : false;
		$ver_users = $this->verification->get_all();
		foreach($ver_users as $key=>$us){
			$ver_users[$key]->PROPS      = $this->verification->get_one_by_date($us->id, $from, $to);
			$ver_users[$key]->odob_count = $this->verification->get_status_two_orders($us->id, $from, $to);
			//time delta
			$del_t = $this->verification->get_average_time($us->id, $from, $to);
			$temp_delta = array();
			if($del_t && is_array($del_t)){
				foreach($del_t as $delta){
					$subs = explode(':',str_replace('-', '', $delta->delta));
					$temp_delta[] = $subs[0]*3600+$subs[1]*60+$subs[2];
				}
			}
			$t = ceil((int)((int)array_sum($temp_delta)/(int)count($temp_delta))/60);
			$ver_users[$key]->average_time = $t;
			//defaults
			$stats    = $this->verification->get_defaults($us->id, $from, $to);
			$summ     = 0;
			$one_plus = 0;
			if($stats && is_array($stats)){
				foreach($stats as $st){
					$summ = $summ + (int)$st->amount;
				}
				$one_plus = count($stats);
			}
			
			$ver_users[$key]->defaults = array('summ'=>$summ, 'one_plus'=>$one_plus);
		}
		
		$orders_count = $this->verification->get_orders_count($from, $to);
		//$orders_count[0]->counter;
		//echo '<pre>';
		//print_r($ver_users);
		//echo '</pre>';
		$this->design->assign('ver_users', $ver_users);
		$this->design->assign('from', $from);
		$this->design->assign('to', $to);
		$this->design->assign('orders_count', $orders_count[0]->counter);
		return $this->design->fetch('verification.tpl');
	}
}