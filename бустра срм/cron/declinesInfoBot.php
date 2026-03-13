<?php
error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');

require_once __DIR__.'/../api/Simpla.php';

class DeclinesInfoBotCron extends Simpla
{
    public const ORDER_STATUS_TITLES = [
        Orders::ORDER_STATUS_CRM_NEW => 'Новая',
        Orders::ORDER_STATUS_CRM_APPROVED => 'Одобрена',
        Orders::ORDER_STATUS_CRM_REJECT => 'Отказ',
        Orders::ORDER_STATUS_CRM_CORRECTION => 'CRM на исправлении',
        Orders::ORDER_STATUS_CRM_CORRECTED => 'CRM исправлена',
        Orders::ORDER_STATUS_CRM_WAITING => 'CRM ожидание',
        Orders::ORDER_STATUS_CRM_ISSUED => 'Выдан',
        Orders::ORDER_STATUS_CRM_NOT_ISSUED => 'Не удалось выдать',
        Orders::ORDER_STATUS_CRM_WAIT => 'Ожидание выдачи',
        Orders::STATUS_WAIT_CARD => 'Ожидание привязки карты на сектор кросс-ордера',
        Orders::ORDER_STATUS_CRM_AUTOCONFIRM => 'Ожидание автоподписания',
        Orders::STATUS_WAIT_VIRTUAL_CARD => 'Ожидание виртуальной карты',
    ];
    
    public function __construct()
    {
    	parent::__construct();
    }
    
