<?php
require_once 'AService.php';

/**
 * Класс отдает в 1С код АСП по договору
 * Class GetAspService
 */
class GetAspService extends AService
{
    public function run()
    {
        $number = $this->request->get('contract_number');

        $this->response = $this->contracts->getAspByContractNumber($number);
        $this->json_output();
    }
}

(new GetAspService())->run();
