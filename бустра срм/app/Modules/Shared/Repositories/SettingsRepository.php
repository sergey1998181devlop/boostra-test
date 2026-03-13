<?php

namespace App\Modules\Shared\Repositories;

/**
 * Репозиторий для работы с настройками системы
 */
class SettingsRepository
{
    /** @var \Database */
    private $db;

    public function __construct(\Database $db)
    {
        $this->db = $db;
    }

    /**
     * Получить значение настройки по ключу
     *
     * @param string $key Ключ настройки
     * @param mixed $default Значение по умолчанию
     * @return mixed Значение настройки
     */
    public function get(string $key, $default = null)
    {
        $this->db->query("SELECT value FROM s_settings WHERE name = ?", $key);
        $result = $this->db->result();
        return $result ? $result->value : $default;
    }
    
    /**
     * Получить значение настройки по имени (алиас для get)
     *
     * @param string $name Имя настройки
     * @return string|null Значение настройки
     */
    public function getValue(string $name): ?string
    {
        return $this->get($name);
    }
    
    /**
     * Получить пароль API для 1C
     *
     * @return string Пароль или пустая строка если не найден
     */
    public function get1CApiPassword(): string
    {
        $password = $this->getValue('api_password');
        
        if ($password === null) {
            logger('error')->warning('api_password not found in s_settings');
            return '';
        }
        
        return $password;
    }
}
