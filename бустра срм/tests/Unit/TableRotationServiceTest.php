<?php

namespace Tests\Unit;

use App\Modules\VoxCallsArchive\Application\Service\TableRotationService;
use App\Modules\VoxCallsArchive\Infrastructure\Repository\VoxCallArchiveRepository;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Helpers\FakeMedoo;
use Tests\Unit\Helpers\FakePdoStatement;

/**
 * @testdox TableRotationService - сервис ротации таблиц архива
 */
class TableRotationServiceTest extends TestCase
{
    /** @var FakeMedoo */
    private $fakeMedoo;

    /** @var TableRotationService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeMedoo = new FakeMedoo();
        $this->service = $this->createServiceWithFakeMedoo($this->fakeMedoo);
    }

    /** @var \ReflectionClass */
    private $serviceRef;

    /**
     * Создать сервис с инъекцией FakeMedoo
     *
     * @param FakeMedoo $fakeMedoo
     * @return TableRotationService
     */
    private function createServiceWithFakeMedoo(FakeMedoo $fakeMedoo): TableRotationService
    {
        // Создаём сервис через reflection без вызова конструктора
        $this->serviceRef = new \ReflectionClass(TableRotationService::class);
        $service = $this->serviceRef->newInstanceWithoutConstructor();

        // Инъектируем FakeMedoo в archiveDb
        $archiveDbProp = $this->serviceRef->getProperty('archiveDb');
        $archiveDbProp->setAccessible(true);
        $archiveDbProp->setValue($service, $fakeMedoo);

        // Создаём repository с тем же FakeMedoo
        $repoRef = new \ReflectionClass(VoxCallArchiveRepository::class);
        $repository = $repoRef->newInstanceWithoutConstructor();
        $repoArchiveDbProp = $repoRef->getProperty('archiveDb');
        $repoArchiveDbProp->setAccessible(true);
        $repoArchiveDbProp->setValue($repository, $fakeMedoo);

        // Инъектируем repository
        $repoProp = $this->serviceRef->getProperty('repository');
        $repoProp->setAccessible(true);
        $repoProp->setValue($service, $repository);

        // Инициализируем dryRun
        $dryRunProp = $this->serviceRef->getProperty('dryRun');
        $dryRunProp->setAccessible(true);
        $dryRunProp->setValue($service, false);

        return $service;
    }

    /**
     * Инъектировать mainDb через reflection
     *
     * @param FakeMainDatabase $mainDb
     */
    private function injectMainDb(FakeMainDatabase $mainDb): void
    {
        $mainDbProp = $this->serviceRef->getProperty('mainDb');
        $mainDbProp->setAccessible(true);
        $mainDbProp->setValue($this->service, $mainDb);
    }

    /**
     * @testdox rotate() возвращает успех когда архивная таблица уже существует
     */
    public function testRotateReturnsEarlyWhenArchiveTableExists(): void
    {
        $archiveTableName = $this->getArchiveTableName();

        // Настраиваем, что архивная таблица уже существует
        $stmt = new FakePdoStatement();
        $stmt->setRowCount(1);
        $this->fakeMedoo->setQueryResult($archiveTableName, $stmt);

        $result = $this->service->rotate();

        $this->assertTrue($result['success']);
        $this->assertSame($archiveTableName, $result['old_table']);
        $this->assertStringContainsString('already exists', $result['message']);

        // Не должно быть RENAME или CREATE TABLE запросов
        $renameCount = 0;
        $createCount = 0;
        foreach ($this->fakeMedoo->queries as $query) {
            if (strpos($query, 'RENAME TABLE') !== false) {
                $renameCount++;
            }
            if (strpos($query, 'CREATE TABLE') !== false) {
                $createCount++;
            }
        }
        $this->assertSame(0, $renameCount);
        $this->assertSame(0, $createCount);
    }

