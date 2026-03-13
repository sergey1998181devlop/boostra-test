<?php

namespace App\Core\Application\Session;

use App\Core\Application\Session\Abstracts\Session as AbstractSession;
use App\Core\Application\Traits\Singleton;

class FlushMessage extends AbstractSession {
    use Singleton;

    private function __construct() {
        if (isset($_SESSION['flush'])) {
            foreach ($_SESSION['flush'] as $key => $flashMessage) {
                $_SESSION['flush'][$key]['remove'] = true;
            }
        }
    }

    /**
     * Set data in flush session.
     */
    public function set(string $key, $data): void {
        $_SESSION['flush'][$key] = [
            'remove' => false,
            'data'   => $data,
        ];

    }

    /**
     * Get data from the flush session.
     */
    public function get(string $key) {
        return $_SESSION['flush'][$key]['data'] ?? null;
    }

    /**
     * Check if the data exists.
     */
    public function has(string $key): bool {
        if (isset($_SESSION['flush'][$key])) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Delete data from flush session.
     */
    private function destroyFlushSession(): void {
        if (isset($_SESSION['flush'])) {
            foreach ($_SESSION['flush'] as $key => $flash) {
                if ($_SESSION['flush'][$key]['remove']) {
                    unset($_SESSION['flush'][$key]);
                }
            }
        }
    }

    public function __destruct() {
        $this->destroyFlushSession();
    }
}
