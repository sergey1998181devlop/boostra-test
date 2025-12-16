<?php
require_once 'Simpla.php';

/**
 * Class PostBack
 * Класс для работы с постбеками
 */
final class PostBack extends Simpla
{

    /**
     * Тип постбека об отказе заявки
     */
    public const TYPE_REJECT = 'reject';

    /**
     * Тип постбека о новой поступившей заявки
     */
    public const TYPE_HOLD = 'hold';

    public const PARTNER = 'boostra';

    /**
     * Массив партнеров, которые исключаются из проверки на новый займ у клиента
     */
    public const REPEAT_UTM_SOURCE = ['unicom24'];

    /**
     * Токен к апи LW (ранее leads.su)
     */
    public const LEADS_SU_TOKEN = '3c37bb58f2f592d3a0574f8e91ef6c4d';

    public const LEADS_SU2_TOKEN = '0d77bd893bb34806d9640432e1bcfa8c';
    public const AKVA_LEADS_SU_TOKEN = '2f35fde3d857b9f4ee915a9092c0fbec';

    /*
     * Токен к апи alliance.ru
     */
    private const ALLIANCE_TOKEN = '108739fa4e711aa1a683b996923d2a18';

    private const BANKIROS_TOKEN = 'TiQQLxspZ9RZpNFE7';

    private const TBANK_TOKEN = 'zzAM3VZu7jgu8aA3mzCcDNKXv3rtRm6UYefXt00a';

    /**
     * Статичные данные для leadstech-api
     */
    public const LEADSTECH_API = [
        'goal_id' => 79,
    ];

    /**
     * Отказ akva_leads.su
     * @param $order
     * @return void
     */
    private function sendRejectToAkvaLeadsSu($order)
    {
        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::AKVA_LEADS_SU_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->id . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->id, 'akva_leads_su.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ
     * @param $order
     * @return void
     */
    public function sendRejectToAkvaSravni($order)
    {
        $link_lead = 'https://sravni.go2cloud.org/aff_goal?a=lsr&goal_name=reject&adv_id=973&transaction_id='.$order->click_hash.'&adv_sub='.$order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'akva_sravni.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ по заявке для click2.money
     * @param $order
     * @return void
     */
    private function sendRejectToC2M($order)
    {
        $link_lead = 'https://c2mpbtrck.com/cpaCallback?cid=' . $order->click_hash  . '&partner=' . self::PARTNER . '&action=reject&lead_id=' . $order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'c2m.txt', 'reject', false, '');
    }

    /**
     * Отказ leadstech
     * @param $order
     * @return void
     */
    private function sendRejectToleadstech($order)
    {
        $link_lead = 'https://offers.leads.tech/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=2&transaction_id=' . $order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'leadstech.txt', 'reject', false, '');
    }

    /**
     * Отказ cityads
     * @param $order
     * @return void
     */
    private function sendRejectToCityAds($order)
    {
        $link_lead = 'https://postback.cityads.com/service/postback?Campaign_secret=0dqggp&order_id=' . $order->id . '&click_id=' . $order->click_hash . '&status=cancel';
        $this->sendRequest($link_lead, (int)$order->id, 'cityads.txt', 'reject', false);
    }

