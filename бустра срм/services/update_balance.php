<?php

ini_set('max_execution_time', 600); 

require_once 'AService.php';

class UpdateBalanceService extends AService
{
    public function __construct()
    {
    	parent::__construct();
        
//        $this->response['info'] = array(
//            
//        );
        
        if ($this->request->get('test'))
            $this->test();
        else
            $this->run();
    }
    
    private function run()
    {
        if ($json = $this->request->post('json'))
        {
            if ($json_decode = json_decode($json))
            {
                foreach ($json_decode as $item)
                {
                    if (isset($item->УИДКлиента))
                    {
                        $balance = new StdClass();

                        $balance->zaim_number = $item->НомерЗайма;
                        $balance->last_update = date('Y-m-d H:i:s');
                        
                        if (isset($item->ПроцентнаяСтавка))
                            $balance->percent = $item->ПроцентнаяСтавка;
                        if (isset($item->СуммаЗайма))
                            $balance->zaim_summ = $item->СуммаЗайма;
                        if (isset($item->ОстатокОД))
                            $balance->ostatok_od = $item->ОстатокОД;
                        if (isset($item->ОстатокПроцентов))
                            $balance->ostatok_percents = $item->ОстатокПроцентов;
                        if (isset($item->ОстатокПени))
                            $balance->ostatok_peni = $item->ОстатокПени;
                        if (isset($item->Клиент))
                            $balance->client = $item->Клиент;
                        if (isset($item->ДатаЗайма))
                            $balance->zaim_date = $item->ДатаЗайма;
                        if (isset($item->Заявка))
                            $balance->zayavka = $item->Заявка;
                        if (isset($item->ИнформацияОПродаже))
                            $balance->sale_info = $item->ИнформацияОПродаже;
                        if (isset($item->ПланДата))
                            $balance->payment_date = $item->ПланДата;
                        if (isset($item->СуммаДляПролонгации))
                            $balance->prolongation_amount = $item->СуммаДляПролонгации;
                        if (isset($item->СуммаДляПролонгации_Проценты))
                            $balance->prolongation_summ_percents = $item->СуммаДляПролонгации_Проценты;
                        if (isset($item->СуммаДляПролонгации_Страховка))
                            $balance->prolongation_summ_insurance = $item->СуммаДляПролонгации_Страховка;
                        if (isset($item->СуммаДляПролонгации_СМС))
                            $balance->prolongation_summ_sms = $item->СуммаДляПролонгации_СМС;
                        if (isset($item->СуммаДляПролонгации_Стоимость))
                            $balance->prolongation_summ_cost = $item->СуммаДляПролонгации_Стоимость;
                        if (isset($item->КоличествоПролонгаций))
                            $balance->prolongation_count = $item->КоличествоПролонгаций;
                        if (isset($item->ПоследняяПролонгация))
                            $balance->last_prolongation = $item->ПоследняяПролонгация;

                        if (isset($item->УжеНачислено))
                            $balance->allready_added = $item->УжеНачислено;
                        if (isset($item->Реструктуризация))
                            $balance->restructurisation = $item->Реструктуризация;

                        if ($user_id = $this->users->get_uid_user_id($item->УИДКлиента))
                        {
                            if ($user_balance = $this->users->get_user_balance($user_id))
                            {
                                $balance->user_id = $user_id;
                                
                                $this->users->update_user_balance($user_balance->id, $balance);
                            }
                            else
                            {
                                $this->logging('error_user_balance', '', $item, '', 'update_balance.txt');
                            }
                        }
                        else
                        {
                            $this->logging('error_user_id', '', $item, '', 'update_balance.txt');
                        }
                    }
                    else
                    {
                        $this->logging('data_error', '', $item, '', 'update_balance.txt');
                        
                    }
                }
                
                $this->logging('success', '', $json_decode, date('Y-m-d H:i:s'), 'update_balance.txt');
                
                $this->response['success'] = 1;                    
            }
            else
            {
                $this->logging('json_error', '', $base64_decode, '', 'update_balance.txt');

                $this->response['error'] = 'Не удалось декодировать json';                
            }
        
        }
        else
        {
            $this->logging('request', $_SERVER['REQUEST_URI'], $_POST, $_FILES, 'update_balance.txt');
            $this->response['error'] = 'EMPTY_JSON';
        }
        
        
        $this->json_output();
    }
    
    private function test()
    {
        $balance1 = $this->soap->get_user_balance_1c('a2c1dd09-5cf4-11ec-9994-00155d2d0507');
        $balance2 = $this->soap->get_user_balance_1c('e3501cc6-5b6d-11ec-9993-00155d2d0507');
        
        $balance1->return->УидКлиента = 'a2c1dd09-5cf4-11ec-9994-00155d2d0507';
        $balance2->return->УидКлиента = 'e3501cc6-5b6d-11ec-9993-00155d2d0507';
        $array = array(
            $balance1->return,
            $balance2->return
        );
        echo base64_encode(json_encode($array));

    }
    
}
new UpdateBalanceService();