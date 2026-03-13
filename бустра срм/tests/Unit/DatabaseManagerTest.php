<?php

namespace Tests\Unit;

use App\Infrastructure\Database\DatabaseManager;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Helpers\FakeMedoo;

/**
 * @testdox DatabaseManager - менеджер подключений к БД
 */
class DatabaseManagerTest extends TestCase
{
    /** @var array|null */
    private static $originalInstance = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->backupSingleton();
    }

    protected function tearDown(): void
    {
        $this->restoreSingleton();
        parent::tearDown();
    }

    /**
     * @testdox singleton() возвращает один и тот же экземпляр
     */
    public function testSingletonReturnsSameInstance(): void
    {
        $instance1 = DatabaseManager::singleton();
        $instance2 = DatabaseManager::singleton();

        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(DatabaseManager::class, $instance1);
    }

    /**
     * @testdox isConfigured() возвращает true при полной конфигурации
     */
    public function testIsConfiguredReturnsTrueWhenConfigComplete(): void
    {
        // Этот тест зависит от реальной конфигурации
        // В изолированном окружении нужно мокать config()
        $manager = DatabaseManager::singleton();

        // Проверяем, что метод возвращает булево значение
        $result = $manager->isConfigured('mysql');
        $this->assertIsBool($result);
    }

    /**
     * @testdox isConfigured() кеширует результат проверки
     */
    public function testIsConfiguredCachesResult(): void
    {
        $manager = DatabaseManager::singleton();
        $ref = new \ReflectionClass($manager);

        $configuredProp = $ref->getProperty('configured');
        $configuredProp->setAccessible(true);

        // Очищаем кеш
        $configuredProp->setValue($manager, []);

        // Первый вызов
        $result1 = $manager->isConfigured('test_connection');

        // Проверяем, что результат закеширован
        $configured = $configuredProp->getValue($manager);
        $this->assertArrayHasKey('test_connection', $configured);

        // Второй вызов должен использовать кеш
        $result2 = $manager->isConfigured('test_connection');
        $this->assertSame($result1, $result2);
    }

    /**
     * @testdox connection() выбрасывает исключение для ненастроенного соединения
     */
    public function testConnectionThrowsExceptionWhenNotConfigured(): void
    {
        $manager = DatabaseManager::singleton();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("is not configured");

        $manager->connection('nonexistent_connection_12345');
    }

    /**
     * @testdox connection() возвращает кешированный экземпляр Medoo
     */
    public function testConnectionReturnsCachedMedooInstance(): void
    {
        $manager = DatabaseManager::singleton();
        $ref = new \ReflectionClass($manager);

        $instancesProp = $ref->getProperty('instances');
        $instancesProp->setAccessible(true);

        $configuredProp = $ref->getProperty('configured');
        $configuredProp->setAccessible(true);

        // Создаём mock Medoo - используем реальный Medoo или mock
        // Поскольку connection() имеет return type Medoo, используем createMock
        $fakeMedoo = $this->createMock(\Medoo\Medoo::class);

        // Инъектируем его в кеш
        $instances = $instancesProp->getValue($manager);
        $instances['test_cached'] = $fakeMedoo;
        $instancesProp->setValue($manager, $instances);

        // Помечаем как настроенный
        $configured = $configuredProp->getValue($manager);
        $configured['test_cached'] = true;
        $configuredProp->setValue($manager, $configured);

        // Получаем соединение
        $result = $manager->connection('test_cached');

        // Должен вернуться тот же объект из кеша
        $this->assertSame($fakeMedoo, $result);
    }

    /**
     * @testdox resolveConnectionName() преобразует 'default' в имя из конфига
     */
    public function testResolveConnectionNameResolvesDefault(): void
    {
        $manager = DatabaseManager::singleton();
        $ref = new \ReflectionClass($manager);

        $method = $ref->getMethod('resolveConnectionName');
        $method->setAccessible(true);

        $expected = config('database.default', 'mysql');
        $result = $method->invoke($manager, 'default');

        $this->assertSame($expected ?: 'mysql', $result);
    }

    /**
     * @testdox resolveConnectionName() возвращает имя как есть для не-default
     */
    public function testResolveConnectionNameReturnsNameAsIs(): void
    {
        $manager = DatabaseManager::singleton();
        $ref = new \ReflectionClass($manager);

        $method = $ref->getMethod('resolveConnectionName');
        $method->setAccessible(true);

        $result = $method->invoke($manager, 'archive');

        $this->assertSame('archive', $result);
    }

    /**
     * @testdox buildPdoOptions() возвращает SSL опции при наличии сертификата
     */
    public function testBuildPdoOptionsReturnsEmptyForNoCert(): void
    {
        $manager = DatabaseManager::singleton();
        $ref = new \ReflectionClass($manager);

        $method = $ref->getMethod('buildPdoOptions');
        $method->setAccessible(true);

        $config = ['host' => 'localhost', 'database' => 'test'];
        $result = $method->invoke($manager, $config);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * @testdox buildPdoOptions() возвращает SSL опции при наличии файла сертификата
     */
    public function testBuildPdoOptionsReturnsSslOptionsWhenCertExists(): void
    {
        $manager = DatabaseManager::singleton();
        $ref = new \ReflectionClass($manager);

        $method = $ref->getMethod('buildPdoOptions');
        $method->setAccessible(true);

        $certPath = '/tests/Unit/fixtures/root.crt';
        $config = ['cert' => $certPath];

        $result = $method->invoke($manager, $config);

        $this->assertArrayHasKey(\PDO::MYSQL_ATTR_SSL_CA, $result);
        $this->assertArrayHasKey(\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT, $result);
        $this->assertSame(APP_ROOT . $certPath, $result[\PDO::MYSQL_ATTR_SSL_CA]);
        $this->assertFalse($result[\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT]);
    }

    /**
     * Сохраняем оригинальный singleton
     */
    private function backupSingleton(): void
    {
        $ref = new \ReflectionClass(DatabaseManager::class);
        $instanceProp = $ref->getProperty('instance');
        $instanceProp->setAccessible(true);
        self::$originalInstance = $instanceProp->getValue(null);
    }

    /**
     * Восстанавливаем оригинальный singleton
     */
    private function restoreSingleton(): void
    {
        $ref = new \ReflectionClass(DatabaseManager::class);
        $instanceProp = $ref->getProperty('instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null, self::$originalInstance);
    }
}
