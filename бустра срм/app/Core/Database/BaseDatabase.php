<?php

namespace App\Core\Database;

use App\Core\Application\Traits\Singleton;
use App\Infrastructure\Database\DatabaseManager;
use Medoo\Medoo;

class BaseDatabase {
    use Singleton;

    private ?Medoo $medooInstance = null;

    private function __construct() {}

    public function db(): Medoo {
        if ($this->medooInstance === null) {
            $this->medooInstance = DatabaseManager::singleton()->connection('default');
        }

        return $this->medooInstance;
    }
}