    public function run()
    {
        if(isset($_GET['date'])) {
            $date = (new DateTime($_GET['date']))->format('Y-m-d');
            $this->db->query("SET @start_date := '{$date}'");
        } else {
            $this->db->query("SET @start_date := IF(UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(CURRENT_DATE()) > 1800, CURRENT_DATE(), CURRENT_DATE() - INTERVAL 1 DAY)");
        }
        $this->db->query("SET @end_date := @start_date + INTERVAL 1 DAY");

        $this->db->query("SELECT @start_date title");
        $title = $this->db->result('title');
        $this->db->query("SELECT NOW() title_now");
        $title_now = $this->db->result('title_now');

        $this->db->query("SELECT RPAD(COUNT(*), 8, ' ') val, cc.title
                            FROM s_checker_requests cr
                            JOIN s_checker_clients_ip ccip
                                ON ccip.id = cr.client_ip_id
                            JOIN s_checker_clients cc
                                ON ccip.client_id = cc.id
                            WHERE
                                cr.request_time >= @start_date
                                AND cr.request_time < @end_date
                            GROUP BY cc.title");
        $checker = $this->db->results();

        $this->db->query("SELECT
                            ud.user_id
                            , ud.`value` nk_rejected
                            , (nk_visit.`value` IS NOT NULL) nk_visit
                            , (ud_pk.`value` IS NOT NULL) 'ud_lk'
                            , (ud_ping3.`value` IS NOT NULL) 'ud_ping3'
                            , IFNULL(ud_skip.`value`, 0) ud_skip
                            , IFNULL(ud_skip_reason.`key`, 'UNDEFINED') ud_skip_reason
                            , IFNULL(ud_timeouts.`value`, 0) ud_timeouts
                            , IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(org.short_name ORDER BY o.id ASC SEPARATOR  '~'), '~', 1), 'NO_ORDER') org
                            , IFNULL(SUBSTRING_INDEX(GROUP_CONCAT(o.`status` ORDER BY o.id ASC SEPARATOR  '~'), '~', 1), 'NO_ORDER') 'status'
                        FROM s_user_data ud
                        LEFT JOIN s_orders o
                            ON ud.user_id  = o.user_id
                        LEFT JOIN s_organizations org
                            ON org.id = o.organization_id
                        LEFT JOIN s_user_data ud_pk
                            ON ud.user_id = ud_pk.user_id
                            AND ud_pk.`key` = 'rejected_pk_url'
                        LEFT JOIN s_user_data ud_skip
                            ON ud.user_id = ud_skip.user_id
                            AND ud_skip.`key` = 'rejected_nk_skipped'
                        LEFT JOIN s_user_data ud_timeouts
                            ON ud.user_id = ud_timeouts.user_id
                            AND ud_timeouts.`key` = 'rejected_nk_timeout'
                        LEFT JOIN s_user_data ud_skip_reason
                            ON ud.user_id = ud_skip_reason.user_id
                            AND ud_skip_reason.`key` IN ('bonon_disabled', 'bonon_utm_skipped', 'bonon_inn_skipped', 'bonon_no_order'
                                                        , 'bonon_organic_skipped', 'bonon_pk_skipped', 'bonon_empty_setting', 'bonon_empty_url'
                                                        , 'bonon_skip_chance', 'bonon_empty_scorings', 'bonon_scorista_nnfu', 'bonon_flow_skipped')
                        LEFT JOIN s_user_data nk_visit
                            ON ud.user_id = nk_visit.user_id
                            AND nk_visit.`key` = 'rejected_nk_visited'
                        LEFT JOIN s_user_data ud_ping3
                            ON ud.user_id = ud_ping3.user_id
                            AND ud_ping3.`key` = 'partner_user_response'
                        WHERE
                            ud.`key` = 'is_rejected_nk'
                            AND ud.updated >= @start_date
                            AND ud.updated < @end_date
                            AND (ud_ping3.`value` IS NULL OR ud_ping3.`value` = 'new')
                        GROUP BY
                            ud.user_id
                            , ud.`value`
                            , (nk_visit.`value` IS NOT NULL)
                            , (ud_pk.`value` IS NOT NULL)
                            , (ud_ping3.`value` IS NOT NULL)
                            , IFNULL(ud_skip.`value`, 0)
                            , IFNULL(ud_skip_reason.`key`, 'UNDEFINED')
                            , IFNULL(ud_timeouts.`value`, 0)");
        $stats = $this->db->results();

        $this->db->query("SELECT COUNT(*) cnt
                        FROM s_user_data ud
                        WHERE
                            ud.`key` = 'rejected_pk_url'
                            AND ud.updated >= @start_date
                            AND ud.updated < @end_date");
        $pk_cnt = $this->db->result('cnt');
        
        $ping3 = [];
        $reg_flow = [];
        $splitted_lk = [];
        $declines = [
            'skipped' => [],
            'skipped_lk' => [],
            'timeouts' => [],
            'timeouts_lk' => [],
            'no_split' => [],
            'no_split_lk' => [],
            'splitted_flow' => [],
        ];
        foreach($stats as $row) {
            if($row->ud_ping3) {
                $ping3[$row->org] = ($ping3[$row->org] ?? 0) + 1;
            } else {
                $status = static::ORDER_STATUS_TITLES[(int)$row->status] ?? "UNDEFINED({$row->status})";
                $reg_flow[$status][$row->org] = ($reg_flow[$status][$row->org] ?? 0) + 1;
                if($row->status == Orders::ORDER_STATUS_CRM_REJECT) {
                    if($row->ud_skip) {
                        $reason = $row->ud_skip_reason ?: 'UNDEFINED';
                        $declines['skipped'][$reason][$row->org] = ($declines['skipped'][$reason][$row->org] ?? 0) + 1;
                        $declines['skipped_lk'][$reason][$row->org] = ($declines['skipped_lk'][$reason][$row->org] ?? 0) + (int)$row->ud_lk;
                    } elseif($row->ud_timeouts) {
                        $declines['timeouts'][$row->org] = ($declines['timeouts'][$row->org] ?? 0) + 1;
                        $declines['timeouts_lk'][$row->org] = ($declines['timeouts_lk'][$row->org] ?? 0) + (int)$row->ud_lk;
                    } elseif($row->nk_rejected) {
                        $declines['splitted_flow'][$row->org] = ($declines['splitted_flow'][$row->org] ?? 0) + 1;
                    } else {
                        $reason = $row->ud_skip_reason ?: 'UNDEFINED';
                        $declines['no_split'][$reason][$row->org] = ($declines['no_split'][$reason][$row->org] ?? 0) + 1;
                        $declines['no_split_lk'][$reason][$row->org] = ($declines['no_split_lk'][$reason][$row->org] ?? 0) + (int)$row->ud_lk;
                    }
                }
            }
        }
        $message = "DATE: $title\n"
                    . "CHECKPOINT: $title_now\n"
                    . 'Всего НК ping3: ' . array_sum($ping3) . "\n"
                    . '<pre>' . implode("\n", array_map(fn($key) => str_pad($ping3[$key], 8, ' ') . $key, array_keys($ping3))) . "</pre>\n"
                    . 'Всего НК сайт: ' . array_reduce($reg_flow, fn($cum, $arr) => $cum + array_sum($arr), 0) . "\n"
                    . '<pre>' . implode("\n", array_map(fn($reason) => "$reason: " . array_sum($reg_flow[$reason]) . "\n"
                                                                       . implode("\n", array_map(fn($org) => "  $org: {$reg_flow[$reason][$org]}" 
                                                                                                 , array_keys($reg_flow[$reason])))
                                                            , array_keys($reg_flow))) . "</pre>\n"
                    . 'Всего отказные НК с сайта: ' . array_sum([
                                                        array_reduce($declines['skipped'], fn($cum, $arr) => $cum + array_sum($arr), 0),
                                                        array_sum($declines['timeouts']),
                                                        array_reduce($declines['no_split'], fn($cum, $arr) => $cum + array_sum($arr), 0),
                                                        array_sum($declines['splitted_flow']),
                                                    ]) . "\n"
                    . 'Отказные в обход Бонон: ' . array_reduce($declines['skipped'], fn($cum, $arr) => $cum + array_sum($arr), 0) . "\n"
                    . '<pre>' . implode("\n", array_map(fn($reason) => "$reason: " . array_sum($declines['skipped'][$reason]) . "\n"
                                                                       . implode("\n", array_map(fn($org) => "  $org: {$declines['skipped'][$reason][$org]}"
                                                                                                 , array_keys($declines['skipped'][$reason])))
                                                            , array_keys($declines['skipped']))) . "</pre>\n"
                    . 'Отказные НК без сплита: ' . array_reduce($declines['no_split'], fn($cum, $arr) => $cum + array_sum($arr), 0) . "\n"
                    . '<pre>' . implode("\n", array_map(fn($reason) => "$reason: " . array_sum($declines['no_split'][$reason]) . "\n"
                                                                       . implode("\n", array_map(fn($org) => "  $org: {$declines['no_split'][$reason][$org]}"
                                                                                                 , array_keys($declines['no_split'][$reason])))
                                                            , array_keys($declines['no_split']))) . "</pre>\n"
                    . 'Таймауты НК: ' . array_sum($declines['timeouts']) . "\n"
                    . '<pre>' . implode("\n", array_map(fn($org) => "$org: {$declines['timeouts'][$org]}", array_keys($declines['timeouts']))) . "</pre>\n"
                    . 'Сплиты из ЛК: ' . $pk_cnt . "\n"
                    . 'Сплиты из регистраций: ' . array_sum($declines['splitted_flow']) . "\n"
                    . '<pre>' . implode("\n", array_map(fn($org) => "$org: {$declines['splitted_flow'][$org]}", array_keys($declines['splitted_flow']))) . "</pre>\n"
                    . "=====================\n"
                    . "Обращения к чекеру\n"
                    . '<pre>' . implode("\n", array_map(fn($row) => "{$row->val}{$row->title}", $checker)) . "</pre>\n";
        $postdata = json_encode([
            'chat_id' => -1002616933125,
            'parse_mode' => 'HTML',
            'text' => $message,
        ]);
        $opts = [
            'http' => [
                'method'  => 'POST',
                'header'  => 'Content-Type: application/json',
                'content' => $postdata
            ]
        ];
        $context = stream_context_create($opts);
        file_get_contents('https://api.telegram.org/bot7826205165:AAGQOoHwZjeaFAryJetKuVmTJwBhqXZ-MXI/sendMessage', false, $context);
    }
}

$cron = new DeclinesInfoBotCron();
$cron->run();
