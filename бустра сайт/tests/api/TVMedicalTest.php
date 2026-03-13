<?php

namespace api;

require __DIR__ . "/../../api/TVMedical.php";
require __DIR__ . "/../../api/Database.php";

use Database;
use PHPUnit\Framework\TestCase;
use TVMedical;

class TVMedicalTest extends TestCase
{
    private $dbMock;
    private $tvMedical;

    protected function setUp(): void
    {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $_SERVER['SERVER_PORT'] = '8088';
        $_SERVER['HTTP_HOST'] = 'localhost:8088';

        // Создаем мок объекта Database
        $this->dbMock = $this->createMock(Database::class);

        // Инициализируем объект TVMedical с моком Database
        $this->tvMedical = new TVMedical($this->dbMock);
    }

    public function testGetVItaMedPriceForNewClient()
    {
        // Настройка ожидаемого результата и входных параметров
        $amount = 3001;
        $is_new_client = true;
        $expectedPrice = 650; // Ожидаемая цена

        $expectedQuery = "SELECT id, price FROM __vita_med_conditions WHERE is_new = 1 AND to_amount >= 1000 ORDER BY to_amount ASC LIMIT 1";

        // Создание мока для mysqli_result
        $mysqliResultMock = $this->createMock(\mysqli_result::class);
        $mysqliResultMock->method('fetch_object')->willReturn((object) ['price' => $expectedPrice]);

        // Мокирование placehold, query и result
        $this->dbMock->method('placehold')
            ->with(
                'SELECT id, price FROM __vita_med_conditions WHERE is_new = ? AND to_amount >= ? ORDER BY to_amount ASC LIMIT 1',
                $is_new_client,
                $amount
            )
            ->willReturn($expectedQuery);

        $this->dbMock->method('query')
            ->with($expectedQuery)
            ->willReturn($mysqliResultMock);

        $this->dbMock->method('result')
            ->willReturn((object) ['price' => $expectedPrice]);

        $result = $this->tvMedical->getVItaMedPrice($amount, $is_new_client);

        $this->assertEquals($expectedPrice, $result->price);
    }
}
