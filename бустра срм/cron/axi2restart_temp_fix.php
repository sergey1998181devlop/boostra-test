<?php
error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');

session_start();
chdir('..');

require_once 'api/Simpla.php';

class Axi2RestartTempFix extends Simpla
{
    public function run()
    {
        $this->db->query("
            SELECT * 
            FROM s_scorings
            WHERE 
                `type` = 17 AND 
                `status` = 5 AND 
                created >= '2026-01-28 00:00:00' AND 
                created <= '2026-01-29 02:00:00' AND
                scorista_id = 'paused' 
            ORDER BY id DESC 
            LIMIT 100
        ");

        $scorings = $this->db->results() ?: [];
        foreach ($scorings as $scoring) {
            $this->dbrainAxi->getInfo($scoring);
        }

        var_dump("ok");
    }
}

(new Axi2RestartTempFix())->run();