    /**
     * @testdox rotate() переименовывает таблицу, создаёт новую и сохраняет метаданные
     */
    public function testRotateRenamesCreatesAndSavesMeta(): void
    {
        $archiveTableName = $this->getArchiveTableName();

        // Архивная таблица НЕ существует
        $stmtNotExists = new FakePdoStatement();
        $stmtNotExists->setRowCount(0);
        $this->fakeMedoo->setQueryResult($archiveTableName, $stmtNotExists);

        // Активная таблица существует
        $stmtExists = new FakePdoStatement();
        $stmtExists->setRowCount(1);
        $this->fakeMedoo->setQueryResult('s_vox_calls', $stmtExists);

        // Настраиваем count и get для статистики
        $this->fakeMedoo->setCountResult(100);
        $this->fakeMedoo->setGetResult([
            'min_datetime' => '2025-01-01 00:00:00',
            'max_datetime' => '2025-01-31 23:59:59',
        ]);

        // Создаём fake mainDb
        $mainDb = new FakeMainDatabase();

        $this->injectMainDb($mainDb);
        $result = $this->service->rotate();

        $this->assertTrue($result['success']);
        $this->assertSame($archiveTableName, $result['old_table']);
        $this->assertSame(100, $result['records_count']);

        // Проверяем, что были нужные запросы
        $hasRename = false;
        $hasCreate = false;
        foreach ($this->fakeMedoo->queries as $query) {
            if (strpos($query, 'RENAME TABLE `s_vox_calls` TO `' . $archiveTableName . '`') !== false) {
                $hasRename = true;
            }
            if (strpos($query, 'CREATE TABLE `s_vox_calls`') !== false) {
                $hasCreate = true;
            }
        }

        $this->assertTrue($hasRename, 'RENAME TABLE query was not executed');
        $this->assertTrue($hasCreate, 'CREATE TABLE query was not executed');

        // Проверяем, что метаданные записаны
        $this->assertNotEmpty($mainDb->queries);
        $this->assertStringContainsString($archiveTableName, $mainDb->queries[0]);
    }

