<?php

require_once 'Simpla.php';

class NeomaniPostback extends Simpla
{
    /**
     * Тип постбека о выдаче
     */
    public const TYPE_SALE = 'sale';

    /**
     * Токен к апи LW (ранее leads.su)
     */
    public const LEADS_SU_NEO_TOKEN = 'cc7a35da4b6cc3b05b92b2abd7e24caa';

    /**
     * Новая логика постбеков для Neomani
     * Здесь реализуйте отправки по utm_source, когда будут требования.
     */
    public function sendSaleOrder($order): void
    {
        if (!empty($order->utm_source)) {
            switch ($order->utm_source) {
                case 'leads_neo':
                    $this->sendSaleLeadsSu($order);
                    break;
                case 'LG_neo':
                    $this->sendSaleLeadGid($order);
                    break;
                case 'tech_neo':
                    $this->sendSaleLeadstech($order);
                    break;
                case 'banki_neo':
                    $this->sendSaleBankiru($order);
                    break;
                case 'sravni_neo':
                    $this->sendSaleSravnileads($order);
                    break;
            }
        }
    }

    /**
     * Отправка выдача LW (ранее leads.su)
     * @param $order
     * @return void
     */
    private function sendSaleLeadsSu($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU_NEO_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=approved';
            $this->post_back->sendRequest($link_lead, (int)$order->order_id, 'leads_su_neo.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Отправка выдача leadgid
     * @param $order
     * @return void
     */
    private function sendSaleLeadGid($order)
    {
        if ($order->have_close_credits == 0) {
            $link_leadgid = 'https://api.leadgid.com/autostats/v1/postbacks?offer_id=7012&transaction_id='.$order->click_hash.'&adv_sub='.$order->order_id.'&action=update&status=approved';
            $this->post_back->sendRequest($link_leadgid, (int)$order->order_id, 'leadgid_neo.txt', self::TYPE_SALE, true, 0);
        }
    }

    /**
     * Отправка выдача leadstech
     * @param $order
     * @return void
     */
    private function sendSaleLeadstech($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://t.leads.tech/add-conversion/?click_id='.$order->click_hash.'&goal_id=3&status=1&transaction_id='.$order->order_id.'&sumConfirm=1941';
            $this->post_back->sendRequest($link_lead, (int)$order->order_id, 'leadstech_neo.txt', self::TYPE_SALE, true, '');
        }
    }

    /**
     * Отправка выдача bankiru
     * @param $order
     * @return void
     */
    private function sendSaleBankiru($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead =  'https://tracking.banki.ru/GPJs3?adv_sub1='.$order->order_id.'&adv_sub2=&adv_sub3=&adv_sub4=&adv_sub5=&transaction_id='.$order->click_hash;
            $this->post_back->sendRequest($link_lead, (int)$order->order_id, 'bankiru_neo.txt', self::TYPE_SALE, true, 0);
        }
    }

    /**
     * Отправка выдача sravni
     * @param $order
     * @return void
     */
    private function sendSaleSravnileads($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://goto.startracking.ru/api/v1/postback?goal_name=issued&adv_id=1005&transaction_id='.$order->click_hash.'&adv_sub='.$order->order_id.'&offer_id=2683';
            $this->post_back->sendRequest($link_lead, (int)$order->order_id, 'sravni_neo.txt', self::TYPE_SALE, true);
        }
    }
}