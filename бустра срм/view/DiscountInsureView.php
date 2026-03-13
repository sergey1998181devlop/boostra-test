<?php

//ini_set('display_errors', 1);
//error_reporting(E_ALL);

require_once dirname(__DIR__) . '/PHPExcel/Classes/PHPExcel.php';
require_once 'View.php';

class DiscountInsureView extends View
{
    public function __construct()
    {
        parent::__construct();
        $action = $this->request->get('action');

        if (method_exists(self::class, $action)) {
            $this->{$action}();
        }
    }

    public function fetch()
    {
        $discounts = $this->discount_insure->getDiscounts();

        $this->design->assign('discounts', $discounts);

        return $this->design->fetch('discount_insure.tpl');
    }

    /**
     * Добавляет новую акцию страховки
     * @return void
     */
    private function addDiscount()
    {
        $response = [];
        $discount_data = [];

        $prices = $this->request->post('prices');
        $date_discount = $this->request->post('date_discount');
        $discount_data['status'] = $this->request->post('status', 'integer');

        $date_discount_array = array_map('trim', explode('-', $date_discount));
        $discount_data['date_start'] = str_replace('.', '-', $date_discount_array[0]);
        $discount_data['date_end'] = str_replace('.', '-', $date_discount_array[1]);

        function sort_prices($a, $b)
        {
            $price_a = $a['price'] ?? 0;
            $price_b = $b['price'] ?? 0;

            return $price_b <=> $price_a;
        }

        uasort($prices, "sort_prices");

        $discount_data['prices'] = serialize($prices);
        $response['success'] = $this->discount_insure->addDiscount($discount_data);

        $this->response->json_output($response);
    }

    /**
     * Удаляем скидку
     * @return void
     */
    private function deleteDiscount()
    {
        $response = [];

        $id = $this->request->post('id', 'integer');
        $response['success'] = $this->discount_insure->deleteDiscount($id);

        $this->response->json_output($response);
    }

    /**
     * Обновляет скидку
     * @return void
     */
    private function updateDiscount()
    {
        $response = [];
        $id = $this->request->get('id', 'integer');

        $prices = $this->request->post('prices');
        $date_discount = $this->request->post('date_discount');
        $discount_data['status'] = $this->request->post('status', 'integer');

        $date_discount_array = array_map('trim', explode('-', $date_discount));
        $discount_data['date_start'] = str_replace('.', '-', $date_discount_array[0]);
        $discount_data['date_end'] = str_replace('.', '-', $date_discount_array[1]);

        function sort_prices($a, $b)
        {
            $price_a = $a['price'] ?? 0;
            $price_b = $b['price'] ?? 0;

            return $price_b <=> $price_a;
        }

        uasort($prices, "sort_prices");

        $discount_data['prices'] = serialize($prices);

        $response['success'] = $this->discount_insure->updateDiscount($id, $discount_data);

        $this->response->json_output($response);
    }

    /**
     * Загружает номера телефонов
     * @return void
     */
    private function uploadPhones()
    {
        $response = [];
        $file = $this->request->files('upload');
        $id = $this->request->get('id', 'integer');

        $this->discount_insure->deletePhones($id);

        $objPHPExcel = PHPExcel_IOFactory::load($file['tmp_name']);
        $total_rows = $objPHPExcel->setActiveSheetIndex()->getHighestRow();

        $data_discount_phone = [
            'discount_insurer_id' => $id,
            'phone' => ''
        ];

        for ($i= 2; $i <= $total_rows; $i++)
        {
            $phone = $objPHPExcel->getActiveSheet()->getCell('A'.$i )->getValue();

            if (empty($phone)) {
                continue;
            }

            // уберем все кроме цифр
            $phone_regex = preg_replace('/[^\d]/', '', $phone);
            // добавим 7 можно сделать (+7)
            $phone_formatted = preg_replace('/^(\d)?/i', '7', $phone_regex);
            $data_discount_phone['phone'] = $phone_formatted;

            $result = $this->discount_insure->addDiscountPhone($data_discount_phone);
            if(!$result) {
                $response['errors']['error_phone'][] = $phone;
            }
        }

        if (empty($response['errors'])) {
            $response['success'] = true;
        } else {
            $response['errors']['error_message'] = 'Ошибка при добавлении телефонов в акцию: ' . $id;
            $response['errors']['error_discount_id'] = $id;
        }

        $this->response->json_output($response);
    }

    /**
     * Выводит список телефонов акции
     * @return void
     */
    private function getPhones()
    {
        $id = $this->request->get('id', 'integer');
        $items_per_page = 50;

        $current_page = max(1, $this->request->get('page', 'integer'));
        $this->design->assign('current_page_num', $current_page);

        $filter_data['page'] = $current_page;
        $filter_data['limit'] = $items_per_page;

        $phones_total = $this->discount_insure->getTotalPhonesByDiscountId($id);
        $phones = $this->discount_insure->getPhonesByDiscountId($id, $filter_data);

        $pages_num = ceil($phones_total/$items_per_page);
        $this->design->assign('total_pages_num', $pages_num);
        $this->design->assign('total_orders_count', $phones_total);

        $this->design->assign('phones', $phones);
        $response =  $this->design->fetch('discount_insurer_phones.tpl');

        $this->response->html_output($response);
    }

    /**
     * Удаляет телефон
     * @return void
     */
    private function deletePhone()
    {
        $response = [];
        $id = $this->request->post('id', 'integer');

        $response['success'] = $this->discount_insure->deletePhone($id);

        $this->response->json_output($response);
    }
}
