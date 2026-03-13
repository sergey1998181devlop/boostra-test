<?php

require_once 'View.php';
require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';

class ClientsView extends View
{
    public function fetch()
    {
        if ($this->request->get('downloadBlockedAdvSmsUsers')) {
            $this->downloadBlockedAdvSmsUsers();
        }

        if ($this->request->get('uploadBlockedAdvSmsUsers')) {
            $this->uploadBlockedAdvSmsUsers();
        }

        if ($this->request->get('uploadOverdueHideUserService')) {
            $this->uploadOverdueHideUserService();
        }

        if (!in_array('clients', $this->manager->permissions))
        	return $this->design->fetch('403.tpl');
        
        $items_per_page = 20;

    	$filter = array();

        if (!($sort = $this->request->get('sort', 'string')))
        {
            $sort = 'id_desc';
        }
        $filter['sort'] = $sort;
        $this->design->assign('sort', $sort);
        
        if ($search = $this->request->get('search'))
        {
            $filter['search'] = array_filter($search);
            $this->design->assign('search', array_filter($search));
        }
        
		$current_page = $this->request->get('page', 'integer');
		$current_page = max(1, $current_page);
		$this->design->assign('current_page_num', $current_page);

        $site_id = $this->request->get('site_id');
        if (!empty($site_id)) {
            $filter['site_id'] = $site_id;
        }
        $this->design->assign('site_id', $site_id ?: $this->organizations::SITE_BOOSTRA);

		$clients_count = $this->users->count_users($filter);
		
		$pages_num = ceil($clients_count/$items_per_page);
		$this->design->assign('total_pages_num', $pages_num);
		$this->design->assign('total_orders_count', $clients_count);

		$filter['page'] = $current_page;
		$filter['limit'] = $items_per_page;
    	

        if ($this->manager->id == 167) {
            $filter['id'] = 594201;
        }
        $clients = $this->users->get_users($filter);

//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($clients);echo '</pre><hr />';
        
        $this->design->assign('clients', $clients);
        
        return $this->design->fetch('clients.tpl');
    }

    /**
     * Выгрузка пользователей которым заблокировали рекламные смс
     * @return void
     */
    private function downloadBlockedAdvSmsUsers()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'On');

        $filename = 'files/reports/' . date('Y-m-d') . '_blocked_adv_sms_clients.xls';
        $sheet_name = 'Список клиентов';

        $writer = new XLSXWriter();
        $fields_name = [
            'Id клиента',
            'Телефон',
            'Дата блокировки'
        ];
        $header = array_combine($fields_name, array_fill(0, count($fields_name), 'string'));
        $writer->writeSheetHeader($sheet_name, $header);

        $items = $this->blocked_adv_sms->getItems();
        foreach ($items as $item) {
            $writer->writeSheetRow($sheet_name, [$item->id, $item->phone, $item->created_at]);
        }

        $writer->writeToFile($this->config->root_dir . '/' . $filename);
        header('Location:' . $this->config->root_url . '/' . $filename);
        exit;
    }

    private function uploadBlockedAdvSmsUsers()
    {
        $file = $this->request->files('upload');
        $objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name']);
        $total_rows = $objPHPExcel->setActiveSheetIndex()->getHighestRow();
        $sms_type = 'adv';

        for ($i= 1; $i <= $total_rows; $i++)
        {
            $phone = (string)$objPHPExcel->getActiveSheet()->getCell('A'.$i )->getValue();

            if (empty($phone)) {
                continue;
            }
            // уберем все кроме цифр
            $phone_regex = preg_replace('/[^\d]/', '', $phone);
            if ($user_id = $this->users->get_phone_user($phone_regex))
            {
                $this->blocked_adv_sms->addItem(compact('user_id', 'sms_type', 'phone'));
            }
        }

        $this->request->json_output(['success' => true]);
    }

    /**
     * @return void
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     */
    private function uploadOverdueHideUserService()
    {
        $file = $this->request->files('upload');
        $objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name']);
        $total_rows = $objPHPExcel->setActiveSheetIndex()->getHighestRow();

        for ($i= 1; $i <= $total_rows; $i++)
        {
            $phone = trim($objPHPExcel->getActiveSheet()->getCell('A'.$i )->getValue());
            // уберем все кроме цифр
            $phone_regex = preg_replace('/[^\d]/', '', $phone);

            if (empty($phone_regex)) {
                continue;
            }

            $this->users->addOverdueHideUserService(['phone' => $phone_regex]);
        }

        $this->request->json_output(['success' => true]);
    }
}
