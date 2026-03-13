<?php

chdir('..');

$root = dirname($_SERVER['PHP_SELF'], 2);
require 'api/Simpla.php';
$simpla = new Simpla();

class ScheduleCronMinusDays extends Simpla
{
    public function __construct()
    {
        $this->run();
    }

    /**
     * @return void
     */
    public function run()
    {
        $periods = ['-5', '-4', '-3', '-2', '-1'];
        $managerData = $this->getManagerData();
        $day = 5;
        $errorLogs = [];
        $this->logging(__METHOD__, 'schedule_cron_minus_days.php', (array)[25 => $managerData], [], 'schedule_cron_minus.log');
        if (!empty($managerData)) {
            try {
                foreach ($periods as $period) {
                    $filter['from'] = $filter['to'] = date('Y-m-d', strtotime("+$day days"));
                    $day--;
                    $tasks = $this->users->get_cctasks($filter);
                    $formattedTasks = $this->formatTasks($tasks);
                    $manager = $managerData[$period];
                    $chunkedTasks = array_chunk($formattedTasks, 100);
                    foreach ($chunkedTasks as $chunk) {
                        $values = [];
                        foreach ($chunk as $task) {
                            $taskDate = date('Y-m-d');
                            $created = date('Y-m-d H:i:s');
                            $user = $this->users->get_user((int)$task->user_id);
                            $timezone = $this->users->get_timezone($user->Faktregion);
                            if ($task->loan_type == 'IL') {
                                $task->ostatok_od = $task->overdue_debt_od_IL + $task->next_payment_od;
                                $task->ostatok_percents = $task->overdue_debt_percent_IL + $task->next_payment_percent;
                            }
                            $values[] = "('$task->zaim_number', '$task->user_id', '$taskDate', $task->id, $manager->id, 0, 0, '$created', $task->ostatok_od, $task->ostatok_percents, '$period', 0, $timezone)";
                        }
                        $taskData = implode(',', $values);
                        $this->insertTasks($taskData);
                        $errorLogs[] = [
                            'data' => [50 => $managerData],
                        ];
                    }
                }
            } catch (Throwable $e) {
                $this->logging(__METHOD__, 'schedule_cron_minus_days.php', (array)[54 => $e->getMessage()], [], 'schedule_cron_minus.log');
                $this->logging(__METHOD__, 'schedule_cron_minus_days.php', (array)[55 => $e->getCode()], [], 'schedule_cron_minus.log');
            }
            $this->logging(__METHOD__, 'schedule_cron_minus_days.php', (array)$errorLogs, [], 'schedule_cron_minus.log');
            $this->logging(__METHOD__, 'schedule_cron_minus_days.php', (array)[64 => $managerData], [], 'schedule_cron_minus.log');
            foreach ($managerData as $manager) {
                $this->voximplant->sendCcprolongations($manager->id, false, 'robot_minus', $manager->minus);

            }
        }

    }

    /**
     * @param $tasks
     * @return array
     */
    private function formatTasks($tasks): array
    {
        $formattedTasks = [];
        $numbers = [];

        foreach ($tasks as $task) {
            if (!isset($numbers[$task->zaim_number])) {
                $numbers[$task->zaim_number] = $task;
            } elseif ($task->user_id > $numbers[$task->zaim_number]->user_id) {
                $numbers[$task->zaim_number] = $task;
            }
        }

        foreach ($numbers as $number_task) {
            $formattedTasks[] = $number_task;
        }
        return $formattedTasks;
    }

    private function getManagerData(): array
    {
        $managerData = [];
        $query = $this->db->placehold("SELECT manager_id as id, company, minus FROM manager_company WHERE minus IN('-1', '-2', '-3', '-4', '-5')");
        $this->db->query($query);
        while ($row = $this->db->result()) {
            $managerData[$row->minus] = $row;
        }
        return $managerData;

    }

    /**
     * @param $data
     * @return void
     */
    private function insertTasks($data)
    {
        $query = $this->db->placehold("INSERT INTO s_pr_tasks
            (number, user_id, task_date,user_balance_id, manager_id,close,prolongation,created,od_start,percents_start,period,status,timezone)
                VALUES $data");

        $this->db->query($query);
        $this->logging(__METHOD__, 'schedule_cron_minus_days.php', (array)[112 => $query], [], 'schedule_cron_minus.log');

    }
}

new ScheduleCronMinusDays();
