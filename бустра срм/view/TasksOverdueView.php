<?php

require_once 'View.php';
use TasksOverdueController;

class TasksOverdueView extends View {

    public function fetch() {
        
        $tasksObj = new TasksOverdueController();
        $tasks = $tasksObj->getTasks();

        foreach ($tasks as $task) {
            $json = json_decode($task->json);
            if (isset($json->calls[0]->time)) {
                $task->dateOfTheLastCall = date('', $json->calls[0]->time);
            }

            foreach ($json->custom as $custom) {
                if ($custom->id == 1712849) {
                    if(isset($custom->value[0])){
                        $task->iPromiseToPayWithinTwoDays = $custom->value[0];
                    }
                } elseif ($custom->id == 1712852) {
                    if (isset($custom->value[0])) {
                        $task->ReadyToExtend = $custom->value[0];
                    }
                } elseif ($custom->id == 1712850) {
                    if (isset($custom->value[0])) {
                        $task->professionalHelp = $custom->value[0];
                    }
                } elseif ($custom->id == 1706957) {
                    if (isset($custom->value[0])) {
                        $task->lptComment = $custom->value[0];
                    }
                }
            }
            if(isset($json->calls[0]->record)){
                $task->recordCall = $json->calls[0]->record;
            }
            
            $task->dayDelay = ceil(floor((strtotime(date('Y-m-d h:i:s')) - strtotime($task->payment_date)) / (60 * 60 * 24)));
        }
        $this->design->assign('tasks', $tasks);

        $statuses = [
            1608851 => 'Выход за 1-3',
            1586258 => 'Недозвон',
            1582208 => 'Оплата',
            1586259 => "Бросил Трубку",
            1582207 => "Договор отправлен",
            1582205 => "Встреча назначена",
            1582214 => "Интерес подтвержден"
        ];

        $this->design->assign('statuses', $statuses);
        return $this->design->fetch('TasksOverdue.tpl');
    }

}
