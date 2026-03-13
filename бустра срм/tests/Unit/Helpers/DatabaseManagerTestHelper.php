<?php

namespace Tests\Unit\Helpers;

use App\Infrastructure\Database\DatabaseManager;

/**
 * Trait DatabaseManagerTestHelper
 * Хелпер для подмены DatabaseManager singleton в тестах
 */
trait DatabaseManagerTestHelper
{
    /** @var array|null */
    private static $originalInstances = null;

    /** @var array|null */
    private static $originalConfigured = null;

    /**
     * Инъекция FakeMedoo в DatabaseManager
     *
     * @param FakeMedoo $fakeMedoo
     * @param string $connectionName
     */
    protected function injectFakeMedoo(FakeMedoo $fakeMedoo, string $connectionName = 'archive'): void
    {
        $manager = DatabaseManager::singleton();

        $ref = new \ReflectionClass($manager);

        // Сохраняем оригинальные instances
        $instancesProp = $ref->getProperty('instances');
        $instancesProp->setAccessible(true);
        if (self::$originalInstances === null) {
            self::$originalInstances = $instancesProp->getValue($manager);
        }

        // Сохраняем оригинальный configured
        $configuredProp = $ref->getProperty('configured');
        $configuredProp->setAccessible(true);
        if (self::$originalConfigured === null) {
            self::$originalConfigured = $configuredProp->getValue($manager);
        }

        // Инъектируем fake
        $instances = $instancesProp->getValue($manager);
        $instances[$connectionName] = $fakeMedoo;
        $instancesProp->setValue($manager, $instances);

        // Помечаем соединение как настроенное
        $configured = $configuredProp->getValue($manager);
        $configured[$connectionName] = true;
        $configuredProp->setValue($manager, $configured);
    }

    /**
     * Восстановление оригинального DatabaseManager
     */
    protected function restoreDatabaseManager(): void
    {
        $manager = DatabaseManager::singleton();
        $ref = new \ReflectionClass($manager);

        if (self::$originalInstances !== null) {
            $instancesProp = $ref->getProperty('instances');
            $instancesProp->setAccessible(true);
            $instancesProp->setValue($manager, self::$originalInstances);
            self::$originalInstances = null;
        }

        if (self::$originalConfigured !== null) {
            $configuredProp = $ref->getProperty('configured');
            $configuredProp->setAccessible(true);
            $configuredProp->setValue($manager, self::$originalConfigured);
            self::$originalConfigured = null;
        }
    }

    /**
     * Сброс singleton (для изолированного тестирования)
     */
    protected function resetDatabaseManagerSingleton(): void
    {
        $ref = new \ReflectionClass(DatabaseManager::class);
        $instanceProp = $ref->getProperty('instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null, null);
    }
}
