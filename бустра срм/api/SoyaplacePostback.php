<?php

require_once 'Simpla.php';

class SoyaplacePostback extends Simpla
{
    /**
     * Тип постбека о выдаче
     */
    public const TYPE_SALE = 'sale';


    /**
     * Токен к апи LW (ранее leads.su)
     */
    public const LEADS_SU_SOY_TOKEN = 'c91b805990f96fd0df0b41ea40d37ab0';


    /**
     * Новая логика постбеков для Soyaplace
     * Здесь реализуйте отправки по utm_source, когда будут требования.
     */
    public function sendSaleOrder($order): void
    {
        if (!empty($order->utm_source)) {
            switch ($order->utm_source) {
                case 'leads_soy':
                    $this->sendSaleLeadsSu($order);
                    break;
                case 'LG_soy':
                    $this->sendSaleLeadGid($order);
                    break;
                case 'tech_soy':
                    $this->sendSaleLeadstech($order);
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
            $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU_SOY_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=approved';
            $this->post_back->sendRequest($link_lead, (int)$order->order_id, 'leads_su_soy.txt', self::TYPE_SALE, true);
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
            $link_leadgid = 'https://api.leadgid.com/autostats/v1/postbacks?offer_id=7053&transaction_id='.$order->click_hash.'&adv_sub='.$order->order_id.'&action=update&status=approved';
            $this->post_back->sendRequest($link_leadgid, (int)$order->order_id, 'leadgid_soy.txt', self::TYPE_SALE, true, 0);
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
            $link_lead = 'https://t.leads.tech/add-conversion/?click_id='.$order->click_hash.'&goal_id=3&status=1&transaction_id='.$order->order_id.'&sumConfirm=3398';
            $this->post_back->sendRequest($link_lead, (int)$order->order_id, 'leadstech_soy.txt', self::TYPE_SALE, true, '');
        }
    }
}
