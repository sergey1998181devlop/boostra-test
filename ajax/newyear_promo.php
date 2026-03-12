<?php

chdir('..');
require_once 'view/NewYearPromotionView.php';

$view = new NewYearPromotionView();
$view->fetch();

