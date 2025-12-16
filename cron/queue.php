<?php

require_once dirname(__DIR__) . '/api/Simpla.php';

(new Simpla())->queue->run();
