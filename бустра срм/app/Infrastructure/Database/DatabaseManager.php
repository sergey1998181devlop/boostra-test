<?php

namespace App\Infrastructure\Database;

use App\Core\Application\Traits\Singleton;
use Medoo\Medoo;
use PDO;

class DatabaseManager
{
    use Singleton;

    /** @var Medoo[] */
    private array $instances = [];

    /** @var array */
    private array $configured = [];

    private function __construct()
    {
    }

    /**
     * Проверяет, настроено ли подключение к БД
     *
     * @param string $name
     * @return bool
     */
    public function isConfigured(string $name = 'default'): bool
    {
        $connectionName = $this->resolveConnectionName($name);
        if (array_key_exists($connectionName, $this->configured)) {
            return $this->configured[$connectionName];
        }

        $connections = config('database.connections');
        $config = $connections[$connectionName] ?? null;

        if (empty($config)) {
            $this->configured[$connectionName] = false;
            return false;
        }

        $this->configured[$connectionName] = !empty($config['host'])
            && !empty($config['database'])
            && !empty($config['username']);

        return $this->configured[$connectionName];
    }

    /**
     * Получить соединение с БД
     *
     * @param string $name
     * @return Medoo
     */
    public function connection(string $name = 'default'): Medoo
    {
        $connectionName = $this->resolveConnectionName($name);

        if (!isset($this->instances[$connectionName])) {
            if (!$this->isConfigured($connectionName)) {
                throw new \RuntimeException("Database connection '{$connectionName}' is not configured. Please check config/config.php");
            }

            $connections = config('database.connections');
            $config = $connections[$connectionName];

            $this->instances[$connectionName] = new Medoo([
                'type'     => $config['driver'],
                'host'     => $config['host'],
                'port'     => $config['port'],
                'database' => $config['database'],
                'username' => $config['username'],
                'password' => $config['password'],
                'charset'  => $config['charset'] ?? 'utf8mb4',
                'error'    => $config['error'] ?? PDO::ERRMODE_EXCEPTION,
                'option'   => $this->buildPdoOptions($config),
                'logging'  => true,
            ]);
        }

        return $this->instances[$connectionName];
    }

    /**
     * Получить PDO объект
     *
     * @param string $name
     * @return PDO
     */
    public function pdo(string $name = 'default'): PDO
    {
        return $this->connection($name)->pdo;
    }

    /**
     * Выполнить произвольный SQL запрос
     *
     * @param string $sql
     * @param string $name
     * @return \PDOStatement|null
     */
    public function query(string $sql, string $name = 'default'): ?\PDOStatement
    {
        return $this->connection($name)->query($sql);
    }

    private function resolveConnectionName(string $name): string
    {
        if ($name === 'default') {
            $default = config('database.default');
            return $default ?: 'mysql';
        }

        return $name;
    }

    private function buildPdoOptions(array $config): array
    {
        $options = [];

        if (!empty($config['cert'])) {
            $certPath = APP_ROOT . $config['cert'];
            if (file_exists($certPath)) {
                $options = [
                    PDO::MYSQL_ATTR_SSL_CA => $certPath,
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                ];
            }
        }

        return $options;
    }
}
