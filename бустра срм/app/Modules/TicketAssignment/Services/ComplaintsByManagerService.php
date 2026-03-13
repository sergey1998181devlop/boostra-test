<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\TicketAssignment\Repositories\ComplaintsByManagerRepository;
use App\Modules\TicketAssignment\Enums\TicketType;

/**
 * Сервис для статистики жалоб по менеджерам
 */
class ComplaintsByManagerService
{
    /** @var ComplaintsByManagerRepository */
    private $repository;

    public function __construct(\Simpla $db)
    {
        $this->repository = new ComplaintsByManagerRepository($db);
    }

    /**
     * Получить статистику жалоб по менеджерам
     *
     * @param string $dateFrom Дата начала периода (Y-m-d)
     * @param string $dateTo Дата окончания периода (Y-m-d)
     * @param string $type Тип тикета (collection | additional_services)
     * @return array Статистика жалоб
     */
    public function getComplaintsByManager(string $dateFrom, string $dateTo, string $type = 'collection'): array
    {
        $rawData = $this->repository->getComplaintsStats($dateFrom, $dateTo, $type);
        $managers = $this->repository->getManagers($type);
        $parentId = $type === TicketType::ADDITIONAL_SERVICES ? TicketType::ADDITIONAL_SERVICES_PARENT_ID : TicketType::COLLECTION_PARENT_ID;
        $subjects = $this->repository->getSubjectsByParent($parentId);

        return $this->formatComplaintsData($rawData, $managers, $subjects);
    }

    /**
     * Форматирование данных жалоб для отображения
     *
     * @param array $rawData Сырые данные из БД
     * @param array $managers Менеджеры
     * @param array $subjects Темы
     * @return array Отформатированные данные
     */
    private function formatComplaintsData(array $rawData, array $managers, array $subjects): array
    {
        $formattedData = [
            'managers' => [],
            'subjects' => [],
            'data' => []
        ];

        // Подготавливаем списки
        foreach ($managers as $manager) {
            $formattedData['managers'][] = $manager->name;
            $formattedData['data'][$manager->name] = [
                'total' => 0
            ];
        }

        foreach ($subjects as $subject) {
            $formattedData['subjects'][] = $subject->name;
        }

        // Заполняем данные
        foreach ($rawData as $row) {
            $managerName = $row->manager_name;
            $subjectName = $row->subject_name;
            $count = (int)$row->complaint_count;

            if (!isset($formattedData['data'][$managerName])) {
                $formattedData['data'][$managerName] = [
                    'total' => 0
                ];
            }

            $formattedData['data'][$managerName][$subjectName] = $count;
            $formattedData['data'][$managerName]['total'] += $count;
        }

        // Добавляем нули для отсутствующих комбинаций
        foreach ($formattedData['data'] as &$managerData) {
            foreach ($formattedData['subjects'] as $subject) {
                if (!isset($managerData[$subject])) {
                    $managerData[$subject] = 0;
                }
            }
        }

        return $formattedData;
    }
}
