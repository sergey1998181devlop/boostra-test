<?php
namespace api\helpers;

class DumpHelper {

    public static function dump($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    public static function dd($data)
    {
        self::dump($data);
        die();
    }

}