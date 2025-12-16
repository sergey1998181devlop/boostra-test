<?php

require_once dirname(__DIR__) . '/api/Simpla.php';

class PartnerPhoneApiCron extends Simpla
{
    private int $offset = 0;
    private int $limit = 500;

    public function __construct()
    {
        parent::__construct();

        ini_set('max_execution_time', 300);

        $this->initLogs();
    	$this->run();
    }

    /**
     * @return void
     */
    private function initLogs()
    {
        error_reporting(E_ERROR);
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', $this->config->root_dir . 'logs/partner_phone_cron_error.log');
    }

    private function run()
    {
        $time_start = microtime(true);
        $this->open_search_logger->create("Partner phone cron started", ['start' => date('Y-m-d H:i:s')], 'partner_phone_cron');

        do {
            $items = $this->getItems();
            foreach ($items as $item) {
                $this->open_search_logger->create("Обрабатывается телефон", ['phone' => $item->phone, 'id' => $item->id], 'partner_phone_cron');
                $this->processPhone($item);
            }
        } while (!empty($items));


        $time_end = microtime(true);
        $exec_time = $time_end - $time_start;
        $this->open_search_logger->create("Partner phone cron ended", ['end' => date('Y-m-d H:i:s'), 'time_execute_seconds' => $exec_time], 'partner_phone_cron');
    }

    /**
     * Получаем записи которые необходимо обработать
     * @return mixed
     */
    private function getItems()
    {
        $items = $this->phone_partner_model->getCronWaitingPhone($this->limit, $this->offset, $this->checkNewClientAvailable());
        $this->offset += $this->limit;
        return $items;
    }

    /**
     * Обработка телефонов
     * @param object $item
     * @return void
     */
    private function processPhone(object $item)
    {
        try {
            switch ($item->client_type) {
                case PhonePartnerModel::CLIENT_TYPE_OLD:
                    $this->actionOldClient($item);
                    break;
                case PhonePartnerModel::CLIENT_TYPE_NEW:
                    $this->actionNewClient($item);
                    break;
            }
        } catch (Exception $e) {
            error_log("Exception:" . $e->getMessage());
            $this->open_search_logger->create("Обработка телефона завершено с ошибкой", ['phone' => $item->phone, 'error' => [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]], 'partner_phone_cron');

            $this->phone_partner_model->updateItem($item->id, ['cron_status' => PhonePartnerModel::CRON_STATUS_ERROR, 'cron_finished_at' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Действие нового пользователя
     * @param object $item
     * @return void
     */
    private function actionOldClient(object $item)
    {
        $user_id = $this->users->get_phone_user($item->phone);
        $this->orders_auto_approve->addAutoApproveNK(
            [
                'user_id' => $user_id,
                'status' => $this->orders_auto_approve::STATUS_CRON_NEW,
                'date_cron' => date('Y-m-d H:i:s'),
            ]
        );

        $this->phone_partner_model->updateItem($item->id, ['cron_status' => PhonePartnerModel::CRON_STATUS_SUCCESS, 'cron_finished_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Действие со старым пользователем
     * @param object $item
     * @return void
     */
    private function actionNewClient(object $item)
    {
        if (!$this->checkNewClientAvailable()) {
            return;
        }

        $msg = iconv('utf-8', 'cp1251', trim($this->settings->sms_template_phone_partner['template']));
        $send_id = $this->notify->send_sms($item->phone, $msg);

        if (is_numeric($send_id)) {
            $this->sms->add_message(
                [
                    'phone' => $item->phone,
                    'message' => trim($this->settings->sms_template_phone_partner['template']),
                    'send_id' => $send_id,
                    'type' => 'partner_phone_api',
                ]
            );

            $this->phone_partner_model->updateItem($item->id, ['cron_status' => PhonePartnerModel::CRON_STATUS_SUCCESS, 'cron_finished_at' => date('Y-m-d H:i:s')]);
        } else {
            $this->open_search_logger->create("Смс не было отправлено", ['phone' => $item->phone, 'error' => $send_id], 'partner_phone_cron');
            $this->phone_partner_model->updateItem($item->id, ['cron_status' => PhonePartnerModel::CRON_STATUS_SMS_ERROR, 'cron_finished_at' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Возможность обработать новых клиентов
     * @return bool
     */
    public function checkNewClientAvailable(): bool
    {
        return !empty($this->settings->sms_template_phone_partner['status']) && !empty($this->settings->sms_template_phone_partner['template']);
    }
}

new PartnerPhoneApiCron();
