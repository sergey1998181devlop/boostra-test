<?php

require_once 'Simpla.php';

class NewLk extends Simpla
{
    private $salt = 'atcBiamOzvaOyngStqnTcpzRmwcAwxn';
    private $url = 'https://crm.akvariusmkk.ru/api/boostra/transfer';

    public function check_redirect($user)
    {
        $check_redirect_list = array_map('trim', explode(',', $this->settings->check_redirect_list));

        $check = in_array($user->id, $check_redirect_list);

        if ($check)
        {
            $data = $this->get_data($user->id);
            return [
                'data' => base64_encode($data),
                'signature' => $this->get_signature($data),
                'url' => $this->get_url(),
            ];
        }
        
        return NULL;
    }

    public function get_url()
    {
        return $this->url;
    }
    
    public function get_data($user_id)
    {
        $request = [];
        if ($user = $this->users->get_user((int)$user_id))
        {
            $request['user'] = $user;
            
            $request['files'] = [];
            if ($files = $this->users->get_files(['user_id' => $user_id]))
            {
                foreach ($files as $file)
                {
                    $file->url = $this->config->root_url.'/'.$this->config->users_files_dir.$file->name;
                    $request['files'][] = $file;
                }
            }
            
            $request['credit_doctor'] = $this->credit_doctor->getSuccessUserCreditDoctor($user_id);
        }
        
        return json_encode($request, JSON_UNESCAPED_UNICODE);
    }
    
    public function get_signature($data_string)
    {
        $signature = md5($data_string.$this->salt);
    
        return $signature;
    }
}