<?php

require_once 'View.php';

class DocsHistoryView extends View
{

    public function fetch()
    {

        $logs = $this->docs->get_logs();
        $this->design->assign('logs', $logs);

        return $this->design->fetch('docs-history.tpl');
    }
}
