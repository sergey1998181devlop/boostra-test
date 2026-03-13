<?php

require_once 'View.php';

class TotalReportView extends View
{
    public function fetch()
    {
        if ($daterange = $this->request->get('daterange'))
        {
            list($from, $to) = explode('-', $daterange);

        	$date_from = date('Y-m-d', strtotime($from));
        	$date_to = date('Y-m-d', strtotime($to));

            $this->design->assign('date_from', $date_from);
            $this->design->assign('date_to', $date_to);
            $this->design->assign('from', $from);
            $this->design->assign('to', $to);

            $filter_scorista = $this->request->get('scorista');
            $this->design->assign('filter_scorista', $filter_scorista);

            $_SESSION['filter_scorista'] = serialize($filter_scorista);

            $scorista_groups = array();
            foreach ($filter_scorista['from'] as $index => $val)
            {
                $scorista_group = new StdClass();

                $scorista_group->name = $val;
                $scorista_group->interval = $filter_scorista['from'][$index].'-'.$filter_scorista['to'][$index];

                if (!empty($filter_scorista['from'][$index]))
                {
                    $scorista_group->query_from = "
                    AND (
                        SELECT scorista_ball
                        FROM __scorings AS s
                        WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                        AND status = ".$this->scorings::STATUS_COMPLETED."
                        AND s.user_id = o.user_id
                        ORDER BY s.id DESC
                        LIMIT 1
                    ) >= ".intval($filter_scorista['from'][$index]);
                }
                else
                {
                    $scorista_group->query_from = "
                    ";
                }

                if (!empty($filter_scorista['to'][$index]) && $filter_scorista['to'][$index] != 1000)
                {
                    if (!empty($filter_scorista['from'][$index]))
                    {
                        $scorista_group->query_to = "
                        AND (
                            SELECT scorista_ball
                            FROM __scorings AS s
                            WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                            AND status = ".$this->scorings::STATUS_COMPLETED."
                            AND s.user_id = o.user_id
                            ORDER BY s.id DESC
                            LIMIT 1
                        ) <= ".intval($filter_scorista['to'][$index]);
                    }
                    else
                    {
                        $scorista_group->query_to = "
                        AND
                        (
                            (
                                SELECT scorista_ball
                                FROM __scorings AS s
                                WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                                AND status = ".$this->scorings::STATUS_COMPLETED."
                                AND s.user_id = o.user_id
                                ORDER BY s.id DESC
                                LIMIT 1
                            ) <= ".intval($filter_scorista['to'][$index])."
                            OR
                            (
                                SELECT scorista_ball
                                FROM __scorings AS s
                                WHERE s.type = ".$this->scorings::TYPE_SCORISTA."
                                AND status = ".$this->scorings::STATUS_COMPLETED."
                                AND s.user_id = o.user_id
                                ORDER BY s.id DESC
                                LIMIT 1
                            ) IS NULL
                        )";

                    }
                }
                else
                {
                    $scorista_group->query_to = "";
                }

                $scorista_groups[] = $scorista_group;
            }
            $this->design->assign('scorista_groups', $scorista_groups);

            $base_scorista = $this->db->placehold("
            ");

            $utm_source_list = [];
            $query_source = '';
            $query_visitor_source = '';
            if ($filter_source = $this->request->get('filter_source'))
            {
                $query_source_array = array();
                $query_visitor_source_array = array();
                foreach ($filter_source as $filter_source_item)
                {
                    $q = explode('-', $filter_source_item);
                    $utm_source = $q[0];
                    $webmaster_id = isset($q[1]) ? $q[1] : '';

                    $utm_source_list[] = $utm_source;

                    $query_source_array_item = $this->db->placehold('o.utm_source = ?', $utm_source);
                    if (!empty($webmaster_id))
                        $query_source_array_item .= $this->db->placehold(' AND o.webmaster_id = ? ', $webmaster_id);
                    $query_source_array[] = '('.$query_source_array_item.')';

                    $query_visitor_source_array_item = $this->db->placehold('v.utm_source = ?', $utm_source);
                    if (!empty($webmaster_id))
                        $query_visitor_source_array_item .= $this->db->placehold(' AND v.webmaster_id = ? ', $webmaster_id);

                    $query_visitor_source_array[] = '('.$query_visitor_source_array_item.')';
                }
                if (!empty($query_source_array))
                    $query_source = "AND (".implode(' OR ', $query_source_array).')';
                if (!empty($query_visitor_source_array))
                    $query_visitor_source = "AND (".implode(' OR ', $query_visitor_source_array).')';

                $this->design->assign('filter_source', $filter_source);
            }

            if ($filter_sub_source = $this->request->get('filter_sub_source'))
            {
                $query_source = '';
                $query_visitor_source = ''; //! обнуляем предыдущее значение фильтра по визиторам
                $query_source_array = array();
                $query_visitor_source_array = array();
                foreach ($filter_sub_source as $filter_source_item)
                {
                    $q = explode('-', $filter_source_item);
                    $utm_source = $q[0];
                    $webmaster_id = isset($q[1]) ? $q[1] : '';

                    $query_source_array_item = $this->db->placehold('o.utm_source = ?', $utm_source);
                    if (!empty($webmaster_id))
                        $query_source_array_item .= $this->db->placehold(' AND o.webmaster_id = ? ', $webmaster_id);
                    $query_source_array[] = '('.$query_source_array_item.')';

                    $query_visitor_source_array_item = $this->db->placehold('v.utm_source = ?', $utm_source);
                    if (!empty($webmaster_id))
                        $query_visitor_source_array_item .= $this->db->placehold(' AND v.webmaster_id = ? ', $webmaster_id);

                    $query_visitor_source_array[] = '('.$query_visitor_source_array_item.')';
                }
                if (!empty($query_source_array))
                    $query_source = "AND (".implode(' OR ', $query_source_array).')';
                if (!empty($query_visitor_source_array))
                    $query_visitor_source = "AND (".implode(' OR ', $query_visitor_source_array).')';

                $this->design->assign('filter_sub_source', $filter_sub_source);
            }

            $query_client = '';
            $query_visitor_client = '';
            if ($filter_client = $this->request->get('filter_client'))
            {
                if ($filter_client == 'pk')
                {
                    $query_client = "AND o.have_close_credits = 1";
                    $query_visitor_client = "AND v.user_id IN (SELECT user_id FROM __orders WHERE have_close_credits = 1)";
                }
                if ($filter_client == 'nk')
                {
                    $query_client = "AND o.have_close_credits != 1";
                    $query_visitor_client = "AND (v.user_id NOT IN (SELECT user_id FROM __orders WHERE have_close_credits = 1) OR v.user_id IS NULL)";
                }
                $this->design->assign('filter_client', $filter_client);
            }

            $query_order = '';
            $query_visitor_order = '';
            if ($filter_order = $this->request->get('filter_order'))
            {
                if ($filter_order == 'repeat')
                {
                    $query_order = "AND o.first_loan = 0";
                    // $query_visitor_order = "AND v.user_id IN (SELECT user_id FROM __orders WHERE have_close_credits = 1)";
                }
                if ($filter_order == 'new')
                {
                    $query_order = "AND o.first_loan = 1";
                    // $query_visitor_order = "AND v.user_id NOT IN (SELECT user_id FROM __orders WHERE have_close_credits = 1)";
                }
                $this->design->assign('filter_order', $filter_order);
            }


            $report = array();

            $period_from = $date_from;
            $period_to = $date_to;

            do {
                $index = date('Ymd', strtotime($period_from));
                $report[$index] = new StdClass();
                $report[$index]->u_date = $period_from;
                $period_from = date('Y-m-d', strtotime($period_from) + 86400);
            } while (strtotime($period_from) <= strtotime($period_to));

            $query = $this->db->placehold("
                SELECT
                    DATE(v.created) AS u_date,
                    COUNT(v.id) AS r_total
                FROM __visitors AS v
                WHERE DATE(v.created) >= ?
                AND DATE(v.created) <= ?
                $query_visitor_source
                $query_visitor_client
                $query_visitor_order
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->r_total = $result->r_total;
            }
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

            if ($filter_client == 'nk')
            {
                $query = $this->db->placehold("
                    SELECT
                        DATE(u.created) AS u_date,
                        COUNT(u.id) AS u_total
                    FROM __users AS u
                    LEFT JOIN __visitors AS v
                    ON v.user_id = u.id
                    WHERE DATE(u.created) >= ?
                    AND DATE(u.created) <= ?
                    $query_visitor_source
                    $query_visitor_order
                    GROUP BY u_date
                ", $date_from, $date_to);
                $this->db->query($query);

            }
            else
            {
                $query = $this->db->placehold("
                    SELECT
                        DATE(v.created) AS u_date,
                        COUNT(v.id) AS u_total
                    FROM __visitors AS v
                    WHERE DATE(v.created) >= ?
                    AND DATE(v.created) <= ?
                    AND v.user_id IS NOT NULL
                    $query_visitor_source
                    $query_visitor_client
                    $query_visitor_order
                    GROUP BY u_date
                ", $date_from, $date_to);
                $this->db->query($query);

            }
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->u_total = $result->u_total;
            }


            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->o_total = $result->o_total;
            }

            // автопроверки
            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->auto = $result->o_total;
            }

            // фссп
            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->fms = $result->o_total;
            }

            // contact
            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->contact = $result->o_total;
            }

