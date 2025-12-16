<?php

namespace Messengers\Models;

use Messengers\Config;

final class Manager extends Model
{

    protected static $tableName = Config::DB_MANGERS_TABLE;
    public $name = Config::DEFAULT_MANAGER_NAME;
    public $avatar = Config::DEFAULT_MESSENGER_AVATAR;

    /**
     * Инициализация таблиц в базе
     * @return bool
     */
    public function init(): bool
    {
        return true;
    }

    public function getManagerInfo(int $managerId): self
    {
        $manager = $this->find($managerId);
        if ($manager) {
            return $manager;
        }
        return $this;
    }
}