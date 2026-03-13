<?php

use boostra\domains\UsersAddress;
use boostra\dto\UserAddressDto;
use boostra\services\RegionService;
use boostra\services\UsersAddressService;

require_once 'Simpla.php';

class Import1c extends Simpla
{
    public function import_user($user_id, $details, $override = true)
    {
//        echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($details);echo '</pre><hr />';
        
        if ($details->Пол == 'Мужской')
            $gender = 'male';
        elseif ($details->Пол == 'Женский')
            $gender = 'female';
//        else
//            $gender = '';
        
        if (!empty($details->ДатаРожденияПоПаспорту))
        {
            if ($strtotime_birth = strtotime(str_replace('.', '-', $details->ДатаРожденияПоПаспорту)))
                $birth = date('d.m.Y', $strtotime_birth);
            else
                $birth = '';
        }
            
        
        if (!empty($details->ПаспортДатаВыдачи))
        {
            if ($strtotime_passport_date = strtotime(str_replace('.', '-', $details->ПаспортДатаВыдачи)))
                $passport_date = date('d.m.Y', $strtotime_passport_date);
            else
                $passport_date = '';
        }
        
//        if (empty($passport_date))
//            $passport_date = '';
//        
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


        $user = array();
        
        if (!empty($details->Email))
            $user['email'] = $details->Email;
        
        if (!empty($details->Фамилия))
            $user['lastname'] = $details->Фамилия;
        if (!empty($details->Имя))
            $user['firstname'] = $details->Имя;
        if (!empty($details->Отчество))
            $user['patronymic'] = $details->Отчество;

        if (!empty($gender))
            $user['gender'] = $gender;
        if (!empty($birth))
            $user['birth'] = empty($birth) ? '' : $birth;
        if (!empty($details->МестоРожденияПоПаспорту))
            $user['birth_place'] = $details->МестоРожденияПоПаспорту;

        if (!empty($details->ПаспортСерия) && $details->ПаспортСерия != 'ложь' && !empty($details->ПаспортНомер) && $details->ПаспортНомер != 'ложь')
            $user['passport_serial'] = $details->ПаспортСерия.' '.$details->ПаспортНомер;
        if (!empty($details->ПаспортКодПодразделения))
            $user['subdivision_code'] = $details->ПаспортКодПодразделения;
        if (!empty($passport_date))
            $user['passport_date'] = $passport_date;
        if (!empty($details->ПаспортКемВыдан))
            $user['passport_issued'] = $details->ПаспортКемВыдан;
        
        if (!empty($details->АдресРегистрацииИндекс))
            $user['Regindex'] = $details->АдресРегистрацииИндекс;
        if (!empty($regregion))
            $user['Regregion'] = $regregion;
        if (!empty($regregion_shorttype))
            $user['Regregion_shorttype'] = $regregion_shorttype;
        if (!empty($regdistrict))
            $user['Regdistrict'] = $regdistrict;
        if (!empty($reglocality))
            $user['Reglocality'] = $reglocality;
        if (!empty($regcity) && $override)
            $user['Regcity'] = $regcity;
        if (!empty($regcity_shorttype))
            $user['Regcity_shorttype'] = $regcity_shorttype;
        if (!empty($regstreet))
            $user['Regstreet'] = $regstreet;
        if (!empty($regstreet_shorttype))
            $user['Regstreet_shorttype'] = $regstreet_shorttype;
        if (!empty($details->АдресРегистрацииДом))
            $user['Reghousing'] = $details->АдресРегистрацииДом;
        if (!empty($details->АдресРегистрацииКвартира))
            $user['Regroom'] = $details->АдресРегистрацииКвартира;

        if (!empty($details->АдресФактическогоПроживанияИндекс))
            $user['Faktindex'] = $details->АдресФактическогоПроживанияИндекс;
        if (!empty($faktregion))
            $user['Faktregion'] = $faktregion;
        if (!empty($faktregion_shorttype))
            $user['Faktregion_shorttype'] = $faktregion_shorttype;
        if (!empty($faktdistrict))
            $user['Faktdistrict'] = $faktdistrict;
        if (!empty($faktlocality))
            $user['Faktlocality'] = $faktlocality;
        if (!empty($faktcity) && $override)
            $user['Faktcity'] = $faktcity;
        if (!empty($faktcity_shorttype))
            $user['Faktcity_shorttype'] = $faktcity_shorttype;
        if (!empty($faktstreet))
            $user['Faktstreet'] = $faktstreet;
        if (!empty($faktstreet_shorttype))
            $user['Faktstreet_shorttype'] = $faktstreet_shorttype;
        if (!empty($details->АдресФактическогоПроживанияДом))
            $user['Fakthousing'] = $details->АдресФактическогоПроживанияДом;
        if (!empty($details->АдресФактическогоПроживанияКвартира))
            $user['Faktroom'] = $details->АдресФактическогоПроживанияКвартира;

        if (!empty($details->ОрганизацияСфераДеятельности))
            $user['employment'] = $details->ОрганизацияСфераДеятельности;
        if (!empty($details->ОрганизацияДолжность))
            $user['profession'] = $details->ОрганизацияДолжность;
        if (!empty($details->ОрганизацияНазвание))
            $user['workplace'] = $details->ОрганизацияНазвание;
        if (!empty($details->ОрганизацияАдрес))
            $user['work_address'] = $details->ОрганизацияАдрес;
        if (!empty($details->ОрганизацияТелефон))
            $user['work_phone'] = $details->ОрганизацияТелефон;
        if (!empty($details->ОрганизацияФИОРуководителя))
            $user['workdirector_name'] = $details->ОрганизацияФИОРуководителя;
        if (!empty($details->ОрганизацияЕжемесячныйДоход))
            $user['income_base'] = $details->ОрганизацияЕжемесячныйДоход;
        if (!empty($details->VK_id))
            $user['social_vk'] = $details->VK_id;

        $isset_contactpersons = $this->contactpersons->get_contactpersons(array('user_id'=>$user_id));        
        if (!empty($details->КонтактныеЛица[0]))
        {
            $found_contactperson = 0;
            $prepare_phone = str_replace(array('('.')','-',' ','+'), '', $details->КонтактныеЛица[0]->ТелефонМобильный);
            foreach ($isset_contactpersons as $isset_contactperson)
            {
                $import_cp_name = trim(mb_strtolower($details->КонтактныеЛица[0]->Фамилия, 'utf-8'));
                $import_cp_name .= ' '.trim(mb_strtolower($details->КонтактныеЛица[0]->Имя, 'utf-8'));
                $import_cp_name .= ' '.trim(mb_strtolower($details->КонтактныеЛица[0]->Отчество, 'utf-8'));
                if (mb_strtolower(trim($isset_contactperson->name), 'utf-8') == $import_cp_name)
                    $found_contactperson = 1;
            }
            if (empty($found_contactperson))
            {
                $this->contactpersons->add_contactperson(array(
                    'user_id' => $user_id,
                    'name' => trim($details->КонтактныеЛица[0]->Фамилия).' '.trim($details->КонтактныеЛица[0]->Имя).' '.trim($details->КонтактныеЛица[0]->Отчество),
                    'relation' => $details->КонтактныеЛица[0]->СтепеньРодства,
                    'phone' => $prepare_phone
                ));
            }
        }
        
        if (!empty($details->КонтактныеЛица[1]))
        {
            $found_contactperson2 = 0;
            $prepare_phone = str_replace(array('('.')','-',' ','+'), '', $details->КонтактныеЛица[1]->ТелефонМобильный);
            foreach ($isset_contactpersons as $isset_contactperson)
            {
                $import_cp_name = trim(mb_strtolower($details->КонтактныеЛица[1]->Фамилия, 'utf-8'));
                $import_cp_name .= ' '.trim(mb_strtolower($details->КонтактныеЛица[1]->Имя, 'utf-8'));
                $import_cp_name .= ' '.trim(mb_strtolower($details->КонтактныеЛица[1]->Отчество, 'utf-8'));
                if (mb_strtolower(trim($isset_contactperson->name), 'utf-8') == $import_cp_name)
                    $found_contactperson2 = 1;
            }
            if (empty($found_contactperson2))
            {
                $this->contactpersons->add_contactperson(array(
                    'user_id' => $user_id,
                    'name' => trim($details->КонтактныеЛица[1]->Фамилия).' '.trim($details->КонтактныеЛица[1]->Имя).' '.trim($details->КонтактныеЛица[1]->Отчество),
                    'relation' => $details->КонтактныеЛица[1]->СтепеньРодства,
                    'phone' => $prepare_phone
                ));
            }
        }
        
        if (!empty($details->КонтактныеЛица[2]))
        {
            $found_contactperson3 = 0;
            $prepare_phone = str_replace(array('('.')','-',' ','+'), '', $details->КонтактныеЛица[2]->ТелефонМобильный);
            foreach ($isset_contactpersons as $isset_contactperson)
            {
                $import_cp_name = trim(mb_strtolower($details->КонтактныеЛица[2]->Фамилия, 'utf-8'));
                $import_cp_name .= ' '.trim(mb_strtolower($details->КонтактныеЛица[2]->Имя, 'utf-8'));
                $import_cp_name .= ' '.trim(mb_strtolower($details->КонтактныеЛица[2]->Отчество, 'utf-8'));
                if (mb_strtolower(trim($isset_contactperson->name), 'utf-8') == $import_cp_name)
                    $found_contactperson3 = 1;
            }
            if (empty($found_contactperson3))
            {
                $this->contactpersons->add_contactperson(array(
                    'user_id' => $user_id,
                    'name' => trim($details->КонтактныеЛица[2]->Фамилия).' '.trim($details->КонтактныеЛица[2]->Имя).' '.trim($details->КонтактныеЛица[2]->Отчество),
                    'relation' => $details->КонтактныеЛица[2]->СтепеньРодства,
                    'phone' => $prepare_phone
                ));
            }
        }

        try {
            if (file_exists(__DIR__ . '/../lib/autoloader.php')) {
                require_once __DIR__ . '/../lib/autoloader.php';
                $usersAddressService = new UsersAddressService();

                $registrationAddress = $usersAddressService->getRegistrationAddressFromUser($user);
                $factualAddress = $usersAddressService->getFactualAddressFromUser($user);

                $user['registration_address_id'] = $usersAddressService->saveNewAddress($registrationAddress);
                $user['factual_address_id'] = $usersAddressService->saveNewAddress($factualAddress);

                $usersAddressService->saveOktmo($user_id, $registrationAddress);
            }
        } catch (Throwable $e) {
            $this->logging(json_encode($_SERVER), '$registrationAddress', $e->getMessage() . $e->getTraceAsString() . $e->getFile() . $e->getLine(), [$registrationAddress ?? [], $factualAddress ?? []], 'users_addresses_1c.txt');
        }

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($user);echo '</pre><hr />';        
        $this->users->update_user($user_id, $user);
        
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