            // actual
            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                $base_scorista
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->actual = $result->o_total;
            }

            $report[$index]->actual_scorista = array();
            foreach ($scorista_groups as $group)
            {
                $query = $this->db->placehold("
                    SELECT
                        DATE(o.date) AS u_date,
                        COUNT(o.id) AS o_total
                    FROM __orders AS o
                    WHERE DATE(o.date) >= ?
                    AND DATE(o.date) <= ?
                    AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                    AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                    AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                    AND (o.reason_id IS NULL OR o.reason_id != 1)
                    AND (o.reason_id IS NULL OR o.reason_id != 3)
                    AND (o.reason_id IS NULL OR o.reason_id != 4)
                    {$group->query_to}
                    {$group->query_from}
                    $query_source
                    $query_client
                    $query_order
                    GROUP BY u_date
                ", $date_from, $date_to);
                $this->db->query($query);
                //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
                $results = $this->db->results();
                foreach ($results as $result)
                {
                    $index = date('Ymd', strtotime($result->u_date));
                    $report[$index]->actual_scorista[$group->name] = $result->o_total;
                }
            }

            // scorista
            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                AND (o.reason_id IS NULL OR o.reason_id != 1)
                AND (o.reason_id IS NULL OR o.reason_id != 3)
                AND (o.reason_id IS NULL OR o.reason_id != 4)
                AND (o.reason_id IS NULL OR o.reason_id != 5)
                $base_scorista
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to);
            $this->db->query($query);
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->scorista = $result->o_total;
            }

            $report[$index]->scorista_scorista = array();
            foreach ($scorista_groups as $group)
            {
                $query = $this->db->placehold("
                    SELECT
                        DATE(o.date) AS u_date,
                        COUNT(o.id) AS o_total
                    FROM __orders AS o
                    WHERE DATE(o.date) >= ?
                    AND DATE(o.date) <= ?
                    AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                    AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCAL_TIME." AND success = 1)
                    AND o.id IN (SELECT order_id FROM __scorings WHERE type = ".$this->scorings::TYPE_LOCATION." AND success = 1)
                    AND (o.reason_id IS NULL OR o.reason_id != 1)
                    AND (o.reason_id IS NULL OR o.reason_id != 3)
                    AND (o.reason_id IS NULL OR o.reason_id != 4)
                    AND (o.reason_id IS NULL OR o.reason_id != 5)
                    {$group->query_to}
                    {$group->query_from}
                    $query_source
                    $query_client
                    $query_order
                    GROUP BY u_date
                ", $date_from, $date_to);
                $this->db->query($query);
                //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';
                $results = $this->db->results();
                foreach ($results as $result)
                {
                    $index = date('Ymd', strtotime($result->u_date));
                    $report[$index]->scorista_scorista[$group->name] = $result->o_total;
                }
            }

            // цель
            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.approve_date IS NOT NULL
                AND o.approve_date IS NOT NULL
                $base_scorista
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);
            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->target = $result->o_total;
            }

            $report[$index]->target_scorista = array();
            foreach ($scorista_groups as $group)
            {
                $query = $this->db->placehold("
                    SELECT
                        DATE(o.date) AS u_date,
                        COUNT(o.id) AS o_total
                    FROM __orders AS o
                    WHERE DATE(o.date) >= ?
                    AND DATE(o.date) <= ?
                    AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                    AND o.approve_date IS NOT NULL
                    AND o.approve_date IS NOT NULL
                    {$group->query_to}
                    {$group->query_from}
                    $query_source
                    $query_client
                    $query_order
                    GROUP BY u_date
                ", $date_from, $date_to, $date_from, $date_to);
                $this->db->query($query);

                $results = $this->db->results();
                foreach ($results as $result)
                {
                    $index = date('Ymd', strtotime($result->u_date));
                    $report[$index]->target_scorista[$group->name] = $result->o_total;
                }
            }

            // выдача
            $query = $this->db->placehold("
                SELECT
                    DATE(o.date) AS u_date,
                    COUNT(o.id) AS o_total
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
                AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                AND o.confirm_date IS NOT NULL
                AND o.confirm_date IS NOT NULL
                $base_scorista
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);

            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->credit = $result->o_total;
            }

            $report[$index]->credit_scorista = array();
            foreach ($scorista_groups as $group)
            {
                $query = $this->db->placehold("
                    SELECT
                        DATE(o.date) AS u_date,
                        COUNT(o.id) AS o_total
                    FROM __orders AS o
                    WHERE DATE(o.date) >= ?
                    AND DATE(o.date) <= ?
                    AND (o.manager_id != 0 AND o.manager_id IS NOT NULL)
                    AND o.confirm_date IS NOT NULL
                    AND o.confirm_date IS NOT NULL
                    {$group->query_to}
                    {$group->query_from}
                    $query_source
                    $query_client
                    $query_order
                    GROUP BY u_date
                ", $date_from, $date_to, $date_from, $date_to);

                $this->db->query($query);

                $results = $this->db->results();
                foreach ($results as $result)
                {
                    $index = date('Ymd', strtotime($result->u_date));
                    $report[$index]->credit_scorista[$group->name] = $result->o_total;
                }
            }

            // pd1
            $query = $this->db->placehold("
                SELECT
                    DATE(ub.zaim_date) AS u_date,
                    COUNT(ub.id) AS o_total
                FROM __user_balance AS ub
                LEFT JOIN __orders AS o
                ON ub.zayavka = o.1c_id
                WHERE ub.expired_days = 1
                AND DATE(ub.zaim_date) >= ?
                AND DATE(ub.zaim_date) <= ?
                $base_scorista
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);

            $this->db->query($query);
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query, $results);echo '</pre><hr />';exit;
            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->pd1 = $result->o_total;
            }

            // pd5
            $query = $this->db->placehold("
                SELECT
                    DATE(ub.zaim_date) AS u_date,
                    COUNT(ub.id) AS o_total
                FROM __user_balance AS ub
                LEFT JOIN __orders AS o
                ON ub.zayavka = o.1c_id
                WHERE ub.expired_days = 5
                AND DATE(ub.zaim_date) >= ?
                AND DATE(ub.zaim_date) <= ?
                $base_scorista
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);

            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->pd5 = $result->o_total;
            }

            // pd30
            $query = $this->db->placehold("
                SELECT
                    DATE(ub.zaim_date) AS u_date,
                    COUNT(ub.id) AS o_total
                FROM __user_balance AS ub
                LEFT JOIN __orders AS o
                ON ub.zayavka = o.1c_id
                WHERE ub.expired_days = 30
                AND DATE(ub.zaim_date) >= ?
                AND DATE(ub.zaim_date) <= ?
                $base_scorista
                $query_source
                $query_client
                $query_order
                GROUP BY u_date
            ", $date_from, $date_to, $date_from, $date_to);

            $this->db->query($query);

            $results = $this->db->results();
            foreach ($results as $result)
            {
                $index = date('Ymd', strtotime($result->u_date));
                $report[$index]->pd30 = $result->o_total;
            }
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($query);echo '</pre><hr />';

            $report[$index]->credit_scorista_pd = array();
            foreach ($scorista_groups as $group)
            {
                $query = $this->db->placehold("
                    SELECT
                        DATE(ub.zaim_date) AS u_date,
                        COUNT(ub.id) AS o_total
                    FROM __user_balance AS ub
                    LEFT JOIN __orders AS o
                    ON ub.zayavka = o.1c_id
                    WHERE ub.expired_days = 1
                    AND DATE(ub.zaim_date) >= ?
                    AND DATE(ub.zaim_date) <= ?
                    {$group->query_to}
                    {$group->query_from}
                    $query_source
                    $query_client
                    $query_order
                    GROUP BY u_date
                ", $date_from, $date_to, $date_from, $date_to);

                $this->db->query($query);

                $results = $this->db->results();
                foreach ($results as $result)
                {
                    $index = date('Ymd', strtotime($result->u_date));
                    $report[$index]->credit_scorista_pd1[$group->name] = $result->o_total;
                }

                $query = $this->db->placehold("
                    SELECT
                        DATE(ub.zaim_date) AS u_date,
                        COUNT(ub.id) AS o_total
                    FROM __user_balance AS ub
                    LEFT JOIN __orders AS o
                    ON ub.zayavka = o.1c_id
                    WHERE ub.expired_days = 5
                    AND DATE(ub.zaim_date) >= ?
                    AND DATE(ub.zaim_date) <= ?
                    {$group->query_to}
                    {$group->query_from}
                    $query_source
                    $query_client
                    $query_order
                    GROUP BY u_date
                ", $date_from, $date_to, $date_from, $date_to);

                $this->db->query($query);

                $results = $this->db->results();
                foreach ($results as $result)
                {
                    $index = date('Ymd', strtotime($result->u_date));
                    $report[$index]->credit_scorista_pd5[$group->name] = $result->o_total;
                }

                $query = $this->db->placehold("
                    SELECT
                        DATE(ub.zaim_date) AS u_date,
                        COUNT(ub.id) AS o_total
                    FROM __user_balance AS ub
                    LEFT JOIN __orders AS o
                    ON ub.zayavka = o.1c_id
                    WHERE ub.expired_days = 30
                    AND DATE(ub.zaim_date) >= ?
                    AND DATE(ub.zaim_date) <= ?
                    {$group->query_to}
                    {$group->query_from}
                    $query_source
                    $query_client
                    $query_order
                    GROUP BY u_date
                ", $date_from, $date_to, $date_from, $date_to);

                $this->db->query($query);

                $results = $this->db->results();
                foreach ($results as $result)
                {
                    $index = date('Ymd', strtotime($result->u_date));
                    $report[$index]->credit_scorista_pd30[$group->name] = $result->o_total;
                }

            }



            /*
                $opens = $this->soap->get_open_zaims();
                $pd1_numbers = array();
                $pd5_numbers = array();
                $pd30_numbers = array();
                foreach ($opens as $open)
                {
                    if ($open->ДниПросрочки == 1)
                        $pd1_numbers[] = $open->Номер;
                    if ($open->ДниПросрочки == 5)
                        $pd5_numbers[] = $open->Номер;
                    if ($open->ДниПросрочки == 30)
                        $pd30_numbers[] = $open->Номер;
                }

                foreach ($report as $item)
                {
                    $item->pd1 = 0;
                    $item->pd5 = 0;
                    $item->pd30 = 0;
                }

                if (!empty($pd1_numbers))
                {
                    if ($balances = $this->users->get_number_balance($pd1_numbers))
                    {
                        foreach ($balances as $b)
                        {
                            $format_date = date('Ymd', strtotime($b->zaim_date));
                            if (isset($report[$format_date]))
                                $report[$format_date]->pd1++;
                        }
                    }
                }
                if (!empty($pd5_numbers))
                {
                    if ($balances = $this->users->get_number_balance($pd5_numbers))
                    {
                        foreach ($balances as $b)
                        {
                            $format_date = date('Ymd', strtotime($b->zaim_date));
                            if (isset($report[$format_date]))
                                $report[$format_date]->pd5++;
                        }
                    }
                }
                if (!empty($pd30_numbers))
                {
                    if ($balances = $this->users->get_number_balance($pd30_numbers))
                    {
                        foreach ($balances as $b)
                        {
                            $format_date = date('Ymd', strtotime($b->zaim_date));
                            if (isset($report[$format_date]))
                                $report[$format_date]->pd30++;
                        }
                    }
                }
            */
            //echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($report);echo '</pre><hr />';

            $this->design->assign('report', $report);


            $query = $this->db->placehold("
                SELECT DISTINCT o.utm_source, o.webmaster_id
                FROM __orders AS o
                WHERE DATE(o.date) >= ?
                AND DATE(o.date) <= ?
            ", $date_from, $date_to);
            $this->db->query($query);

            $query_source = '';
            $query_visitor_source = '';

            $sources = array();
            $subSources = array();
            if ($results = $this->db->results())
            {
                foreach ($results as $result)
                {
                    $sources[] = $result->utm_source;

                    if (count($utm_source_list)) {
                        if (in_array($result->utm_source, $utm_source_list)) {
                            if ($result->utm_source == 'leadgid') {
                                if (!in_array('leadgid', $subSources)) {
                                    $subSources[] = 'leadgid';
                                }
                                $subSources[] = 'leadgid-'.$result->webmaster_id;
                            } elseif($result->utm_source == 'leadcraft') {
                                continue;
                            } elseif(!empty($result->webmaster_id)) {
                                $subSources[] = $result->utm_source.'-'.$result->webmaster_id;
                            }
                        }
                    } else {
                        if ($result->utm_source == 'leadgid') {
                            if (!in_array('leadgid', $subSources)) {
                                $subSources[] = 'leadgid';
                            }
                            $subSources[] = 'leadgid-'.$result->webmaster_id;
                        } elseif($result->utm_source == 'leadcraft') {
                            continue;
                        } elseif(!empty($result->webmaster_id)) {
                            $subSources[] = $result->utm_source.'-'.$result->webmaster_id;
                        }
                    }
                }
            }
            $sources = array_unique($sources);
            sort($sources);
            $this->design->assign('sources', $sources);

            $subSources = array_unique($subSources);
            sort($subSources);
            $this->design->assign('subSources', $subSources);
        }
        else
        {
            if (!empty($_SESSION['filter_scorista']))
            {
                $filter_scorista = unserialize($_SESSION['filter_scorista']);
            }
            else
            {
                $filter_scorista = array(
                    'from' => array(
                        '0',
                        '450',
                        '600',
                        '700',
                    ),
                    'to' => array(
                        '449',
                        '599',
                        '699',
                        '1000',
                    ),
                );
            }
            $this->design->assign('filter_scorista', $filter_scorista);
        }

        return $this->design->fetch('total_report.tpl');
    }

}