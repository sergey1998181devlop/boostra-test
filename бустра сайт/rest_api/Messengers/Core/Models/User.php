<?php

namespace Messengers\Core\Models;

use Messengers\Config;

final class User extends Model
{

    protected static $tableName = Config::DB_USERS_TABLE_NAME;

    public function init(): bool
    {
        return true;
    }
}