    /**
     * Отказ Unicom24
     * @param $order
     * @return void
     */
    private function sendRejectToUnicom24($order)
    {
        $link_lead = 'https://unicom24.ru/offer/postback/' . $order->click_hash . '/?status=reject&external_id=' . $order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'unicom24.txt', 'reject', false, '');
    }

    /**
     * Отказ LW (ранее leads.su)
     * @param $order
     * @return void
     */
    private function sendRejectToLeadsSu($order)
    {
        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->id . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->id, 'leads_su.txt', 'reject', false);
    }

    /**
     * Отказ LW (ранее leads.su) - версия 2
     * @param $order
     * @return void
     */
    private function sendRejectToLeadsSu2($order)
    {
        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU2_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->id . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->id, 'leads_su.txt', 'reject', false);
    }

    /**
     * Отказ leadtarget.ru
     * @param $order
     * @return void
     */
    private function sendRejectToLeadtarget($order)
    {
        $link_lead = 'http://service.leadtarget.ru/postback/?application=' . $order->id . '&click_id=' . $order->click_hash . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->id, 'leadtarget_ru.txt', 'reject', false);
    }

    /**
     * Отказ alliance
     * @param $order
     * @return void
     */
    private function sendRejectToAlliance($order)
    {
        $link_lead = 'https://alianscpa.ru/postback/get/partners?token=' . self::ALLIANCE_TOKEN
            . '&from=bystra&status=3&click_id=' . $order->click_hash . '&sub1=' . $order->utm_medium;
        $this->sendRequest($link_lead, (int)$order->id, 'alliance.txt', 'reject', false);
    }

    /**
     * Отказ kosmos
     * @param $order
     * @return void
     */
    private function sendRejectToKosmosleads($order)
    {
        $link_lead = 'https://tr.ksms.pro/a3b405f/postback?subid=' . $order->click_hash . '&status=rejected&tid=' . $order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'kosmos.txt', 'reject', false);
    }

    /**
     * Отказ по заявке для leadcraft
     * @param $order
     * @return void
     */
    private function sendRejectToLeadCraft($order)
    {
        if (!$this->hasPostBackByOrderId((int)$order->id, self::TYPE_REJECT)) {
            $reviseDate = date("Y-m-d");
            $link_lead = 'https://api.leadcraft.ru/v1/advertisers/actions?token=b3ed1da5f51b24e8abb0851f7206357a4e47468eb647364fd56087121694c6be&actionID=270&status=cancelled&clickID=' . $order->click_hash . '&advertiserID=' . $order->id . '&reviseDate=' . $reviseDate;
            $this->sendRequest($link_lead, (int)$order->id, 'leadcraft.txt', 'reject', false, '');
        }
    }
    /**
     * Отказ vbr
     * @param $order
     * @return void
     */
    private function sendRejectToVbrleads($order)
    {
        $link_lead = 'https://adv.vbr.ru/api/v2/postback/bystra?id=' . $order->click_hash . '&status=DeclinedRequest';
        $this->sendRequest($link_lead, (int)$order->id, 'vbr.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ rafinad
     * @param $order
     * @return void
     */
    public function sendRejectToRafinadleads($order)
    {
        $link_lead = 'https://rfndtrk.com/r/?target=mmmmmc6gre&clickid=' . $order->click_hash . '&order_id='.$order->id.'&api_key=6708d9dba3501b6efff45df2c4403cd6e58acebb';
        $this->sendRequest($link_lead, (int)$order->id, 'rafinad.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ leadfin
     * @param $order
     * @return void
     */
    public function sendRejectToLeadfin($order)
    {
        $link_lead =  'https://offers-leadfin.affise.com/postback?clickid='.$order->click_hash.'&action_id='.$order->id.'&goal=1&status=3';
        $this->sendRequest($link_lead, (int)$order->id, 'leadfin.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ Finuslugi
     * @param $order
     * @return void
     */
    public function sendRejectToFinuslugi($order)
    {
        $link_lead =  'http://54081f.binomlink.com/click.php?cnv_id=' . $order->click_hash . '&clickid=' . $order->id . '&cnv_status=rejected';
        $this->sendRequest($link_lead, (int)$order->id, 'finuslugi.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ Guruleads
     * @param $order
     * @return void
     */
    public function sendRejectToGuruleads($order)
    {
        $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=3&secure=3a73d13766a50ab402268e5bd339b3f9&action_id=' . $order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'guruleads.txt', self::TYPE_REJECT, false);
    }


    /**
     * Отказ Guruleads new
     * @param $order
     * @return void
     */
    public function sendRejectToGuruleadsV2($order)
    {
        $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=3&secure=85e5f770935139862ef7e2aa6c0fe222&action_id=' . $order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'guruleads_v2.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ FinCpa
     * @param $order
     * @return void
     */
    public function sendRejectToFinCpa($order)
    {
        $link_lead = 'https://adv.fincpanetwork.ru/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=2&transaction_id=' . $order->id;
        $this->sendRequest($link_lead, (int)$order->id, 'fin_cpa.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ Bankiros
     * @param $order
     * @return void
     */
    public function sendRejectToBankiros($order)
    {
        $link_lead = 'https://tracker.cpamerix.ru/api/orders/' . self::BANKIROS_TOKEN . '?aff=' . $order->click_hash . '&type=img&conversion=' . $order->id . '&status=reject&payout=' . $order->amount;
        $this->sendRequest($link_lead, (int)$order->id, 'bankiros.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ mvp_dir
     * @param $order
     * @return void
     */
    public function sendRejectToMvpDir($order)
    {
        $params = [
            'subid' => $order->click_hash,
            'status' => 'rejected',
        ];

        $link_lead = 'http://global.cpaserver.ru/postback?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->id, 'mvp_dir.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отправка постбека об отказе в banki.ru от апи партнеров
     *
     * @param $order
     * @return void
     */
    public function sendRejectToBankiRuApi($order)
    {
        $params = [
            'adv_sub1' => $order->id
        ];

        $link_lead = 'https://tracking.banki.ru/GPBGE?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->id, 'banki-ru_api.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ leadstech-api
     * @param $order
     * @return void
     */
    public function sendRejectLeadsTechApi($order)
    {
        $params = [
            'goal_id' => self::LEADSTECH_API['goal_id'],
            'status' => 2,
            'click_id' => $order->click_hash,
            'transaction_id' => $order->id,
            'ping3uuid' => $this->order_data->read($order->id, 'ping3uuid'),
        ];

        $link_lead = 'https://t.leads.tech/add-conversion/?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->id, 'leadstech_api.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ leadssu3 или LW3
     * @param $order
     * @return void
     */
    private function sendRejectToLeadsSu3($order)
    {
        $params = [
            'token' => self::LEADS_SU2_TOKEN,
            'goal_id' => 0,
            'transaction_id' => $order->click_hash,
            'adv_sub' => $order->id,
            'status' => 'rejected',
            'sum' => 0,
            'utm_source' => 'LW3'
        ];

        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->id, 'LW3.txt', self::TYPE_REJECT, false);
    }

    /**
     * Общий метод для отказа заявок
     * @param $order
     * @return void
     */
    public function sendReject($order)
    {
        if (!empty($order->utm_source)) {
            switch ($order->utm_source) {
                case 'c2m':
                    $this->sendRejectToC2M($order);
                    break;
                case 'leadstech':
                    $this->sendRejectToleadstech($order);
                    break;
                case 'unicom24':
                    $this->sendRejectToUnicom24($order);
                    break;
                case 'cityads':
                    $this->sendRejectToCityAds($order);
                    break;
                case 'LW':
                    $this->sendRejectToLeadsSu($order);
                    break;
                case 'akva_leads.su':
                    $this->sendRejectToAkvaLeadsSu($order);
                    break;
                case 'akva_sravni':
                    $this->sendRejectToAkvaSravni($order);
                    break;
                case 'LW2':
                    $this->sendRejectToLeadsSu2($order);
                    break;
                case 'LW3':
                    $this->sendRejectToLeadsSu3($order);
                    break;
                case 'leadtarget':
                    $this->sendRejectToLeadtarget($order);
                    break;
                case 'alliance':
                    $this->sendRejectToAlliance($order);
                    break;
                case 'kosmos':
                    $this->sendRejectToKosmosleads($order);
                    break;
                case 'leadcraft':
                    $this->sendRejectToLeadCraft($order);
                    break;
                case 'vibery':
                    $this->sendRejectToVbrleads($order);
                    break;
                case 'rafinad':
                    $this->sendRejectToRafinadleads($order);
                    break;
                case 'leadfin':
                    $this->sendRejectToLeadfin($order);
                    break;
                case 'finuslugi':
                    $this->sendRejectToFinuslugi($order);
                    break;
                case 'guruleads':
                    $this->sendRejectToGuruleads($order);
                    break;
                case 'guruleads_v2':
                    $this->sendRejectToGuruleadsV2($order);
                    break;
                case 'fin_cpa':
                    $this->sendRejectToFinCpa($order);
                    break;
                case 'bankiros':
                    $this->sendRejectToBankiros($order);
                    break;
                case 'mvp_dir':
                    $this->sendRejectToMvpDir($order);
                    break;
                /** Постбеки партнерского апи */
                case 'bankiru-api':
                    $this->sendRejectToBankiRuApi($order);
                    break;
                case 'leadstech-api':
                    $this->sendRejectLeadsTechApi($order);
                    break;
                /** ***************************/
            }
        }
    }

    /**
     * Отправка новой заявки
     * @param $order_data
     * @return void
     */
    public function sendNewOrder($order_data)
    {
        // Т.к. у нас как попало используются данные преобразуем к единому виду с CRM
        $order = is_object($order_data) ? $order_data : (object)$order_data;

        if (!empty($order->utm_source)) {
            switch ($order->utm_source) {
                case 'c2m':
                    $link_lead = 'https://c2mpbtrck.com/cpaCallback?cid=' . $order->click_hash . '&partner=' . self::PARTNER . '&action=hold&lead_id=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'c2m.txt', 'hold', false, '');
                    break;
                case 'leadstech':
                    $link_lead = 'https://offers.leads.tech/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=0&transaction_id=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'leadstech.txt', 'hold', false, '');
                    break;
                case 'unicom24':
                    $link_lead = 'https://unicom24.ru/offer/postback/' . $order->click_hash . '/?status=receive&external_id=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'unicom24.txt', 'hold', false, '');
                    break;
                case 'guruleads':
                    $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=2&secure=3a73d13766a50ab402268e5bd339b3f9&action_id=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'guruleads.txt', 'hold', false, '');
                    break;
                case 'guruleads_v2':
                    $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=2&secure=85e5f770935139862ef7e2aa6c0fe222&action_id=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'guruleads_v2.txt', 'hold', false, '');
                    break;
                case 'leadgid':
                    $link_lead = 'http://go.leadgid.ru/aff_lsr?offer_id=4806&adv_sub=' . $order->id . '&transaction_id=' . $order->click_hash;
                    $this->sendRequest($link_lead, (int)$order->id, 'leadgid.txt', 'hold', false, '');
                    break;
                case 'mvp':
                    $link_lead = 'http://109.68.214.82/b08845a/postback?subid=' . $order->click_hash . '&status=pending';
                    $this->sendRequest($link_lead, (int)$order->id, 'mvp_leads.txt', 'hold', false, '');
                    break;
                case 'mvp_dir':
                    $link_lead = ' http://global.cpaserver.ru/postback?subid=' . $order->click_hash . '&status=pending';
                    $this->sendRequest($link_lead, (int)$order->id, 'mvp_dir.txt', 'hold', false, '');
                    break;
                case 'leadcraft':
                    $reviseDate = date("Y-m-d");
                    $link_lead = 'https://api.leadcraft.ru/v1/advertisers/actions?token=b3ed1da5f51b24e8abb0851f7206357a4e47468eb647364fd56087121694c6be&actionID=270&status=pending&clickID=' . $order->click_hash . '&advertiserID=' . $order->id . '&reviseDate=' . $reviseDate;
                    $this->sendRequest($link_lead, (int)$order->id, 'leadcraft.txt', 'hold', false, '');
                    break;
                case 'bankiru':
                    $link_lead = 'https://tracking.banki.ru/SP2Bk?adv_sub=' . $order->id . '&transaction_id=' . $order->click_hash;
                    $this->sendRequest($link_lead, (int)$order->id, 'bankiru.txt', 'hold', false, '');
                    break;
                case 'beegl':
                    $link_lead = 'http://ru.beegl.net/postback?clickid=' . $order->click_hash . '&goal=pending&status=2&action_id=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'beegl.txt', 'hold', false, '');
                    break;
                case 'cityads':
                    $link_lead = 'https://postback.cityads.com/service/postback?Campaign_secret=0dqggp&order_id=' . $order->id . '&click_id=' . $order->click_hash . '&status=new';
                    $this->sendRequest($link_lead, (int)$order->id, 'cityads.txt', 'hold', false, '');
                    break;
                case 'LW':
                    $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->id . '&status=pending';
                    $this->sendRequest($link_lead, (int)$order->id, 'leads_su.txt', 'hold', false, '');
                    break;
                case 'LW2':
                    $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU2_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->id . '&status=pending';
                    $this->sendRequest($link_lead, (int)$order->id, 'leads_su.txt', 'hold', false, '');
                    break;
                case 'LW3':
                    $this->sendHoldLeadsSu3($order);
                    break;
                case 'leadtarget':
                    $link_lead = 'http://service.leadtarget.ru/postback/?application=' . $order->id . '&click_id=' . $order->click_hash . '&status=pending';
                    $this->sendRequest($link_lead, (int)$order->id, 'leadtarget_ru.txt', 'hold', false, '');
                    break;
                case 'alliance':
                    $link_lead = 'https://alianscpa.ru/postback/get/partners?token=' . self::ALLIANCE_TOKEN
                        . '&from=bystra&status=2&click_id=' . $order->click_hash . '&sub1=' . $order->utm_medium;
                    $this->sendRequest($link_lead, (int)$order->id, 'alliance.txt', 'hold', false, '');
                    break;
                case 'kosmos':
                    $link_lead = 'https://tr.ksms.pro/a3b405f/postback?subid=' . $order->click_hash . '&status=lead&tid=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'kosmos.txt', 'hold', false, '');
                    break;
                case 'vibery':
                    $link_lead = 'https://adv.vbr.ru/api/v2/postback/bystra?id=' . $order->click_hash . '&status=Request';
                    $this->sendRequest($link_lead, (int)$order->id, 'vbr.txt', 'hold', false, '');
                    break;
                case 'sravni':
                    $link_lead = 'https://goto.startracking.ru/api/v1/postback?goal_name=default&transaction_id='.$order->click_hash.'&adv_sub='.$order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'sravni.txt', 'hold', false, '');
                    break;
                case 'rafinad':
                    $link_lead = 'https://rfndtrk.com/p/?target=mmmmmc6gre&clickid=' . $order->click_hash . '&order_id'.$order->id.'&api_key=6708d9dba3501b6efff45df2c4403cd6e58acebb';
                    $this->sendRequest($link_lead, (int)$order->id, 'rafinad.txt', 'hold', false, '');
                    break;
                case 'leadfin':
                    $link_lead = 'https://offers-leadfin.affise.com/postback?clickid='.$order->click_hash.'&action_id='.$order->id.'&goal=1&status=2';
                    $this->sendRequest($link_lead, (int)$order->id, 'leadfin.txt', 'hold', false, '');
                    break;
                case 'finuslugi':
                    $link_lead =  'http://54081f.binomlink.com/click.php?cnv_id=' . $order->click_hash . '&clickid=' . $order->id . '&cnv_status=pending';
                    $this->sendRequest($link_lead, (int)$order->id, 'finuslugi.txt', 'hold', false, '');
                    break;
                case 'fin_cpa':
                    $link_lead = 'https://adv.fincpanetwork.ru/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=0&transaction_id=' . $order->id;
                    $this->sendRequest($link_lead, (int)$order->id, 'fin_cpa.txt', 'hold', false, '');
                    break;
                case 'bankiros':
                    $link_lead = 'https://tracker.cpamerix.ru/api/orders/' . self::BANKIROS_TOKEN . '?aff=' . $order->click_hash . '&type=img&conversion=' . $order->id . '&status=pending';
                    $this->sendRequest($link_lead, (int)$order->id, 'bankiros.txt', 'hold', false, '');
                    break;
                case 'adsfinpro':
                    $link_lead = 'https://reg.adsfin.net/postback/?click_id=' . $order->click_hash . '&conversion=1&sum=0';
                    $this->sendRequest($link_lead, (int)$order->id, 'ads_fin.txt', 'hold', false, '');
                    break;
                case 'leadstech2':
                    $this->sendHoldleadstech2($order);
                    break;
                case 'weblab':
                    $this->sendHoldWebLab($order);
                    break;
                case 'tbank':
                case 'tpartners':
                    $this->sendHoldTBank($order);
                    break;
            }
        }
    }

    /**
     * Новая заявка leadstech2
     * @param $order
     * @return void
     */
    private function sendHoldleadstech2($order)
    {
        $link_lead = 'https://t.leads.tech/add-conversion/?click_id=' . $order->click_hash . '&goal_id=254&status=0&transaction_id=' . $order->id . '&sumConfirm=0';
        $this->sendRequest($link_lead, (int)$order->id, 'leadstech2.txt', 'hold', false, '');
    }

    /**
     * Новая заявка weblab
     * @param $order
     * @return void
     */
    private function sendHoldWebLab($order)
    {
        $link_lead = 'https://gate.fin-me.ru/cpa-postback?click_id=' . $order->click_hash . '&conversion_id='  . $order->id . '&status=0&goal=first';
        $this->sendRequest($link_lead, (int)$order->id, 'weblab.txt', 'hold', false, '');
    }

    /**
     * Отправка постбека о новой заявке в T-Bank  и TPartners
     *
     * @param $order
     * @return void
     */
    public function sendHoldTBank($order)
    {
        $amount = $this->settings->postback[$order->utm_source]['amount'] ?? 0;

        $params = [
            'tcpa_id' => self::TBANK_TOKEN ,
            'tcpa_click_id' => $order->click_hash,
            'tcpa_request_id' => $order->id,
            'tcpa_event_id' => 'hold',
            'tcpa_partner_profit' => $amount,
        ];

        $link_lead = 'https://partners.tbank.ru/api/postbacks?' . http_build_query($params);
        $logFile = $order->utm_source . '.txt';
        $this->sendRequest($link_lead, (int)$order->id, $logFile, 'hold', false, $amount);
    }

    /**
     * Отправка постбека о новой заявке в T-Bank  и TPartners
     *
     * @param $order
     * @return void
     */
    public function sendHoldLeadsSu3($order)
    {
        $params = [
            'token' => self::LEADS_SU2_TOKEN,
            'goal_id' => 0,
            'transaction_id' => $order->click_hash,
            'adv_sub' => $order->id,
            'status' => 'pending',
            'sum' => 0,
            'utm_source' => 'LW3'
        ];

        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->id, 'LW3.txt', self::TYPE_HOLD, false);
    }

    /**
     * @param string $link_lead
     * @param int $order_id
     * @param string $file_name
     * @param string $url_type_log
     * @param int|string $price
     * @param bool $save_result
     * @return bool|string
     */
    public function sendRequest(string $link_lead, int $order_id, string $file_name, string $url_type_log, bool $save_result, $price = '')
    {
        $ch = curl_init($link_lead);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $res = curl_exec($ch);
        curl_close($ch);

        $this->logging(__METHOD__, $url_type_log, compact('link_lead', 'order_id'), $res, $file_name);

        $date_added = date('Y-m-d H:i:s');

        // сохраним постбек
        $post_back_data = [
            'order_id' =>  $order_id,
            'url' => $link_lead,
            'type' => $url_type_log,
            'method' => 'GET',
            'date_added' => $date_added,
            'response' => $res,
        ];
        $this->savePostBack($post_back_data);

        if ($save_result) {
            // записываем в базу дату постбека о выдаче
            $this->orders->update_order($order_id, array(
                'leadgid_postback_date' => $date_added,
                'leadgen_postback' => $link_lead,
                'payout_grade' => $price,
            ));
        }

        return $res;
    }

    /**
     * Добавляет постбек
     * @param array $data
     * @return mixed
     */
    public function savePostBack(array $data)
    {
        $query = $this->db->placehold("INSERT INTO __postback SET ?%", $data);
        $this->db->query($query);
        return $this->db->insert_id();
    }

    /**
     * Проверяет есть ли постбек
     * @param int $order_id
     * @param string $type
     * @return bool|false
     */
    public function hasPostBackByOrderId(int $order_id, string $type): bool
    {
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM __postback WHERE order_id = ? AND `type` = ?) as r", $order_id, $type);
        $this->db->query($query);

        return (bool)$this->db->result('r');
    }

    /**
     * Отправка постбека для новой заявки
     *
     * @param $last_order
     * @return void
     */
    public function sendPostBackNewOrder($last_order)
    {
        if (is_array($last_order)) {
            $last_order = (object)$last_order;
        }

        // Проверим, был ли ранее постбек
        if (!$this->hasPostBackByOrderId((int)$last_order->id, 'hold')) {
            // отправим постбек о новой заявке после последнего шага
            if (empty($last_order->have_close_credits) || in_array($last_order->utm_source, self::REPEAT_UTM_SOURCE)) {
                $this->sendNewOrder($last_order);
            }
        }
    }
}
