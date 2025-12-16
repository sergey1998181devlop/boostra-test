<?php

namespace App\Core\Application\Session;

use App\Core\Application\Session\Abstracts\Session as AbstractSession;
use App\Core\Application\Traits\Singleton;

class Session extends AbstractSession {
    use Singleton;

    /**
     * Initialize session if not started
     */
    protected function initialize(): void {
        if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Check if session is available
     */
    public function isActive(): bool {
        return PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Set data in session.
     */
    public function set(string $key, $data): void {
        if (!$this->isActive()) {
            return;
        }
        $_SESSION[$key] = $data;
    }

    /**
     * Get data from the session.
     */
    public function get(string $key) {
        if (!$this->isActive()) {
            return null;
        }
        return $_SESSION[$key] ?? null;
    }

    /**
     * Delete data from session.
     */
    public function delete(string $key): void {
        if ($this->isActive()) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Check if the data exists.
     */
    public function has(string $key): bool {
        return $this->isActive() && isset($_SESSION[$key]);
    }
}