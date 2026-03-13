<?php

error_reporting(-1);
ini_set('display_errors', 'On');

chdir('..');

require 'api/Simpla.php';

class SmsUsersCron extends Simpla
{
    const PERIODS = ['zero', '-1', '-2', '-3', '-4', '-5'];
    const PERIOD_ZERO = ['zero'];
    const TYPE = 'sms-prolongation';
    const MANAGER_ID = 50;

    public function __construct()
    {
        $this->run();
    }

    /**
     * Run the SMS sending process
     *
     * @return void
     */
    public function run()
    {
        $date = date('Y-m-d');
        $template = $this->sms->get_templates(['type' => self::TYPE]);
        $periods = empty($template[0]->status) ? self::PERIOD_ZERO : self::PERIODS;

        foreach ($periods as $period) {
            $tasks = $this->tasks->get_tasks([
                'period' => $period,
                'task_date_from' => $date,
                'task_date_to' => $date,
                'closed' => true
            ]);

            foreach ($tasks as $task) {
                $user = (object)['id' => $task->user_id, 'phone_mobile' => $task->phone_mobile];
                $zaim = (object)['zaim_number' => $task->zaim_number];
                $smsData = $this->smsShortLink->run($user, $zaim, self::TYPE, null, self::MANAGER_ID, 'cron');
                $this->logSmsDetails($task, $smsData['template'], $smsData['resp']);
                $this->insertSms([
                    'sent' => $smsData['resp'][1] > 0 ? 1 : 0,
                    'period' => $period,
                    'phone' => $task->phone_mobile,
                    'user_id' => $task->user_id,
                    'date' => $date
                ]);
            }

            $this->updateSmsTable([
                'date' => $date,
                'period' => $period
            ]);
        }
    }

    /**
     * Log SMS details
     *
     * @param $task
     * @param $template
     * @param $resp
     * @return void
     */
    private function logSmsDetails($task, $template, $resp)
    {
        $this->sms->add_message([
            'user_id' => $task->user_id,
            'order_id' => 0,
            'phone' => $task->phone_mobile,
            'message' => $template,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $resp[1],
            'delivery_status' => '',
            'send_id' => $resp[0]
        ]);

        $this->changelogs->add_changelog([
            'manager_id' => self::MANAGER_ID,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'send_sms',
            'old_values' => '',
            'new_values' => $template,
            'user_id' => $task->user_id
        ]);

        if (!empty($task->zaim_number) && $task->ostatok_od > 0) {
            $this->soap->send_number_of_sms($task->zaim_number, $task->phone_mobile, $template);
        }
    }

    /**
     * Insert SMS data into the database
     *
     * @param array $data
     * @return void
     */
    private function insertSms(array $data): void
    {
        $query = $this->db->placehold("INSERT INTO __pr_tasks_sms_daily SET ?%", $data);
        $this->db->query($query);
    }

    /**
     * Update SMS table
     *
     * @param array $data
     * @return void
     */
    private function updateSmsTable(array $data): void
    {
        $query = $this->db->placehold("INSERT INTO __pr_tasks_sms SET ?%", $data);
        $this->db->query($query);
    }
}


(new SmsUsersCron());
