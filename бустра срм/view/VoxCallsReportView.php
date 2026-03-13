<?php

require_once 'View.php';

use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveQueryService;
use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveService;

class VoxCallsReportView extends View
{
    private const PAGE_CAPACITY = 50;
    private const DETAILS_LIMIT = 500;

    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;

    private array $filters;
    private array $queueIds;
    private string $orderBy;
    private string $dateFrom;
    private string $dateTo;

    /** @var VoxCallsArchiveQueryService|null */
    private $archiveQueryService = null;

    private static array $summaryColumns = [
        ['key' => 'operator', 'label' => 'ФИО оператора', 'sort_key' => 'operator'],
        ['key' => 'assessment_1', 'label' => 'Оценка 1', 'sort_key' => 'assessment_1', 'type' => 'integer'],
        ['key' => 'assessment_2', 'label' => 'Оценка 2', 'sort_key' => 'assessment_2', 'type' => 'integer'],
        ['key' => 'assessment_3', 'label' => 'Оценка 3', 'sort_key' => 'assessment_3', 'type' => 'integer'],
        ['key' => 'assessment_4', 'label' => 'Оценка 4', 'sort_key' => 'assessment_4', 'type' => 'integer'],
        ['key' => 'assessment_5', 'label' => 'Оценка 5', 'sort_key' => 'assessment_5', 'type' => 'integer'],
        ['key' => 'total_rated', 'label' => 'Всего оценок', 'sort_key' => 'total_rated', 'type' => 'integer'],
        ['key' => 'total', 'label' => 'Всего звонков', 'sort_key' => 'total', 'type' => 'integer'],
        ['key' => 'avg_assessment', 'label' => 'Средний балл', 'sort_key' => 'avg_assessment', 'type' => 'number'],
        ['key' => 'assessment_percent', 'label' => '% оценивания', 'sort_key' => 'assessment_percent', 'type' => 'number'],
    ];

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();

        $this->filters = $this->setFilters();
        $this->queueIds = $this->getQueueIdsFilter();
        $this->orderBy = $this->setOrderBy();

        $this->totalItems = $this->getTotals();
        $this->pagesNum = (int)ceil($this->totalItems / self::PAGE_CAPACITY);

