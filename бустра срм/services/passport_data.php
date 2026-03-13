<?php

require_once 'AService.php';

class PassportDataService extends AService
{
    public function __construct()
    {
    	parent::__construct();
        
//        $this->response['info'] = array(
//            
//        );
        
        $this->run();
    }
    
    private function run()
    {
        if ($uid = $this->request->get('uid'))
        {
            if ($user_id = $this->users->get_uid_user_id($uid))
            {
                $user = $this->users->get_user($user_id);
                
                $clear_passport = str_replace(array('-', ' '), '', $user->passport_serial);
                $passport_serial = substr($clear_passport, 0, 4);
                $passport_number = substr($clear_passport, 4, 6);
                
                $regaddress = array(
                    'index' => isset($user->Regindex) ? $user->Regindex : '',
                    'region' => isset($user->Regregion) ? trim($user->Regregion.' '.$user->Regregion_shorttype) : '',
                    'city' => isset($user->Regcity) ? trim($user->Regcity.' '.$user->Regcity_shorttype) : '',
                    'street' => isset($user->Regstreet) ? trim($user->Regstreet.' '.$user->Regstreet_shorttype) : '',
                    'building' => isset($user->Regbuilding) ? trim($user->Regbuilding) : '',
                    'housing' => isset($user->Reghousing) ? trim($user->Reghousing) : '',
                    'room' => isset($user->Regroom) ? trim($user->Regroom) : '',
                );
                
                $faktaddress = array(
                    'index' => isset($user->Faktindex) ? $user->Faktindex : '',
                    'region' => isset($user->Faktregion) ? trim($user->Faktregion.' '.$user->Faktregion_shorttype) : '',
                    'city' => isset($user->Faktcity) ? trim($user->Faktcity.' '.$user->Faktcity_shorttype) : '',
                    'street' => isset($user->Faktstreet) ? trim($user->Faktstreet.' '.$user->Faktstreet_shorttype) : '',
                    'building' => isset($user->Faktbuilding) ? trim($user->Faktbuilding) : '',
                    'housing' => isset($user->Fakthousing) ? trim($user->Fakthousing) : '',
                    'room' => isset($user->Faktroom) ? trim($user->Faktroom) : '',
                );
                
                $workaddress = array(
                    'index' => isset($user->Workindex) ? $user->Workindex : '',
                    'region' => isset($user->Workregion) ? trim($user->Workregion.' '.$user->Workregion_shorttype) : '',
                    'city' => isset($user->Workcity) ? trim($user->Workcity.' '.$user->Workcity_shorttype) : '',
                    'street' => isset($user->Workstreet) ? trim($user->Workstreet.' '.$user->Workstreet_shorttype) : '',
                    'building' => isset($user->Workbuilding) ? trim($user->Workbuilding) : '',
                    'housing' => isset($user->Workhousing) ? trim($user->Workhousing) : '',
                    'room' => isset($user->Workroom) ? trim($user->Workroom) : '',
                );
                
                $contactpersons = $this->contactpersons->get_contactpersons(array('user_id' => $user->id));
//ещё работу добавить и контактные лица ? В тот сервис где я забираю у тебя данные ?                
                $this->response['data'] = array(
                    'passport_serial' => $passport_serial,
                    'passport_number' => $passport_number,
                    'passport_date' => $user->passport_date,
                    'passport_issued' => $user->passport_issued,
                    'subdivision_code' => $user->subdivision_code,
                    'birth_place' => $user->birth_place,
                    'regaddress' => $regaddress,
                    'faktaddress' => $faktaddress,
                    'workaddress' => $workaddress,
                    'workaddress_1c' => $user->work_address,
                    'workplace' => $user->workplace,
                    'work_scope' => $user->work_scope,
                    'profession' => $user->profession,
                    'work_phone' => $user->work_phone,
                    'workdirector_name' => $user->workdirector_name,
                    'contact_persons' => $contactpersons,
                    'income' => isset($user->income_base) ? $user->income_base : '',
                );
                
                $this->response['success'] = 1;
                
            }
            else
            {
                $this->response['error'] = 'USER_NOT_FOUND';
            }
        }
        else
        {
            $this->response['error'] = 'EMPTY_UID';
        }
        
        $this->json_output();
    }
}
new PassportDataService();