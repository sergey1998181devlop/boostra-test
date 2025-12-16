<?PHP

require_once('api/Simpla.php');

############################################
# Class Product - edit the static section
############################################
class OrderAdmin extends Simpla
{
	public function fetch()
	{
		$order = new stdClass;
		if($this->request->method('post'))
		{
			$order->id = $this->request->post('id', 'integer');
			$order->name = $this->request->post('name');
			$order->email = $this->request->post('email');
			$order->phone = $this->request->post('phone');
			$order->address = $this->request->post('address');
			$order->comment = $this->request->post('comment');
			$order->note = $this->request->post('note');
			$order->discount = $this->request->post('discount', 'floatr');
			$order->coupon_discount = $this->request->post('coupon_discount', 'floatr');
			$order->delivery_id = $this->request->post('delivery_id', 'integer');
			$order->delivery_price = $this->request->post('delivery_price', 'float');
			$order->payment_method_id = $this->request->post('payment_method_id', 'integer');
			$order->paid = $this->request->post('paid', 'integer');
			$order->user_id = $this->request->post('user_id', 'integer');
			$order->separate_delivery = $this->request->post('separate_delivery', 'integer');
	 
	 		if(!$order_labels = $this->request->post('order_labels'))
	 			$order_labels = array();

			if(empty($order->id))
			{
  				$order->id = $this->orders->add_order($order);
				$this->design->assign('message_success', 'added');
  			}
    		else
    		{
    			$this->orders->update_order($order->id, $order);
				$this->design->assign('message_success', 'updated');
    		}	

	    	$this->orders->update_order_labels($order->id, $order_labels);
			
			if($order->id)
			{					
				// Принять?
				if($this->request->post('status_new'))
					$new_status = 0;
				elseif($this->request->post('status_accept'))
					$new_status = 1;
				elseif($this->request->post('status_done'))
					$new_status = 2;
				elseif($this->request->post('status_deleted'))
					$new_status = 4;
				else
					$new_status = $this->request->post('status', 'string');
	

				if($new_status == 0)					
				{
					//if(!$this->orders->open(intval($order->id)))
					//	$this->design->assign('message_error', 'error_open');
					//else
						$this->orders->update_order($order->id, array('status'=>0));
				}
				elseif($new_status == 1)					
				{
					//if(!$this->orders->close(intval($order->id)))
					//	$this->design->assign('message_error', 'error_closing');
					//else
						$this->orders->update_order($order->id, array('status'=>1));
				}
				elseif($new_status == 2)					
				{
					//if(!$this->orders->close(intval($order->id)))
					//	$this->design->assign('message_error', 'error_closing');
					//else
						$this->orders->update_order($order->id, array('status'=>2));
				}
				elseif($new_status == 3)					
				{
					//if(!$this->orders->open(intval($order->id)))
					//	$this->design->assign('message_error', 'error_open');
					//else
						$this->orders->update_order($order->id, array('status'=>3));
					header('Location: '.$this->request->get('return'));
				}
				elseif($new_status == 4)					
				{
					//if(!$this->orders->open(intval($order->id)))
					//	$this->design->assign('message_error', 'error_open');
					//else
						$this->orders->update_order($order->id, array('status'=>4));
					header('Location: '.$this->request->get('return'));
				}
				$order = $this->orders->get_order($order->id);
	
				// Отправляем письмо пользователю
				//if($this->request->post('notify_user'))
				//	$this->notify->email_order_user($order->id);
			}

		}
		else
		{
			$order->id = $this->request->get('id', 'integer');
			$order = $this->orders->get_order(intval($order->id));
			// Метки заказа
			$order_labels = array();
			if(isset($order->id))
			foreach($this->orders->get_order_labels($order->id) as $ol)
				$order_labels[] = $ol->id;			
		}

		
		// Если новый заказ и передали get параметры
		if(empty($order->id))
		{
			$order = new stdClass;
			if(empty($order->phone))
				$order->phone = $this->request->get('phone', 'string');
			if(empty($order->name))
				$order->name = $this->request->get('name', 'string');
			if(empty($order->address))
				$order->address = $this->request->get('address', 'string');
			if(empty($order->email))
				$order->email = $this->request->get('email', 'string');
		}

		$this->design->assign('subtotal', $subtotal);
		$this->design->assign('order', $order);

		if(!empty($order->id))
		{
			// Способ доставки
			//$delivery = $this->delivery->get_delivery($order->delivery_id);
			//$this->design->assign('delivery', $delivery);
	
			// Способ оплаты
			$payment_method = $this->payment->get_payment_method($order->payment_method_id);
			
			if(!empty($payment_method))
			{
				$this->design->assign('payment_method', $payment_method);
		
				// Валюта оплаты
				$payment_currency = $this->money->get_currency(intval($payment_method->currency_id));
				$this->design->assign('payment_currency', $payment_currency);
			}
			// Пользователь
			if($order->user_id)
				$this->design->assign('user', $this->users->get_user(intval($order->user_id)));
	
			// Соседние заказы
			$this->design->assign('next_order', $this->orders->get_next_order($order->id, $this->request->get('status', 'string')));
			$this->design->assign('prev_order', $this->orders->get_prev_order($order->id, $this->request->get('status', 'string')));
		}

		// Все способы доставки
		//$deliveries = $this->delivery->get_deliveries();
		//$this->design->assign('deliveries', $deliveries);

		// Все способы оплаты
		$payment_methods = $this->payment->get_payment_methods();
		$this->design->assign('payment_methods', $payment_methods);

		// Метки заказов
	  	$labels = $this->orders->get_labels();
	 	$this->design->assign('labels', $labels);
	  	
	 	$this->design->assign('order_labels', $order_labels);	  	
		
		//if($this->request->get('view') == 'print')
 		//  	return $this->design->fetch('order_print.tpl');
 	  	//else
	 	  	return $this->design->fetch('order.tpl');
	}
}