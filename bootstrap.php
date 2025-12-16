<?php

// определяем абсолютный путь к корню проекта и дублируем его в ROOT,
// чтобы во всём коде (хелперах, конфигах, автозагрузчиках) не пришлось хардкодить пути.
defined('APP_ROOT') or define('APP_ROOT', realpath(__DIR__));
defined('ROOT')     or define('ROOT', APP_ROOT);

require APP_ROOT . '/vendor/autoload.php';
require APP_ROOT . '/lib/autoloader.php';

require APP_ROOT . '/app/Core/Helpers/BaseHelper.php';