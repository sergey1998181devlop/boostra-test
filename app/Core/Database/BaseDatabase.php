<?php

namespace App\Core\Database;

use App\Core\Application\Application;
use App\Core\Application\Traits\Singleton;
use Database;
use Exception;

class BaseDatabase {
    use Singleton;

    /** @var Database|null */
    private $db = null;

    private function __construct() {}

    public static function getInstance(): BaseDatabase
    {
        return self::singleton();
    }

    /**
     * @return Database
     * @throws Exception
     */
    public function db(): Database
    {
        if ($this->db === null) {
            // резолвим из контейнера
            $this->db = Application::getInstance()->make(Database::class);
        }
        return $this->db;
    }

}
