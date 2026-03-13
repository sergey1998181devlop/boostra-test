<?php

require_once 'Simpla.php';

class Megafon extends Simpla
{
    const LOGIN = 'boostra.ru';
    const PASSWORD = 'wxJXmEff';
    const URL = 'https://hub.megafon.ru/';

    /**
     * @param $user_id
     * @param $manager_id
     * @param $template_id
     * @param $balance
     * @param $limit
     * @return string[]
     */

    public function sendMessage($user_id, $manager_id, $template_id, $balance, $limit = true): array
    {

        if ($limit && !$this->checkLimit($user_id)) {
            return ['error' => 'limit_sms'];
        }

        $url = self::URL . 'messaging/v1/send';
        $user = $this->users->get_user($user_id);
        $template = $this->sms->get_template($template_id);

        $data = [
            'scenario' => [
                [
                    "channel" => "sms",
                    "recipient" => [
                        "type" => "MSISDN",
                        "value" => $user->phone_mobile
                    ],
                    "sender" => "MegaHubTest",
                    "text" => $template->template,
                ]
            ]
        ];

        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response = $this->send(
            $url,
            $data,
        );
        $response = json_decode($response);
        $this->addMessage(
            $user,
            $template,
            $response,
            $limit
        );
        $this->addLog(
            $user,
            $manager_id,
            $template
        );
        $this->addComment(
            $user_id,
            $manager_id,
            $limit,
            $response,
            $template
        );
        $this->send1c(
            $balance,
            $user,
            $template

        );
        return !empty($response->state) && $response->state == 'ACCEPTED' ?  ['success' => 'true'] : ['success' => 'false'];
    }

    /**
     * @param $user_id
     * @return bool
     */
    private function checkLimit($user_id): bool
    {
        $balance = $this->users->get_user_balance($user_id);
        $expired_day = date("Y-m-d", strtotime($balance->payment_date));;
        $messageType = "ccprolongation";
        $currentDate = date("Y-m-d");

        $query = $this->db->placehold("SELECT
        SUM(CASE WHEN DATE(created) = '$currentDate' AND DATE(created) > '$expired_day' THEN 1 ELSE 0 END) AS daily_count,
        SUM(CASE WHEN YEARWEEK(DATE(created)) = YEARWEEK(NOW()) AND DATE(created) > '$expired_day' THEN 1 ELSE 0 END) AS weekly_count,
        SUM(CASE WHEN MONTH(DATE(created)) = MONTH(NOW()) AND YEAR(DATE(created)) = YEAR(NOW()) AND DATE(created) > '$expired_day' THEN 1 ELSE 0 END) AS monthly_count
    FROM s_sms_messages
    WHERE user_id = $user_id AND type = '$messageType' AND send_status = 'ACCEPTED'");
        $this->db->query($query);
        $result = $this->db->result();
        $asp = $this->users->getZaimListAsp($balance->zaim_number);

        if (empty($asp) && (($result->daily_count >= 2) || $result->weekly_count >= 4 || $result->monthly_count >= 16)) {
            return false;
        } elseif (!empty($asp) && (($result->daily_count >= 5) || $result->weekly_count >= 25 || $result->monthly_count >= 40)) {
            return false;
        }

        return true;

    }


    private function send($url, $data)
    {
        $username = self::LOGIN;
        $password = self::PASSWORD;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
            'Authorization: Basic ' . base64_encode($username . ":" . $password)
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            error_log('Curl error: ' . curl_error($ch));
        }

        curl_close($ch);
        return $response;
    }

    /**
     * @param $user
     * @param $template
     * @param $response
     * @param $limit
     * @return void
     */
    private function addMessage($user, $template, $response, $limit)
    {
        $type = 'ccprolongation';
        if (!$limit) {
            $type = 'ccprolongation_zero';
        }
        $this->sms->add_message(array(
            'user_id' => $user->id,
            'order_id' => 0,
            'phone' => $user->phone_mobile,
            'message' => $template->template,
            'created' => date('Y-m-d H:i:s'),
            'send_status' => $response->state || '',
            'delivery_status' => '',
            'send_id' => $response->txId || "",
            'type' => $type
        ));
    }

    /**
     * @param $user
     * @param $manager_id
     * @param $template
     * @return void
     */
    private function addLog($user, $manager_id, $template)
    {
        $this->changelogs->add_changelog(array(
            'manager_id' => $manager_id,
            'created' => date('Y-m-d H:i:s'),
            'type' => 'send_sms',
            'old_values' => '',
            'new_values' => $template->template,
            'user_id' => $user->id,
        ));
    }

    /**
     * @param $user_id
     * @param $manager_id
     * @param $limit
     * @param $response
     * @param $template
     * @return void
     */
    private function addComment($user_id, $manager_id, $limit, $response, $template)
    {
        $type = 'ccprolongation';
        if ($limit) {
            $type = 'ccprolongation_zero';
        }

        if (empty($response->state) && ($response->state == 'DELIVERED' || $response->state == 'SEEN' || $response->state == 'EXPIRED' || $response->state == 'ACCEPTED')) {
            $txt = "Отправлено сообщение sms $template->template";
        } else {
            $txt = "Сообщение  sms $template->template не было отправлено";
        }
        $comment = array(
            'manager_id' => $manager_id,
            'user_id' => $user_id,
            'block' => $type,
            'text' => $txt,
            'created' => date('Y-m-d H:i:s'),
        );

        $this->comments->add_comment($comment);
    }

    private function send1c($balance,$user,$template)
    {
        if ((!empty($balance->zaim_number) && $balance->ostatok_od > 0)){
            $this->soap->send_number_of_sms($balance->zaim_number, $user->phone_mobile, $template->template);
        }


    }

}
