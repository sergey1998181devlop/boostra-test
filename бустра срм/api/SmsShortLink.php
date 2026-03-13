<?php

chdir('..');
class SmsShortLink extends Simpla
{
    public const SHORT_LINK_TYPE_LK = 'lk';

    function run($user,$zaim,$type,$orderId,$manager,$cron = false,$phone = null)
    {
        $phone = $phone ?: $user->phone_mobile;
        $code = $this->getCode($user->id, $phone, $zaim->zaim_number, $type,$orderId);
        $template = $this->sendSms($phone, $type, $code,$cron, $user->site_id);
        if ($cron) {
            return $template;
        }
        $this->addComment($manager, $user->id, $template, $orderId);
    }

    private function getCode($userId, $phone, $zaimNumber, $type,$orderId)
    {
        $code = $this->orders->getShortLink($userId, $zaimNumber, $type);

        if (empty($code)) {
            return $this->generateLink($userId, $phone, $zaimNumber, $type, $orderId);
        }

        return $code;
    }

    /**
     * @param $userId
     * @param $phone
     * @param $zaimNumber
     * @param $type
     * @param $orderId
     * @return string
     */
    public function generateLink($userId, $phone, $zaimNumber, $type, $orderId): string
    {
        $code = Helpers::generateLink();
        $count = $this->orders->getLinkExists($code);

        while ($count > 0) {
            $code = Helpers::generateLink();
            $count = $this->orders->getLinkExists($code);
        }

        $this->orders->add_short_link([
            'link' => $code,
            'user_id' => $userId,
            'phone' => $phone,
            'zaim_number' => $zaimNumber,
            'active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'type' => $type,
            'order_id' => $orderId
        ]);

        return $code;
    }

    /**
     * @param array $where
     * @return array|bool|null
     */
    public function getShortLink(array $where)
    {
        $conditions = [];
        foreach ($where as $condition => $value) {
            $conditions[] = $this->db->placehold("`$condition` = ?", $value);
        }

        $conditions = implode(' AND ', $conditions);
        $this->db->query("SELECT * FROM __short_link WHERE $conditions ORDER BY id DESC");
        return $this->db->results();
    }

    private function sendSms($phone, $type, $code,$cron, $site_id)
    {
        $template = $this->sms->get_templates(['type' => $type]);
        $property = $template[0]->template . '_' . $site_id;
        $text = $template[0]->$property . ' ' . $this->config->front_url . '/pay/' . $code;

        // TODO: [временно, далее будет ссылка на переход в лк]
        if (($site_id != $this->organizations::SITE_BOOSTRA) && in_array($type, ['sms-lk', 'sms-prolongation', 'sms-payment'])){
            $text = 'https://' . $this->sites->getDomainBySiteId($site_id) . '/login';
        }
        $resp = $this->smssender->send_sms(
            $phone,
            $text,
            $site_id,
            1
        );
        $result = $text;
        if ($cron) {
            $result = ['resp' => $resp,'template'=> $text];
        }
        return $result;
    }

    private function addComment($manager, $userId, $template, $orderId)
    {
        $this->comments->add_comment([
            'manager_id' => $manager,
            'user_id' => $userId,
            'order_id' => $orderId,
            'block' => 'prolongation',
            'text' => $template,
            'created' => date('Y-m-d H:i:s'),
        ]);
    }

}