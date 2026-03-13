<?php
error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__FILE__) . '/../../api/Simpla.php';

class WorkerKiller_mt extends Simpla
{
    private $args;
    private const KILLERS_LIST = [
        'efrsb' => 'ScoringsCronEFRSBmt_EXIT',
        'fns' => 'ScoringsCronFNSmt_EXIT',
        'audit' => 'ScoringsCron_mt_EXIT',
        'audit2' => 'ScoringsCron2mt_EXIT',
        'audit3' => 'ScoringsCron3mt_EXIT',
        'axi2cron' => 'Axi2Cron_mt_EXIT',
        'axicron' => 'AxiCron_mt_EXIT',
        'scorista_import' => 'ScoristaImportCron_mt_EXIT',
        'scorista_new' => 'ScoristaNewCron_mt_EXIT',
    ];

    public function __construct($args)
    {
        $this->args = $args;
        parent::__construct();
    }

    public function run()
    {
        array_shift($this->args);
        $queries = array_map(fn($killer_id) => "SELECT GET_LOCK('{$killer_id}', 50)", array_intersect_key(self::KILLERS_LIST, array_fill_keys($this->args, '')));
        if(empty($queries)) {
            echo 'Please, use available parameters: ' . implode(', ', array_keys(self::KILLERS_LIST)) . "\n";
            return;
        }

        $this->db->query(implode(' UNION ALL ', $queries));
        echo "Processing:\n" . implode("\n", $queries) . "\n";
        echo "*** PLEASE WAIT ***\n";
        sleep(55);
        echo "*** JOB FINISHED ***\n";
    }
}

set_time_limit(60);
$cron = new WorkerKiller_mt($argv);
$cron->run();