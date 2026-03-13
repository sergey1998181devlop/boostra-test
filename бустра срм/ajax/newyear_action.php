<?php

chdir('..');
require 'api/Simpla.php';

class NewYearAction extends Simpla
{
    public function analyzeAction()
    {
        $action = $this->request->post('action', 'string');

        if ($action == 'download') {
            $view = new NewYearCodesView();
            $view->downloadParticipantCodes();
        }
    }
}

(new NewYearAction())->analyzeAction();
