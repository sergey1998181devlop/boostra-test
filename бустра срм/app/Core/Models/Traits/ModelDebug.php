<?php

namespace App\Core\Models\Traits;

trait ModelDebug {

    public function debug()
    {
        $this->db->debug();
        return $this;
    }

    public function beginDebug()
    {
        $this->db->beginDebug();
        return $this;
    }

    public function debugLog(): array {
        return $this->db->debugLog();
    }

    public function log(): array {
        return $this->db->log();
    }

    public function last(): string {
        return $this->db->last();
    }

    public function error() {
        return $this->db->error;
    }

    public function errorInfo() {
        return $this->db->errorInfo;
    }

    public function action(callable $function)
    {
        $this->db->action($function);

        return $this;
    }
}
