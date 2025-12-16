<?php

namespace api;

require __DIR__ . "/../../api/Multipolis.php";

use DateTime;
use Multipolis;
use PHPUnit\Framework\TestCase;
use stdClass;


class MultipolisTest extends TestCase
{
    /**
     * Проверка суммы консьерж-сервиса на текущую дату платежа
     * @return void
     * @throws \Exception
     */
    public function testGetMultipolisAmountWithCurrentDate()
    {
        $user = $this->createUser('now');
        $multipolis = new Multipolis();
        $expected = 200; // Ожидаемый результат, если нет просрочки
        $result = $multipolis->getMultipolisAmount($user);
        $this->assertEquals($expected, $result);
    }

    /**
     * Проверка суммы консьерж-сервиса на дату с просрочкой в 30 дней
     * @return void
     * @throws \Exception
     */
    public function testGetMultipolisAmountWithPastDate()
    {
        $user = $this->createUser('-30 days'); // 30 дней назад
        $multipolis = new Multipolis();
        $expected = 300; // Примерный результат при просрочке
        $result = $multipolis->getMultipolisAmount($user);
        $this->assertEquals($expected, $result);
    }

    /**
     * Проверка суммы консьерж-сервиса на дату платежа на 30 дней вперед
     * @return void
     * @throws \Exception
     */
    public function testGetMultipolisAmountWithFutureDate()
    {
        $user = $this->createUser('+30 days'); // 30 дней вперед
        $multipolis = new Multipolis();
        $expected = 200; // Предполагаем, что просрочки нет
        $result = $multipolis->getMultipolisAmount($user);
        $this->assertEquals($expected, $result);
    }

    /**
     * @param $dateModifier
     * @return stdClass
     * @throws \Exception
     */
    private function createUser($dateModifier): stdClass
    {
        $user = new stdClass();
        $user->balance = new stdClass();
        $user->balance->payment_date = (new DateTime($dateModifier))->format('Y-m-d');
        $user->balance->ostatok_od = 1000;
        return $user;
    }
}
