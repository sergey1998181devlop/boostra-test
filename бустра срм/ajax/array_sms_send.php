<?php

chdir('..');

require 'api/Simpla.php';

class SmsUsers extends Simpla
{
    private $response = [
        'success' => false,
        'sms' => '',
        'data' => [],
    ];

    public function __construct()
    {
        parent::__construct();
        $this->run();
        $this->json_output();
    }

    public function run()
    {
        $action = $this->request->post('action', 'string');

        if ($action !== 'sms_arr') {
            return $this->response["sms"] = 'Action is not support!';
        }

        $dataPost = $this->request->post();
        parse_str($dataPost, $dataPost);

        $template = $this->sms->get_template($dataPost['template_id']);
        $users = $this->getUserBalances($dataPost['users_ids']);
        foreach ($users as $user) {
            if ($this->isUserExceedingLimit($user, $template)) {
                $this->response['data'][] = "{$user->user_id} - превышен лимит звонков!";
                continue;
            }

            $resp = $this->sendSms($user, $template);

            $this->logSmsDetails($user, $template, $resp, $dataPost['manager']);

            $this->response['data'][] = $resp[1] > 0 ? "{$user->user_id} - отправлено!" : "{$user->user_id} - ошибка!";
        }

        $this->response['sms'] = "<div>" . implode('<br/>', $this->response['data']) . "</div>";
        $this->response['success'] = true;
    }

    private function isUserExceedingLimit($user, $template)
    {
        $asp = $this->users->getZaimListAsp($user->zaim_number);
        if (empty($asp)) {
            $this->tasks->update_vox_call($user->user_id);
        }

        if (empty($template->check_limit)) {
            return false;
        } elseif (!empty($user->zaim_number) && $user->ostatok_od > 0) {
            return $this->soap->limit_sms($user->zaim_number) != 1;
        } else {
            return true;
        }
    }

    private function sendSms($user, $template)
    {
        // нужный шаблон, для каждого пользователя
        $property = $template->template . '_' . $user->site_id;
        return $this->smssender->send_sms(
            $user->user_phone,
            $template->$property,
            $user->site_id,
            1
        );

    }

    private function logSmsDetails($user, $template, $resp, $managerId)
    {
        $this->sms->add_message([
            'user_id' => $user->user_id,
            'order_id' => 0,
            'phone' => $user->user_phone,
            'message' => $template->template,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $resp[1],
            'delivery_status' => '',
            'send_id' => $resp[0],
        ]);

        $this->changelogs->add_changelog([
            'manager_id' => $managerId,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'send_sms',
            'old_values' => '',
            'new_values' => $template->template,
            'user_id' => $user->user_id,
        ]);

        if (!empty($user->zaim_number) && $user->ostatok_od > 0) {
            $this->soap->send_number_of_sms($user->zaim_number, $user->user_phone, $template->template);
        }
    }

    private function getUserBalances(array $users_ids)
    {
        $query = $this->db->placehold("SELECT 
            u.id as user_id,
            u.phone_mobile as user_phone,
            b.zaim_number,
            b.ostatok_od
            FROM __users u
            LEFT JOIN __user_balance b ON b.user_id = u.id 
            WHERE u.id IN (?@)
            ", $users_ids);
        $this->db->query($query);
        return $this->db->results();
    }

    private function json_output()
    {
        header('Content-type: application/json; charset=UTF-8');
        header('Cache-Control: must-revalidate');
        header('Pragma: no-cache');
        header('Expires: -1');

        echo json_encode($this->response);
    }
}

(new SmsUsers());
