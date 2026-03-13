<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '60');

require_once dirname(__FILE__).'/../api/Simpla.php';

class SmsQueueForLeadgid extends Simpla
{
    const LIMIT_SMS = 17;

    public function __construct()
    {
        parent::__construct();

        $this->run();

        echo 'отправлено';
    }

    public function run() {
        $smsCollection = $this->leadgid->get_queue_for_sending_sms(self::LIMIT_SMS);
        foreach ($smsCollection as $sms) {
            $mutex_key = "GET_LOCK('queue_for_sending_sms_$sms->id', 0)";
            $this->db->query("SELECT $mutex_key");
            $result_mutex = $this->db->result();

            if(!empty($result_mutex->{$mutex_key})) {
                $smsCount = (int) $sms->number_of;
                if ($smsCount === 1) {
                    if (isset($sms->firstname)) { // TODO: SMS FIX SEND
                        $this->leadgid->send_leadgid_sms($sms->phone, $sms->message, $sms->site_id);
                    }

                    $this->leadgid->update_leadgid_sms($sms->id, [
                        'number_of' => ($smsCount + 1)
                    ]);
                } elseif ($smsCount === 2) {
                    $this->sendtemplateSms($sms, $smsCount, $this->sms::AUTO_REJECT_TEMPLATE_THIRD);
                } elseif ($smsCount === 3) {
                    $this->sendtemplateSms($sms, $smsCount, $this->sms::AUTO_REJECT_TEMPLATE_FOURTH);
                } elseif ($smsCount === 4) {
                    $this->sendtemplateSms($sms, $smsCount, $this->sms::AUTO_REJECT_TEMPLATE_FIFTH);
                }

                $this->db->query("DO RELEASE_LOCK('queue_for_sending_sms_$sms->id')");
            }
        }
    }

    private function sendTemplateSms(object $sms, int $smsCount, string $templateId): void
    {
        if (!empty($sms->firstname)) {
            $template = $this->sms->get_template($templateId);
            if (!empty($template->status)) {
                $message = $template->template;
                if (str_contains($message, '{{firstname}}')) {
                    $message = strtr($message, [
                        '{{firstname}}' => $sms->firstname,
                    ]);
                }

                $this->leadgid->send_leadgid_sms($sms->phone, $message); // TODO: SMS FIX SEND
            }
        }

        $this->leadgid->update_leadgid_sms($sms->id, ['number_of' => ($smsCount + 1)]);
    }
}

$start = microtime(true);
new SmsQueueForLeadgid();

$end = microtime(true);
$time_worked =  $end - $start;
exit(date('c', $start) . ' - ' . date('c', $end) . ' :: script ' . __FILE__ . ' work ' . $time_worked . '  s.');
