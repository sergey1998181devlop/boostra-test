<?php

namespace api;

require __DIR__ . "/../../api/Installments.php";

use DateInterval;
use DateTime;
use Installments;
use PHPUnit\Framework\TestCase;


class InstallmentsTest extends TestCase
{
    /**
     * InstallmentsTest::testCheckAcceptWithNewerDate()
     *
     * @return void
     * @throws \Exception
     */
    public function testCheckAcceptWithNewerDate()
    {
        $expected = 0; // Ожидаемый результат, если дата не наступила

        $installments = new Installments();        
        $test_date = $this->getTestDate(-1);

        $result = $installments->check_accept($test_date);
        $this->assertEquals($expected, $result);
        
    }

    /**
     * InstallmentsTest::testCheckAcceptWithOlderDate()
     *
     * @return void
     * @throws \Exception
     */
    public function testCheckAcceptWithOlderDate()
    {
        $expected = 1; // Ожидаемый результат, если дата наступила

        $installments = new Installments();        
        $test_date = $this->getTestDate(-15);

        $result = $installments->check_accept($test_date);
        $this->assertEquals($expected, $result);
        
    }

    /**
     * InstallmentsTest::getTestDate()
     *
     * @param mixed $shift
     * @return string
     * @throws \Exception
     */
    private function getTestDate($shift): string
    {
        $interval = 'P'.abs($shift).'D';
        $dt = new DateTime();
        
        if ($shift > 0) {
            $dt->add(new DateInterval($interval));
        } elseif ($shift < 0) {
            $dt->sub(new DateInterval($interval));
        }
        
        return $dt->format('Y-m-d H:i:s');
    }
    
}
