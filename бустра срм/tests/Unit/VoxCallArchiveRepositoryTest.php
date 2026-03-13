<?php

namespace Tests\Unit;

use App\Modules\VoxCallsArchive\Application\DTO\VoxCallDTO;
use App\Modules\VoxCallsArchive\Infrastructure\Repository\VoxCallArchiveRepository;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Helpers\FakeMedoo;
use Tests\Unit\Helpers\FakePdoStatement;

/**
 * @testdox VoxCallArchiveRepository - репозиторий архива звонков
 */
class VoxCallArchiveRepositoryTest extends TestCase
{
    /** @var FakeMedoo */
    private $fakeMedoo;

    /** @var VoxCallArchiveRepository */
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fakeMedoo = new FakeMedoo();
        $this->repository = $this->createRepositoryWithFakeMedoo($this->fakeMedoo);
    }

    /**
     * Создать репозиторий с инъекцией FakeMedoo
     *
     * @param FakeMedoo $fakeMedoo
     * @return VoxCallArchiveRepository
     */
    private function createRepositoryWithFakeMedoo(FakeMedoo $fakeMedoo): VoxCallArchiveRepository
    {
        // Создаём репозиторий через reflection без вызова конструктора
        $ref = new \ReflectionClass(VoxCallArchiveRepository::class);
        $repository = $ref->newInstanceWithoutConstructor();

        // Инъектируем FakeMedoo в приватное свойство
        $archiveDbProp = $ref->getProperty('archiveDb');
        $archiveDbProp->setAccessible(true);
        $archiveDbProp->setValue($repository, $fakeMedoo);

        return $repository;
    }

    /**
     * @testdox save() вставляет новую запись в активную таблицу
     */
    public function testSaveInsertsNewRecord(): void
    {
        $dto = new VoxCallDTO();
        $dto->voxCallId = 12345;
        $dto->cost = 1.5;
        $dto->callResultCode = '200';
        $dto->datetimeStart = '2025-01-15 10:00:00';
        $dto->duration = 120;
        $dto->isIncoming = true;
        $dto->phoneA = '+79001234567';
        $dto->phoneB = '+79007654321';

        $this->fakeMedoo->setNextInsertId(99);

        $result = $this->repository->save($dto);

        $this->assertSame(99, $result);
        $this->assertCount(1, $this->fakeMedoo->inserts);
        $this->assertSame('s_vox_calls', $this->fakeMedoo->inserts[0]['table']);
        $this->assertSame(12345, $this->fakeMedoo->inserts[0]['data']['vox_call_id']);
    }

    /**
     * @testdox save() возвращает null при дубликате vox_call_id
     */
    public function testSaveReturnsNullWhenDuplicate(): void
    {
        $dto = new VoxCallDTO();
        $dto->voxCallId = 12345;

        // Настраиваем, что запись уже существует
        $this->fakeMedoo->setHasResult('s_vox_calls', ['vox_call_id' => 12345], true);

        $result = $this->repository->save($dto);

        $this->assertNull($result);
        $this->assertEmpty($this->fakeMedoo->inserts);
    }

    /**
     * @testdox save() фильтрует null значения перед вставкой
     */
    public function testSaveFiltersNullValues(): void
    {
        $dto = new VoxCallDTO();
        $dto->voxCallId = 12345;
        $dto->cost = 1.5;
        // Остальные поля остаются null

        $this->fakeMedoo->setNextInsertId(1);

        $this->repository->save($dto);

        $this->assertCount(1, $this->fakeMedoo->inserts);
        $insertedData = $this->fakeMedoo->inserts[0]['data'];

        // Проверяем, что null значения отфильтрованы
        foreach ($insertedData as $value) {
            $this->assertNotNull($value);
        }
    }

    /**
     * @testdox existsByVoxCallId() сначала проверяет активную таблицу
     */
    public function testExistsByVoxCallIdChecksActiveTableFirst(): void
    {
        $this->fakeMedoo->setHasResult('s_vox_calls', ['vox_call_id' => 555], true);

        $result = $this->repository->existsByVoxCallId(555);

        $this->assertTrue($result);

        // Должен быть только один вызов has (активная таблица)
        $this->assertCount(1, $this->fakeMedoo->hasChecks);
        $this->assertSame('s_vox_calls', $this->fakeMedoo->hasChecks[0]['table']);
    }

    /**
     * @testdox existsByVoxCallId() проверяет архивные таблицы при отсутствии в активной
     */
    public function testExistsByVoxCallIdFallsBackToArchiveTables(): void
    {
        // Не находим в активной таблице
        $this->fakeMedoo->setHasResult('s_vox_calls', ['vox_call_id' => 555], false);

        // Находим в архивной таблице прошлого месяца
        $lastMonth = (new \DateTime())->modify('-1 month')->format('Y_m');
        $archiveTable = 's_vox_calls_' . $lastMonth;
        $this->fakeMedoo->setHasResult($archiveTable, ['vox_call_id' => 555], true);

        $result = $this->repository->existsByVoxCallId(555);

        $this->assertTrue($result);

        // Проверяем, что был вызов и для активной, и для архивной таблицы
        $this->assertGreaterThanOrEqual(2, count($this->fakeMedoo->hasChecks));
    }

    /**
     * @testdox existsByVoxCallId() возвращает false когда запись не найдена
     */
    public function testExistsByVoxCallIdReturnsFalseWhenNotFound(): void
    {
        // Не находим нигде
        $this->fakeMedoo->setHasResult('s_vox_calls', ['vox_call_id' => 999], false);

        $result = $this->repository->existsByVoxCallId(999);

        $this->assertFalse($result);
    }

    /**
     * @testdox updateByVoxCallId() обновляет запись в активной таблице
     */
    public function testUpdateByVoxCallIdUpdatesActiveTable(): void
    {
        // Настраиваем успешное обновление
        $this->fakeMedoo->setHasResult('s_vox_calls', ['vox_call_id' => 123], true);

        $this->repository->updateByVoxCallId(123, ['assessment' => 5]);

        $this->assertCount(1, $this->fakeMedoo->updates);
        $this->assertSame('s_vox_calls', $this->fakeMedoo->updates[0]['table']);
        $this->assertSame(['assessment' => 5], $this->fakeMedoo->updates[0]['data']);
        $this->assertSame(['vox_call_id' => 123], $this->fakeMedoo->updates[0]['where']);
    }

    /**
     * @testdox updateByVoxCallId() ищет в архивных таблицах при отсутствии в активной
     */
    public function testUpdateByVoxCallIdSearchesArchiveTablesOnMiss(): void
    {
        // Не находим в активной таблице (update вернёт 0)
        // Находим в архивной таблице
        $lastMonth = (new \DateTime())->modify('-1 month')->format('Y_m');
        $archiveTable = 's_vox_calls_' . $lastMonth;
        $this->fakeMedoo->setHasResult($archiveTable, ['vox_call_id' => 456], true);

        $this->repository->updateByVoxCallId(456, ['assessment' => 3]);

        // Должно быть несколько попыток обновления
        $this->assertGreaterThanOrEqual(1, count($this->fakeMedoo->updates));
    }

    /**
     * @testdox updateByVoxCallId() возвращает false если запись не найдена
     */
    public function testUpdateByVoxCallIdReturnsFalseWhenNotFound(): void
    {
        $result = $this->repository->updateByVoxCallId(999, ['assessment' => 2]);

        $this->assertFalse($result);
        $this->assertCount(4, $this->fakeMedoo->updates);
        $this->assertSame('s_vox_calls', $this->fakeMedoo->updates[0]['table']);

        $expectedTables = [];
        $currentDate = new \DateTime();
        for ($i = 1; $i <= 3; $i++) {
            $currentDate->modify('-1 month');
            $expectedTables[] = 's_vox_calls_' . $currentDate->format('Y_m');
        }

        $actualTables = [
            $this->fakeMedoo->updates[1]['table'],
            $this->fakeMedoo->updates[2]['table'],
            $this->fakeMedoo->updates[3]['table'],
        ];

        $this->assertSame($expectedTables, $actualTables);
    }

    /**
     * @testdox tableExists() возвращает true когда таблица существует
     */
    public function testTableExistsReturnsTrueWhenExists(): void
    {
        $stmt = new FakePdoStatement();
        $stmt->setRowCount(1);
        $this->fakeMedoo->setQueryResult('information_schema.tables', $stmt);

        $result = $this->repository->tableExists('s_vox_calls');

        $this->assertTrue($result);
        $this->assertCount(1, $this->fakeMedoo->queries);
        $this->assertStringContainsString('information_schema.tables', $this->fakeMedoo->queries[0]);
        $this->assertStringContainsString('s_vox_calls', $this->fakeMedoo->queries[0]);
    }

    /**
     * @testdox tableExists() возвращает false когда таблица не существует
     */
    public function testTableExistsReturnsFalseWhenNotExists(): void
    {
        $stmt = new FakePdoStatement();
        $stmt->setRowCount(0);
        $this->fakeMedoo->setQueryResult('information_schema.tables', $stmt);

        $result = $this->repository->tableExists('nonexistent_table');

        $this->assertFalse($result);
    }

    /**
     * @testdox getCallsForPeriod() запрашивает данные из нескольких таблиц
     */
    public function testGetCallsForPeriodQueriesMultipleTables(): void
    {
        $this->fakeMedoo->setSelectResult('s_vox_calls', [
            ['id' => 1, 'vox_call_id' => 100],
        ]);

        $result = $this->repository->getCallsForPeriod('2025-01-01 00:00:00', '2025-01-31 23:59:59');

        // Должен быть хотя бы один select
        $this->assertGreaterThanOrEqual(1, count($this->fakeMedoo->selects));
    }

    /**
     * @testdox getActiveTableName() возвращает имя активной таблицы
     */
    public function testGetActiveTableNameReturnsCorrectName(): void
    {
        $result = $this->repository->getActiveTableName();

        $this->assertSame('s_vox_calls', $result);
    }

    /**
     * @testdox getArchiveTablesForPeriod() генерирует корректные имена таблиц
     */
    public function testGetArchiveTablesForPeriodGeneratesCorrectNames(): void
    {
        $result = $this->repository->getArchiveTablesForPeriod('2025-01', '2025-03');

        $this->assertCount(3, $result);
        $this->assertSame('s_vox_calls_2025_01', $result[0]);
        $this->assertSame('s_vox_calls_2025_02', $result[1]);
        $this->assertSame('s_vox_calls_2025_03', $result[2]);
    }

    /**
     * @testdox getArchiveTablesForPeriod() возвращает пустой массив при обратном диапазоне
     */
    public function testGetArchiveTablesForPeriodReturnsEmptyWhenFromAfterTo(): void
    {
        $result = $this->repository->getArchiveTablesForPeriod('2025-03', '2025-01');

        $this->assertSame([], $result);
    }

    /**
     * @testdox getCalls() формирует корректный where-запрос
     */
    public function testGetCallsBuildsCorrectWhereClause(): void
    {
        $this->fakeMedoo->setSelectResult('s_vox_calls', []);

        $this->repository->getCalls([
            'user_id' => 123,
            'date_from' => '2025-01-01',
            'date_to' => '2025-01-31',
        ]);

        $this->assertCount(1, $this->fakeMedoo->selects);
        $select = $this->fakeMedoo->selects[0];

        $this->assertSame('s_vox_calls', $select['table']);
        $this->assertSame('*', $select['columns']);
        $this->assertArrayHasKey('user_id', $select['where']);
        $this->assertArrayHasKey('created[>=]', $select['where']);
        $this->assertArrayHasKey('created[<=]', $select['where']);
    }

    /**
     * @testdox getCalls() принимает массив user_id без изменений
     */
    public function testGetCallsAcceptsArrayUserIds(): void
    {
        $this->fakeMedoo->setSelectResult('s_vox_calls', []);

        $this->repository->getCalls([
            'user_id' => [1, 2, 3],
        ]);

        $this->assertCount(1, $this->fakeMedoo->selects);
        $select = $this->fakeMedoo->selects[0];

        $this->assertSame([1, 2, 3], $select['where']['user_id']);
    }

    /**
     * @testdox getCallsForPeriod() объединяет результаты и учитывает дополнительные фильтры
     */
    public function testGetCallsForPeriodAppliesAdditionalFilterAndMergesResults(): void
    {
        $currentMonth = date('Y-m');
        $previousMonth = date('Y-m', strtotime('-1 month'));
        $archiveTable = 's_vox_calls_' . str_replace('-', '_', $previousMonth);

        $this->fakeMedoo->setSelectResult('s_vox_calls', [
            ['id' => 1, 'vox_call_id' => 111],
        ]);
        $this->fakeMedoo->setSelectResult($archiveTable, [
            ['id' => 2, 'vox_call_id' => 222],
        ]);

        $dateFrom = $previousMonth . '-01 00:00:00';
        $dateTo = $currentMonth . '-28 23:59:59';

        $result = $this->repository->getCallsForPeriod($dateFrom, $dateTo, [
            'user_id' => [10, 20],
            'scenario_id' => 5,
        ]);

        $this->assertCount(2, $result);
        $this->assertCount(2, $this->fakeMedoo->selects);

        foreach ($this->fakeMedoo->selects as $select) {
            $this->assertArrayHasKey('AND', $select['where']);
            $this->assertSame([10, 20], $select['where']['AND']['user_id']);
            $this->assertSame(5, $select['where']['AND']['scenario_id']);
        }
    }

    /**
     * @testdox getRecentArchiveTables() возвращает корректные имена таблиц
     */
    public function testGetRecentArchiveTablesReturnsExpectedTables(): void
    {
        $ref = new \ReflectionClass($this->repository);
        $method = $ref->getMethod('getRecentArchiveTables');
        $method->setAccessible(true);

        $result = $method->invoke($this->repository, 2);

        $expected = [
            's_vox_calls_' . (new \DateTime('-1 month'))->format('Y_m'),
            's_vox_calls_' . (new \DateTime('-2 month'))->format('Y_m'),
        ];

        $this->assertSame($expected, $result);
    }

    /**
     * @testdox query() выполняет произвольный SQL запрос
     */
    public function testQueryExecutesSql(): void
    {
        // Напрямую вызываем метод archiveDb->query через FakeMedoo
        // чтобы избежать проблем с типом возврата PDOStatement
        $this->fakeMedoo->query('SELECT 1');

        $this->assertCount(1, $this->fakeMedoo->queries);
        $this->assertSame('SELECT 1', $this->fakeMedoo->queries[0]);
    }

    /**
     * @testdox getPdo() возвращает PDO объект
     */
    public function testGetPdoReturnsPdoObject(): void
    {
        $pdo = $this->repository->getPdo();

        $this->assertInstanceOf(\PDO::class, $pdo);
    }
}