        $this->handleAction();
    }

    /**
     * Получить сервис чтения из архива
     *
     * @return VoxCallsArchiveQueryService
     */
    private function getArchiveQueryService(): VoxCallsArchiveQueryService
    {
        if ($this->archiveQueryService === null) {
            $this->archiveQueryService = new VoxCallsArchiveQueryService();
        }
        return $this->archiveQueryService;
    }

    private function handleAction(): void
    {
        $action = $this->request->get('action');
        if ($action && method_exists($this, $action)) {
            $this->$action();
        }
    }

    public function fetch(): string
    {
        $items = $this->getSummaryResults($this->currentPage);

        $reportHeaders = [];
        foreach (self::$summaryColumns as $col) {
            $reportHeaders[] = [
                'key' => $col['key'],
                'label' => $col['label'],
                'sort_key' => $col['sort_key'] ?? null,
            ];
        }

        $this->design->assign_array([
            'reportHeaders' => $reportHeaders,
            'reportRows' => $items,
            'items' => $items,
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
            'filterConfigurations' => $this->getFilterConfiguration(),
        ]);

        return $this->design->fetch('vox_calls_report.tpl');
    }

    private function setupDateRange(): void
    {
        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('-7 days')) . ' - ' . date('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);
        $this->dateFrom = date('Y-m-d', strtotime($from));
        $this->dateTo = date('Y-m-d', strtotime($to));
    }

    private function setFilters(): array
    {
        return [
            'operator' => $this->request->get('operator') ?? null,
            'queue' => $this->request->get('queue') ?? null,
        ];
    }

    /**
     * Получить массив ID очередей для фильтрации
     *
     * @return array
     */
    private function getQueueIdsFilter(): array
    {
        if (!empty($this->filters['queue'])) {
            return [(int)$this->filters['queue']];
        }

        $enabledIds = $this->voxQueues->getEnabledQueueIds();
        return !empty($enabledIds) ? $enabledIds : [];
    }

    private function setOrderBy(): string
    {
        $orderBy = 'operator_name ASC';

        $sort = $this->request->get('sort') ?? null;
        if ($sort) {
            $pos = strrpos($sort, '_');
            if ($pos !== false) {
                $field = substr($sort, 0, $pos);
                $direction = substr($sort, $pos + 1);
                $direction = (strtoupper($direction) === 'ASC') ? 'ASC' : 'DESC';

                switch ($field) {
                    case 'operator':
                        $orderBy = "operator_name $direction";
                        break;
                    case 'assessment_1':
                    case 'assessment_2':
                    case 'assessment_3':
                    case 'assessment_4':
                    case 'assessment_5':
                    case 'total_rated':
                    case 'total':
                    case 'avg_assessment':
                    case 'assessment_percent':
                        $orderBy = "$field $direction";
                        break;
                    default:
                        break;
                }
            }
        }

        return $orderBy;
    }

    private function getSummaryResults(int $currentPage): array
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        // 1. Получаем данные из архива (без имён операторов)
        $archiveResults = $this->getArchiveQueryService()->getReportData(
            $this->dateFrom,
            $this->dateTo,
            $this->queueIds,
            self::PAGE_CAPACITY * 10, // Берём с запасом для сортировки
            0
        );

        if (empty($archiveResults)) {
            return [];
        }

        // 2. Собираем vox_user_id
        $voxUserIds = array_map(function($row) {
            return (int)$row->vox_user_id;
        }, $archiveResults);

        // 3. Получаем имена операторов из основной БД
        $operatorNames = $this->getOperatorNamesByVoxUserIds($voxUserIds);

        // 4. Применяем фильтр по имени оператора (если задан)
        $operatorFilter = $this->filters['operator'] ?? null;

        // 5. Объединяем данные
        $combined = [];
        foreach ($archiveResults as $item) {
            $voxUserId = (int)$item->vox_user_id;
            $operatorName = $operatorNames[$voxUserId] ?? 'Оператор не определён';

            // Фильтруем по имени оператора
            if ($operatorFilter && stripos($operatorName, $operatorFilter) === false) {
                continue;
            }

            $combined[] = [
                'vox_user_id' => (string)$voxUserId,
                'operator' => $operatorName,
                'operator_name' => $operatorName,
                'assessment_1' => (int)($item->assessment_1 ?? 0),
                'assessment_2' => (int)($item->assessment_2 ?? 0),
                'assessment_3' => (int)($item->assessment_3 ?? 0),
                'assessment_4' => (int)($item->assessment_4 ?? 0),
                'assessment_5' => (int)($item->assessment_5 ?? 0),
                'total_rated' => (int)($item->total_rated ?? 0),
                'total' => (int)($item->total ?? 0),
                'avg_assessment' => (float)($item->avg_assessment ?? 0),
                'assessment_percent' => (float)($item->assessment_percent ?? 0),
            ];
        }

        // 6. Сортировка
        $this->sortResults($combined, $this->orderBy);

        // 7. Применяем пагинацию
        $paginated = array_slice($combined, $offset, self::PAGE_CAPACITY);

        // 8. Форматируем для вывода
        $out = [];
        foreach ($paginated as $item) {
            $out[] = [
                'vox_user_id' => $item['vox_user_id'],
                'operator' => $item['operator'],
                'assessment_1' => $item['assessment_1'],
                'assessment_2' => $item['assessment_2'],
                'assessment_3' => $item['assessment_3'],
                'assessment_4' => $item['assessment_4'],
                'assessment_5' => $item['assessment_5'],
                'total_rated' => $item['total_rated'],
                'total' => $item['total'],
                'avg_assessment' => number_format($item['avg_assessment'], 2),
                'assessment_percent' => number_format($item['assessment_percent'], 1) . '%',
            ];
        }

        return $out;
    }

    /**
     * Сортировка результатов
     *
     * @param array $results
     * @param string $orderBy
     */
    private function sortResults(array &$results, string $orderBy): void
    {
        preg_match('/^(\w+)\s+(ASC|DESC)$/i', $orderBy, $matches);
        if (count($matches) < 3) {
            return;
        }

        $field = $matches[1];
        $direction = strtoupper($matches[2]);

        usort($results, function($a, $b) use ($field, $direction) {
            $valA = $a[$field] ?? '';
            $valB = $b[$field] ?? '';

            if (is_numeric($valA) && is_numeric($valB)) {
                $cmp = $valA <=> $valB;
            } else {
                $cmp = strcmp((string)$valA, (string)$valB);
            }

            return $direction === 'DESC' ? -$cmp : $cmp;
        });
    }

    /**
     * Получить имена операторов по vox_user_id
     *
     * @param array $voxUserIds
     * @return array Ассоциативный массив vox_user_id => full_name
     */
    private function getOperatorNamesByVoxUserIds(array $voxUserIds): array
    {
        if (empty($voxUserIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($voxUserIds), '?'));
        $this->db->query(
            "SELECT vox_user_id, full_name FROM s_vox_users WHERE vox_user_id IN ($placeholders)",
            ...$voxUserIds
        );

        $names = [];
        foreach ($this->db->results() ?: [] as $row) {
            $names[(int)$row->vox_user_id] = $row->full_name;
        }

        return $names;
    }

    private function getTotals(): int
    {
        // Получаем количество из архива
        $total = $this->getArchiveQueryService()->getReportTotalCount(
            $this->dateFrom,
            $this->dateTo,
            $this->queueIds
        );

        // Если есть фильтр по оператору - нужно пересчитать
        $operatorFilter = $this->filters['operator'] ?? null;
        if ($operatorFilter) {
            // Получаем все vox_user_id
            $voxUserIds = $this->getArchiveQueryService()->getDistinctVoxUserIds(
                $this->dateFrom,
                $this->dateTo,
                $this->queueIds
            );

            if (empty($voxUserIds)) {
                return 0;
            }

            // Получаем имена и фильтруем
            $operatorNames = $this->getOperatorNamesByVoxUserIds($voxUserIds);

            $count = 0;
            foreach ($voxUserIds as $voxUserId) {
                $name = $operatorNames[$voxUserId] ?? '';
                if (stripos($name, $operatorFilter) !== false) {
                    $count++;
                }
            }
            return $count;
        }

        return $total;
    }

    private function download(): void
    {
        $maxPeriod = 365;

        $dateFromTimestamp = strtotime($this->dateFrom);
        $dateToTimestamp = strtotime($this->dateTo);
        $diffInDays = ($dateToTimestamp - $dateFromTimestamp) / (60 * 60 * 24);

        if ($diffInDays > $maxPeriod) {
            $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
            return;
        }

        $header = [];
        foreach (self::$summaryColumns as $col) {
            $header[$col['label']] = $col['type'] ?? 'string';
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        // 1. Получаем данные из архива
        $archiveResults = $this->getArchiveQueryService()->getReportData(
            $this->dateFrom,
            $this->dateTo,
            $this->queueIds
        );

        if (!empty($archiveResults)) {
            // 2. Собираем vox_user_id
            $voxUserIds = array_map(function($row) {
                return (int)$row->vox_user_id;
            }, $archiveResults);

            // 3. Получаем имена операторов из основной БД
            $operatorNames = $this->getOperatorNamesByVoxUserIds($voxUserIds);

            // 4. Применяем фильтр по имени оператора (если задан)
            $operatorFilter = $this->filters['operator'] ?? null;

            // 5. Объединяем данные
            $combined = [];
            foreach ($archiveResults as $item) {
                $voxUserId = (int)$item->vox_user_id;
                $operatorName = $operatorNames[$voxUserId] ?? 'Оператор не определён';

                // Фильтруем по имени оператора
                if ($operatorFilter && stripos($operatorName, $operatorFilter) === false) {
                    continue;
                }

                $combined[] = [
                    'operator_name' => $operatorName,
                    'assessment_1' => (int)($item->assessment_1 ?? 0),
                    'assessment_2' => (int)($item->assessment_2 ?? 0),
                    'assessment_3' => (int)($item->assessment_3 ?? 0),
                    'assessment_4' => (int)($item->assessment_4 ?? 0),
                    'assessment_5' => (int)($item->assessment_5 ?? 0),
                    'total_rated' => (int)($item->total_rated ?? 0),
                    'total' => (int)($item->total ?? 0),
                    'avg_assessment' => (float)($item->avg_assessment ?? 0),
                    'assessment_percent' => (float)($item->assessment_percent ?? 0),
                ];
            }

            // 6. Сортировка
            $this->sortResults($combined, $this->orderBy);

            // 7. Записываем в Excel
            foreach ($combined as $item) {
                $row = [
                    $item['operator_name'],
                    (string)$item['assessment_1'],
                    (string)$item['assessment_2'],
                    (string)$item['assessment_3'],
                    (string)$item['assessment_4'],
                    (string)$item['assessment_5'],
                    (string)$item['total_rated'],
                    (string)$item['total'],
                    number_format($item['avg_assessment'], 2),
                    number_format($item['assessment_percent'], 1) . '%',
                ];
                $writer->writeSheetRow('Отчёт', $row);
            }
        }

        $filename = 'vox_calls_report_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    /**
     * Синхронизация звонков из Voximplant за выбранный период
     */
    private function sync(): void
    {
        $dateFrom = $this->dateFrom . ' 00:00:00';
        $dateTo = $this->dateTo . ' 23:59:59';

        try {
            $enabledQueueIds = $this->voxQueues->getEnabledQueueIds();
            if (empty($enabledQueueIds)) {
                $this->json_output(['status' => 'error', 'message' => 'Нет очередей с enabled_for_report = 1']);
                return;
            }

            $archiveService = new VoxCallsArchiveService();

            $page = 1;
            $perPage = 50;
            $pageCount = 1;
            $created = 0;
            $updated = 0;
            $skippedNoUser = 0;
            $totalProcessed = 0;
            $failedRequests = 0;

            do {
                $response = $this->voximplant->searchCallsPaginated($dateFrom, $dateTo, $page, $perPage, [
                    'with_tags' => true,
                    'queue_ids' => json_encode($enabledQueueIds),
                    'sort' => 'id',
                ]);

                if (empty($response['success']) || empty($response['result']) || !is_array($response['result'])) {
                    if ($page === 1 && empty($response['result'])) {
                        break;
                    }
                    $failedRequests++;
                    break;
                }

                $pageCount = isset($response['_meta']['pageCount']) ? (int)$response['_meta']['pageCount'] : 1;

                foreach ($response['result'] as $call) {
                    if (!is_array($call)) {
                        continue;
                    }

                    if (empty($call['user_id'])) {
                        $skippedNoUser++;
                        continue;
                    }

                    $voxCallId = (int)$call['id'];

                    if ($archiveService->existsByVoxCallId($voxCallId)) {
                        $callData = json_decode($call['call_data'] ?? '{}', true);
                        $metaData = $call;
                        if (isset($callData['assessment'])) {
                            $metaData['assessment'] = $callData['assessment'];
                        }
                        $archiveService->updateReportMeta($metaData);
                        $updated++;
                    } else {
                        $archiveService->saveFromArray($this->mapSyncCallToArray($call));
                        $created++;
                    }

                    $totalProcessed++;
                }

                $page++;
            } while ($page <= $pageCount);

            $this->json_output([
                'status' => 'success',
                'message' => "Синхронизация завершена: обработано $totalProcessed, создано $created, обновлено $updated",
                'total_processed' => $totalProcessed,
                'created' => $created,
                'updated' => $updated,
                'skipped_no_user' => $skippedNoUser,
                'failed_requests' => $failedRequests,
            ]);
        } catch (\Throwable $e) {
            error_log('[VoxSync] Ошибка синхронизации: ' . $e->getMessage());
            $this->json_output(['status' => 'error', 'message' => 'Ошибка синхронизации: ' . $e->getMessage()]);
        }
    }

    /**
     * Маппинг звонка из Vox API в формат для VoxCallDTO::fromArray()
     *
     * @param array $call
     * @return array
     */
    private function mapSyncCallToArray(array $call): array
    {
        $callData = json_decode($call['call_data'] ?? '{}', true);

        $phoneA = function_exists('formatPhoneNumber') ? formatPhoneNumber($call['phone_a'] ?? '') : ($call['phone_a'] ?? null);
        $phoneB = function_exists('formatPhoneNumber') ? formatPhoneNumber($call['phone_b'] ?? '') : ($call['phone_b'] ?? null);

        return [
            'cost' => $call['call_cost'] ?? null,
            'call_result_code' => $call['call_result_code'] ?? null,
            'datetime_start' => $call['datetime_start'] ?? null,
            'duration' => $call['duration'] ?? null,
            'vox_call_id' => $call['id'] ?? null,
            'is_incoming' => $call['is_incoming'] ?? null,
            'phone_a' => $phoneA ?: ($call['phone_a'] ?? null),
            'phone_b' => $phoneB ?: ($call['phone_b'] ?? null),
            'scenario_id' => $call['scenario_id'] ?? null,
            'tags' => isset($call['tags']) ? json_encode($call['tags']) : null,
            'created' => date('Y-m-d H:i:s'),
            'queue_id' => $call['queue_id'] ?? null,
            'vox_user_id' => $call['user_id'] ?? null,
            'record_url' => $call['record_url'] ?? null,
            'assessment' => $callData['assessment'] ?? null,
        ];
    }

    private function getFilterConfiguration(): array
    {
        $operators = $this->getOperatorsOptions();
        $queues = $this->voxQueues->getEnabledOptions();

        return [
            [
                'name' => 'operator',
                'label' => 'Оператор',
                'type' => 'select',
                'value' => $this->filters['operator'] ?? '',
                'options' => $operators,
            ],
            [
                'name' => 'queue',
                'label' => 'Очередь',
                'type' => 'select',
                'value' => $this->filters['queue'] ?? '',
                'options' => $queues,
            ],
        ];
    }

    private function getOperatorsOptions(): array
    {
        $enabledIds = $this->voxQueues->getEnabledQueueIds();
        if (empty($enabledIds)) {
            return ['' => 'Все'];
        }

        // 1. Получаем уникальные vox_user_id из архива за последний год
        $dateFrom = date('Y-m-d', strtotime('-1 year'));
        $dateTo = date('Y-m-d');

        $voxUserIds = $this->getArchiveQueryService()->getDistinctVoxUserIds(
            $dateFrom,
            $dateTo,
            $enabledIds
        );

        if (empty($voxUserIds)) {
            return ['' => 'Все'];
        }

        // 2. Получаем имена операторов из основной БД
        $operatorNames = $this->getOperatorNamesByVoxUserIds($voxUserIds);

        // 3. Формируем список опций
        $options = ['' => 'Все'];
        $names = array_values($operatorNames);
        sort($names);

        foreach ($names as $name) {
            if (!empty($name)) {
                $options[$name] = $name;
            }
        }

        return $options;
    }

    private function details(): void
    {
        $voxUserId = (int)($this->request->get('vox_user_id', 'integer') ?? 0);
        $assessmentParam = $this->request->get('assessment');

        if ($voxUserId <= 0) {
            $this->json_output(['status' => 'error', 'message' => 'Некорректные параметры детализации']);
            return;
        }

        // Определяем фильтр по оценке
        $assessment = null;
        $hasAssessment = false;
        if ($assessmentParam !== null && $assessmentParam !== '') {
            if ($assessmentParam === 'rated') {
                $hasAssessment = true;
            } else {
                $assessment = (int)$assessmentParam;
            }
        }

        // 1. Получаем данные из архива
        $archiveResults = $this->getArchiveQueryService()->getCallDetails(
            $this->dateFrom,
            $this->dateTo,
            $voxUserId,
            $assessment,
            $hasAssessment,
            self::DETAILS_LIMIT
        );

        if (empty($archiveResults)) {
            $action = $this->request->get('download_details');
            if ($action) {
                $this->downloadDetails([], $voxUserId, $assessmentParam);
                return;
            }

            $this->json_output([
                'status' => 'success',
                'items' => [],
                'limit' => self::DETAILS_LIMIT,
            ]);
            return;
        }

        // 2. Собираем queue_id и vox_user_id
        $queueIds = [];
        $voxUserIds = [];
        foreach ($archiveResults as $row) {
            if (!empty($row->queue_id)) {
                $queueIds[] = (int)$row->queue_id;
            }
            if (!empty($row->vox_user_id)) {
                $voxUserIds[] = (int)$row->vox_user_id;
            }
        }
        $queueIds = array_unique($queueIds);
        $voxUserIds = array_unique($voxUserIds);

        // 3. Получаем названия очередей из основной БД
        $queueTitles = $this->getQueueTitlesByIds($queueIds);

        // 4. Получаем имена операторов из основной БД
        $operatorNames = $this->getOperatorNamesByVoxUserIds($voxUserIds);

        // 5. Объединяем данные
        $items = [];
        foreach ($archiveResults as $item) {
            $isIncoming = !empty($item->is_incoming);
            $phone = $isIncoming ? ($item->phone_a ?? '') : ($item->phone_b ?? '');
            $tags = implode("\n", array_column(json_decode($item->tags, true) ?? [], 'tag_name'));

            $queueId = (int)($item->queue_id ?? 0);
            $itemVoxUserId = (int)($item->vox_user_id ?? 0);

            $items[] = [
                'datetime' => !empty($item->datetime_start) ? date('d.m.Y H:i:s', strtotime($item->datetime_start)) : '',
                'phone' => (string)$phone,
                'queue' => (string)($queueTitles[$queueId] ?? ''),
                'operator' => (string)($operatorNames[$itemVoxUserId] ?? 'Оператор не определён'),
                'tags' => $tags,
                'assessment' => $item->assessment !== null ? (string)$item->assessment : '',
                'record_url' => (string)($item->record_url ?? ''),
                'user_id' => (string)($item->user_id ?? ''),
            ];
        }

        $action = $this->request->get('download_details');
        if ($action) {
            $this->downloadDetails($items, $voxUserId, $assessmentParam);
            return;
        }

        $this->json_output([
            'status' => 'success',
            'items' => $items,
            'limit' => self::DETAILS_LIMIT,
        ]);
    }

    /**
     * Получить названия очередей по ID
     *
     * @param array $queueIds
     * @return array Ассоциативный массив queue_id => title
     */
    private function getQueueTitlesByIds(array $queueIds): array
    {
        if (empty($queueIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($queueIds), '?'));
        $this->db->query(
            "SELECT vox_queue_id, title FROM s_vox_queues WHERE vox_queue_id IN ($placeholders)",
            ...$queueIds
        );

        $titles = [];
        foreach ($this->db->results() ?: [] as $row) {
            $titles[(int)$row->vox_queue_id] = $row->title;
        }

        return $titles;
    }

    private function downloadDetails(array $items, int $voxUserId, ?string $assessmentParam): void
    {
        $header = [
            'Дата и время' => 'string',
            'Номер телефона' => 'string',
            'Очередь' => 'string',
            'ФИО оператора' => 'string',
            'Тэг' => 'string',
            'Оценка' => 'string',
            'Ссылка на аудио' => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Детализация', $header);

        $userUrl = $this->config->back_url . '/client/';
        foreach ($items as $item) {
            // Ссылка на запись
            $recordUrl = trim((string)($item['record_url'] ?? ''));
            if (!empty($recordUrl)) {
                // Лимит Excel для функции HYPERLINK - 255 символов.
                // Если ссылка длиннее, вставляем её как обычный текст, иначе ячейка будет пустой.
                if (mb_strlen($recordUrl) > 255) {
                    $recordCell = $recordUrl;
                } else {
                    $recordCell = '=HYPERLINK("' . $recordUrl . '", "Открыть")';
                }
            } else {
                $recordCell = '';
            }

            // Ссылка на клиента
            $userId = $item['user_id'] ?? '';
            $phone = (string)($item['phone'] ?? '');

            if (!empty($userId) && $userId !== '0') {
                $phoneCell = '=HYPERLINK("' . $userUrl . $userId . '", "' . $phone . '")';
            } else {
                $phoneCell = $phone;
            }

            $row = [
                $item['datetime'],
                $phoneCell,
                $item['queue'],
                $item['operator'],
                $item['tags'],
                $item['assessment'],
                $recordCell,
            ];
            $writer->writeSheetRow('Детализация', $row);
        }

        $assessmentLabel = $assessmentParam ?? 'all';
        $filename = 'vox_details_' . $voxUserId . '_assessment_' . $assessmentLabel . '_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }
}
