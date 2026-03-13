<?php

require_once 'Simpla.php';

class Import1c extends Simpla
{
    public function import_user_balance($user_id, $balance_1c)
    {
        if (empty($balance_1c->НомерЗайма))
            return false;
        
        $balance_1c_norm = new stdClass();
		$balance_1c_norm->user_id = $user_id;
		$balance_1c_norm->zaim_number = $balance_1c->НомерЗайма;
		$balance_1c_norm->zaim_summ = $balance_1c->СуммаЗайма;
		$balance_1c_norm->percent = $balance_1c->ПроцентнаяСтавка;
		$balance_1c_norm->ostatok_od = $balance_1c->ОстатокОД;
		$balance_1c_norm->ostatok_percents = $balance_1c->ОстатокПроцентов;
		$balance_1c_norm->ostatok_peni = $balance_1c->ОстатокПени;
		$balance_1c_norm->client = $balance_1c->Клиент;
		$balance_1c_norm->zaim_date = $balance_1c->ДатаЗайма;
		$balance_1c_norm->zayavka = $balance_1c->Заявка;
		$balance_1c_norm->restructurisation = serialize(json_decode($balance_1c->Реструктуризация));
		$balance_1c_norm->sale_info = $balance_1c->ИнформацияОПродаже;
		$balance_1c_norm->payment_date = $balance_1c->ПланДата;
		$balance_1c_norm->prolongation_amount = $balance_1c->СуммаДляПролонгации;
		$balance_1c_norm->last_prolongation = $balance_1c->ПоследняяПролонгация;

		$balance_1c_norm->prolongation_summ_percents = $balance_1c->СуммаДляПролонгации_Проценты;
		$balance_1c_norm->prolongation_summ_insurance = $balance_1c->СуммаДляПролонгации_Страховка;
		$balance_1c_norm->prolongation_summ_sms = $balance_1c->СуммаДляПролонгации_СМС;
		$balance_1c_norm->prolongation_summ_cost = $balance_1c->СуммаДляПролонгации_Стоимость;
		$balance_1c_norm->prolongation_count = $balance_1c->КоличествоПролонгаций;
		$balance_1c_norm->allready_added = $balance_1c->УжеНачислено;

        $balance_1c_norm->overdue_debt_od_IL = $balance_1c->ПросроченныйДолг_ОД ?? null;
        $balance_1c_norm->overdue_debt_percent_IL = $balance_1c->ПросроченныйДолг_Процент ?? null;
        $balance_1c_norm->next_payment_od = $balance_1c->БлижайшийПлатеж_Сумма_ОД ?? null;
        $balance_1c_norm->next_payment_percent = $balance_1c->БлижайшийПлатеж_Сумма_Процент ?? null;
		$balance_1c_norm->last_update = date('Y-m-d H:i:s');
            
        $this->db->query("
            SELECT id 
            FROM __user_balance
            WHERE user_id = ?
        ", $user_id);
        if ($id = $this->db->result('id'))
        {
            $this->lpt->refresh_lpt_lead($id, $balance_1c_norm);

            $query = $this->db->placehold("
                UPDATE __user_balance 
                SET ?% 
                WHERE id = ?
            ", $balance_1c_norm, $id);
            $this->db->query($query);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';            
        }
        else
        {
            $query = $this->db->placehold("
                INSERT INTO __user_balance 
                SET ?% 
            ", $balance_1c_norm);
            $this->db->query($query);
            
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';            
        }
        

    }

    /**
     * Import_1c::import_user()
     * Записывает в базу данные пользователя полученные из 1с
     * 
     * @param string $uid
     * @param object $details
     * @return integer $user_id
     */
    public function import_user($uid, $details)
    {
//        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($details);echo '</pre><hr />';
        
        if ($details->Пол == 'Мужской')
            $gender = 'male';
        elseif ($details->Пол == 'Женский')
            $gender = 'female';
        else
            $gender = '';
        
        $birth = date('d.m.Y', strtotime(str_replace('.', '-', $details->ДатаРожденияПоПаспорту)));
        if (empty($birth))
            $birth = '';
        
        $passport_date = date('d.m.Y', strtotime(str_replace('.', '-', $details->ПаспортДатаВыдачи)));
        if (empty($passport_date))
            $passport_date = '';
        
        list($regregion, $regregion_shorttype) = $this->parse_shorttype($details->АдресРегистрацииРегион);
        list($regdistrict, $regdistrict_shorttype) = $this->parse_shorttype($details->АдресРегистрацииРайон);
        list($reglocality, $reglocality_shorttype) = $this->parse_shorttype($details->АдресРегистрацииНасПункт);
        list($regcity, $regcity_shorttype) = $this->parse_shorttype($details->АдресРегистрацииГород, ',');
        list($regstreet, $regstreet_shorttype) = $this->parse_shorttype($details->АдресРегистрацииУлица);

        list($faktregion, $faktregion_shorttype) = $this->parse_shorttype($details->АдресФактическогоПроживанияРегион);
        list($faktdistrict, $faktdistrict_shorttype) = $this->parse_shorttype($details->АдресФактическогоПроживанияРайон);
        list($faktlocality, $faktlocality_shorttype) = $this->parse_shorttype($details->АдресФактическогоПроживанияНасПункт);
        list($faktcity, $faktcity_shorttype) = $this->parse_shorttype($details->АдресФактическогоПроживанияГород, ',');
        list($faktstreet, $faktstreet_shorttype) = $this->parse_shorttype($details->АдресФактическогоПроживанияУлица);


        $user = array(
            'UID' => $uid,
            'loaded_from_1c' => 1,
            'email' => $details->Email,
            
            'stage_personal' => 1,
            'stage_passport' => 1,
            'stage_address' => 1,
            'stage_work' => 1,
            'stage_files' => 1,
            'stage_card' => 1,
            
            'created' => date('Y-m-d H:i:s'),
            'enabled' => 1,
            'last_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            'reg_ip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '',
            
            'lastname' => $details->Фамилия,
            'firstname' => $details->Имя,
            'patronymic' => $details->Отчество,

            'gender' => $gender,
            'birth' => $birth,
            'birth_place' => $details->МестоРожденияПоПаспорту,
            'phone_mobile' => $this->sms->clear_phone($details->АдресФактическогоПроживанияМобильныйТелефон),

            'passport_serial' => $details->ПаспортСерия.'-'.$details->ПаспортНомер,
            'subdivision_code' => $details->ПаспортКодПодразделения,
            'passport_date' => $passport_date,
            'passport_issued' => $details->ПаспортКемВыдан,

            'Regindex' => $details->АдресРегистрацииИндекс,
            'Regregion' => empty($regregion) ? '' : $regregion,
            'Regregion_shorttype' => empty($regregion_shorttype) ? '' : $regregion_shorttype,
            'Regdistrict' => empty($regdistrict) ? '' : $regdistrict,
            'Regdistrict_shorttype' => empty($regdistrict_shorttype) ? '' : $regdistrict_shorttype,
            'Reglocality' => empty($reglocality) ? '' : $reglocality,
            'Reglocality_shorttype' => empty($reglocality_shorttype) ? '' : $reglocality_shorttype,
            'Regcity' => empty($regcity) ? '' : $regcity,
            'Regcity_shorttype' => empty($regcity_shorttype) ? '' : $regcity_shorttype,
            'Regstreet' => empty($regstreet) ? '' : $regstreet,
            'Regstreet_shorttype' => empty($regstreet_shorttype) ? '' : $regstreet_shorttype,
            'Reghousing' => $details->АдресРегистрацииДом,
            'Regbuilding' => '',
            'Regroom' => $details->АдресРегистрацииКвартира,

            'Faktindex' => $details->АдресФактическогоПроживанияИндекс,
            'Faktregion' => empty($faktregion) ? '' : $faktregion,
            'Faktregion_shorttype' => empty($faktregion_shorttype) ? '' : $faktregion_shorttype,
            'Faktdistrict' => empty($faktdistrict) ? '' : $faktdistrict,
            'Faktdistrict_shorttype' => empty($faktdistrict_shorttype) ? '' : $faktdistrict_shorttype,
            'Faktlocality' => empty($faktlocality) ? '' : $faktlocality,
            'Faktlocality_shorttype' => empty($faktlocality_shorttype) ? '' : $faktlocality_shorttype,
            'Faktcity' => empty($faktcity) ? '' : $faktcity,
            'Faktcity_shorttype' => empty($faktcity_shorttype) ? '' : $faktcity_shorttype,
            'Faktstreet' => empty($faktstreet) ? '' : $faktstreet,
            'Faktstreet_shorttype' => empty($faktstreet_shorttype) ? '' : $faktstreet_shorttype,
            'Fakthousing' => $details->АдресФактическогоПроживанияДом,
            'Faktbuilding' => '',
            'Faktroom' => $details->АдресФактическогоПроживанияКвартира,
            
            'profession' => $details->ОрганизацияДолжность,
            'workplace' => $details->ОрганизацияНазвание,
            'workaddress' => $details->ОрганизацияАдрес,
            'workcomment' => $details->ОрганизацияКомментарийКТелефону,
            'workphone' => $this->sms->clear_phone($details->ОрганизацияТелефон),
            'chief_name' => $details->ОрганизацияФИОРуководителя,
            'chief_position' => '',
            'chief_phone' => $this->sms->clear_phone($details->ОрганизацияТелефонРуководителя),

            'income' => $details->ОрганизацияЕжемесячныйДоход,
            'expenses' => $details->ОбщаяСуммаРасходов,
            'social' => $details->VK_id,
            
        );
        
        if (!empty($details->КонтактныеЛица[0]))
        {
            $user['contact_person_name'] = $details->КонтактныеЛица[0]->Фамилия.' '.$details->КонтактныеЛица[0]->Имя.' '.$details->КонтактныеЛица[0]->Отчество;
            $user['contact_person_phone'] = $this->sms->clear_phone($details->КонтактныеЛица[0]->ТелефонМобильный);
            $user['contact_person_relation'] = $details->КонтактныеЛица[0]->СтепеньРодства;
        }
        
        if (!empty($details->КонтактныеЛица[1]))
        {
            $user['contact_person2_name'] = $details->КонтактныеЛица[1]->Фамилия.' '.$details->КонтактныеЛица[1]->Имя.' '.$details->КонтактныеЛица[1]->Отчество;
            $user['contact_person2_phone'] = $this->sms->clear_phone($details->КонтактныеЛица[1]->ТелефонМобильный);
            $user['contact_person2_relation'] = $details->КонтактныеЛица[1]->СтепеньРодства;
        }
        
        if (!empty($details->КонтактныеЛица[2]))
        {
            $user['contact_person3_name'] = $details->КонтактныеЛица[2]->Фамилия.' '.$details->КонтактныеЛица[2]->Имя.' '.$details->КонтактныеЛица[2]->Отчество;
            $user['contact_person3_phone'] = $this->sms->clear_phone($details->КонтактныеЛица[2]->ТелефонМобильный);
            $user['contact_person3_relation'] = $details->КонтактныеЛица[2]->СтепеньРодства;
        }
        
        $user_id = $this->users->add_user($user);
        
        return $user_id;
    }
    
    /**
     * Import_1c::parse_shorttype()
     * Парсит названия городов, регионов улиц и извлекает тип 
     * 
     * @param string $subject
     * @param string $delimiter
     * @return array
     */
    private function parse_shorttype($subject, $delimiter = ' ')
    {
        $response = array(
            0 => '', // main
            1 => '', // shorttype
        );
        
        if (!empty($subject))
        {
            $expl = explode($delimiter, $subject);
            if (count($expl) > 1)
            {
                $response[1] = mb_strtolower(array_pop($expl), 'utf-8');
                $response[0] = implode($delimiter, $expl);
            }
            else
            {
                $response[0] = $subject;
            }
        }
        
        return $response;
    }

    
}