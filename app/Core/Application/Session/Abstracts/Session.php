<?php

namespace App\Core\Application\Session\Abstracts;

abstract class Session {

    /**
     * Session start.
     */
    protected function __construct()
    {
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    /**
     * Set data in session.
     */
    public function set(string $key, $data): void {}

    /**
     * Get data from the session.
     */
    public function get(string $key) {}

    /**
     * Delete data from session.
     */
    public function delete(string $key): void {}

}
