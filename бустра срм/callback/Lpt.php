<?php
session_start();

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');
require_once 'api/Simpla.php';

class LptCallback extends Simpla
{
    public function __construct()
    {
        parent::__construct();

        $this->run();
    }

    private function run()
    {
        $json = $this->request->post('data');

        $json = mb_convert_encoding($json, "UTF-8");

        $data = json_decode($json, true);

        $stage = $data['stage']['name'];

        $this->logging(__METHOD__, 'callback', $json, $stage, 'lpt.txt');

        switch ($stage) {
            case "Новый лид":
                $user_balance_id = ltrim(explode('|', $data['contact']['name'])[1]);

                $lpt = $this->lpt->get_ltp_by_user_balance($user_balance_id);
                if ($lpt & ($lpt->lpt_id == $data['id'])) {
                    $item = [
                        'json' => $json,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    $this->lpt->update_item($data['id'], $item);
                } else {
                    $item = [
                        'status' => 'Новый лид',
                        'json' => $json,
                        'user_balance_id' => $user_balance_id,
                        'lpt_id' => $data['id'],
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->lpt->add_item($item);
                }
                break;

            case "Интерес подтвержден":
                $lpt = $this->lpt->get_lpt_by_lpt_id($data['id']);
                $item = [
                    'status' => 'Интерес подтвержден',
                    'json' => $json,
                    'status_before' => $lpt->status,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->lpt->update_item($data['id'], $item);
                break;

            case "Недозвон":
                $lpt = $this->lpt->get_lpt_by_lpt_id($data['id']);
                $item = [
                    'status' => 'Недозвон',
                    'json' => $json,
                    'tag' => 'Недозвон',
                    'status_before' => $lpt->status,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->lpt->update_item($data['id'], $item);
                break;

            case "Оплата":
                $lpt = $this->lpt->get_lpt_by_lpt_id($data['id']);
                $item = [
                    'status' => 'Оплата',
                    'json' => $json,
                    'status_before' => $lpt->status,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->lpt->update_item($data['id'], $item);
                break;

            case "Договор закрыт":
                $lpt = $this->lpt->get_lpt_by_lpt_id($data['id']);
                $item = [
                    'status' => 'Договор закрыт',
                    'json' => $json,
                    'status_before' => $lpt->status,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->lpt->update_item($data['id'], $item);
                break;

            case "Отказ":
                $lpt = $this->lpt->get_lpt_by_lpt_id($data['id']);
                $item = [
                    'status' => 'Отказ',
                    'json' => $json,
                    'status_before' => $lpt->status,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->lpt->update_item($data['id'], $item);
                break;

            case "Выход за 1-3":
                $lpt = $this->lpt->get_lpt_by_lpt_id($data['id']);
                $item = [
                    'status' => 'Выход за 1-3',
                    'json' => $json,
                    'status_before' => $lpt->status,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->lpt->update_item($data['id'], $item);
                break;

            //case "Бросил Трубку":
            //    //В Своя CRM лид двигается на "Закрыто и не реализовано" ставится тег "Бросил трубку"
            //    $item = [
            //        'status' => 'Закрыто и не реализовано',
            //        'json' => $json,
            //        'tag' => 'Бросил трубку'
            //    ];
            //    $this->lpt->update_item($data['id'], $item);
            //    break;

            //case "СМС Отправить":
            //    //В Своя CRM лид двигается на
            //    // "Отправить СМС с информацией",
            //    // также добовляются кастомные поля
            //    // "Обещаете оплатить в течении 2 дней?",
            //    // "Помощь специалиста", "Сложности", "Готов продлить?"
            //    $item = [
            //        'status' => 'Отправить СМС с информацией',
            //        'json' => $json,
            //        'custom_array' => serialize($data['custom'])
            //    ];
            //    $this->lpt->update_item($data['id'], $item);
            //    break;

            //case "Перезвонить":
            //    //В Своя CRM лид двигается на "Перезвонить менеджеру"
            //    // также добовляются кастомные поля
            //    // "Обещаете оплатить в течении 2 дней?",
            //    // "Помощь специалиста", "Сложности", "Готов продлить?"
            //    $item = [
            //        'status' => 'Перезвонить менеджеру',
            //        'json' => $json,
            //        'custom_array' => serialize($data['custom'])
            //    ];
            //
            //    $this->lpt->update_item($data['id'], $item);
            //    break;

            //case "Нулевой долг":
            //    $item = [
            //        'status' => 'Нулевой долг',
            //        'json' => $json,
            //        'tag' => 'Нулевой долг'
            //    ];
            //
            //    $this->lpt->update_item($data['id'], $item);
            //    break;

            //case "Выход за 1-3":
            //    $item = [
            //        'status' => 'Выход за 1-3',
            //        'json' => $json,
            //        'tag' => 'Выход за 1-3'
            //    ];
            //
            //    $this->lpt->update_item($data['id'], $item);
            //    break;

            default:
                $item = [
                    'json' => $json,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $this->lpt->update_item($data['id'], $item);
                break;
        }
    }
}

new LptCallback();