<?php

namespace App\Modules\TicketAssignment\Services;

use App\Modules\TicketAssignment\Repositories\ComplaintsByResponsibleRepository;
use App\Modules\TicketAssignment\Enums\TicketType;

/**
 * Сервис для статистики жалоб по ответственным лицам
 */
class ComplaintsByResponsibleService
{
    /** @var ComplaintsByResponsibleRepository */
    private $repository;

    public function __construct(\Simpla $db)
    {
        $this->repository = new ComplaintsByResponsibleRepository($db);
    }

    /**
     * Получить статистику жалоб по ответственным лицам
     *
     * @param string $dateFrom Дата начала периода (Y-m-d)
     * @param string $dateTo Дата окончания периода (Y-m-d)
     * @param string $type Тип тикета (collection | additional_services)
     * @return array Статистика жалоб
     */
    public function getComplaintsByResponsible(string $dateFrom, string $dateTo, string $type = 'collection'): array
    {
        $rawData = $this->repository->getComplaintsStats($dateFrom, $dateTo, $type);
        $responsiblePersons = $this->repository->getResponsiblePersons();
        $parentId = $type === TicketType::ADDITIONAL_SERVICES ? TicketType::ADDITIONAL_SERVICES_PARENT_ID : TicketType::COLLECTION_PARENT_ID;
        $subjects = $this->repository->getSubjectsByParent($parentId);

        return $this->formatComplaintsData($rawData, $responsiblePersons, $subjects);
    }

    /**
     * Форматирование данных жалоб для отображения
     *
     * @param array $rawData Сырые данные из БД
     * @param array $responsiblePersons Ответственные лица
     * @param array $subjects Темы
     * @return array Отформатированные данные
     */
    private function formatComplaintsData(array $rawData, array $responsiblePersons, array $subjects): array
    {
        $formattedData = [
            'responsible_persons' => [],
            'subjects' => [],
            'data' => []
        ];

        // Подготавливаем списки
        foreach ($responsiblePersons as $person) {
            $formattedData['responsible_persons'][] = $person->name;
            $formattedData['data'][$person->name] = [
                'total' => 0
            ];
        }

        foreach ($subjects as $subject) {
            $formattedData['subjects'][] = $subject->name;
        }

        // Заполняем данные
        foreach ($rawData as $row) {
            $responsibleName = $row->responsible_name;
            $subjectName = $row->subject_name;
            $count = (int)$row->complaint_count;

            if (!isset($formattedData['data'][$responsibleName])) {
                $formattedData['data'][$responsibleName] = [
                    'total' => 0
                ];
            }

            $formattedData['data'][$responsibleName][$subjectName] = $count;
            $formattedData['data'][$responsibleName]['total'] += $count;
        }

        // Добавляем нули для отсутствующих комбинаций
        foreach ($formattedData['data'] as &$personData) {
            foreach ($formattedData['subjects'] as $subject) {
                if (!isset($personData[$subject])) {
                    $personData[$subject] = 0;
                }
            }
        }

        return $formattedData;
    }
}
