<?php

require_once dirname(__DIR__) . '/api/Simpla.php';
require_once dirname(__DIR__) . '/services/autoloader.php';

use services\IntegrationService;

//try {
    $integrationService = new IntegrationService();
    $integrationService->bankiRuStart();

    echo 'Operation successful completed';
//} catch (Exception $e) {
 //   echo $e->getMessage();
//}