<?php

require_once 'View.php';

class PaymentLinkView extends View
{
    public function fetch()
    {
        //var_dump(123);
        //$order_id = $this->request->get('order_id');
        //$user_id = $this->request->get('user_id');

        $number = base64_decode($this->request->get('number'));

        if ($number === 'B22-0501950') {
            header('Location: /');
        }

        //$user_balance = $this->users->get_user_balance($user_id);

        $query = $this->db->placehold("SELECT
        ub.id,
        ub.user_id,
        ub.zaim_number,
        ub.zaim_summ,
        ub.percent,
        ub.ostatok_od,
        ub.ostatok_percents,
        ub.ostatok_peni,
        ub.client,
        ub.zaim_date,
        ub.zayavka,
        ub.sale_info,
        ub.payment_date,
        ub.prolongation_amount,
        ub.prolongation_summ_percents,
        ub.prolongation_summ_insurance,
        ub.prolongation_summ_sms,
        ub.prolongation_summ_cost,
        ub.prolongation_count,
        ub.allready_added,
        ub.last_prolongation,
        ub.last_update,
        ub.buyer
        FROM __user_balance ub WHERE ub.zaim_number=? LIMIT 1", $number);

        $this->db->query($query);
        $user_balance = $this->db->result();


        //$order = $this->orders->get_order($order_id);

        //$this->design->assign('order', $order);
        $this->design->assign('user_balance', $user_balance);

        
        return $this->design->fetch('payment_link/link.tpl');
    }

}