<?php

require_once('View.php');

class UserCreditRatingView extends View
{
    function fetch()
    {
        //ФИО
        $user_name = "{$this->user->lastname} {$this->user->firstname} {$this->user->patronymic}";
        $this->design->assign('user_name', $user_name);

        //показываем баннер КР
        $this->design->assign('show_rating_banner', true);

        //кредитные карты пользователя
        $cards = $this->users->getUserCardsByUserId((int)$this->user->id);
        $this->design->assign('cards', $cards);

        //переменная пользователя
        $this->design->assign('user', $this->user);

        //необходима для оплаты КР
        $insurer = $this->orders->get_insure_ip();
        $this->design->assign('insurer', $insurer);

        if (empty($this->user->date_skip_cr_visit)) {
            $this->users->updateSkipUserTime($this->user->id, date('Y-m-d H:i:s', strtotime('+ 5 minute')));
        }

        $this->design->assign('use_b2p', $this->settings->b2p_enabled || $this->user->use_b2p);
        $this->design->assign('is_credit_rating_page', 1);

        // добавим кастомную метрику
        $this->custom_metric->addMetricAction($this->custom_metric::GOAL_CR_NK_VISIT_PAGE, 1);

        return $this->design->fetch('user_credit_rating.tpl');
    }
}
