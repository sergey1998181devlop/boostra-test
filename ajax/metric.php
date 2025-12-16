<?php
session_start();
chdir('..');

require_once 'vendor/autoload.php';
require_once 'api/Simpla.php';

/**
 * Взаимодействие метрики с фронтом
 * Class AjaxMetric
 */
class AjaxMetric extends Simpla {

    public function run()
    {
        $metric_goal_id = $this->request->post('metric_goal_id', 'integer');
        $this->custom_metric->addMetricAction($metric_goal_id);
        $this->request->json_output(['status' => 'ok']);
    }
}

(new AjaxMetric())->run();
