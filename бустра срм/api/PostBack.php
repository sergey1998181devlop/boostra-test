<?php

use Carbon\Carbon;

require_once 'Simpla.php';

final class PostBack extends Simpla
{
    /**
     * Тип постбека о новой поступившей заявки
     */
    public const TYPE_HOLD = 'hold';

    /**
     * Тип постбека о выдаче
     */
    public const TYPE_SALE = 'sale';

    /**
     * Тип постбека об отказе заявки
     */
    public const TYPE_REJECT = 'reject';

    public const PARTNER = 'boostra';

    /**
     * Токен к апи LW (ранее leads.su)
     */
    public const LEADS_SU_TOKEN = '3c37bb58f2f592d3a0574f8e91ef6c4d';
    public const LEADS_SU2_TOKEN = '0d77bd893bb34806d9640432e1bcfa8c';
    private const ALLIANCE_TOKEN = '108739fa4e711aa1a683b996923d2a18';
    public const AKVA_LEADS_SU_TOKEN = '2f35fde3d857b9f4ee915a9092c0fbec';

    private const BANKIROS_TOKEN = 'TiQQLxspZ9RZpNFE7';

    public const POSTBACK_DOMAIN_FINUSLUGI = 'http://54081f.binomlink.com/click.php';

    private const TBANK_TOKEN = 'zzAM3VZu7jgu8aA3mzCcDNKXv3rtRm6UYefXt00a';

    /**
     * Статичные данные для leadstech-api
     */
    public const LEADSTECH_API = [
        'repeat' => [
            'goal_ids' => [
                'create' => 0,
                'issued' => 4,
                'reject' => 0,
            ],
            'status' => [
                'create' => 0,
                'issued' => 1,
                'reject' => 2,
            ],
            'sum' => 300,
        ],
        'new' => [
            'goal_ids' => [
                'create' => 0,
                'issued' => 3,
                'reject' => 0,
            ],
            'status' => [
                'create' => 0,
                'issued' => 1,
                'reject' => 2,
            ],
            'sum' => 5000,
        ]
    ];

    /**
     * Отказ по заявке для click2.money
     * @param $order
     * @return void
     */
    private function sendRejectToLeadFinances($order)
    {
        $query = $this->db->placehold(
            "INSERT INTO __reject_queue SET ?%",
            ['order_id' => $order->order_id]
        );
        $this->db->query($query);
    }

