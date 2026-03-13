<?php

declare(strict_types=1);

require_once 'View.php';
require dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/../api/services/MissingService.php';

/**
 * Класс для вывода отчёта по отвалам
 */
class ReportMissingsNewView extends View
{

    private array $managerIds = [232, 177, 125, 305, 294, 363, 295, 320, 392, 594, 372, 123, 667];

    private MissingService $missingService;

    public function __construct()
    {
        parent::__construct();

        $this->missingService = new MissingService($this->db);
        $action = $this->request->get( 'action');

        if ($action && method_exists($this, $action)) {
            $this->{$action}();
            exit;
        }
    }

    public function fetch(): string
    {
        $defaultFrom = date('Y-m-01 00:00:00');
        $defaultTo   = date('Y-m-d H:i:s');
        $dateFrom = $this->normalizeDate($this->request->get('date_from'), $defaultFrom);
        $dateTo   = $this->normalizeDate($this->request->get('date_to'), $defaultTo, false);

        $this->design->assign('date_from', date('Y-m-d', strtotime($dateFrom)));
        $this->design->assign('date_to', date('Y-m-d', strtotime($dateTo)));
        $this->design->assign('report_uri', strtok($_SERVER['REQUEST_URI'], '?'));

        return $this->design->fetch('report_missings_new.tpl');
    }

