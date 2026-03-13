<?php

namespace App\Core\Database;

require_once __DIR__ . '/../../../api/Database.php';

use App\Core\Application\Application;
use App\Core\Application\Traits\Singleton;
use Database;
use Exception;

class SimplaDatabase {
    use Singleton;

    /** @var Database|null */
    private ?Database $db = null;

    private function __construct() {}

    public static function getInstance(): SimplaDatabase
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
            $this->db = Application::getInstance()->make(Database::class);
        }
        return $this->db;
    }

}
