<?php

header("Content-type: application/json; charset=UTF-8");
header("Cache-Control: must-revalidate");
header("Pragma: no-cache");
header("Expires: -1");

chdir('..');

require 'api/Simpla.php';

class LinksActions extends Simpla
{

    public function analyzeActions()
    {
        $action = $this->request->post('action', 'string');

        switch ($action) {
            case 'generateLink':
                $this->generateLinksAction();
                break;

            default:
                break;
        }
    }

    private function generateLinksAction()
    {
        $linkStats = $this->linkToSafeFlow->generateSafeLink();
        echo json_encode($linkStats);
        exit;
    }


}

(new LinksActions())->analyzeActions();