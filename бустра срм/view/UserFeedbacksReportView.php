<?php

require_once 'View.php';

class UserFeedbacksReportView extends View
{
    private const PAGE_CAPACITY = 15;
    private int $currentPage;
    private int $totalItems;
    private int $pagesNum;
    private string $dateFrom;
    private string $dateTo;
    private bool $canSeeClientUrl;

    public function __construct()
    {
        parent::__construct();

        $this->currentPage = max(1, $this->request->get('page', 'integer') ?? 1);
        $this->setupDateRange();
        $this->totalItems = $this->getTotals();
        $this->pagesNum = (int)ceil($this->totalItems / self::PAGE_CAPACITY);
        $this->canSeeClientUrl = in_array('clients', $this->manager->permissions);

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
        $items = $this->getResults($this->currentPage);

        $this->design->assign_array(array(
            'items' => $items,
            'current_page_num' => $this->currentPage,
            'total_pages_num' => $this->pagesNum,
            'total_items' => $this->totalItems,
            'reportUri' => strtok($_SERVER['REQUEST_URI'], '?'),
            'can_see_client_url' => $this->canSeeClientUrl,
            'date_from' => date('d.m.Y', strtotime($this->dateFrom)),
            'date_to' => date('d.m.Y', strtotime($this->dateTo)),
        ));

        return $this->design->fetch('user_feedbacks_report.tpl');
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

    private function getResults(int $currentPage)
    {
        $offset = self::PAGE_CAPACITY * ($currentPage - 1);

        $this->db->query("
            SELECT 
                u.lastname, 
                u.firstname, 
                u.patronymic,
                uf.user_id,
                uf.created_at,
                uf.order_id,
                o.have_close_credits,
                uf.data AS feedback_data,
                ROUND(AVG(CAST(JSON_EXTRACT(uf.data, '$.rate') AS UNSIGNED)) OVER (), 1) AS avg_rate
            FROM s_user_feedbacks uf
            LEFT JOIN s_users u ON u.id = uf.user_id
            LEFT JOIN s_orders o ON o.id = uf.order_id
            WHERE DATE(uf.created_at) BETWEEN ? AND ?
            ORDER BY uf.created_at DESC
            LIMIT ? OFFSET ?",
            $this->dateFrom, $this->dateTo, self::PAGE_CAPACITY, $offset
        );

        return $this->db->results();
    }

    /**
     * @throws Exception
     */
    private function getAllResults(): array
    {
        $this->db->query("
            SELECT 
                u.lastname, 
                u.firstname, 
                u.patronymic,
                uf.user_id,
                uf.created_at,
                uf.order_id,
                o.have_close_credits,
                uf.data AS feedback_data,
                ROUND(AVG(CAST(JSON_EXTRACT(uf.data, '$.rate') AS UNSIGNED)) OVER (), 1) AS avg_rate
            FROM s_user_feedbacks uf
            LEFT JOIN s_users u ON u.id = uf.user_id
            LEFT JOIN s_orders o ON o.id = uf.order_id
            WHERE DATE(uf.created_at) BETWEEN ? AND ?
            ORDER BY uf.created_at DESC",
            $this->dateFrom, $this->dateTo
        );

        return $this->db->results();
    }

    private function getTotals(): int
    {
        $query = $this->db->placehold("
            SELECT COUNT(id) AS total 
            FROM s_user_feedbacks 
            WHERE DATE(created_at) BETWEEN ? AND ?",
            $this->dateFrom, $this->dateTo
        );
        $this->db->query($query);
        return (int)$this->db->result('total');
    }

    /**
     * @throws Exception
     */
    private function download(): void
    {
        $maxPeriod = 365; // 1 год в днях

        $dateFromTimestamp = strtotime($this->dateFrom);
        $dateToTimestamp = strtotime($this->dateTo);
        $diffInDays = ($dateToTimestamp - $dateFromTimestamp) / (60 * 60 * 24);

        // Проверка, что выбранный диапазон не превышает 1 год
        if ($diffInDays > $maxPeriod) {
            $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
        }

        $header = [
            'Клиент' => 'string',
            'Тип клиента' => 'string',
            'Заявка' => 'string',
            'Дата оценки' => 'string',
            'Время оценки' => 'string',
            'Оценка' => 'string',
            'Причина оценки' => 'string',
        ];

        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Отчёт', $header);

        $items = $this->getAllResults();

        $userUrl = $this->config->back_url . "/client/";
        $orderUrl = $this->config->back_url . "/order/";

        foreach ($items as $item) {
            $userFullname = trim("{$item->lastname} {$item->firstname} {$item->patronymic}") ?? '';
            $createdDate = date('d.m.Y', strtotime($item->created_at)) ?? '';
            $createdTime = date('H:i:s', strtotime($item->created_at)) ?? '';
            $feedbackData = json_decode($item->feedback_data);

            $orderId = $item->order_id ?? '';
            $orderHyperlink = '=HYPERLINK("' . $orderUrl . $orderId . '", "' . $orderId . '")';

            $userId = $item->user_id ?? '';
            $userHyperlink = '=HYPERLINK("' . $userUrl . $userId . '", "' . $userFullname . '")';

            $clientType = ($item->have_close_credits == 1) ? 'ПК' : 'НК';

            $writer->writeSheetRow('Отчёт', [
                $this->canSeeClientUrl ? $userHyperlink : $userFullname,
                $clientType,
                $this->canSeeClientUrl ? $orderHyperlink : $orderId,
                $createdDate,
                $createdTime,
                $feedbackData->rate ?? '',
                $feedbackData->reason ?? ''
            ]);
        }

        $writer->writeSheetRow('Отчёт', [
            'Средняя оценка',
            '',
            '',
            '',
            '',
            $items[0]->avg_rate ?? '',
            ''
        ]);

        $filename = 'user_feedbacks_report_' . date('Y-m-d') . '.xlsx';

        // Отправка файла для загрузки
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }

    private function downloadStatistics(): void
    {
        $maxPeriod = 365;

        $dateFromTimestamp = strtotime($this->dateFrom);
        $dateToTimestamp = strtotime($this->dateTo);
        $diffInDays = ($dateToTimestamp - $dateFromTimestamp) / (60 * 60 * 24);

        if ($diffInDays > $maxPeriod) {
            $this->json_output(['status' => 'error', 'message' => 'Выбранный период превышает допустимый лимит в 1 год.']);
        }

        $allItems = $this->getAllResults();
        $pkItems = [];
        $nkItems = [];
        foreach ($allItems as $item) {
            if ($item->have_close_credits == 1) {
                $pkItems[] = $item;
            } else {
                $nkItems[] = $item;
            }
        }

        $writer = new XLSXWriter();
        $writer->setAuthor('Feedback Report');

        // *** Все клиенты ***
        $items = $allItems;
        $sheet = 'Все';
        $total = count($items);
        $positive = 0;
        $negative = 0;
        $reasons = ['positive' => [], 'negative' => []];

        foreach ($items as $item) {
            $feedback = json_decode($item->feedback_data);
            $rate = (int)($feedback->rate ?? 0);
            $reason = trim($feedback->reason ?? 'Не указано');

            if ($rate >= 4) {
                $positive++;
                $category = 'positive';
            } elseif ($rate >= 1) {
                $negative++;
                $category = 'negative';
            } else {
                continue;
            }

            if (!isset($reasons[$category][$reason])) {
                $reasons[$category][$reason] = 0;
            }

            $reasons[$category][$reason]++;
        }

        $title = 'Отчёт по отзывам за период:' . date('d.m.Y', strtotime($this->dateFrom)) . ' – ' . date('d.m.Y', strtotime($this->dateTo));
        $writer->writeSheetRow($sheet, [$title], ['font-style' => 'bold', 'font-size' => 14, 'halign' => 'center']);
        $writer->markMergedCell($sheet, 0, 0, 0, 5);
        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Всего отзывов', $total]);
        $writer->writeSheetRow($sheet, ['Оценки 4-5', $positive, round($positive / $total * 100, 2) . '%']);
        $writer->writeSheetRow($sheet, ['Оценки 1-3', $negative, round($negative / $total * 100, 2) . '%']);
        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Позитивные причины'], ['font-style' => 'bold', 'font-size' => 13]);
        $writer->writeSheetRow($sheet, []);
        $writer->writeSheetRow($sheet, ['', 'кол-во', 'от позитивных', 'от всех'], ['font-size' => 12]);

        arsort($reasons['positive']);

        foreach ($reasons['positive'] ?? [] as $reason => $count) {
            $percentFromGroup = $positive > 0 ? round($count / $positive * 100, 2) : 0;
            $percentFromTotal = $total > 0 ? round($count / $total * 100, 2) : 0;
            $writer->writeSheetRow($sheet, [$reason, $count, "{$percentFromGroup}%", "{$percentFromTotal}%"]);
        }

        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Негативные причины'], ['font-style' => 'bold', 'font-size' => 13]);
        $writer->writeSheetRow($sheet, []);
        $writer->writeSheetRow($sheet, ['', 'кол-во', 'от негативных', 'от всех'], ['font-size' => 12]);

        arsort($reasons['negative']);

        foreach ($reasons['negative'] ?? [] as $reason => $count) {
            $percentFromGroup = $negative > 0 ? round($count / $negative * 100, 2) : 0;
            $percentFromTotal = $total > 0 ? round($count / $total * 100, 2) : 0;
            $writer->writeSheetRow($sheet, [$reason, $count, "{$percentFromGroup}%", "{$percentFromTotal}%"]);
        }

        // *** Постоянные клиенты ***
        $items = $pkItems;
        $sheet = 'ПК';
        $total = count($items);
        $positive = 0;
        $negative = 0;
        $reasons = ['positive' => [], 'negative' => []];

        foreach ($items as $item) {
            $feedback = json_decode($item->feedback_data);
            $rate = (int)($feedback->rate ?? 0);
            $reason = trim($feedback->reason ?? 'Не указано');

            if ($rate >= 4) {
                $positive++;
                $category = 'positive';
            } elseif ($rate >= 1) {
                $negative++;
                $category = 'negative';
            } else {
                continue;
            }

            if (!isset($reasons[$category][$reason])) {
                $reasons[$category][$reason] = 0;
            }

            $reasons[$category][$reason]++;
        }

        $title = 'Отчёт по отзывам за период:' . date('d.m.Y', strtotime($this->dateFrom)) . ' – ' . date('d.m.Y', strtotime($this->dateTo));
        $writer->writeSheetRow($sheet, [$title], ['font-style' => 'bold', 'font-size' => 14, 'halign' => 'center']);
        $writer->markMergedCell($sheet, 0, 0, 0, 5);
        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Всего отзывов', $total]);
        $writer->writeSheetRow($sheet, ['Оценки 4-5', $positive, round($positive / $total * 100, 2) . '%']);
        $writer->writeSheetRow($sheet, ['Оценки 1-3', $negative, round($negative / $total * 100, 2) . '%']);
        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Позитивные причины'], ['font-style' => 'bold', 'font-size' => 13]);
        $writer->writeSheetRow($sheet, []);
        $writer->writeSheetRow($sheet, ['', 'кол-во', 'от позитивных', 'от всех'], ['font-size' => 12]);

        arsort($reasons['positive']);

        foreach ($reasons['positive'] ?? [] as $reason => $count) {
            $percentFromGroup = $positive > 0 ? round($count / $positive * 100, 2) : 0;
            $percentFromTotal = $total > 0 ? round($count / $total * 100, 2) : 0;
            $writer->writeSheetRow($sheet, [$reason, $count, "{$percentFromGroup}%", "{$percentFromTotal}%"]);
        }

        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Негативные причины'], ['font-style' => 'bold', 'font-size' => 13]);
        $writer->writeSheetRow($sheet, []);
        $writer->writeSheetRow($sheet, ['', 'кол-во', 'от негативных', 'от всех'], ['font-size' => 12]);

        arsort($reasons['negative']);

        foreach ($reasons['negative'] ?? [] as $reason => $count) {
            $percentFromGroup = $negative > 0 ? round($count / $negative * 100, 2) : 0;
            $percentFromTotal = $total > 0 ? round($count / $total * 100, 2) : 0;
            $writer->writeSheetRow($sheet, [$reason, $count, "{$percentFromGroup}%", "{$percentFromTotal}%"]);
        }

        // *** Новые клиенты ***
        $items = $nkItems;
        $sheet = 'НК';
        $total = count($items);
        $positive = 0;
        $negative = 0;
        $reasons = ['positive' => [], 'negative' => []];

        foreach ($items as $item) {
            $feedback = json_decode($item->feedback_data);
            $rate = (int)($feedback->rate ?? 0);
            $reason = trim($feedback->reason ?? 'Не указано');

            if ($rate >= 4) {
                $positive++;
                $category = 'positive';
            } elseif ($rate >= 1) {
                $negative++;
                $category = 'negative';
            } else {
                continue;
            }

            if (!isset($reasons[$category][$reason])) {
                $reasons[$category][$reason] = 0;
            }

            $reasons[$category][$reason]++;
        }

        $title = 'Отчёт по отзывам за период:' . date('d.m.Y', strtotime($this->dateFrom)) . ' – ' . date('d.m.Y', strtotime($this->dateTo));
        $writer->writeSheetRow($sheet, [$title], ['font-style' => 'bold', 'font-size' => 14, 'halign' => 'center']);
        $writer->markMergedCell($sheet, 0, 0, 0, 5);
        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Всего отзывов', $total]);
        $writer->writeSheetRow($sheet, ['Оценки 4-5', $positive, round($positive / $total * 100, 2) . '%']);
        $writer->writeSheetRow($sheet, ['Оценки 1-3', $negative, round($negative / $total * 100, 2) . '%']);
        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Позитивные причины'], ['font-style' => 'bold', 'font-size' => 13]);
        $writer->writeSheetRow($sheet, []);
        $writer->writeSheetRow($sheet, ['', 'кол-во', 'от позитивных', 'от всех'], ['font-size' => 12]);

        arsort($reasons['positive']);

        foreach ($reasons['positive'] ?? [] as $reason => $count) {
            $percentFromGroup = $positive > 0 ? round($count / $positive * 100, 2) : 0;
            $percentFromTotal = $total > 0 ? round($count / $total * 100, 2) : 0;
            $writer->writeSheetRow($sheet, [$reason, $count, "{$percentFromGroup}%", "{$percentFromTotal}%"]);
        }

        $writer->writeSheetRow($sheet, []);

        $writer->writeSheetRow($sheet, ['Негативные причины'], ['font-style' => 'bold', 'font-size' => 13]);
        $writer->writeSheetRow($sheet, []);
        $writer->writeSheetRow($sheet, ['', 'кол-во', 'от негативных', 'от всех'], ['font-size' => 12]);

        arsort($reasons['negative']);

        foreach ($reasons['negative'] ?? [] as $reason => $count) {
            $percentFromGroup = $negative > 0 ? round($count / $negative * 100, 2) : 0;
            $percentFromTotal = $total > 0 ? round($count / $total * 100, 2) : 0;
            $writer->writeSheetRow($sheet, [$reason, $count, "{$percentFromGroup}%", "{$percentFromTotal}%"]);
        }


        $filename = 'user_feedback_statistics_' . date('Y-m-d') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer->writeToStdOut();
        exit;
    }
}