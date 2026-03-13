<?php

require_once 'AService.php';

require_once(__DIR__ . '/../scorings/Fns.php');

class InnService extends AService
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
        $lastname = $this->request->get('lastname');
        $firstname = $this->request->get('firstname');
        $patronymic = $this->request->get('patronymic');
        $birthday = $this->request->get('birthday');
        $passport_serial = $this->request->get('passport_serial');
        $passportdate = $this->request->get('passportdate');
        if (
            $lastname ||
            $firstname ||
            $patronymic ||
            $birthday ||
            $passport_serial ||
            $passportdate
        ) {
            //$birthday = date('d.m.Y', strtotime($user->birth));
            //$passportdate = date('d.m.Y', strtotime($user->passport_date));
            $fns = (new Fns())->get_inn($lastname, $firstname, $patronymic, $birthday, 21, $passport_serial, $passportdate);

            if (!empty($fns->code)) {
                $scoring = array(
                    'type' => $this->scorings::TYPE_FNS,
                    'body' => $fns->inn,
                    'success' => 1,
                    'string_result' => 'ИНН найден'
                );

                $this->response = $scoring;
            } else {
                $scoring = array(
                    'body' => '',
                    'success' => 0,
                    'string_result' => 'ИНН не найден'
                );

                $this->response = $scoring;
            }
        } else {
            $this->response['error'] = 'EMPTY_SOME_FIELD';
        }

        $this->json_output();
    }
}

new InnService();