    /**
     * Отказ по заявке для click2.money
     * @param $order
     * @return void
     */
    private function sendRejectToC2M($order)
    {
        $link_lead = 'https://c2mpbtrck.com/cpaCallback?cid=' . $order->click_hash  . '&partner=' . self::PARTNER . '&action=reject&lead_id=' . $order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'c2m.txt', self::TYPE_REJECT, false, '');
    }

    /**
     * Отказ по заявке для leadcraft
     * @param $order
     * @return void
     */
    private function sendRejectToLeadCraft($order)
    {
        if (!$this->hasPostBackByOrderId((int)$order->order_id, self::TYPE_REJECT)) {
            $reviseDate = date("Y-m-d");
            $link_lead = 'https://api.leadcraft.ru/v1/advertisers/actions?token=b3ed1da5f51b24e8abb0851f7206357a4e47468eb647364fd56087121694c6be&actionID=270&status=cancelled&clickID=' . $order->click_hash . '&advertiserID=' . $order->order_id . '&reviseDate=' . $reviseDate;
            $this->sendRequest($link_lead, (int)$order->order_id, 'leadcraft.txt', self::TYPE_REJECT, false, '');
        }
    }

    /**
     * Отказ leadstech
     * @param $order
     * @return void
     */
    private function sendRejectToleadstech($order)
    {
        $link_lead = 'https://offers.leads.tech/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=2&transaction_id=' . $order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'leadstech.txt', self::TYPE_REJECT, false, '');
    }

    /**
     * Отказ Unicom24
     * @param $order
     * @return void
     */
    private function sendRejectToUnicom24($order)
    {
        $link_lead = 'https://unicom24.ru/offer/postback/' . $order->click_hash . '/?status=reject&external_id=' . $order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'unicom24.txt', self::TYPE_REJECT, false, '');
    }

    /**
     * Отказ cityads
     * @param $order
     * @return void
     */
    private function sendRejectToCityAds($order)
    {
        $link_lead = 'https://postback.cityads.com/service/postback?Campaign_secret=0dqggp&order_id=' . $order->order_id . '&click_id=' . $order->click_hash . '&status=cancel';
        $this->sendRequest($link_lead, (int)$order->order_id, 'cityads.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ akva_leads.su
     * @param $order
     * @return void
     */
    private function sendRejectToAkvaLeadsSu($order)
    {
        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::AKVA_LEADS_SU_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->order_id, 'akva_leads_su.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ LW (ранее leads.su)
     * @param $order
     * @return void
     */
    private function sendRejectToLeadsSu($order)
    {
        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->order_id, 'leads_su.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ LW3 (ранее leads.su)
     * @param $order
     * @return void
     */
    private function sendRejectToLeadsSu3($order)
    {
        $params = [
            'token' => self::LEADS_SU2_TOKEN,
            'goal_id' => 0,
            'transaction_id' => $order->click_hash,
            'adv_sub' => $order->order_id,
            'status' => 'rejected',
            'sum' => 0,
            'utm_source' => 'LW3'
        ];

        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->order_id, 'LW3.txt', self::TYPE_REJECT, false);
    }

    private function sendRejectToLeadsSu2($order)
    {
        $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU2_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->order_id, 'leads_su.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ leadtarget
     * @param $order
     * @return void
     */
    private function sendRejectToLeadtarget($order)
    {
        $link_lead = 'http://service.leadtarget.ru/postback/?application=' . $order->order_id . '&click_id=' . $order->click_hash . '&status=rejected';
        $this->sendRequest($link_lead, (int)$order->order_id, 'leadtarget_ru.txt', self::TYPE_REJECT, false);
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
        $this->sendRequest($link_lead, (int)$order->order_id, 'alliance.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ kosmos
     * @param $order
     * @return void
     */
    private function sendRejectToKosmosleads($order)
    {
        $link_lead = 'https://tr.ksms.pro/a3b405f/postback?subid=' . $order->click_hash . '&status=rejected&tid=' . $order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'kosmos.txt', self::TYPE_REJECT, false);
    }


    /**
     * Отказ vbr
     * @param $order
     * @return void
     */
    private function sendRejectToVbrleads($order)
    {
        $link_lead = 'https://adv.vbr.ru/api/v2/postback/bystra?id=' . $order->click_hash . '&status=DeclinedRequest';
        $this->sendRequest($link_lead, (int)$order->order_id, 'vbr.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ rafinad
     * @param $order
     * @return void
     */
    public function sendRejectToRafinadleads($order)
    {
        $link_lead = 'https://rfndtrk.com/r/?target=mmmmmc6gre&clickid=' . $order->click_hash . '&order_id='.$order->order_id.'&api_key=6708d9dba3501b6efff45df2c4403cd6e58acebb';
        $this->sendRequest($link_lead, (int)$order->order_id, 'rafinad.txt', self::TYPE_REJECT, false);
    }
    /**
     * Отказ leadfin
     * @param $order
     * @return void
     */
    public function sendRejectToLeadfin($order)
    {
        $link_lead =  'https://offers-leadfin.affise.com/postback?clickid='.$order->click_hash.'&action_id='.$order->order_id.'&goal=1&status=3';
        $this->sendRequest($link_lead, (int)$order->order_id, 'leadfin.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ mvp
     * @param $order
     * @return void
     */
    public function sendRejectToMvp($order)
    {
        $link_lead = 'https://tracker.mvpgroup.ru/d595109/postback?subid='.$order->click_hash.'&status=rejected';
        $this->sendRequest($link_lead, (int)$order->order_id, 'rafinad.txt', self::TYPE_REJECT, false);
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
        $this->sendRequest($link_lead, (int)$order->order_id, 'mvp_dir.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ Finuslugi
     * @param $order
     * @return void
     */
    public function sendRejectToFinuslugi($order)
    {
        $link_lead =  'http://54081f.binomlink.com/click.php?cnv_id=' . $order->click_hash . '&clickid=' . $order->order_id . '&cnv_status=rejected';
        $this->sendRequest($link_lead, (int)$order->order_id, 'finuslugi.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ Guruleads
     * @param $order
     * @return void
     */
    public function sendRejectToGuruleads($order)
    {
        $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=3&secure=3a73d13766a50ab402268e5bd339b3f9&action_id=' . $order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'guruleads.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ Guruleads new
     * @param $order
     * @return void
     */
    public function sendRejectToGuruleadsV2($order)
    {
        $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=3&secure=85e5f770935139862ef7e2aa6c0fe222&action_id=' . $order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'guruleads_v2.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ FinCpa
     * @param $order
     * @return void
     */
    public function sendRejectToFinCpa($order)
    {
        $link_lead = 'https://adv.fincpanetwork.ru/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=2&transaction_id=' . $order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'fin_cpa.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ
     * @param $order
     * @return void
     */
    public function sendRejectToAkvaSravni($order)
    {
        $link_lead = 'https://sravni.go2cloud.org/aff_goal?a=lsr&goal_name=reject&adv_id=973&transaction_id='.$order->click_hash.'&adv_sub='.$order->order_id;
        $this->sendRequest($link_lead, (int)$order->order_id, 'akva_sravni.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ bankiros
     * @param $order
     * @return void
     */
    public function sendRejectToBankiros($order)
    {
        $link_lead = 'https://tracker.cpamerix.ru/api/orders/' . self::BANKIROS_TOKEN . '?aff=' . $order->click_hash . '&type=img&conversion=' . $order->order_id . '&status=reject&payout=' . $order->amount;
        $this->sendRequest($link_lead, (int)$order->order_id, 'bankiros.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отправка постбека об отказе в banki.ru от апи партнеров
     *
     * @param $order
     * @return void
     */
    public function sendRejectToBankiRuApi($order)
    {
        $order_id = $this->getActualPing3OrderId($order->order_id);

        $params = [
            'adv_sub1' => $order_id
        ];

        $link_lead = 'https://tracking.banki.ru/GPBGE?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->order_id, 'banki-ru_api.txt', self::TYPE_REJECT, false);
    }

    /**
     * Отказ leadstech-api
     * @param $order
     * @return void
     */
    public function sendRejectLeadsTechApi($order)
    {
        $order_id = $this->getActualPing3OrderId($order->order_id);

        //new / repeat
        $user_ping_status = $this->order_data->read($order_id, $this->ping3_data::PING3_USER_STATUS);

        $params = [
            'goal_id' => self::LEADSTECH_API[$user_ping_status]['goal_ids']['reject'],
            'status' => self::LEADSTECH_API[$user_ping_status]['status']['reject'],
            'click_id' => $order->click_hash,
            'transaction_id' => $order_id,
            'ping3uuid' => $this->order_data->read($order_id, 'ping3_meta_ping3uuid'),
        ];

        $link_lead = 'https://t.leads.tech/add-conversion/?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->order_id, 'leadstech_api.txt', self::TYPE_REJECT, false);
    }

    /**
     * Общий метод для отказа заявок
     * @param $order
     * @return void
     */
    public function sendReject($order)
    {
        // Проверяем если заявка ping3 + (cross_order или автозаявка), то выполним postback по партнеру
        $ping3partner = $this->ping3_data->getPing3AutoOrderUtmSource($order->order_id);
        $utm_source = $order->utm_source;

        if ($ping3partner) {
            $utm_source = $ping3partner;
        }

        if (!empty($utm_source)) {
            switch ($utm_source) {
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
                case 'LW3':
                    $this->sendRejectToLeadsSu3($order);
                    break;
                case 'akva_leads.su':
                    $this->sendRejectToAkvaLeadsSu($order);
                    break;
                case 'LW2':
                    $this->sendRejectToLeadsSu2($order);
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
                case 'mvp':
                    $this->sendRejectToMvp($order);
                    break;
                case 'mvp_dir':
                    $this->sendRejectToMvpDir($order);
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
                case 'akva_sravni':
                    $this->sendRejectToAkvaSravni($order);
                    break;
                case 'bankiros':
                    $this->sendRejectToBankiros($order);
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

        $this->sendRejectToLeadFinances($order);
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
     * Отправка выдача leadgid
     * @param $order
     * @return void
     */
    private function sendSaleLeadGid($order)
    {
        $user = $this->users->get_user($order->user_id);

        if ($order->have_close_credits == 0) {
            if ($user->site_id === 'neomani') {
                $link_leadgid = 'https://api.leadgid.com/autostats/v1/postbacks?offer_id=7012&transaction_id='.$order->click_hash.'&adv_sub='.$order->id.'&action=update&status=approved';
                $this->sendRequest($link_leadgid, (int)$order->order_id, 'leadgid.txt', self::TYPE_SALE, true, 0);
            } else {
                $scorings = $this->scorings->get_scorings_by_scorista($user->id);
                if (isset($scorings[0])) {
                    $goal_id = 5010;

                    $link_leadgid = 'http://go.leadgid.ru/aff_goal?a=lsr&goal_id=' . $goal_id . '&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '';
                    $this->sendRequest($link_leadgid, (int)$order->order_id, 'leadgid.txt', self::TYPE_SALE, true, $goal_id);
                } else {
                    $link_leadgid = 'http://go.leadgid.ru/aff_goal?a=lsr&goal_id=5010&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '';
                    $this->sendRequest($link_leadgid, (int)$order->order_id, 'leadgid.txt', self::TYPE_SALE, true, 0);
                    $this->logging(
                        __METHOD__,
                        'not have score',
                        $link_leadgid,
                        '',
                        'leadgid_errors.txt'
                    );
                }
            }
            
        } elseif ($order->utm_source == $user->utm_source & $order->webmaster_id == $user->webmaster_id) {
            if ((strtotime('now') - strtotime($user->created)) < 864000) {
                $link_leadgid = 'http://my.leadgid.ru/stats/universal?offer=4806&subid=' . $order->order_id . '&status=approved';
                $this->logging(__METHOD__, self::TYPE_HOLD, $link_leadgid, '', 'leadgid_hold.txt');
            }
        }
    }

    /**
     * Отправка выдача MVP
     * @param $order
     * @return void
     */
    private function sendSaleMvp($order)
    {
        if ($order->have_close_credits == 0) {
            //Скорбалл 0-599 - 1250
            //Скорбалл 600-699 - 1700
            //Скорбалл 700-749 - 2100
            //Скорбалл 750+ - 2500
            $user = $this->users->get_user($order->user_id);
            $scorings = $this->scorings->get_scorings_by_scorista($user->id);

            $price = 0;

            if (isset($scorings[0])) {
                $score = $scorings[0]->scorista_ball;
                if ($score < 600) {
                    $price = 1250;
                } elseif ($score >= 600 & $score < 700) {
                    $price = 1700;
                } elseif ($score >= 700 & $score < 750) {
                    $price = 2100;
                } elseif ($score >= 750) {
                    $price = 2500;
                } else {
                    $price = 0;
                }
            }

            $link_lead = 'https://tracker.mvpgroup.ru/d595109/postback?subid=' . $order->click_hash . '&status=sale&payout=' . $price;
            $this->sendRequest($link_lead, (int)$order->order_id, 'mvp_leads_mvpgroup.txt', self::TYPE_SALE, true, $price);
        }
    }

    /**
     * Отправка выдача MVP dir
     * @param $order
     * @return void
     */
    private function sendSaleMvpDir($order)
    {
        if ($order->have_close_credits == 0) {
            $price = 7000;

            $params = [
                'subid' => $order->click_hash,
                'sale' => 'sale',
                'payout' => $price,
            ];

            $link_lead = 'http://global.cpaserver.ru/postback?' . http_build_query($params);
            $this->sendRequest($link_lead, (int)$order->order_id, 'mvp_dir.txt', self::TYPE_SALE, true, $price);
        }
    }

    /**
     * Отправка выдача leadcraft
     * @param $order
     * @return void
     */
    private function sendSaleLeadcraft($order)
    {
        if ($order->have_close_credits == 0) {
            //Скорбалл 0-599 - 1200
            //Скорбалл 600-699 - 1600
            //Скорбалл 700-749 - 2000
            //Скорбалл 750+ - 2400
            $user = $this->users->get_user($order->user_id);
            $scorings = $this->scorings->get_scorings_by_scorista($user->id);

            $price = 0;

            if (isset($scorings[0])) {
                $score = $scorings[0]->scorista_ball;
                if ($score < 600) {
                    $price = 1500;
                } elseif ($score >= 600 & $score < 700) {
                    $price = 2000;
                } elseif ($score >= 700 & $score < 750) {
                    $price = 2500;
                } elseif ($score >= 750) {
                    $price = 3000;
                } else {
                    $price = 0;
                }
            }

            $reviseDate = date("Y-m-d");
            $link_lead = 'https://api.leadcraft.ru/v1/advertisers/actions?token=b3ed1da5f51b24e8abb0851f7206357a4e47468eb647364fd56087121694c6be&actionID=270&status=approved&clickID=' . $order->click_hash . '&advertiserID=' . $order->order_id . '&reviseDate=' . $reviseDate;
            if ($price != 0) {
                $this->sendRequest($link_lead, (int)$order->order_id, 'leadcraft.txt', self::TYPE_SALE, true, $price);
            } else {
                $this->sendRequest($link_lead, (int)$order->order_id, 'leadcraft.txt', self::TYPE_SALE, true, 0);
            }
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

            $user = $this->users->get_user($order->user_id);

            if ($user->site_id === 'neomani') {
                $link_lead = 'https://t.leads.tech/add-conversion/?click_id='.$order->click_hash.'&goal_id=3&status=1&transaction_id='.$order->order_id.'&sumConfirm=1941';
                $this->sendRequest($link_lead, (int)$order->order_id, 'leadstech.txt', self::TYPE_SALE, true, '');
            } else {
                $price = 2700;
                $link_lead = 'https://offers.leads.tech/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=1&transaction_id=' . $order->order_id;// . '&sumConfirm=' . $price
                $this->sendRequest($link_lead, (int)$order->order_id, 'leadstech.txt', self::TYPE_SALE, true, '');
            }
        }
    }

    /**
     * Отправка выдача beegl
     * @param $order
     * @return void
     */
    private function sendSaleBeegl($order)
    {
        if ($order->have_close_credits == 0) {
            //0-599 1500 руб
            //600-699 2000 руб
            //700-749 2500 руб
            //750 и выше 3000 руб
            $user = $this->users->get_user($order->user_id);
            $scorings = $this->scorings->get_scorings_by_scorista($user->id);

            $price = 0;

            if (isset($scorings[0])) {
                $score = $scorings[0]->scorista_ball;
                if ($score < 600) {
                    $price = 1500;
                } elseif ($score >= 600 & $score < 700) {
                    $price = 2000;
                } elseif ($score >= 700 & $score < 750) {
                    $price = 2000;
                } elseif ($score >= 750) {
                    $price = 2500;
                } else {
                    $price = 0;
                }
            }

            $link_lead = 'http://ru.beegl.net/postback?clickid=' . $order->click_hash . '&goal=' . $price . '&status=1&action_id=' . $order->order_id . '&sum=' . $price;
            $res = $this->sendRequest($link_lead, (int)$order->order_id, 'beegl.txt', self::TYPE_SALE, true, $price);
            if ($price === 0) {
                $this->logging(__METHOD__, self::TYPE_SALE, ['link_lead' => $link_lead, 'order_id' => $order->order_id], $res, 'beegl_errors.txt');
            }
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
            //Скорбалл 0-599 - Выдача 1(GP2Bn)
            //Скорбалл 600-699 - Выдача 2(GP2Bq)
            //Скорбалл 700-749 - Выдача 3(GP2Bt)
            //Скорбалл 750+ - Выдача 4(GP2Bw)
            $user = $this->users->get_user($order->user_id);
            if ($user->site_id === 'neomani') {
                $link_lead =  'https://tracking.banki.ru/GPJs3?adv_sub1='.$order->order_id.'&adv_sub2=&adv_sub3=&adv_sub4=& adv_sub5=&transaction_id='.$order->click_hash;
                $this->sendRequest($link_lead, (int)$order->order_id, 'bankiru.txt', self::TYPE_SALE, true, 0);
            } else {
                $base_link = 'https://tracking.banki.ru/GP2Bn';
            $price = 'Выдача 1 (GP2Bn)';
            $link_lead = $base_link . '?transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id;
            $this->sendRequest($link_lead, (int)$order->order_id, 'bankiru.txt', self::TYPE_SALE, true, $price);
            }   
        }
    }

    /**
     * Отправка выдача c2m
     * @param $order
     * @return void
     */
    private function sendSaleC2m($order)
    {
        if ($order->have_close_credits == 0) {
            $price = 2100;
            $link_lead = 'https://c2mpbtrck.com/cpaCallback?cid=' . $order->click_hash  . '&partner=' . $this->post_back::PARTNER . '&action=approve&lead_id=' . $order->order_id . '&payout=' . $price;
            $this->sendRequest($link_lead, (int)$order->order_id, 'c2m.txt', self::TYPE_SALE, true, $price);
        }
    }

    /**
     * Отправка выдача unicom24
     * @param $order
     * @return void
     */
    private function sendSaleUnicom24($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://unicom24.ru/offer/postback/' . $order->click_hash . '/?status=accept&external_id=' . $order->order_id;
            $this->sendRequest($link_lead, (int)$order->order_id, 'unicom24.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Отправка выдача guruleads
     * @param $order
     * @return void
     */
    private function sendSaleGuruleads($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=1&secure=3a73d13766a50ab402268e5bd339b3f9&action_id=' . $order->order_id;
            $this->sendRequest($link_lead, (int)$order->order_id, 'guruleads.txt', self::TYPE_SALE, true, 2100);
        }
    }

    /**
     * Отправка выдача guruleads new
     * @param $order
     * @return void
     */
    private function sendSaleGuruleadsV2($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://offers.guruleads.ru/postback?clickid=' . $order->click_hash . '&goal=loan&status=1&secure=85e5f770935139862ef7e2aa6c0fe222&action_id=' . $order->order_id;
            $this->sendRequest($link_lead, (int)$order->order_id, 'guruleads_v2.txt', self::TYPE_SALE, true, 2700);
        }
    }

    /**
     * Отправка выдача cityads
     * @param $order
     * @return void
     */
    private function sendSaleCityAds($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://postback.cityads.com/service/postback?Campaign_secret=0dqggp&order_id=' . $order->order_id . '&click_id=' . $order->click_hash . '&status=done';
            $this->sendRequest($link_lead, (int)$order->order_id, 'cityads.txt', self::TYPE_SALE, true);
        }
    }


    /**
     * Отправка выдача LeadGid
     * @param $order
     * @return void
     */
    private function sendSaleAkvaLeadGid($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://go.leadgid.com/aff_goal?a=lsr&goal_id=8184&transaction_id=' . $order->click_hash;
            $this->sendRequest($link_lead, (int)$order->order_id, 'akva_leadgid.txt', self::TYPE_SALE, true);
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
            $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=approved';
            $this->sendRequest($link_lead, (int)$order->order_id, 'leads_su.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Отправка выдача akva_leads.su
     * @param $order
     * @return void
     */
    private function sendSaleAkvaLeadsSu($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::AKVA_LEADS_SU_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=approved';
            $this->sendRequest($link_lead, (int)$order->order_id, 'akva_leads_su.txt', self::TYPE_SALE, true);
        }
    }

    private function sendSaleLeadsSu2($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?token=' . self::LEADS_SU2_TOKEN . '&goal_id=0&transaction_id=' . $order->click_hash . '&adv_sub=' . $order->order_id . '&status=approved';
            $this->sendRequest($link_lead, (int)$order->order_id, 'leads_su.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Выдача leadssu3 или LW3
     * @param $order
     * @return void
     */
    private function sendSaleLeadsSu3($order)
    {
        if ($order->have_close_credits == 0) {
            list($goal_id, $amount) = $this->getParamsPostback($order);

            $params = [
                'token' => self::LEADS_SU2_TOKEN,
                'goal_id' => $goal_id,
                'transaction_id' => $order->click_hash,
                'adv_sub' => $order->order_id,
                'status' => 'approved',
                'sum' => $amount,
                'utm_source' => 'LW3'
            ];

            $link_lead = 'https://api.leads.su/advertiser/conversion/createUpdate?' . http_build_query($params);
            $this->sendRequest($link_lead, (int)$order->order_id, 'LW3.txt', self::TYPE_SALE, true, $amount);
        }
    }


    /**
     * Отправка выдача leadtarget
     * @param $order
     * @return void
     */
    private function sendSaleLeadtarget($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'http://service.leadtarget.ru/postback/?application=' . $order->order_id . '&click_id=' . $order->click_hash . '&status=approved';
            $this->sendRequest($link_lead, (int)$order->order_id, 'leadtarget_ru.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Отправка выдача альянс
     * @param object $order
     * @return void
     */
    private function sendSaleAlliance(object $order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://alianscpa.ru/postback/get/partners?token=' . self::ALLIANCE_TOKEN
                . '&from=bystra&status=1&click_id=' . $order->click_hash . '&sub1=' . $order->utm_medium;
            $this->sendRequest($link_lead, (int)$order->order_id, 'alliance.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Отправка выдача
     * @param $order
     * @return void
     */
    private function sendSaleKosmosleads($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://tr.ksms.pro/a3b405f/postback?subid=' . $order->click_hash . '&status=sale&tid=' . $order->order_id;
            $this->sendRequest($link_lead, (int)$order->order_id, 'kosmos.txt', self::TYPE_SALE, true);

        }
    }

    /**
     * Отправка выдача
     * @param $order
     * @return void
     */
    private function sendSaleVbrleads($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://adv.vbr.ru/api/v2/postback/bystra?id=' . $order->click_hash . '&status=sale';
            $this->sendRequest($link_lead, (int)$order->order_id, 'vbr.txt', self::TYPE_SALE, true);

        }
    }

    /**
     * Отправка выдача
     * @param $order
     * @return void
     */
    private function sendSaleSravnileads($order)
    {
        if ($order->have_close_credits == 0) {
            $user = $this->users->get_user($order->user_id);
            if ($user->site_id === 'neomani') {
                $link_lead = 'https://goto.startracking.ru/api/v1/postback?goal_name=issued&adv_id=1005&transaction_id='.$order->click_hash.'&adv_sub='.$order->order_id.'&offer_id=2683';
                $this->sendRequest($link_lead, (int)$order->order_id, 'sravni.txt', self::TYPE_SALE, true);
            } else {
                $link_lead = 'https://goto.startracking.ru/api/v1/postback?goal_name=issued&transaction_id='.$order->click_hash.'&adv_sub='.$order->order_id;
                $this->sendRequest($link_lead, (int)$order->order_id, 'sravni.txt', self::TYPE_SALE, true);
            }
        }
    }

    /**
     * Отправка выдача
     * @param $order
     * @return void
     */
    private function sendSaleAkvaSravnileads($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://sravni.go2cloud.org/aff_goal?a=lsr&goal_name=issued&adv_id=973&transaction_id='.$order->click_hash.'&adv_sub='.$order->order_id;
            $this->sendRequest($link_lead, (int)$order->order_id, 'akva_sravni.txt', self::TYPE_SALE, true);
        }
    }

    public function sendSaleRafinadleads($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://rfndtrk.com/p/?target=mmmmmwcm8m&clickid='.$order->click_hash.'&order_id='.$order->order_id.'&api_key=6708d9dba3501b6efff45df2c4403cd6e58acebb';
            $this->sendRequest($link_lead, (int)$order->order_id, 'rafinad.txt', self::TYPE_SALE, true);
        }
    }

    public function sendSaleLeadfin($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://offers-leadfin.affise.com/postback?clickid='.$order->click_hash.'&action_id='.$order->order_id.'&goal=1&status=1';
            $this->sendRequest($link_lead, (int)$order->order_id, 'leadfin.txt', self::TYPE_SALE, true);
        }
    }

    public function sendSaleFinuslugi($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'http://54081f.binomlink.com/click.php?cnv_id=' . $order->click_hash . '&clickid=' . $order->order_id . '&cnv_status=issued&payout=4000';
            $this->sendRequest($link_lead, (int)$order->order_id, 'finuslugi.txt', self::TYPE_SALE, true);
        }
    }

    public function sendSaleFinCpa($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://adv.fincpanetwork.ru/add-conversion/?click_id=' . $order->click_hash . '&goal_id=3&status=1&transaction_id=' . $order->order_id;
            $this->sendRequest($link_lead, (int)$order->order_id, 'fin_cpa.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Отправка постбеков об одобрении в bankiros
     * @param $order
     * @return void
     */
    public function sendSaleBankiros($order)
    {
        $link_lead = 'https://tracker.cpamerix.ru/api/orders/' . self::BANKIROS_TOKEN . '?aff=' . $order->click_hash . '&type=img&conversion=' . $order->order_id . '&status=approve';

        $this->sendRequest($link_lead, (int)$order->order_id, 'bankiros.txt', self::TYPE_SALE, true);

    }

    /**
     * Отправка постбеков об одобрении в adsfin.net
     * @param $order
     * @return void
     */

    public function sendSaleToAdsFin($order)
    {
        $link_lead = 'https://reg.adsfin.net/postback/?click_id=' . $order->click_hash . '&conversion=2&sum=5000';
        $this->sendRequest($link_lead, (int)$order->id, 'ads_fin.txt', self::TYPE_SALE, false);
    }


    /**
     * Отправка постбеков об одобрении в sendSaleToLeadstech2
     *
     * @param $order
     * @return void
     */
    public function sendSaleToLeadstech2($order)
    {
        list($goal_id, $amount) = $this->getParamsPostback($order);

        $link_lead = 'https://t.leads.tech/add-conversion/?click_id=' . $order->click_hash . '&goal_id=' . $goal_id . '&status=1&transaction_id=' . $order->order_id . '&sumConfirm=' . $amount;
        $this->sendRequest($link_lead, (int)$order->order_id, 'leadstech2.txt', self::TYPE_SALE, true, $amount);
    }

    /**
     * Отправка постбека о выдаче в bonon
     *
     * @param $order
     * @return void
     */
    public function sendSaleToBonon($order)
    {
        $amount = $this->settings->postback['bonon']['amount'];
        $localTime = Carbon::now('UTC');

        $params = [
            'click_id' => $order->click_hash,
            'offer_id' => 22,
            'amount' => $amount,
            'date' => $localTime->format('Y-m-d H:i:s'),
            'cpa' => 'link',
            'belongs' => 'bonon',
            'external_id' => $order->order_id,
        ];

        $link_lead = 'https://m7.su/webhook/postback/force?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->order_id, 'bonon.txt', self::TYPE_SALE, true, $amount);
    }

    /**
     * Отправка постбека о выдачи в kekas
     *
     * @param $order
     * @return void
     */
    public function sendSaleToKekas($order)
    {
        $amount = $this->settings->postback['kekas']['amount'];
        $localTime = Carbon::now('UTC');

        $params = [
            'click_id' => $order->click_hash,
            'offer_id' => 22,
            'amount' => $amount,
            'date' => $localTime->format('Y-m-d H:i:s'),
            'cpa' => 'link',
            'belongs' => 'kekas',
            'external_id' => $order->order_id,
        ];

        $link_lead = 'https://m7.su/webhook/postback/force?' . http_build_query($params);
        $this->sendRequest($link_lead, (int)$order->order_id, 'kekas.txt', self::TYPE_SALE, true, $amount);
    }

    /**
     * Отправка постбека о выдаче в T-Bank и TPartners
     *
     * @param $order
     * @return void
     */
    public function sendSaleToTBank($order)
    {
        $amount = $this->settings->postback[$order->utm_source]['amount'] ?? 0;

        $params = [
            'tcpa_id' => self::TBANK_TOKEN,
            'tcpa_click_id' => $order->click_hash,
            'tcpa_request_id' => $order->order_id,
            'tcpa_event_id' => self::TYPE_SALE,
            'tcpa_partner_profit' => $amount,
        ];

        $link_lead = 'https://partners.tbank.ru/api/postbacks?' . http_build_query($params);
        $logFile = $order->utm_source . '.txt';
        $this->sendRequest($link_lead, (int)$order->order_id, $logFile, self::TYPE_SALE, true, $amount);
    }

    /**
     * Отправка постбека о выдачи в weblab
     *
     * @param $order
     * @return void
     */
    public function sendSaleToWebLab($order)
    {
        if ($order->have_close_credits == 0) {
            $link_lead = 'https://gate.fin-me.ru/cpa-postback?click_id=' . $order->click_hash . '&conversion_id='  . $order->order_id . '&status=1&goal=first';
            $this->sendRequest($link_lead, (int)$order->order_id, 'weblab.txt', self::TYPE_SALE, true);
        }
    }

    /**
     * Отправка постбека о выдачи в banki.ru от апи партнеров
     *
     * @param $order
     * @return void
     */
    public function sendSaleToBankiRuApi($order)
    {
        $status = $this->order_data->read($order->order_id, $this->ping3_data::PING3_USER_STATUS);
        $partner_id = $status === $this->ping3_data::CHECK_USER_RESPONSE_NEW ? 'GPJ7e' : 'GPAyp';

        $order_id = $this->getActualPing3OrderId($order->order_id);

        // Првоерим переход если не было постбек не отпарвим
        $visit_ping3 = $this->order_data->read($order_id, $this->user_data::PING3_VISIT);
        if (!$visit_ping3) {
            return;
        }

        $params = [
            'adv_sub1' => $order_id
        ];

        $link_lead = "https://tracking.banki.ru/$partner_id?" . http_build_query($params);
        $this->sendRequest($link_lead, $order->order_id, 'banki-ru_api.txt', self::TYPE_SALE, true);
    }

    /**
     * Выдача lead.su
     * @param $order
     * @return void
     */
    public function sendSaleToLeadsu($order)
    {
        if ($order->have_close_credits == 0) {
            $user = $this->users->get_user($order->user_id);
            if ($user->site_id === 'neomani') {
                $link_lead = 'http://api.leads.su/advertiser/conversion/createUpdate?token=cc7a35da4b6cc3b05b92b2abd7e24caa&goal_id=0&adv_sub='.$order->order_id.'&status=approved';
                $this->sendRequest($link_lead, (int)$order->order_id, 'banki-ru_api.txt', self::TYPE_SALE, true);
            }
        }
    }

    /**
     * Выдача leadstech-api
     * @param $order
     * @return void
     */
    public function sendSaleLeadsTechApi($order)
    {
        $order_id = $this->getActualPing3OrderId($order->order_id);
        $ping3uuid = $this->order_data->read($order_id, 'ping3_meta_ping3uuid');

        // Првоерим переход если не было постбек не отпарвим
        $visit_ping3 = $this->order_data->read($order_id, $this->user_data::PING3_VISIT);
        if (!$visit_ping3) {
            return;
        }

        //new / repeat
        $user_ping_status = $this->order_data->read($order_id, $this->ping3_data::PING3_USER_STATUS);

        $params = [
            'goal_id' => self::LEADSTECH_API[$user_ping_status]['goal_ids']['issued'],
            'status' => self::LEADSTECH_API[$user_ping_status]['status']['issued'],
            'sum' => self::LEADSTECH_API[$user_ping_status]['sum'],
            'transaction_id' => $order_id,
            'ping3uuid' => $ping3uuid,
        ];

        $this->logging(__METHOD__, 'Логируем данные для ping3', $params, ['order_id' => $order_id, 'main_order_id' => $order->order_id], 'leadstech_api_data.txt');

        $link_lead = 'https://t.leads.tech/add-conversion/?' . http_build_query($params);
        $this->sendRequest($link_lead, $order->order_id, 'leadstech_api.txt', self::TYPE_SALE, true, self::LEADSTECH_API[$user_ping_status]['sum']);
    }

    /**
     * Выдача bonon-api
     * @param $order
     * @return void
     */
    public function sendSaleBononApi($order)
    {
        $order_id = $this->getActualPing3OrderId($order->order_id);

        $params = [
            'application-id' => $order_id,
        ];

        $link_lead = 'https://services.m7.su/api/v1/webhook/mfo/boostra/e986dc76-b459-4b64-a535-7cde0b9cf30f?' . http_build_query($params);
        $this->sendRequest($link_lead, $order->order_id, 'bonon-api.txt', self::TYPE_SALE, true);
    }

    /**
     * Отправка postback finuslugi со статусом issued_old для основного заказа
     * Параметры запроса идентичны issued, меняется только cnv_status
     * @param $order основной заказ (utm_source = finuslugi)
     */
    public function sendSaleCrossOrderFinuslugi($order)
    {
        $link_lead = self::POSTBACK_DOMAIN_FINUSLUGI . '?cnv_id=' . $order->click_hash . '&clickid=' . $order->order_id . '&cnv_status=issued_old&payout=1000';
        $this->sendRequest($link_lead, (int)$order->order_id, 'finuslugi.txt', self::TYPE_SALE, true);
    }

    /**
     * Отправка постбеков о выдаче
     * @param object $order
     * @return void
     */
    public function pushIssuedLoanToQueue($order)
    {
        $delayed_postbacks_leadgens = explode(',', $this->settings->delayed_postbacks_leadgens ?? '');
        if(in_array($order->utm_source, $delayed_postbacks_leadgens)){
            $query = $this->db->placehold("INSERT INTO __external_api_queue SET ?%", ['order_id' => $order->order_id, 'api' => 'send_issued_loans_generic']);
            $this->db->query($query);
        } else {
            $this->post_back->sendSaleOrder($order);
        }
        $query = $this->db->placehold("INSERT INTO __external_api_queue SET ?%", ['order_id' => $order->order_id, 'api' => 'send_issued_loans_bonon']);
        $this->db->query($query);
    }

    /**
     * Отправка постбека о выдаче в Sravni2
     *
     * @param $order
     * @return void
     */
    public function sendSaleSravnileads2($order)
    {
        if ($order->have_close_credits == 0) {
            $params = [
                'goal_name' => 'issued',
                'adv_id' => 732,
                'transaction_id' => $order->click_hash,
                'adv_sub' => $order->order_id,
                'offer_id' => 100648,
            ];

            $link_lead = 'https://goto.startracking.ru/api/v1/postback?' . http_build_query($params);
            $this->sendRequest($link_lead, (int)$order->order_id, 'sravni2.txt', self::TYPE_SALE, true, '');
        }
    }

    /**
     * Отправка постбеков о выдаче
     * @param $order_data
     * @return void
     */
    public function sendSaleOrder($order_data)
    {
        // Т.к. у нас как попало используются данные преобразуем к единому виду с CRM
        $order = is_object($order_data) ? $order_data : (object)$order_data;

        // проверяем выдан ли первый займ если он является разделенным ИЛИ займ не разделенный
        $order_id = (int)$order_data->order_id;
        $order_divide = $this->orders->getDivideOrderByOrderId($order_id);

        // Проверяем если заявка ping3 + (cross_order или автозаявка), то выполним postback по партнеру
        $ping3partner = $this->ping3_data->getPing3AutoOrderUtmSource($order_id);

        if ((!empty($order_divide) && $order_id === $order_divide->main_order_id) || empty($order_divide) || !empty($ping3partner)) {
            $userId = (int)($order->user_id ?? 0);
            $siteId = $this->users->getSiteIdByUserId($userId);
            switch ($siteId) {
                case $this->organizations::SITE_SOYA:
                    $this->soyaplace_postback->sendSaleOrder($order);
                    return;
                case $this->organizations::SITE_NEOMANI:
                    $this->neomani_postback->sendSaleOrder($order);
                    return;
            }

            $utm_source = $order->utm_source;

            if ($ping3partner) {
                $utm_source = $ping3partner;
            }

            if (!empty($utm_source)) {
                switch ($utm_source) {
//                    case 'cross_order':
//                        $this->sendSaleCrossOrder($order);
//                        break;
                    case 'leadgid':
                        $this->sendSaleLeadGid($order);
                        break;
                    case 'akva_leadgid':
                        $this->sendSaleAkvaLeadGid($order);
                        break;
                    case 'mvp':
                        $this->sendSaleMvp($order);
                        break;
                    case 'mvp_dir':
                        $this->sendSaleMvpDir($order);
                        break;
                    case 'leadcraft':
                        $this->sendSaleLeadcraft($order);
                        break;
                    case 'leadstech':
                        $this->sendSaleLeadstech($order);
                        break;
                    case 'beegl':
                        $this->sendSaleBeegl($order);
                        break;
                    case 'bankiru':
                        $this->sendSaleBankiru($order);
                        break;
                    case 'c2m':
                        $this->sendSaleC2m($order);
                        break;
                    case 'unicom24':
                        $this->sendSaleUnicom24($order);
                        break;
                    case 'guruleads':
                        $this->sendSaleGuruleads($order);
                    case 'guruleads_v2':
                        $this->sendSaleGuruleadsV2($order);
                        break;
                    case 'cityads':
                        $this->sendSaleCityAds($order);
                        break;
                    case 'LW':
                        $this->sendSaleLeadsSu($order);
                        break;
                    case 'akva_leads.su':
                        $this->sendSaleAkvaLeadsSu($order);
                        break;
                    case 'LW2':
                        $this->sendSaleLeadsSu2($order);
                        break;
                    case 'LW3':
                        $this->sendSaleLeadsSu3($order);
                        break;
                    case 'leadtarget':
                        $this->sendSaleLeadtarget($order);
                        break;
                    case 'alliance':
                        $this->sendSaleAlliance($order);
                        break;
                    case 'kosmos':
                        $this->sendSaleKosmosleads($order);
                        break;
                    case 'vibery':
                        $this->sendSaleVbrleads($order);
                        break;
                    case 'sravni':
                        $this->sendSaleSravnileads($order);
                        break;
                    case 'sravni2':
                        $this->sendSaleSravnileads2($order);
                        break;
                    case 'akva_sravni':
                        $this->sendSaleAkvaSravnileads($order);
                        break;
                    case 'rafinad':
                        $this->sendSaleRafinadleads($order);
                        break;
                    case 'leadfin':
                        $this->sendSaleLeadfin($order);
                        break;
                    case 'finuslugi':
                        $this->sendSaleFinuslugi($order);
                        break;
                    case 'fin_cpa':
                        $this->sendSaleFinCpa($order);
                        break;
                    case 'bankiros':
                        $this->sendSaleBankiros($order);
                        break;
                    case 'adsfinpro':
                        $this->sendSaleToAdsFin($order);
                        break;
                    case 'leadstech2':
                        $this->sendSaleToLeadstech2($order);
                        break;
                    case 'lead.su':
                        $this->sendSaleToLeadsu($order);
                    case 'bonon':
                        $this->sendSaleToBonon($order);
                        break;
                    case 'kekas':
                        $this->sendSaleToKekas($order);
                        break;
                    case 'weblab':
                        $this->sendSaleToWebLab($order);
                        break;
                    case 'tbank':
                    case 'tpartners':
                        $this->sendSaleToTBank($order);
                        break;
                    /** Постбеки партнерского апи */
                    case 'bankiru-api':
                        $this->sendSaleToBankiRuApi($order);
                        break;
                    case 'leadstech-api':
                        $this->sendSaleLeadsTechApi($order);
                        break;
                    case 'bonon-api':
                        $this->sendSaleBononApi($order);
                        break;
                    /** ***************************/
                }
            }
        }
    }

    /**
     * Отправка постбеков об одобрении
     * @param $order_data
     * @return void
     */
    public function sendApproveOrder($order_data)
    {
        $link_lead = 'https://adv.vbr.ru/api/v2/postback/bystra?id=' . $order_data->click_hash . '&status=ApprovedRequest';
        $this->sendRequest($link_lead, (int)$order_data->order_id, 'vbr.txt', self::TYPE_SALE, false);

    }

    /**
     * Добавляет постбек
     * @param array $data
     * @return mixed
     */
    public function savePostBack(array $data)
    {
        $query = $this->db->placehold("INSERT INTO s_postback SET ?%", $data);
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
        $query = $this->db->placehold("SELECT EXISTS(SELECT * FROM s_postback WHERE order_id = ? AND `type` = ?) as r", $order_id, $type);
        $this->db->query($query);

        return (bool)$this->db->result('r');
    }

    /**
     * Проверяет, отправлялся ли постбек finuslugi со статусом issued за последние $hours часов
     * @param int $order_id
     * @param int $hours
     * @return bool
     */
    public function hasFinuslugiIssuedRecently(int $order_id, string $click_hash, int $hours = 24): bool
    {
        $like_pattern = self::POSTBACK_DOMAIN_FINUSLUGI . '?cnv_id=' . $click_hash . '&clickid=' . $order_id . '&cnv_status=issued%';
        $query = $this->db->placehold(
            "SELECT EXISTS(SELECT 1 FROM s_postback WHERE order_id = ? AND url LIKE ? AND date_added >= (NOW() - INTERVAL ? HOUR)) as r",
            $order_id,
            $like_pattern,
            $hours
        );
        $this->db->query($query);
        return (bool)$this->db->result('r');
    }

    /**
     * В случае кросс-выдачи проверяем основную заявку и условие отправки issued_old для finuslugi
     * @param object $order Текущая заявка (utm_source === 'cross_order')
     * @return void
     */
    private function sendSaleCrossOrder(object $order): void
    {
        if (empty($order->utm_medium)) {
            return;
        }
        $mainOrder = $this->orders->get_order((int)$order->utm_medium);
        if (!empty($mainOrder) && $mainOrder->utm_source === 'finuslugi') {
            if ($this->hasFinuslugiIssuedRecently((int)$mainOrder->order_id, (string)$mainOrder->click_hash, 24)) {
                $this->sendSaleCrossOrderFinuslugi($mainOrder);
            }
        }
    }

    /**
     * Получает дополнительные параметры по постбеку
     *
     * @param $order
     * @return array|null
     */
    private function getParamsPostback($order): ?array
    {
        switch ($order->utm_source) {
            case 'leadstech2':
                return $this->getParamsLeadstech2($order);
            case 'LW3':
                return $this->getParamsLW3($order);
            default:
                return null;
        }
    }

    /**
     * Возвращаем id цели для интеграции и сумму вознаграждения
     *
     * Выдача займа клиенту с хорошей КИ (cкорбалл от 500 до 599 ) 6000 - руб
     * Выдача займа клиенту с отличной КИ (cкорбалл от 600 до 1000 ) 9000 - руб
     * Выдача займа клиенту с очень плохой КИ (cкорбалл от 0 до 450 ) 1500 - руб
     * Выдача займа клиенту с плохой КИ (cкорбалл от 450 до 499 ) 4000 - руб
     *
     * [$1, $2] $1 - id цели у интегратора, $2 - сумма вознаграждения
     *
     * @param $order
     * @return array|null
     */
    private function getParamsLeadstech2($order): ?array
    {
        $scorista_score = $this->scorings->get_last_scorista_for_user($order->user_id, true);
        $scorista_ball = $scorista_score->scorista_ball;

        if (!is_numeric($scorista_ball)) {
            return null;
        }

        if ($scorista_ball >= 600) {
            return [256, 9000];
        } elseif ($scorista_ball >= 500 && $scorista_ball <= 599) {
            return [254, 6000];
        } elseif ($scorista_ball >= 450 && $scorista_ball <= 499) {
            return [251, 4000];
        } else {
            return [302, 1500];
        }
    }

    /**
     * leadssu3 | LW3
     * @param $order
     * @return int[]|null
     */
    private function getParamsLW3($order): ?array
    {
        $scorista_score = $this->scorings->get_last_scorista_for_user($order->user_id, true);
        $scorista_ball = $scorista_score->scorista_ball;

        if (!is_numeric($scorista_ball)) {
            return [null, null];
        }

        if ($scorista_ball >= 600) {
            return [6180, 9000];
        } elseif ($scorista_ball >= 500 && $scorista_ball <= 599) {
            return [6179, 6000];
        } elseif ($scorista_ball >= 451 && $scorista_ball <= 499) {
            return [6178, 4000];
        } else {
            return [0, 1500];
        }
    }

    /**
     * Проверяем, была ли заявка создана для Фриды то вернем ее ид иначе как есть
     *
     * @param int $order_id
     * @return int
     */
    private function getActualPing3OrderId(int $order_id): int
    {
        $result =  $this->order_data->read($order_id, $this->order_data::ORDER_ORG_SWITCH_PARENT_ORDER_ID);
        return !empty($result) ? (int)$result : $order_id;
    }
}
