<?php

error_reporting(-1);
ini_set('display_errors', 'On');
ini_set('max_execution_time', '600');
date_default_timezone_set('Europe/Moscow');

require_once dirname(__FILE__).'/../api/Simpla.php';
require_once __DIR__ . '/../scorings/BoostraPTI.php';

class calculatePTI extends Simpla
{
    /**
     * call crons
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->run();
    }

    /**
     * Run axilink wait crons
     * @return void
     */
    public function run()
    {
        $query = $this->db->placehold("
            SELECT id
            FROM __orders o
            WHERE
                IFNULL(o.confirm_date, '1970-01-01') >= '2023-04-01'
                AND IFNULL(o.pti_loan, -1) < 0
            ORDER BY id DESC
            LIMIT 100");
        $this->db->query($query);
        $orders_ids = $this->db->results();
        foreach($orders_ids as $row) {
            $order = $this->orders->get_order($row->id);
            $pti = new BoostraPTI($order);
            if($pti->setSource()) {
                $pti->toggleDetails(true);
                $dataPTI = $pti->getPTIData();
                $this->orders->update_order($row->id, ['pti_loan' => $dataPTI['rosstat_pti'] ?? 0]);
            }
        }
    }
}

new calculatePTI;