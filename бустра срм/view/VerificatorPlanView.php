<?php

class VerificatorPlanView extends View
{
    public function fetch()
    {

        if ($this->request->method('post'))
        {
            $verificator_daily_plan_pk = $this->request->post('verificator_daily_plan_pk');
            $verificator_daily_plan_nk = $this->request->post('verificator_daily_plan_nk');
            
            $this->settings->verificator_daily_plan_pk = $verificator_daily_plan_pk;
            $this->settings->verificator_daily_plan_nk = $verificator_daily_plan_nk;

            $cc_pr_prolongation_plan = $this->request->post('cc_pr_prolongation_plan');
            $cc_pr_close_plan = $this->request->post('cc_pr_close_plan');
            
            $this->settings->cc_pr_prolongation_plan = $cc_pr_prolongation_plan;
            $this->settings->cc_pr_close_plan = $cc_pr_close_plan;

        }
        else
        {
            $verificator_daily_plan_pk = $this->settings->verificator_daily_plan_pk;
            $verificator_daily_plan_nk = $this->settings->verificator_daily_plan_nk;

            $cc_pr_prolongation_plan = $this->settings->cc_pr_prolongation_plan;
            $cc_pr_close_plan = $this->settings->cc_pr_close_plan;
        }
        
        $this->design->assign('verificator_daily_plan_pk', $verificator_daily_plan_pk);
        $this->design->assign('verificator_daily_plan_nk', $verificator_daily_plan_nk);

        $this->design->assign('cc_pr_prolongation_plan', $cc_pr_prolongation_plan);
        $this->design->assign('cc_pr_close_plan', $cc_pr_close_plan);
        
        return $this->design->fetch('verificator_plan.tpl');
    }
}