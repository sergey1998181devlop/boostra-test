<?php

session_start();
chdir('..');

$root = dirname($_SERVER['PHP_SELF'],2);

require 'api/Simpla.php';
$simpla = new Simpla();


$filter['from']  = date('Y-m-d', time() - 86400*2);
$filter['to'] = date('Y-m-d', time() - 86400);

$tasks = $simpla->users->get_cctasks($filter);
$ccprologation = new CCProlongationsView();

$tasks = $ccprologation->format_tasks($tasks);

$managers = $simpla->managers->getScheduledManagers(date("Y-m-d"),true);

$day = date('d');
if ($day % 2 == 0) {
    usort($managers, function($a, $b) {
        return strcmp($a->name, $b->name);
    });
}else{
    usort($managers, function($a, $b) {
        return strcmp($b->name,$a->name);
    });
}

$i = 0;
$period = 'period_one_two';
$max_i = count($managers);

foreach ($tasks as $t) {

    $user = $simpla->users->get_user((int)$t->user_id);


    $timezone = $simpla->users->get_timezone($user->Faktregion);

    $existingTask = $simpla->tasks->existingTask($t->zaim_number);

    if (empty($existingTask)) {
        $vox_call = false;
        $get_asp = $simpla->users->getZaimListAsp($t->zaim_number);
        if (empty($get_asp)) {
            $vox_call = $simpla->tasks->getVoxCall($t->zaim_number);
        }

        $simpla->tasks->add_pr_task(array(
            'number' => $t->zaim_number,
            'user_id' => $t->user_id,
            'task_date' => date('Y-m-d'),
            'user_balance_id' => $t->id,
            'manager_id' => $managers[$i]->id,
            'close' => 0,
            'prolongation' => 0,
            'created' => date('Y-m-d H:i:s'),
            'od_start' => $t->loan_type == 'IL' ? $t->overdue_debt_od_IL+ $t->next_payment_od : $t->ostatok_od,
            'percents_start' => $t->loan_type == 'IL' ? $t->overdue_debt_percent_IL+ $t->next_payment_percent : $t->ostatok_percents,
            'period' => $period,
            'status' => 0,
            'timezone' => $timezone,
            'vox_call' => $vox_call
        ));

        $i++;
        if ($i == $max_i)
            $i = 0;
    }
}
foreach ($managers as $manager) {
    $simpla->voximplant->sendCcprolongations($manager->id,true, $manager->role);

}
header('Content-type:application/json');
echo json_encode(array('success' => 1));
exit;

