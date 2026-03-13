<?php
session_start();
chdir('..');

require_once dirname(__FILE__).'/../api/Simpla.php';


$tasks = new Tasks();
$voximplant = new Voximplant();
$managers = new Managers();
$selectedManager = $managers->getSelectedManagers(date('Y-m-d',strtotime("-1 days")));

$deletedManager = $managers->getDeletedManager();
$allManagers = array_merge($selectedManager,$deletedManager);

foreach ($deletedManager as $manager) {
    $managers->update_manager_data($manager->manager_id,['vox_deleted'=>false]);
}
foreach ($allManagers  as $manager) {

    $voximplant->getDncNumbers(null,'deleteDnc',$manager->manager_id);

}

exit();
