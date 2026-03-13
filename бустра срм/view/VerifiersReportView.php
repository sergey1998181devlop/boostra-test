<?php

require_once 'View.php';

class VerifiersReportView extends View
{
    public function fetch()
    {
        if ($daterange = $this->request->get('daterange'))
        {
            list($from, $to) = explode('-', $daterange);
            
        	$date_from = date('Y-m-d', strtotime($from));
        	$date_to = date('Y-m-d', strtotime($to)) . ' 23:59:59';
            
            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $report = [];
            $reasons = [];
            $reasons_all = [];
            $reject_list = ['2.Отказано', '7.Технический отказ'];
            $confirmed_list = ['5.Выдан', '6.Закрыт'];
            $approved_list = ['3.Одобрено', '5.Выдан', '6.Закрыт'];

            $queryAll = $this->db->placehold("
                    SELECT
                        m.id,
                        m.name,
                        SUM(IF(o.have_close_credits, 1, 0)) total_count_pk,
                        SUM(IF(!o.have_close_credits, 1, 0)) total_count_nk,
                        SUM(IF(o.have_close_credits
                                AND o.`1c_status` IN (?@), 1, 0)) total_count_approved_pk,
                        SUM(IF(!o.have_close_credits
                                AND o.`1c_status` IN (?@), 1, 0)) total_count_approved_nk,
                        SUM(IF(o.have_close_credits
                                AND o.`1c_status` IN (?@), 1, 0)) total_count_confirmed_pk,
                        SUM(IF(!o.have_close_credits
                                AND o.`1c_status` IN (?@), 1, 0)) total_count_confirmed_nk,
                        SUM(IF(o.have_close_credits
                                AND o.`1c_status` IN (?@), 1, 0)) total_count_reject_pk,
                        SUM(IF(!o.have_close_credits
                                AND o.`1c_status` IN (?@), 1, 0)) total_count_reject_nk,
                        SUM(IF(o.have_close_credits
                                AND o.`1c_status` IN (?@), o.amount, 0)) total_amount_pk,
                        SUM(IF(!o.have_close_credits
                                AND o.`1c_status` IN (?@), o.amount, 0)) total_amount_nk
                    FROM s_orders o
                    JOIN s_managers m
                        ON m.id = o.manager_id
                    WHERE
                        o.accept_date >= ?
                        AND o.accept_date <= ?
                    GROUP BY m.id",
                $approved_list, $approved_list, $confirmed_list, $confirmed_list, $reject_list, 
                $reject_list, $confirmed_list, $confirmed_list, $date_from, $date_to);
            $this->db->query($queryAll);
            
            $results = $this->db->results();
            foreach ($results as $result) {
                $report[$result->id] = (array)$result;
                $total_nk = $result->total_count_approved_nk + $result->total_count_reject_nk;
                $total_pk = $result->total_count_approved_pk + $result->total_count_reject_pk;
                $report[$result->id]['total_avg_nk'] = $total_nk ? number_format($result->total_amount_nk / $result->total_count_confirmed_nk, 0, '', '') : 0;
                $report[$result->id]['total_avg_pk'] = $total_pk ? number_format($result->total_amount_pk / $result->total_count_confirmed_pk, 0, '', '') : 0;
                $report[$result->id]['total_cnv_nk'] = $total_nk ? number_format($result->total_count_confirmed_nk / $result->total_count_nk * 100, 2, '.', '') : 0;
                $report[$result->id]['total_cnv_pk'] = $total_pk ? number_format($result->total_count_confirmed_pk / $result->total_count_pk * 100, 2, '.', '') : 0;
            }

            $queryByReasons = $this->db->placehold("
                    SELECT
                        m.id,
                        o.reason_id,
                        SUM(IF(!o.have_close_credits, 1, 0)) cnt_nk,
                        SUM(IF(o.have_close_credits, 1, 0)) cnt_pk
                    FROM s_orders o
                    JOIN s_managers m
                        ON m.id = o.manager_id
                    JOIN s_reasons r
                        ON r.id = o.reason_id
                    WHERE
                        o.accept_date >= ?
                        AND o.accept_date <= ?
                        AND o.`1c_status` IN (?@)
                    GROUP BY m.id, o.reason_id",
                 $date_from, $date_to, $reject_list);
            $this->db->query($queryByReasons);
            
            $results = $this->db->results();
            foreach ($results as $result) {
                $reasons[$result->id] = $reasons[$result->id] ?? [];
                $reasons[$result->id][$result->reason_id] = (array)$result;
            }

            $queryAllReasons = $this->db->placehold("SELECT * FROM s_reasons r");
            $this->db->query($queryAllReasons);
            
            $results = $this->db->results();
            foreach ($results as $result) {
                $reasons_all[$result->id] = (array)$result;
            }

            $this->design->assign('report', $report);
            $this->design->assign('reasons', $reasons);
            $this->design->assign('reasons_all', $reasons_all);
        }
        
        return $this->design->fetch('verifiers_report.tpl');
    }
}