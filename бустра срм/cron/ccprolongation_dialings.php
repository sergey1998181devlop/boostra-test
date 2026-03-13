<?php
session_start();
chdir('..');

require_once dirname(__FILE__).'/../api/Simpla.php';


$voximplant = new Voximplant();
$dnc = $voximplant->getDncNumbers('ongoing','dialings');

exit();
