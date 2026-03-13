<?php

require_once 'View.php';
class LinkToSafeFlowView extends View
{

    public function fetch()
    {

        $linksStat = $this->linkToSafeFlow->getLinkStats();

        $this->design->assign('links_stat', $linksStat);

        return $this->design->fetch('link_to_safe_flow.tpl');
    }

}