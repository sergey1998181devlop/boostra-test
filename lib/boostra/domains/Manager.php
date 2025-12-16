<?php

namespace boostra\domains;

use boostra\domains\abstracts\EntityObject;

/**
 * @property $id           int
 * @property $login        string
 * @property $password     string
 * @property $name         string
 * @property $name_1c      string
 * @property $role         string
 * @property $last_ip      string
 * @property $last_visit   int
 * @property $salt         string
 * @property $mango_number int
 * @property $avatar       string
 * @property $blocked      int
 * @property $vox_deleted  int
 */
class Manager extends EntityObject{
    
    public static function table(): string
    {
        return 's_managers';
    }
}