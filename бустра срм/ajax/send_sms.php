<?php
session_start();
chdir('..');

require_once dirname(__FILE__).'/../api/Simpla.php';

$simpla = new Simpla();
$user_id = $simpla->request->post('user_id');
$zaim_number = $simpla->request->post('zaim_number');
$asp = $simpla->users->getZaimListAsp($zaim_number);
$txt = "not_updated";
if (empty($asp)){
   $send =  $simpla->tasks->update_vox_call($user_id);
   if ($send){
       $txt = "updated";
   }
}

$simpla->response->json_output(['success' => $txt]);
