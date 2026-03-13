<?php
error_reporting(-1);
ini_set('display_errors', 'On');

date_default_timezone_set('Europe/Moscow');

session_start();
chdir('..');

require_once 'api/Simpla.php';

class Axi2CronTempFix extends Simpla
{
    public function run()
    {
        $this->db->query("
            SELECT * 
            FROM s_scorings 
            WHERE 
                `type` = 17 AND 
                `created` >= '2025-12-31 00:00:00' AND
                `status` = 7 
            ORDER BY id ASC
            LIMIT 100
        ");

        $scorings = $this->db->results() ?: [];
        foreach ($scorings as $scoring) {
            $this->dbrainAxi->getInfo($scoring);
        }

        var_dump("ok");
    }
}

(new Axi2CronTempFix())->run();