    /**
     * @testdox rotate() выбрасывает исключение когда активная таблица отсутствует
     */
    public function testRotateThrowsWhenActiveTableMissing(): void
    {
        $archiveTableName = $this->getArchiveTableName();

        // Архивная таблица НЕ существует
        $stmtNotExists = new FakePdoStatement();
        $stmtNotExists->setRowCount(0);
        $this->fakeMedoo->setQueryResult($archiveTableName, $stmtNotExists);

        // Активная таблица тоже НЕ существует
        // По умолчанию FakePdoStatement возвращает rowCount = 0

        $result = $this->service->rotate();

        $this->assertFalse($result['success']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('does not exist', $result['errors'][0]);
    }

    /**
     * @testdox cleanup() удаляет истёкшие таблицы
     */
    public function testCleanupDeletesExpiredTables(): void
    {
        // Создаём fake mainDb с истёкшими таблицами
        $mainDb = new FakeMainDatabase();
        $mainDb->setResults([
            ['id' => 1, 'table_name' => 's_vox_calls_2020_01', 'year_month' => '2020-01'],
            ['id' => 2, 'table_name' => 's_vox_calls_2020_02', 'year_month' => '2020-02'],
        ]);

        // Настраиваем, что таблицы существуют
        $stmtExists = new FakePdoStatement();
        $stmtExists->setRowCount(1);
        $this->fakeMedoo->setQueryResult('s_vox_calls_2020_01', $stmtExists);
        $this->fakeMedoo->setQueryResult('s_vox_calls_2020_02', $stmtExists);

        $this->injectMainDb($mainDb);
        $result = $this->service->cleanup();

        $this->assertTrue($result['success']);
        $this->assertCount(2, $result['deleted_tables']);
        $this->assertContains('s_vox_calls_2020_01', $result['deleted_tables']);
        $this->assertContains('s_vox_calls_2020_02', $result['deleted_tables']);

        // Проверяем, что были DROP TABLE запросы
        $dropCount = 0;
        foreach ($this->fakeMedoo->queries as $query) {
            if (strpos($query, 'DROP TABLE') !== false) {
                $dropCount++;
            }
        }
        $this->assertSame(2, $dropCount);
    }

    /**
     * @testdox cleanup() возвращает сообщение когда нечего удалять
     */
    public function testCleanupReturnsMessageWhenNothingToDelete(): void
    {
        // Создаём fake mainDb без истёкших таблиц
        $mainDb = new FakeMainDatabase();
        $mainDb->setResults([]);

        $this->injectMainDb($mainDb);
        $result = $this->service->cleanup();

        $this->assertTrue($result['success']);
        $this->assertEmpty($result['deleted_tables']);
        $this->assertStringContainsString('No tables to cleanup', $result['message']);
    }

    /**
     * @testdox dryRun режим не выполняет изменения
     */
    public function testDryRunDoesNotModify(): void
    {
        $archiveTableName = $this->getArchiveTableName();

        // Архивная таблица НЕ существует
        $stmtNotExists = new FakePdoStatement();
        $stmtNotExists->setRowCount(0);
        $this->fakeMedoo->setQueryResult($archiveTableName, $stmtNotExists);

        // Активная таблица существует
        $stmtExists = new FakePdoStatement();
        $stmtExists->setRowCount(1);
        $this->fakeMedoo->setQueryResult('s_vox_calls', $stmtExists);

        $this->fakeMedoo->setCountResult(50);
        $this->fakeMedoo->setGetResult([]);

        $this->service->setDryRun(true);
        $result = $this->service->rotate();

        $this->assertTrue($result['success']);
        $this->assertStringContainsString('[DRY-RUN]', $result['message']);

        // Не должно быть RENAME или CREATE TABLE запросов
        foreach ($this->fakeMedoo->queries as $query) {
            $this->assertStringNotContainsString('RENAME TABLE', $query);
            $this->assertStringNotContainsString('CREATE TABLE', $query);
        }
    }

    /**
     * @testdox ensureActiveTableExists() создаёт таблицу если она отсутствует
     */
    public function testEnsureActiveTableExistsCreatesTableWhenMissing(): void
    {
        // Таблица НЕ существует
        $stmtNotExists = new FakePdoStatement();
        $stmtNotExists->setRowCount(0);
        $this->fakeMedoo->setQueryResult('s_vox_calls', $stmtNotExists);

        $result = $this->service->ensureActiveTableExists();

        $this->assertTrue($result);

        // Проверяем, что был CREATE TABLE запрос
        $hasCreate = false;
        foreach ($this->fakeMedoo->queries as $query) {
            if (strpos($query, 'CREATE TABLE `s_vox_calls`') !== false) {
                $hasCreate = true;
                break;
            }
        }
        $this->assertTrue($hasCreate);
    }

    /**
     * @testdox ensureActiveTableExists() не создаёт таблицу если она существует
     */
    public function testEnsureActiveTableExistsDoesNothingWhenExists(): void
    {
        // Таблица существует
        $stmtExists = new FakePdoStatement();
        $stmtExists->setRowCount(1);
        $this->fakeMedoo->setQueryResult('s_vox_calls', $stmtExists);

        $result = $this->service->ensureActiveTableExists();

        $this->assertFalse($result);
    }

    /**
     * @testdox getArchiveInfo() возвращает информацию об активной и архивных таблицах
     */
    public function testGetArchiveInfoReturnsTableInfo(): void
    {
        // Активная таблица существует
        $stmtExists = new FakePdoStatement();
        $stmtExists->setRowCount(1);
        $this->fakeMedoo->setQueryResult('s_vox_calls', $stmtExists);
        $this->fakeMedoo->setCountResult(500);

        $mainDb = new FakeMainDatabase();
        $mainDb->setResults([
            (object)[
                'table_name' => 's_vox_calls_2025_01',
                'year_month' => '2025-01',
                'records_count' => 1000,
                'rotated_at' => '2025-02-01 00:00:00',
                'expires_at' => '2028-02-01 00:00:00',
            ],
        ]);

        $this->injectMainDb($mainDb);
        $info = $this->service->getArchiveInfo();

        $this->assertArrayHasKey('active_table', $info);
        $this->assertSame('s_vox_calls', $info['active_table']['name']);
        $this->assertTrue($info['active_table']['exists']);
        $this->assertSame(500, $info['active_table']['records']);

        $this->assertArrayHasKey('archive_tables', $info);
        $this->assertCount(1, $info['archive_tables']);
        $this->assertSame('s_vox_calls_2025_01', $info['archive_tables'][0]['table_name']);
    }

    /**
     * Получить имя архивной таблицы для прошлого месяца
     */
    private function getArchiveTableName(): string
    {
        $lastMonth = new \DateTime('first day of last month');
        return 's_vox_calls_' . $lastMonth->format('Y_m');
    }

    /**
     * Получить год-месяц для прошлого месяца
     */
    private function getArchiveYearMonth(): string
    {
        $lastMonth = new \DateTime('first day of last month');
        return $lastMonth->format('Y-m');
    }
}

/**
 * FakeMainDatabase - имитирует Database (Simpla) для тестирования
 */
class FakeMainDatabase
{
    /** @var array */
    public $queries = [];

    /** @var array */
    public $placeholdCalls = [];

    /** @var array */
    private $results = [];

    /**
     * @param array $results
     */
    public function setResults(array $results): void
    {
        $this->results = $results;
    }

    /**
     * @param string $sql
     * @param mixed ...$params
     * @return string
     */
    public function placehold(string $sql, ...$params): string
    {
        $this->placeholdCalls[] = [$sql, $params];
        return $this->interpolate($sql, $params);
    }

    /**
     * @param string $sql
     * @return void
     */
    public function query(string $sql): void
    {
        $this->queries[] = $sql;
    }

    /**
     * @return array|null
     */
    public function results(): ?array
    {
        return $this->results;
    }

    /**
     * @param string $sql
     * @param array $params
     * @return string
     */
    private function interpolate(string $sql, array $params): string
    {
        foreach ($params as $param) {
            $value = is_numeric($param)
                ? (string)$param
                : "'" . str_replace("'", "''", (string)$param) . "'";
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        return $sql;
    }
}
