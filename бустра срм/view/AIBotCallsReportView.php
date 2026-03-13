<?php

require_once 'View.php';

use App\Core\Application\Application;
use App\Service\AIBotCallsReportService;
use App\Service\BotActionDetailsService;
use App\Service\FileStorageFactory;
use App\Service\FileStorageService;

class AIBotCallsReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private const BOT_ACTIONS_MAP = [
        '/app/clients/fromtech-incoming-call' => 'Регистрация звонка',
        '/app/clients/{client_id}/unblock' => 'Разблокировка клиента',
        '/app/clients/{client_id}/block' => 'Блокировка клиента',
        '/app/clients/{client_id}/temporary-unsubscribe-sms' => 'Отписка от SMS',
        '/app/clients' => 'Проверка клиента',
        '/app/orders/{contract_number}/disable-additional-services-by-list' => 'Отключение доп. услуг',
        '/app/orders/switch-prolongation' => 'Переключение пролонгации',
        '/app/sms/send' => 'Отправка SMS',
        'ajax/Recompense.php' => 'Возврат ПО',
    ];
    private AIBotCallsReportService $reportService;
    private BotActionDetailsService $botActionService;
    private FileStorageService $callRecordsStorage;
    private int $currentPage;
    private array $filters;
    private string $dateFrom;
    private string $dateTo;

    private static array $columns = [
        ['key' => 'date_time', 'label' => 'Дата/Время', 'sort_key' => 'date_time'],
        ['key' => 'phone_mobile', 'label' => 'Номер телефона', 'sort_key' => 'phone_mobile'],
        ['key' => 'duration', 'label' => 'Длительность', 'sort_key' => 'duration'],
        ['key' => 'client_fio', 'label' => 'ФИО', 'sort_key' => 'client_fio'],
        ['key' => 'tag', 'label' => 'Тег', 'sort_key' => 'tag'],
        ['key' => 'transcript', 'label' => 'Транскрипция звонка'],
        ['key' => 'call_record', 'label' => 'Запись звонка'],
        ['key' => 'actions', 'label' => 'Действия'],
        ['key' => 'assessment', 'label' => 'Оценка клиента', 'sort_key' => 'assessment'],
        ['key' => 'transferred_to_operator', 'label' => 'Перевод на оператора', 'sort_key' => 'transferred_to_operator'],
    ];

    public function __construct()
    {
        parent::__construct();

        $app = Application::getInstance();
        $this->reportService = $app->make(AIBotCallsReportService::class);
        $this->botActionService = $app->make(BotActionDetailsService::class);

        /** @var FileStorageFactory $fsFactory */
        $fsFactory = $app->make(FileStorageFactory::class);
        $this->callRecordsStorage = $fsFactory->make('call_records');

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();

        $this->filters = $this->setFilters();

        $this->handleAction();
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
        $pagination = [
            'current_page' => $this->currentPage,
            'page_capacity' => self::PAGE_CAPACITY
        ];

        $sorting = $this->getSorting();

        $reportData = $this->reportService->getReportData(
            $this->filters,
            $pagination,
            $sorting,
            $this->dateFrom,
            $this->dateTo
        );

        if (!empty($reportData['items'])) {
            foreach ($reportData['items'] as &$item) {
                $val = (string)($item['call_record'] ?? '');
                if ($val !== '' && stripos($val, 'http://') !== 0 && stripos($val, 'https://') !== 0) {
                    $item['call_record'] = $this->callRecordsStorage->getPublicUrl($val);
                }

                $methodsList = $item['methods_list'] ?? null;
                $userId = (int)($item['client_id'] ?? 0);
                $callTime = $item['date_time'] ?? null;
                
                $item['actions'] = $this->formatActionsFromMethodsList($methodsList, $userId, $callTime);
            }
            unset($item);
        }

        $reportHeaders = [];
        foreach (self::$columns as $col) {
            $reportHeaders[] = [
                'key' => $col['key'],
                'label' => $col['label'],
                'sort_key' => $col['sort_key'] ?? null,
            ];
        }

        $this->design->assign_array([
            'meta_title' => 'Отчёт по звонкам ИИ',
            'reportHeaders' => $reportHeaders,
            'reportRows' => $reportData['items'],
            'items' => $reportData['items'],
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $reportData['total_pages'],
            'total_items' => $reportData['total_items'],
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
            'phone_mobile_filter' => $this->filters['phone_mobile'],
            'client_fio_filter' => $this->filters['client_fio'],
            'tag_filter' => $this->filters['tag'],
            'bot_action_filter' => $this->filters['bot_action'],
            'transferred_to_operator_filter' => $this->filters['transferred_to_operator'],
            'filterConfigurations' => $this->getFilterConfiguration($reportData['tags_options']),
        ]);

        return $this->design->fetch('ai_bot_calls_report.tpl');
    }

    private function setupDateRange(): void
    {
        $daterange = $this->request->get('daterange');
        if (empty($daterange)) {
            $daterange = date('d.m.Y', strtotime('-1 month')) . ' - ' . date('d.m.Y');
        }

        [$from, $to] = explode(' - ', $daterange);
        $this->dateFrom = date('Y-m-d', strtotime($from));
        $this->dateTo = date('Y-m-d', strtotime($to));
    }

    private function download(): void
    {
        $this->setupDateRange();
        $this->filters = $this->setFilters();
        
        $maxPeriod = 365;

        $dateFromTimestamp = strtotime($this->dateFrom);
        $dateToTimestamp = strtotime($this->dateTo);
        $diffInDays = ($dateToTimestamp - $dateFromTimestamp) / (60 * 60 * 24);

        if ($diffInDays > $maxPeriod) {
            $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
            return;
        }

        $header = [];
        foreach (self::$columns as $col) {
            $header[$col['label']] = isset($col['type']) ? $col['type'] : 'string';
        }

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт по звонкам ИИ', $header);

        $sorting = [
            'field' => 'date_time',
            'direction' => 'desc'
        ];
        
        $batch = [];
        $batchSize = 100;
        foreach ($this->reportService->getExportData($this->filters, $sorting, $this->dateFrom, $this->dateTo) as $item) {
            $batch[] = $item;
            if (count($batch) >= $batchSize) {
                $this->writeExportBatch($writer, $batch);
                $batch = [];
            }
        }
        if (!empty($batch)) {
            $this->writeExportBatch($writer, $batch);
        }

        $filename = 'ai_bot_calls_report_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    private function writeExportBatch(XLSXWriter $writer, array $batch): void
    {
        if (empty($batch)) {
            return;
        }

        $prepared = [];
        foreach ($batch as $item) {
            $prepared[] = [
                'item' => $item,
                'call_data' => json_decode($item->call_data, true),
            ];
        }

        $actionsMap = $this->botActionService->buildActionsForBatch(
            $prepared,
            function (string $url): string {
                return $this->resolveActionLabel($url);
            }
        );

        foreach ($prepared as $entry) {
            $item = $entry['item'];
            $callData = $entry['call_data'] ?? [];
            $row = [];
            foreach (self::$columns as $col) {
                if ($col['key'] === 'transcript') {
                    $row[] = $this->reportService->formatTranscriptForExport($callData['call_transcript'] ?? '');
                } elseif ($col['key'] === 'actions') {
                    $row[] = $actionsMap[$item->id] ?? 'Неизвестно';
                } else {
                    $row[] = $this->getExportColumnValue($item, $col['key'], $callData);
                }
            }
            $writer->writeSheetRow('Отчёт по звонкам ИИ', $row);
        }
    }

    private function setFilters(): array
    {
        return [
            'phone_mobile' => $this->request->get('phone_mobile') ?? null,
            'client_fio' => $this->request->get('client_fio') ?? null,
            'tag' => $this->request->get('tag') ?? null,
            'bot_action' => $this->request->get('bot_action') ?? null,
            'transferred_to_operator' => $this->request->get('transferred_to_operator') ?? null,
        ];
    }

    private function getSorting(): array
    {
        $sort = $this->request->get('sort') ?? null;
        if ($sort) {
            $pos = strrpos($sort, '_');
            if ($pos !== false) {
                $field = substr($sort, 0, $pos);
                $direction = substr($sort, $pos + 1);
                return [
                    'field' => $field,
                    'direction' => $direction
                ];
            }
        }
        
        return [
            'field' => 'date_time',
            'direction' => 'desc'
        ];
    }

    private function getExportColumnValue(object $item, string $key, array $callData): string
    {
        switch ($key) {
            case 'date_time':
                return date('d.m.Y H:i:s', strtotime($item->created));
                
            case 'phone_mobile':
                return $item->phone_mobile ?? $callData['msisdn'] ?? '';
                
            case 'duration':
                return $this->reportService->formatDuration($callData['duration'] ?? 0);
                
            case 'client_fio':
                return trim("$item->lastname $item->firstname $item->patronymic") ?: 'Не указано';
                
            case 'tag':
                return $callData['tag'] ?? 'Не указан';
                
            case 'actions':
                $userId = (int)($item->user_id ?? 0);
                $callTime = $item->created ?? null;
                return $this->formatActionsFromMethodsList($callData['methods_list'] ?? null, $userId, $callTime);
                
            case 'assessment':
                return $callData['assessment'] ?? 'Не указано';
                
            case 'transferred_to_operator':
                return $this->reportService->isTransferredToOperator($callData['switch_to_operator'] ?? null) ? 'Да' : 'Нет';
                
            case 'call_record':
                $recordUrl = $callData['call_record'] ?? '';
                if (empty($recordUrl)) {
                    return 'Нет записи';
                }
                return $recordUrl;
                
            default:
                return '';
        }
    }

    private function getFilterConfiguration(array $tagsOptions = []): array
    {
        return [
            [
                'name' => 'phone_mobile',
                'label' => 'Телефон',
                'type' => 'text',
                'value' => $this->filters['phone_mobile'] ?? '',
                'placeholder' => 'Введите телефон'
            ],
            [
                'name' => 'client_fio',
                'label' => 'ФИО клиента',
                'type' => 'text',
                'value' => $this->filters['client_fio'] ?? '',
                'placeholder' => 'Введите ФИО'
            ],
            [
                'name' => 'tag',
                'label' => 'Тег',
                'type' => 'select',
                'value' => $this->filters['tag'] ?? '',
                'options' => $tagsOptions,
            ],
            [
                'name' => 'bot_action',
                'label' => 'Действие бота',
                'type' => 'select',
                'value' => $this->filters['bot_action'] ?? '',
                'options' => $this->getBotActionsFilterOptions(),
            ],
            [
                'name' => 'transferred_to_operator',
                'label' => 'Перевод на оператора',
                'type' => 'select',
                'value' => $this->filters['transferred_to_operator'] ?? '',
                'options' => [
                    '' => 'Все',
                    'Да' => 'Да',
                    'Нет' => 'Нет'
                ],
            ],
        ];
    }

    private function getBotActionsFilterOptions(): array
    {
        $options = ['' => 'Все'];
        
        foreach (self::BOT_ACTIONS_MAP as $pattern => $label) {
            if (strpos($pattern, '{') !== false) {
                $searchPattern = preg_replace('/\{[^}]+\}/', '%', $pattern);
            } else {
                $searchPattern = $pattern;
            }
            
            $options[$searchPattern] = $label;
        }
        
        return $options;
    }

    private function formatActionsFromMethodsList(?array $methodsList, int $userId, ?string $callTime): string
    {
        if (empty($methodsList)) {
            return 'Неизвестно';
        }

        if ($userId === 0 || !$callTime) {
            $actions = $this->collectActions($methodsList, function (string $url): string {
                return $this->resolveActionLabel($url);
            });
            return $this->formatActionsList($actions, ', ');
        }

        $actions = $this->collectActions($methodsList, function (string $url) use ($userId, $callTime): string {
            return $this->botActionService->getActionDetails($url, $userId, $callTime);
        });
        return $this->formatActionsList($actions, '; ');
    }

    private function collectActions(array $methodsList, callable $resolver): array
    {
        $out = [];
        foreach ($methodsList as $url) {
            if (!is_string($url) || $url === '') {
                continue;
            }
            $label = (string)$resolver($url);
            if ($label !== '') {
                $out[] = $label;
            }
        }

        return array_values(array_unique($out));
    }

    private function formatActionsList(array $actions, string $separator): string
    {
        return empty($actions) ? 'Неизвестно' : implode($separator, $actions);
    }

    private function resolveActionLabel(string $url): string
    {
        if (empty($url)) {
            return '';
        }

        foreach (self::BOT_ACTIONS_MAP as $pattern => $label) {
            if (strpos($pattern, '{') === false) {
                if (strpos($url, $pattern) !== false) {
                    return $label;
                }
            }
        }

        // проверяем параметризованные URL
        $path = (string)parse_url($url, PHP_URL_PATH);
        if ($path !== '') {
            foreach (self::BOT_ACTIONS_MAP as $pattern => $label) {
                if (strpos($pattern, '{') === false) {
                    continue;
                }
                
                // заменяем {client_id} и {contract_number} на регулярные выражения
                $regex = '#^' . preg_quote($pattern, '#') . '$#';
                $regex = str_replace('\\{client_id\\}', '\d+', $regex);
                $regex = str_replace('\\{contract_number\\}', '[A-Za-z0-9\-]+', $regex);
                
                if (preg_match($regex, $path)) {
                    return $label;
                }
            }
        }

        // автопарсинг по первому сегменту пути
        if ($path !== '') {
            $path = preg_replace('#^/app/#', '', $path);
            $segments = explode('/', trim($path, '/'));
            $resource = $segments[0] ?? '';
            if ($resource !== '') {
                return ucfirst(str_replace(['-', '_'], ' ', $resource));
            }
        }

        return '';
    }
}
