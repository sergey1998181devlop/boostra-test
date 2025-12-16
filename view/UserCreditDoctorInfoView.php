<?php

require_once('View.php');

/**
 * Class UserCreditDoctorInfoView
 */
class UserCreditDoctorInfoView extends View
{
    function fetch()
    {
        $doc_name = $this->request->get('doc_name');
        if(!empty($doc_name)) {
            $this->design->assign('doc_link', $this->config->root_url . '/files/docs/credit_doctor/' . $doc_name);
            return $this->design->fetch('user_credit_doctor/pages/page.tpl');
        } else {
            $this->design->assign('order_items', $this->user_credit_doctor::ORDER_ITEMS);
            return $this->design->fetch('user_credit_doctor/user_credit_doctor_info.tpl');
        }
    }
}
