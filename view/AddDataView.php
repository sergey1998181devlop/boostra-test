<?php

use boostra\services\RegionService;
use boostra\services\UsersAddressService;

require_once 'View.php';

class AddDataView extends View
{
    use \api\traits\JWTAuthTrait;

    public function fetch()
    {
        $this->jwtAuthValidate();

        if ($this->show_unaccepted_agreement_modal())
        {
            header('Location: '.$this->config->root_url.'/user');
            exit();
        }
        
    	if ($this->request->method('post'))
        {
            $update = array();
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($_POST);echo '</pre><hr />';
            if ($lastname = $this->request->post('lastname'))
                $update['lastname'] = mb_convert_case($lastname, MB_CASE_TITLE);
            if ($firstname = $this->request->post('firstname'))
                $update['firstname'] = mb_convert_case($firstname, MB_CASE_TITLE);
            if ($patronymic = $this->request->post('patronymic'))
                $update['patronymic'] = mb_convert_case($patronymic, MB_CASE_TITLE);

            if ($gender = $this->request->post('gender'))
                $update['gender'] = $gender;
            if ($birth = $this->request->post('birth'))
                $update['birth'] = $birth;
            if ($birth_place = $this->request->post('birth_place'))
                $update['birth_place'] = mb_convert_case($birth_place, MB_CASE_UPPER);
            if ($marital_status = $this->request->post('marital_status'))
                $update['marital_status'] = $marital_status;
            
            if ($passport_serial = $this->request->post('passport_serial'))
                $update['passport_serial'] = $passport_serial;
            if ($passport_date = $this->request->post('passport_date'))
                $update['passport_date'] = $this->users->tryFormatDate($passport_date);
            if ($subdivision_code = $this->request->post('subdivision_code'))
                $update['subdivision_code'] = $subdivision_code;
            if ($passport_issued = $this->request->post('passport_issued'))
                $update['passport_issued'] = mb_convert_case($passport_issued, MB_CASE_UPPER);

            if ($Regindex = $this->request->post('Regindex'))
                $update['Regindex'] = $Regindex;
            if ($Regregion = $this->request->post('Regregion'))
                $update['Regregion'] = $Regregion;
            if ($Regcity = $this->request->post('Regcity'))
                $update['Regcity'] = $Regcity;
            if ($Regstreet = $this->request->post('Regstreet'))
                $update['Regstreet'] = $Regstreet;
            if ($Reghousing = $this->request->post('Reghousing'))
                $update['Reghousing'] = $Reghousing;
            if ($Regbuilding = $this->request->post('Regbuilding'))
                $update['Regbuilding'] = $Regbuilding;
            if ($Regroom = $this->request->post('Regroom'))
                $update['Regroom'] = $Regroom;
            if ($Regregion_shorttype = $this->request->post('Regregion_shorttype'))
                $update['Regregion_shorttype'] = $Regregion_shorttype;
            if ($Regcity_shorttype = $this->request->post('Regcity_shorttype'))
                $update['Regcity_shorttype'] = $Regcity_shorttype;
            if ($Regstreet_shorttype = $this->request->post('Regstreet_shorttype'))
                $update['Regstreet_shorttype'] = $Regstreet_shorttype;

            if ($Faktindex = $this->request->post('Faktindex'))
                $update['Faktindex'] = $Faktindex;
            if ($Faktregion = $this->request->post('Faktregion'))
                $update['Faktregion'] = $Faktregion;
            if ($Faktcity = $this->request->post('Faktcity'))
                $update['Faktcity'] = $Faktcity;
            if ($Faktstreet = $this->request->post('Faktstreet'))
                $update['Faktstreet'] = $Faktstreet;
            if ($Fakthousing = $this->request->post('Fakthousing'))
                $update['Fakthousing'] = $Fakthousing;
            if ($Faktbuilding = $this->request->post('Faktbuilding'))
                $update['Faktbuilding'] = $Faktbuilding;
            if ($Faktroom = $this->request->post('Faktroom'))
                $update['Faktroom'] = $Faktroom;
            if ($Faktregion_shorttype = $this->request->post('Faktregion_shorttype'))
                $update['Faktregion_shorttype'] = $Faktregion_shorttype;
            if ($Faktcity_shorttype = $this->request->post('Faktcity_shorttype'))
                $update['Faktcity_shorttype'] = $Faktcity_shorttype;
            if ($Faktstreet_shorttype = $this->request->post('Faktstreet_shorttype'))
                $update['Faktstreet_shorttype'] = $Faktstreet_shorttype;

            $contact_person_name = strip_tags($this->request->post('contact_person_name'));
            $contact_person_relation = strip_tags($this->request->post('contact_person_relation'));
            $contact_person_phone = strip_tags($this->request->post('contact_person_phone'));
            if (!empty($contact_person_name) && !empty($contact_person_relation) && !empty($contact_person_phone))
            {
                $this->contactpersons->add_contactperson(array(
                    'user_id' => $this->user->id,
                    'name' => $contact_person_name,
                    'relation' => $contact_person_relation,
                    'phone' => $contact_person_phone,
                ));
            }
            
            
            if ($income_base = $this->request->post('income_base'))
                $update['income_base'] = $income_base;

            if ($profession = $this->request->post('profession'))
                $update['profession'] = $profession;
            if ($work_scope = $this->request->post('work_scope'))
                $update['work_scope'] = $work_scope;
            if ($workplace = $this->request->post('workplace'))
                $update['workplace'] = $workplace;
            if ($work_phone = $this->request->post('work_phone'))
                $update['work_phone'] = $work_phone;
            if ($workdirector_name = $this->request->post('workdirector_name'))
                $update['workdirector_name'] = $workdirector_name;

            if ($Workindex = $this->request->post('Workindex'))
                $update['Workindex'] = $Workindex;
            if ($Workregion = $this->request->post('Workregion'))
                $update['Workregion'] = $Workregion;
            if ($Workcity = $this->request->post('Workcity'))
                $update['Workcity'] = $Workcity;
            if ($Workstreet = $this->request->post('Workstreet'))
                $update['Workstreet'] = $Workstreet;
            if ($Workhousing = $this->request->post('Workhousing'))
                $update['Workhousing'] = $Workhousing;
            if ($Workbuilding = $this->request->post('Workbuilding'))
                $update['Workbuilding'] = $Workbuilding;
            if ($Workroom = $this->request->post('Workroom'))
                $update['Workroom'] = $Workroom;
            if ($Workregion_shorttype = $this->request->post('Workregion_shorttype'))
                $update['Workregion_shorttype'] = $Workregion_shorttype;
            if ($Workcity_shorttype = $this->request->post('Workcity_shorttype'))
                $update['Workcity_shorttype'] = $Workcity_shorttype;
            if ($Workstreet_shorttype = $this->request->post('Workstreet_shorttype'))
                $update['Workstreet_shorttype'] = $Workstreet_shorttype;




            if (!empty($update))
            {
                if (file_exists(__DIR__ . '/../lib/autoloader.php')) {
                    require_once __DIR__ . '/../lib/autoloader.php';
                    $this->saveUserAddresses($update);
                }

                $update = array_map('strip_tags', $update);
                $this->users->update_user($this->user->id, $update);
                $this->soap->update_fields($this->user->uid, $update);

                if (!empty($update['passport_serial']))
                    $this->scorings->add_scoring([
                        'user_id' => $this->user->id,
                        'type' => $this->scorings::TYPE_UPRID,
                    ]);
            }
        }
        
        $need_add_fields = array();
        
        $this->user = $this->users->get_user((int)$this->user->id);
        
        if (empty($this->user->lastname))
            $need_add_fields[] = 'lastname';
        if (empty($this->user->firstname))
            $need_add_fields[] = 'firstname';
//        if (empty($this->user->patronymic))
//            $need_add_fields[] = 'patronymic';

        if (empty($this->user->gender))
            $need_add_fields[] = 'gender';
        if (empty($this->user->birth))
            $need_add_fields[] = 'birth';
        if (empty($this->user->birth_place))
            $need_add_fields[] = 'birth_place';
        if (empty($this->user->marital_status))
            $need_add_fields[] = 'marital_status';

        if (empty($this->user->passport_serial))
            $need_add_fields[] = 'passport_serial';
        if (empty($this->user->passport_date))
            $need_add_fields[] = 'passport_date';
        if (empty($this->user->subdivision_code))
            $need_add_fields[] = 'subdivision_code';
        if (empty($this->user->passport_issued))
            $need_add_fields[] = 'passport_issued';
        
        if (empty($this->user->Regindex) || empty($this->user->Regregion))
            $need_add_fields[] = 'regaddress';
        
        if (empty($this->user->Faktindex) || empty($this->user->Faktregion))
            $need_add_fields[] = 'faktaddress';
/*
        $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $this->user->id));
        if (empty($contactpersons))
            $need_add_fields[] = 'contactpersons';
*/
/*        
if ($this->is_developer)
{
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$need_add_fields', $need_add_fields);echo '</pre><hr />';
}
*/
        $need_add_work = array();
        
        
        if (empty($need_add_fields))
        {
            if (empty($this->user->income_base))
                $need_add_work[] = 'income_base';

            if ($this->user->work_scope != 'Пенсионер')
            {
//                if (empty($this->user->Workindex) || empty($this->user->Workregion) || empty($this->user->Workhousing))
//                    $need_add_work[] = 'workaddress';

                if (empty($this->user->work_scope) || empty($this->user->profession) || empty($this->user->workplace))
                    $need_add_work[] = 'workdata';                
            }
        }
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($need_add_fields, $need_add_work);echo '</pre><hr />';
        $this->design->assign('need_add_fields', $need_add_fields);
        $this->design->assign('need_add_work', $need_add_work);

/*
if ($this->is_developer)
{
    echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump('$need_add_work', $need_add_work);echo '</pre><hr />';
}
*/
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($need_add_fields);echo '</pre><hr />';        

        $need_add_files = array(
            'face1' => 1,
            'face2' => 1,
            'passport1' => 1,
        );
        if ($isset_files = $this->users->get_files(array('user_id' => $this->user->id)))
        {
            foreach ($isset_files as $isset_file)
                if (isset($need_add_files[$isset_file->type]))
                    unset($need_add_files[$isset_file->type]);
        }
        
//        $this->design->assign('need_add_files', $need_add_files);
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($need_add_files);echo '</pre><hr />';        
        
        
        if (empty($need_add_fields) && empty($need_add_work)/* && empty($need_add_files)*/)
        {
            $_SESSION['success_add_data'] = 1;
            header('Location: '.$this->config->root_url.'/user');
            exit;
        }

        $this->design->assign('regions', (new RegionService())->getRegions());
        $this->design->assign('professions', \api\enums\ProfessionEnum::getAvailableValues());
        return $this->design->fetch('add_data.tpl');
    }

    /**
     * @param array $user
     * @return void
     */
    private function saveUserAddresses(array &$user): void
    {
        $usersAddressService = new UsersAddressService();

        if (!empty($this->request->safe_post('Regregion'))) {
            if (empty($this->user->registration_address_id)) {
                $registrationAddress = $usersAddressService->getRegistrationAddress($this->request);
                $user['registration_address_id'] = $usersAddressService->saveNewAddress($registrationAddress);
                $usersAddressService->saveOktmo($this->user->id, $registrationAddress);
            } else {
                (new UsersAddressService())->updateRegistrationAddress($this->user->registration_address_id, $this->request);
            }
        }

        if (!empty($this->request->safe_post('Faktregion'))) {
            if (empty($this->user->factual_address_id)) {
                $factualAddress = $usersAddressService->getFactualAddress($this->request);
                $user['factual_address_id'] = $usersAddressService->saveNewAddress($factualAddress);
            } else {
                (new UsersAddressService())->updateFactualAddress($this->user->factual_address_id, $this->request);
            }
        }
    }
}