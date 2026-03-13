<?php
error_reporting(-1);
ini_set('display_errors', 'On');
require_once 'AService.php';

class B2PService extends AService
{
    private $date_from = NULL;
    private $date_to = NULL;
            
    public function __construct()
    {
    	parent::__construct();
                
        $this->run();
    }
    
    private function run()
    {
        $this->date_from = $this->request->get('from');
        $this->date_to = $this->request->get('to');
        
        if (empty($this->date_from) || empty($this->date_to))
        {
            $this->response['error'] = 'Введите даты в формате YYYY-MM-DD';
        }
        else
        {
            $this->response['data'] = [];
            
            $action = $this->request->get('action');
            if ($action == 'payments')
            {
                $this->load_b2p_payments();
            }
            elseif ($action == 'issuances')
            {
                $this->load_b2p_issuances();
            }
            else
            {
                $this->response['error'] = 'UNDEFINED ACTION';
            }

        }
        $this->json_output();
    }
    
    private function load_b2p_issuances()
    {
        $managers = [];
        foreach ($this->managers->get_managers() as $m)
            $managers[$m->id] = $m;
        
        $this->db->query("
            SELECT * 
            FROM b2p_p2pcredits
            WHERE status = 'APPROVED'
            AND complete_date >= ?
            AND complete_date <= ?
        ", $this->date_from, $this->date_to);
        if ($p2pcredits = $this->db->results())
        {
            foreach ($p2pcredits as $p2pcredit)
            {

                $order = $this->orders->get_order($p2pcredit->order_id);
                $order->p2pcredit = $p2pcredit;
                if ($order_insures = $this->best2pay->get_order_insures($p2pcredit->order_id))
                    $order->insure = reset($order_insures);
                $card = $this->best2pay->get_card($order->card_id);
                
                $item = new StdClass();
                
                $item->Организация = 'Boostra';
                $item->Дата = date('YmdHis', strtotime($order->p2pcredit->complete_date)); //формат ггггММддччммсс
                $item->Сумма = $order->amount;  //в рублях
                $item->ПроцентСтавка = $order->percent;
                $item->Срок = (int)$order->period;
                $item->Менеджер = empty($order->manager_id) ? '' : $managers[$order->manager_id]->name_1c;
                $item->УИД_Займ = ''; 
                $item->УИД_Заявка = ''; 
                $item->agrid = ''; // агрид скористы
                $item->НомерЗаявки = $order->id_1c;
                $item->НомерКарты = $card->pan;
                
                $contract_number = 'Б'.date('y', strtotime($order->date)).'-';
                if ($order->order_id > 999999)
                    $contract_number .= $order->order_id; 
                else
                    $contract_number .= '0'.$order->order_id; 
                $item->Номер = $contract_number; // 12345-67890обязательно в номере ТИРЕ. длина номера 11 символов!!!
    
                $item->Клиент = new StdClass();
                $item->Клиент->Фамилия = $order->lastname;
                $item->Клиент->Имя = $order->firstname;
                $item->Клиент->Отчество = $order->patronymic;
                $item->Клиент->ДатаРожденияПоПаспорту = date('YmdHis', strtotime($order->birth));
    
                $item->Клиент->АдресРегистрацииГород = trim($order->Regcity.' '.$order->Regcity_shorttype);
                $item->Клиент->АдресРегистрацииДом = $order->Reghousing.(empty($order->Regbuilding) ? '' : ' стр. '.$order->Regbuilding);
                $item->Клиент->АдресРегистрацииИндекс = $order->Regindex;
                $item->Клиент->АдресРегистрацииКвартира = $order->Regroom;
                $item->Клиент->АдресРегистрацииРегион = trim($order->Regregion.' '.$order->Regregion_shorttype);
                $item->Клиент->АдресРегистрацииУлица = trim($order->Regstreet.' '.$order->Regstreet_shorttype);
                $item->Клиент->АдресРегистрацииРайон = empty($order->Regdistrict) ? '' : trim($order->Regdistrict);
                $item->Клиент->АдресРегистрацииНасПункт = empty($order->Reglocality) ? '' : trim($order->Reglocality);
                $item->Клиент->АдресРегистрацииТелефон = '';
    
                $item->Клиент->АдресФактическогоПроживанияИндекс = $order->Faktindex;
                $item->Клиент->АдресФактическогоПроживанияРегион = trim($order->Faktregion.' '.$order->Faktregion_shorttype);
                $item->Клиент->АдресФактическогоПроживанияРайон = empty($order->Faktdistrict) ? '' : trim($order->Faktdistrict);
                $item->Клиент->АдресФактическогоПроживанияГород = trim($order->Faktcity.' '.$order->Faktcity_shorttype);
                $item->Клиент->АдресФактическогоПроживанияНасПункт = empty($order->Faktlocality) ? '' : trim($order->Faktlocality);
                $item->Клиент->АдресФактическогоПроживанияУлица = trim($order->Faktstreet.' '.$order->Faktstreet_shorttype);
                $item->Клиент->АдресФактическогоПроживанияДом = $order->Fakthousing.(empty($order->Faktbuilding) ? '' : ' стр. '.$order->Faktbuilding);
                $item->Клиент->АдресФактическогоПроживанияКвартира = $order->Faktroom;
                $item->Клиент->АдресФактическогоПроживанияТелефон = '';
    
                $item->Клиент->АдресФактическогоПроживанияМобильныйТелефон = $this->soap1c->format_phone($order->phone_mobile); 
    
                $item->Клиент->ИНН = $order->inn;
                $item->Клиент->КоличествоИждевенцев = '';
                $item->Клиент->МестоРожденияПоПаспорту = $order->birth_place;
                $item->Клиент->Образование = '';
    
                $item->Клиент->ОрганизацияАдрес = $order->work_address;
                $item->Клиент->ОрганизацияГрафикЗанятости = '';
                $item->Клиент->ОрганизацияДолжность = $order->profession;
                $item->Клиент->ОрганизацияЕжемесячныйДоход = $order->income_base;
                $item->Клиент->ОрганизацияНазвание = $order->workplace;
                $item->Клиент->ОрганизацияСтажРаботыЛет = '';
                $item->Клиент->ОрганизацияСфераДеятельности = '';
                $item->Клиент->ОрганизацияТелефон = $this->soap1c->format_phone($order->work_phone);
                $item->Клиент->ОрганизацияФИОРуководителя = $order->workdirector_name;
                $item->Клиент->ОрганизацияТелефонРуководителя = '';
    
                $item->Клиент->ПаспортДатаВыдачи = date('YmdHis', strtotime($order->passport_date)); //формат ггггММддччммсс
                $item->Клиент->ПаспортКемВыдан = $order->passport_issued;
                $item->Клиент->ПаспортКодПодразделения = $order->subdivision_code;
                $item->Клиент->ПаспортНомер = (string)substr(str_replace(array(' ', '-'), '', $order->passport_serial), 4, 6);
                $item->Клиент->ПаспортСерия = (string)substr(str_replace(array(' ', '-'), '', $order->passport_serial), 0, 4);
    
                $item->Клиент->Пол = $order->gender == 'male' ? 'Мужской' : 'Женский';
    
    
                $item->Клиент->КонтактныеЛица = array();

                $item->Payment = new StdClass();
                $item->Payment->CardId = $order->card_id; 
                $item->Payment->Дата = date('YmdHis', strtotime($order->p2pcredit->date)); 
                $item->Payment->PaymentId = $order->p2pcredit->operation_id; 
                $item->Payment->OrderId = $order->p2pcredit->register_id; 
    
                $item->ЗаймСоСтраховкой = empty($order->insure) ? 0 : 1;
                $item->Страховка = new StdClass();
                $item->Страховка->СуммаСтраховки = empty($order->insure) ? 0 : $order->insure->amount; //сумма страховки
                $item->Страховка->OrderID = empty($order->insure->register_id) ? '' : $order->insure->register_id; 
                $item->Страховка->OperationID = empty($order->insure->operation_id) ? '' : $order->insure->operation_id; 
                
                if (empty($order->insure))
                    $item->Страховка->Ставка = 0;
                else
                    $item->Страховка->Ставка = empty($order->insure->stavka) ? round($order->insure->amount / $order->amount * 100) : $order->insure->stavka; 
                
                $item->Страховка->insurer = $order->insurer;
    
                // Добавим КД
                if ($order->is_user_credit_doctor == 1) {
                    if ($credit_doctor = $this->credit_doctor->getUserCreditDoctor((int)$order->order_id, (int)$order->user_id, $this->credit_doctor::CREDIT_DOCTOR_STATUS_SUCCESS))
                        $credit_doctor_transaction = $this->best2pay->get_transaction($credit_doctor->transaction_id);
    
                    if (!empty($credit_doctor) && !empty($credit_doctor_transaction)) {
                        $credit_doctor_data = (object)[
                            'insurer' =>  $order->insurer,
                            'OperationID' => $credit_doctor_transaction->operation ?? '',
                            'OrderID' => $credit_doctor_transaction->register_id ?? '',
                            'СуммаКД' => $credit_doctor->amount,
                            'КомплектНазвание' => 'Комплект ' . $credit_doctor->credit_doctor_condition_id,
                            'КомплектID' => $credit_doctor->credit_doctor_condition_id,
                        ];
    
                        $item->КД = $credit_doctor_data;
                    }
                }
                
                $this->response['data'][] = $item; 
            }    
        }
        
    }

    private function load_b2p_payments()
    {
        $query = $this->db->placehold("
            SELECT 
                p.*,
                CONCAT (u.lastname, ' ', u.firstname, ' ', u.patronymic, ' ', u.birth) AS client,
                u.UID AS client_uid
            FROM b2p_payments AS p
            LEFT JOIN s_users AS u
            ON u.id = p.user_id
            WHERE p.reason_code = 1
            AND DATE(p.created) >= ?
            AND DATE(p.created) <= ?
            ORDER BY id DESC 
        ", $this->date_from, $this->date_to);
        $this->db->query($query);
        if ($b2p_payments = $this->db->results())
        {
            foreach ($b2p_payments as $payment)
            {
                $item = new StdClass();
                
                $item->НомерЗайма = $payment->contract_number;
                $item->ДатаОплаты = date('YmdHis', strtotime($payment->created));
                $item->НомерОплаты = 'PM'.date('y').'-'.$payment->id;//обязательно в номере ТИРЕ. длина номера 11 символов!!!
                
                $item->ID_Заказ = $payment->register_id;
                $item->ID_УспешнаяОперация = $payment->operation_id;
                
                $item->СуммаОплаты = (float)$payment->amount; 
                
                $item->Пролонгация = empty($payment->prolongation) ? 0 : 1;  //1 - истина, 0 ложь
                
                $payment->multipolis = $this->multipolis->selectAll([
                    'filter_payment_id' => (int)$payment->id,
                    'filter_payment_method' => $this->orders::PAYMENT_METHOD_B2P,
                ], false);
    
                if (!empty($payment->multipolis)) {
                    $item->Мультиполис = (object)[
                        'СуммаСтраховки' => (float)$payment->multipolis->amount,
                        'НомерСтраховки' => $payment->multipolis->number,
                    ];
                    $item->СуммаОплаты = $item->СуммаОплаты - $payment->multipolis->amount; // (тут без мультиполиса)
                }
                
                if ($payment->insure > 0) {
                    $item->ШтрафнойКД = (object)[
                        'СуммаКД' => (float)$payment->insure,
                        'OrderID' => $payment->register_id,
                        'OperationID' => $payment->operation_id,
                        'КомплектID' => 99,
                        'КомплектНазвание' => 'ШтрафнойКД'
                    ];
                    $item->СуммаОплаты = $item->СуммаОплаты - $payment->insure;                 

                }
            
                $this->response['data'][] = $item;
            }
        }
    
            

    }            

}
new B2PService();