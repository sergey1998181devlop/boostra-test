<?php

namespace Api\VerifyConnection;

require_once(dirname(__DIR__) . '/VerifyConnection/VerifyBaseData.php');
require_once(dirname(__DIR__) . '/VerifyConnection/VerifyConnection.php');

class VerifyPhoneData extends VerifyBaseData
{
    public $hash;
    public $phone;
    public $phoneDecimal;
    public $count = 0;

    public function __construct(string $phone)
    {
        $this->phone = $phone; //phone format a global vertion '+7 (900) 000-00-00'
        $this->phoneDecimal = $this->phoneDecimal();
        $this->hash = VerifyConnection::hash($this->phoneDecimal);
    }

    public function countIncrement(): int
    {
        return ++$this->count;
    }

    /**
     * Очистка строки телефона
     *
     * @param string $phone
     * @return string only decimal
     */
    public static function cleanPhone(string $phone): string
    {
        return preg_replace("/[^\d]/", "", $phone);
    }

    /**
     * Форматирование телефона с первым символом 7
     *
     * @param string $phone
     * @return string 70000000000
     */
    public function phoneDecimal(string $phone = ''): string
    {
        $phoneToFormat = empty($phone) ? $phone : $this->phone;
        $result = self::cleanPhone($phoneToFormat);
        $result[0] = '7';
        return $result;
    }
}