<?php

namespace App\Service;

use App\Repositories\AIBotCallsRepository;

class AIBotCallsReportService
{
    private AIBotCallsRepository $repository;

    public function __construct(AIBotCallsRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getReportData(array $filters, array $pagination, array $sorting, string $dateFrom, string $dateTo): array
    {
        $items = $this->repository->findByFilters($filters, $dateFrom, $dateTo, $pagination, $sorting);
        $totalItems = $this->repository->countByFilters($filters, $dateFrom, $dateTo);

        return [
            'items' => $this->prepareReportRows($items),
            'total_items' => $totalItems,
            'total_pages' => (int)ceil($totalItems / $pagination['page_capacity']),
            'tags_options' => $this->repository->getTagsFilterOptions($dateFrom, $dateTo)
        ];
    }

    public function getExportData(array $filters, array $sorting, string $dateFrom, string $dateTo): \Generator
    {
        return $this->repository->findByFiltersChunked($filters, $dateFrom, $dateTo, $sorting);
    }

    public function prepareReportRows(array $items): array
    {
        $rows = [];
        foreach ($items as $item) {
            $callData = json_decode($item->call_data, true);

            $row = [
                'date_time' => date('d.m.Y H:i:s', strtotime($item->created)),
                'phone_mobile' => $item->phone_mobile ?? $callData['msisdn'] ?? '',
                'duration' => $this->formatDuration($callData['duration'] ?? 0),
                'client_fio' => trim("$item->lastname $item->firstname $item->patronymic") ?: 'Не указано',
                'tag' => $callData['tag'] ?? 'Не указан',
                'transcript' => $this->formatTranscriptForDisplay($callData['call_transcript'] ?? ''),
                'call_record' => $callData['call_record'] ?? '',
                'methods_list' => $callData['methods_list'] ?? [],
                'assessment' => $callData['assessment'] ?? 'Не указано',
                'transferred_to_operator' => $this->isTransferredToOperator($callData['switch_to_operator'] ?? null) ? 'Да' : 'Нет',
                'full_transcript' => $this->formatTranscriptForExport($callData['call_transcript'] ?? ''),
                'client_id' => $item->user_id,
            ];
            
            $rows[] = $row;
        }
        return $rows;
    }

    public function formatTranscriptForDisplay(string $transcript): string
    {
        if (empty($transcript)) {
            return 'Не указана';
        }

        if (mb_strlen($transcript) > 100) {
            return mb_substr($transcript, 0, 100) . '...';
        }
        
        return $transcript;
    }

    public function formatTranscriptForExport(string $transcript): string
    {
        if (empty($transcript)) {
            return 'Не указана';
        }
        
        return $transcript;
    }

    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . ' сек';
        }
        
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;
        
        return $minutes . ' мин ' . $remainingSeconds . ' сек';
    }

    public function isTransferredToOperator($value): bool
    {
        if (is_bool($value)) return $value;
        if (is_string($value)) return strtolower(trim($value)) === 'true';
        return false;
    }
}
