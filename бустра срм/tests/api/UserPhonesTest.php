<?php

namespace api;

require __DIR__ . "/../../api/UserPhones.php";

use UserPhones;
use PHPUnit\Framework\TestCase;

class UserPhonesTest extends TestCase
{
    const REPORT_FILE_PATH = './tests/api/files/user_phones.xml';

    const REPORT_FILE_PHONES = [
        '+7(917)1676217',
        '+7(927)6029410',
        '+7(995)0989410',
        '79967411819'
    ];

    /**
     * Загружаем тестовый xml файл с кредитной историей и пытаемся прочитать заранее известные номера.
     * @test
     */
    public function get_phones_in_xml_string()
    {
        $string = $this->load_xml();
        // Удалось ли загрузить тестовый файл
        $this->assertIsString($string);

        // Получаем номера из тестового файла
        $userPhones = new UserPhones();
        $parse_result = $userPhones->parse_xml($string);
        $this->assertIsArray($parse_result);

        // Оставляем только уникальные найденные номера без дубликатов значений
        $phones = [];
        foreach ($parse_result as $source) {
            foreach ($source as $phone) {
                // В разных источниках номера в разных полях
                $number = $phone->number ?? $phone->phone;
                // В некоторых источниках может не быть номера (например там почта)
                if (empty($number))
                    continue;

                $number = (string)$number;
                if (!in_array($number, $phones))
                    $phones[] = $number;
            }
        }

        // Найденные номера должны совпадать с теми, которые реально находятся в файле
        $this->assertEquals(self::REPORT_FILE_PHONES, $phones);
    }

    private function load_xml()
    {
        $xmlContent = file_get_contents(self::REPORT_FILE_PATH);
        return $xmlContent;
    }
}