    /**
     * VOX (type='missing'): общая сводка за период
     * POST: date_from, date_to
     * Возвращает: { success: true, data: {...} }
     */
    public function loadVoxCallsSummary(): void
    {
        $dateFrom = $this->normalizeDate($this->request->post('date_from'), date('Y-m-01 00:00:00'));
        $dateTo   = $this->normalizeDate($this->request->post('date_to'), date('Y-m-d H:i:s'), false);

        // Считаем итоги за период (status, created_at)
        $sql = $this->db->placehold("
        SELECT
            COUNT(*)                                         AS total_calls,
            SUM(cal.status = 0)                              AS created_calls,        -- 0 = создан
            SUM(cal.status = 1)                              AS not_reached_calls,    -- 1 = не дозвонились
            SUM(cal.status = 2)                              AS reached_calls,        -- 2 = успешно дозвонились
            SUM(cal.is_redirected_manager = 1)               AS redirected_to_manager -- дошли до менеджера
        FROM s_vox_robot_calls cal
        WHERE cal.type = 'missing'
          AND cal.created_at BETWEEN ? AND ?
    ", $dateFrom, $dateTo);

        $this->db->query($sql);
        $row = $this->db->result();

        $total      = (int)($row->total_calls ?? 0);
        $created    = (int)($row->created_calls ?? 0);
        $notReached = (int)($row->not_reached_calls ?? 0);
        $reached    = (int)($row->reached_calls ?? 0);
        $redirected = (int)($row->redirected_to_manager ?? 0);

        $pct = function (int $num, int $den): float {
            return $den > 0 ? round($num / $den * 100, 2) : 0.0;
        };

        $data = [
            'total_calls'                   => $total,
            'created_calls'                 => $created,
            'not_reached_calls'             => $notReached,
            'reached_calls'                 => $reached,
            'redirected_to_manager'         => $redirected,

            'created_percent'               => $pct($created, $total),
            'not_reached_percent'           => $pct($notReached, $total),
            'success_percent'               => $pct($reached, $total),

            'redirected_percent_all'        => $pct($redirected, $total),
            'redirected_percent_of_reached' => $pct($redirected, $reached),
        ];

        $this->json_output(['success' => true, 'data' => $data]);
    }


    /**
     * VOX (type='missing'): разбивка по дням
     * POST: date_from, date_to
     * Возвращает: { success: true, rows: [...] }
     */
    public function loadVoxCallsByDays(): void
    {
        $dateFrom = $this->normalizeDate($this->request->post('date_from'), date('Y-m-01 00:00:00'));
        $dateTo   = $this->normalizeDate($this->request->post('date_to'), date('Y-m-d H:i:s'), false);

        // Считаем агрегаты по дням (DATE(created_at))
        $rows = $this->getVoxCallsByDays($dateFrom, $dateTo);
        $this->json_output(['success' => true, 'rows' => $rows]);
    }



    /**
     * KPI блок
     */
    public function loadKpi(): void
    {
        $dateFrom = $this->normalizeDate($this->request->post('date_from'), date('Y-m-01 00:00:00'));
        $dateTo   = $this->normalizeDate($this->request->post('date_to'), date('Y-m-d H:i:s'), false);

        $data = $this->missingService->getStatistics($dateFrom, $dateTo);
        $this->json_output(['success' => true, 'data' => $data]);
    }

    /**
     * Менеджеры по дням
     */
    public function loadByDays(): void
    {
        $dateFrom = $this->normalizeDate($this->request->post('date_from'), date('Y-m-01 00:00:00'));
        $dateTo   = $this->normalizeDate($this->request->post('date_to'), date('Y-m-d H:i:s'), false);

        $data = $this->missingService->getManagerStatisticsByDays($dateFrom, $dateTo, $this->managerIds);
        $this->json_output(['success' => true, 'rows' => $data]);
    }

    /**
     * Сводная по менеджерам
     */
    public function loadManagers(): void
    {
        $dateFrom = $this->normalizeDate($this->request->post('date_from'), date('Y-m-01 00:00:00'));
        $dateTo   = $this->normalizeDate($this->request->post('date_to'), date('Y-m-d H:i:s'), false);

        $data = $this->missingService->getManagerStatistics($dateFrom, $dateTo, $this->managerIds);
        $this->json_output(['success' => true, 'rows' => $data]);
    }

    /**
     * Формирование и отдача Excel-отчёта (XLSXWriter)
     *
     * @return void
     */
    public function download(): void
    {
        $defaultFrom = date('Y-m-01 00:00:00'); // start of current month
        $defaultTo   = date('Y-m-d H:i:s');     // now
        $dateFrom = $this->normalizeDate($this->request->get('date_from'), $defaultFrom);
        $dateTo   = $this->normalizeDate($this->request->get('date_to'), $defaultTo, false);

        $statistic = $this->getSummaryStatistics($dateFrom, $dateTo);
        $managers  = $this->missingService->getManagerStatistics($dateFrom, $dateTo, $this->managerIds);
        $details   = $this->missingService->getIssueDetails($dateFrom, $dateTo, $this->managerIds);
        $stages    = $this->getStageStatistics($dateFrom, $dateTo);
        $callsByDays = $this->getVoxCallsByDays($dateFrom, $dateTo);
        $writer = new XLSXWriter();

        // helper function for calculating widths
        $calcWidths = function (array $rows, array $headers): array {
            $widths = array_map('mb_strlen', array_keys($headers));
            foreach ($rows as $row) {
                $i = 0;
                foreach ($row as $val) {
                    $len = mb_strlen((string)$val);
                    if ($len > $widths[$i]) {
                        $widths[$i] = $len;
                    }
                    $i++;
                }
            }
            // add some padding
            return array_map(fn($w) => $w + 2, $widths);
        };

        // ===== Лист 1. Сводка =====
        $summaryHeader = [
            'Показатель'  => 'string',
            'Значение'    => 'string',
            'Комментарий' => 'string',
        ];

        $summaryRows = [
            ['Отчёт по отвалам', '', ''],
            ['Период', "$dateFrom — $dateTo", ''],

            // Общие показатели
            ['Отвалов (totals)', $statistic['totals'], "Всего за период"],
            ['Дошли до заявки (completed_total)', $statistic['completed_total'], "С менеджером: {$statistic['completed_with_manager']} / Самостоятельно: {$statistic['completed_self']}"],
            ['В работе', $statistic['in_progress'], ''],
            ['Необработанные', $statistic['unhandled'], ''],

            // Звонки
            ['Попыток звонка / Принятых / Непринятых',
                "{$statistic['total_calls']} / {$statistic['accepted_calls']} / {$statistic['not_accepted_calls']}",
                "Успешных: {$statistic['call_percent']}%"],
            ['Сред. длит. принятых', "{$statistic['avg_duration_accepted_calls']} мин", "Общая длительность: {$statistic['total_duration_all_calls']} с"],

            // Займы
            ['Пользователи с выданным займом', $statistic['users_loan_issued'], "Конверсия: {$statistic['conversion_total']}%"],
            ['Пользователи с одобренным займом', $statistic['users_loan_approved'], "Конверсия: {$statistic['conversion_approved']}%"],
            ['Пользователи с отказом', $statistic['users_loan_rejected'], "Конверсия отказов: {$statistic['conversion_rejected']}%"],

            // Конверсии завершённых
            ['Конверсия завершённых (всего)', "{$statistic['conversion_completed_total']}%",
                "С менеджером: {$statistic['conversion_completed_with_manager']}% / Самостоятельно: {$statistic['conversion_completed_self']}%"],

            // Continue
            ['Выбрали «Продолжить»', $statistic['continue_count'],
                "Выдачи: {$statistic['issued_from_continue']} / Конверсия: {$statistic['conversion_continue']}%"],

            // Bonon
            ['Клиенты БонОн', $statistic['bonon_count'], ''],
            ['Клиенты не БонОн', $statistic['not_bonon_count'], ''],

            // Дополнительно
            ['Новые клиенты за день', $statistic['new_clients_today'], ''],

            // Стадии (можно вывести только сводку или все по отдельности)
            ['Stage 1', $statistic['stage1'], ''],
            ['Stage 2', $statistic['stage2'], ''],
            ['Stage 3', $statistic['stage3'], ''],
            ['Stage 4', $statistic['stage4'], ''],
            ['Stage 5', $statistic['stage5'], ''],
            ['Stage 6', $statistic['stage6'], ''],
            ['Stage 7', $statistic['stage7'], ''],
        ];


        $writer->writeSheetHeader('Сводка', $summaryHeader, ['widths' => $calcWidths($summaryRows, $summaryHeader)]);
        foreach ($summaryRows as $row) {
            $writer->writeSheetRow('Сводка', $row);
        }

        // ===== Лист 2. Менеджеры =====
        $stagesHeader = [
            'Стадия'       => 'string',
            'Кол-во'       => 'integer',
            '% от общего'  => 'string',
            'Определение'  => 'string',
        ];
        $stageRows = [];
        foreach ($stages as $stage) {
            $stageRows[] = [
                $stage['name'],
                $stage['count'],
                $stage['percent'].'%',
                $stage['description'],
            ];
        }
        $writer->writeSheetHeader('Стадии', $stagesHeader, ['widths' => $calcWidths($stageRows, $stagesHeader)]);
        foreach ($stageRows as $row) {
            $writer->writeSheetRow('Стадии', $row);
        }

        // ===== Лист 3. Менеджеры =====
        $managersHeader = [
            'Менеджер'                   => 'string',
            'Всего отвалов'               => 'integer',
            'Дошли до заявки (всего)'          => 'integer',
            'Конверсия завершённых'      => 'string',
            'Выдано займов'              => 'integer',
            'Конв. выдано'               => 'string',
            'Одобрено займов'            => 'integer',
            'Конв. одобрено'             => 'string',
            'Отказано займов'            => 'integer',
            'Конв. отказано'             => 'string',
            'Клиенты БонОн'              => 'integer',
            'Клиенты не БонОн'           => 'integer',
            'Конв. БонОн'                => 'string',
            'Всего звонков'              => 'integer',
            'Принятые звонки'            => 'integer',
            'Непринятые звонки'          => 'integer',
            'Общая длит. принятых звонков' => 'integer',
            'Сред. длит. принятого звонка' => 'string',
        ];

        $managerRows = [];
        foreach ($managers as $m) {
            $managerRows[] = [
                $m->manager_name ?? '',
                (int)($m->total_requests ?? 0),
                (int)($m->completed_total ?? 0),
                ($m->conversion_completed ?? 0) . '%',
                (int)($m->issued_count ?? 0),
                ($m->conversion_issued ?? 0) . '%',
                (int)($m->approved_count ?? 0),
                ($m->conversion_approved ?? 0) . '%',
                (int)($m->rejected_count ?? 0),
                ($m->conversion_rejected ?? 0) . '%',
                (int)($m->bonon_count ?? 0),
                (int)($m->not_bonon_count ?? 0),
                ($m->conversion_bonon ?? 0) . '%',
                (int)($m->total_calls ?? 0),
                (int)($m->accepted_calls ?? 0),
                (int)($m->not_accepted_calls ?? 0),
                (int)($m->total_duration_accepted_calls ?? 0),
                (string)($m->avg_accepted_duration ?? '0'),
            ];
        }

        $writer->writeSheetHeader('Менеджеры', $managersHeader, ['widths' => $calcWidths($managerRows, $managersHeader)]);
        foreach ($managerRows as $row) {
            $writer->writeSheetRow('Менеджеры', $row);
        }

        // ===== Лист 4. Детализация =====
        if (!empty($details)) {
            $detailsHeader = [
                'Дата'                         => 'string',
                'ID заявки'                    => 'integer',
                'Телефон'                      => 'string',
                'Источник'                     => 'string',
                'Менеджер'                     => 'string',
                'Стадия (текст)'               => 'string',
                'Стадия (номер)'               => 'integer',
                'Статус звонка'                => 'string',
                'Продолжит оформлять'          => 'string',
                'Заполнена полностью'          => 'string',
                // --- метрики заказов ---
                'Выдан займ'                   => 'integer',
                'Одобрен займ'                 => 'integer',
                'Отказ'                        => 'integer',
                'Займ выдан (Да/Нет)'          => 'string',
                // --- bonon ---
                'Bonon'                        => 'string',
                // --- метрики звонков ---
                'Последний звонок'             => 'string',
                'Всего звонков'                => 'integer',
                'Принятые звонки'              => 'integer',
                'Непринятые звонки'            => 'integer',
                'Общая длит. принятых звонков' => 'integer',
            ];

            $detailRows = [];
            foreach ($details as $d) {
                $detailRows[] = [
                    date('d.m.Y', strtotime($d->first_missing_date)),   // Дата
                    (int)$d->id,                                        // ID заявки
                    $d->phone_mobile,                                   // Телефон
                    $d->utm_source,                                     // Источник
                    $d->manager_name ?? '',                             // Менеджер
                    $d->stage_in_contact ?? '',                         // Стадия текст
                    (int)($d->last_step ?? 0),                          // Стадия номер
                    $d->call_status_text ?? '',                         // Статус звонка
                    $d->continue_order_text ?? '',                      // Продолжит оформлять
                    $d->completed ?? 'Нет',                             // Заполнена полностью
                    // --- метрики заказов ---
                    (int)($d->has_issued_loan ?? 0),                    // Выдан займ
                    (int)($d->has_approved_loan ?? 0),                  // Одобрен займ
                    (int)($d->has_rejected_loan ?? 0),                  // Отказ
                    $d->loan_issued ?? 'Нет',                           // Займ выдан (Да/Нет)
                    // --- bonon ---
                    $d->bonon_text ?? 'Не БонОн',                       // Bonon текст
                    // --- метрики звонков ---
                    $d->last_call ?? '',                                // Последний звонок
                    (int)($d->total_calls ?? 0),                        // Всего звонков
                    (int)($d->accepted_calls ?? 0),                     // Принятые звонки
                    (int)($d->not_accepted_calls ?? 0),                 // Непринятые звонки
                    (int)($d->total_duration_accepted_calls ?? 0),      // Общая длительность принятых звонков
                ];
            }
            $writer->writeSheetHeader('Детализация', $detailsHeader, ['widths' => $calcWidths($detailRows, $detailsHeader)]);
            foreach ($detailRows as $row) {
                $writer->writeSheetRow('Детализация', $row);
            }
        }

        // ===== Лист 5. Звонки по дням =====
        $byDaysHeader = [
            'Дата'                            => 'string',
            'Всего звонков'                   => 'integer',
            'Создано'                         => 'integer',
            'Не дозвонились'                  => 'integer',
            'Успешно дозвонились'             => 'integer',
            'Дошли до менеджера'              => 'integer',
            '% создано'                       => 'string',
            '% не дозвонились'                => 'string',
            '% успешно'                       => 'string',
            '% дошли от всех'                 => 'string',
            '% дошли от успешных'             => 'string',
        ];

        $byDaysRows = [];
        foreach ($callsByDays as $r) {
            $byDaysRows[] = [
                (string)$r->day_called,
                (int)($r->total_calls ?? 0),
                (int)($r->created_calls ?? 0),
                (int)($r->not_reached_calls ?? 0),
                (int)($r->reached_calls ?? 0),
                (int)($r->redirected_to_manager ?? 0),
                isset($r->created_percent) ? ($r->created_percent . '%') : '0%',
                isset($r->not_reached_percent) ? ($r->not_reached_percent . '%') : '0%',
                isset($r->success_percent) ? ($r->success_percent . '%') : '0%',
                isset($r->redirected_percent_all) ? ($r->redirected_percent_all . '%') : '0%',
                isset($r->redirected_percent_of_reached) ? ($r->redirected_percent_of_reached . '%') : '0%',
            ];
        }

        // тот же helper $calcWidths, который у тебя выше
        $writer->writeSheetHeader('Звонки по дням', $byDaysHeader, ['widths' => $calcWidths($byDaysRows, $byDaysHeader)]);
        foreach ($byDaysRows as $row) {
            $writer->writeSheetRow('Звонки по дням', $row);
        }

        // ===== Вывод =====
        $filename = 'missings_report_' . date('Y-m-d', strtotime($dateFrom)) . '_' . date('Y-m-d', strtotime($dateTo)) . '.xlsx';
        $xlsxData = $writer->writeToString();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');
        header('Content-Length: ' . strlen($xlsxData));

        echo $xlsxData;
        exit;
    }

    /**
     * Вспомогательная обёртка, возвращающая массив с ключами, используемыми в download()
     */
    private function getSummaryStatistics(string $dateFrom, string $dateTo): array
    {
        $s = $this->missingService->getStatistics($dateFrom, $dateTo);

        return [
            // Базовые
            'totals'        => (int)($s->totals ?? 0),
            'in_progress'   => (int)($s->in_progress ?? 0),
            'unhandled'     => (int)($s->unhandled ?? 0),

            // Заполненные заявки
            'completed_total'        => (int)($s->completed_total ?? 0),
            'completed_with_manager' => (int)($s->completed_with_manager ?? 0),
            'completed_self'         => (int)($s->completed_self ?? 0),

            // Звонки (берём уже готовые метрики)
            'total_calls'                => (int)($s->total_calls ?? 0),
            'accepted_calls'             => (int)($s->accepted_calls ?? 0),
            'not_accepted_calls'         => (int)($s->not_accepted_calls ?? 0),
            'call_percent'               => (float)($s->call_percent ?? 0), // уже считается в SQL
            'avg_duration_accepted_calls'=> (float)($s->avg_duration_accepted_calls ?? 0),
            'total_duration_all_calls'   => (int)($s->total_duration_all_calls ?? 0),

            // Займы
            'users_loan_issued'   => (int)($s->users_loan_issued ?? 0),
            'conversion_total'    => (float)($s->conversion_total ?? 0),
            'users_loan_approved' => (int)($s->users_loan_approved ?? 0),
            'conversion_approved' => (float)($s->conversion_approved ?? 0),
            'users_loan_rejected' => (int)($s->users_loan_rejected ?? 0),
            'conversion_rejected' => (float)($s->conversion_rejected ?? 0),

            // Конверсии завершённых
            'conversion_completed_with_manager' => (float)($s->conversion_completed_with_manager ?? 0),
            'conversion_completed_self'         => (float)($s->conversion_completed_self ?? 0),
            'conversion_completed_total'        => (float)($s->conversion_completed_total ?? 0),

            // Continue
            'continue_count'      => (int)($s->continue_count ?? 0),
            'issued_from_continue'=> (int)($s->issued_from_continue ?? 0),
            'conversion_continue' => (float)($s->conversion_continue ?? 0),

            // Bonon
            'bonon_count'     => (int)($s->bonon_count ?? 0),
            'not_bonon_count' => (int)($s->not_bonon_count ?? 0),

            // Дополнительно
            'new_clients_today' => (int)($s->new_clients_today ?? 0),

            // Стадии (по желанию можно выводить отдельно)
            'stage1' => (int)($s->stage1 ?? 0),
            'stage2' => (int)($s->stage2 ?? 0),
            'stage3' => (int)($s->stage3 ?? 0),
            'stage4' => (int)($s->stage4 ?? 0),
            'stage5' => (int)($s->stage5 ?? 0),
            'stage6' => (int)($s->stage6 ?? 0),
            'stage7' => (int)($s->stage7 ?? 0),
        ];
    }


    /**
     * Возвращает массив стадий с именем, количеством, процентом и описанием
     */
    private function getStageStatistics(string $dateFrom, string $dateTo): array
    {
        $s = $this->missingService->getStatistics($dateFrom, $dateTo);
        $totals = (int)($s->totals ?? 0);

        $stages = [];
        $map = [
            1 => ['name' => 'Стадия 1', 'count' => (int)($s->stage1 ?? 0), 'description' => 'Отвалы на этапе ввода паспортных данных.'],
            2 => ['name' => 'Стадия 2', 'count' => (int)($s->stage2 ?? 0), 'description' => 'Отвалы на этапе ввода адресов'],
            3 => ['name' => 'Стадия 3', 'count' => (int)($s->stage3 ?? 0), 'description' => 'Отвалы на странице с предварительным решением'],
            4 => ['name' => 'Стадия 4', 'count' => (int)($s->stage4 ?? 0), 'description' => 'Отвалы на странице с привязкой карты'],
            5 => ['name' => 'Стадия 5', 'count' => (int)($s->stage5 ?? 0), 'description' => 'Отвалы на странице с загрузкой фото'],
            6 => ['name' => 'Стадия 6', 'count' => (int)($s->stage6 ?? 0), 'description' => 'Отвалы на этапе ввода данных о работе'],
            7 => ['name' => 'Стадия 7', 'count' => (int)($s->stage7 ?? 0), 'description' => 'Другое / Завершено'],
        ];

        foreach ($map as $idx => $info) {
            $count = $info['count'];
            $percent = $totals > 0 ? round(($count / $totals) * 100, 2) : 0;
            $stages[] = [
                'stage' => $idx,
                'name' => $info['name'],
                'count' => $count,
                'percent' => $percent,
                'description' => $info['description'],
            ];
        }

        return $stages;
    }

    /**
     * Агрегаты звонков по дням
     */
    private function getVoxCallsByDays(string $dateFrom, string $dateTo): array
    {
        $sql = $this->db->placehold("
        SELECT
            DATE(cal.created_at) AS day_called,
            COUNT(*)             AS total_calls,
            SUM(cal.status = 0)  AS created_calls,
            SUM(cal.status = 1)  AS not_reached_calls,
            SUM(cal.status = 2)  AS reached_calls,
            SUM(cal.is_redirected_manager = 1) AS redirected_to_manager,

            ROUND(SUM(cal.status = 0) / NULLIF(COUNT(*), 0) * 100, 2) AS created_percent,
            ROUND(SUM(cal.status = 1) / NULLIF(COUNT(*), 0) * 100, 2) AS not_reached_percent,
            ROUND(SUM(cal.status = 2) / NULLIF(COUNT(*), 0) * 100, 2) AS success_percent,

            ROUND(SUM(cal.is_redirected_manager = 1) / NULLIF(COUNT(*), 0) * 100, 2) AS redirected_percent_all,
            ROUND(SUM(cal.is_redirected_manager = 1) / NULLIF(SUM(cal.status = 2), 0) * 100, 2) AS redirected_percent_of_reached
        FROM s_vox_robot_calls cal
        WHERE cal.type = 'missing'
          AND cal.created_at BETWEEN ? AND ?
        GROUP BY day_called
        ORDER BY day_called
    ", $dateFrom, $dateTo);

        $this->db->query($sql);
        return (array)$this->db->results();
    }

    /**
     * Get normalized date in 'Y-m-d H:i:s' format or default.
     *
     * @param string|null $input
     * @param string      $default
     * @param bool        $isStartOfDay True → начало дня, False → конец дня
     * @return string
     */
    private function normalizeDate(?string $input, string $default, bool $isStartOfDay = true): string
    {
        if (empty($input)) {
            return $default;
        }

        $timestamp = strtotime($input);
        if ($timestamp === false) {
            return $default;
        }

        return $isStartOfDay
            ? date('Y-m-d 00:00:00', $timestamp)
            : date('Y-m-d 23:59:59', $timestamp);
    }
}
