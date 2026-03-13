<?php

namespace App\Modules\TicketAssignment\Adapters;

use App\Modules\TicketAssignment\Contracts\SettingsInterface;

/**
 * Адаптер для работы с настройками через Simpla
 */
class SettingsAdapter implements SettingsInterface
{
    /** @var \Simpla */
    private $simpla;

    public function __construct(\Simpla $simpla)
    {
        $this->simpla = $simpla;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key, $default = null)
    {
        return $this->simpla->settings->$key ?? $default;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, $value): bool
    {
        $this->simpla->settings->$key = $value;
        return true;
    }
}
