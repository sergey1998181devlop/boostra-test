<?php

// скрипт удаляет все файлы договоров и тп, загружаемые из 1с

error_reporting(-1);
ini_set('display_errors', 'On');

require_once dirname(__DIR__) . '/api/Simpla.php';

class ContractFilesCleaner extends Simpla
{
    private $contract_dir;
    
    public function run()
    {
    	$this->contract_dir = $this->config->root_dir.'files/contracts/';
    
        $this->cleaner($this->contract_dir);
        
        echo '<br />END<br />';
    }
    
    private function cleaner($dir)
    {
        $scanneds = array_diff(scandir($dir), array('..', '.', 'PFRF'));
        
        foreach ($scanneds as $scan)
        {
            $path_scan = $dir.$scan;
            if (is_dir($path_scan))
            {
                $this->cleaner($path_scan.'/');
            }
            elseif (is_file($path_scan))
            {
                unlink($path_scan);
                echo $path_scan.'<br />';
            }
            
        }
    }
}

$cleaner = new ContractFilesCleaner();
$cleaner->run();






