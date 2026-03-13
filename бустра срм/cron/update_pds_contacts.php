<?php
session_start();
chdir('..');

require_once dirname(__FILE__).'/../api/Simpla.php';

$managers = new Managers();
$selectedManagers = $managers->getSelectedManagers(date("Y-m-d"));
$tasks = new Tasks();

$arr = [];


foreach ($selectedManagers as $manager){
    $db_results = $tasks->getTasks(['task_date'=>date("Y-m-d"),'manager_id'=>$manager->manager_id]);

    $company = $managers->getCompany((int)$manager->manager_id);

    $chunks = array_chunk($db_results, 20);

    foreach ($chunks as $chunk) {

        $voximplant = new Voximplant();
        $voximplant->sendDnc($company, $chunk);
    }

}
